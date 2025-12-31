<?php

namespace App\Services;

// TODO: Adaptar cuando se implementen las reservas
// use App\Models\Reservation;
use App\Models\Customer;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicInvoiceItem;
use App\Models\CompanyTaxSetting;
use App\Models\DianDocumentType;
use App\Models\DianOperationType;
use App\Models\DianPaymentMethod;
use App\Models\DianPaymentForm;
use App\Models\Service;
use App\Services\FactusNumberingRangeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectronicInvoiceService
{
    public function __construct(
        private FactusApiService $apiService,
        private FactusNumberingRangeService $numberingRangeService
    ) {}

    // TODO: Adaptar este método cuando se implementen las reservas
    // La facturación electrónica se generará desde las reservas, no desde ventas
    // public function createFromReservation(Reservation $reservation): ElectronicInvoice
    // {
    //     ...
    // }

    /**
     * Create electronic invoice from form data.
     *
     * @param array<string, mixed> $data
     * @return ElectronicInvoice
     * @throws \Exception
     */
    public function createFromForm(array $data): ElectronicInvoice
    {
        DB::beginTransaction();
        
        try {
            $customer = Customer::with('taxProfile')->findOrFail($data['customer_id']);
            
            if (!$customer->hasCompleteTaxProfileData()) {
                throw new \Exception('El cliente no tiene perfil fiscal completo.');
            }
            
            // Get document type code
            $documentType = DianDocumentType::findOrFail($data['document_type_id']);
            
            // Get numbering range
            $numberingRange = FactusNumberingRangeService::getValidRangeForDocument($documentType->code);
            if (!$numberingRange) {
                throw new \Exception('No hay un rango de numeración activo para el tipo de documento seleccionado.');
            }
            
            // Create invoice
            $invoice = ElectronicInvoice::create([
                'customer_id' => $customer->id,
                'factus_numbering_range_id' => $numberingRange->factus_id,
                'document_type_id' => $data['document_type_id'],
                'operation_type_id' => $data['operation_type_id'],
                'payment_method_code' => $data['payment_method_code'] ?? null,
                'payment_form_code' => $data['payment_form_code'] ?? null,
                'reference_code' => $data['reference_code'] ?? null,
                'status' => 'pending',
                'gross_value' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => 0,
            ]);
            
            // Create items and calculate totals
            $grossValue = 0;
            $taxAmount = 0;
            
            foreach ($data['items'] as $itemData) {
                $service = Service::with(['unitMeasure', 'standardCode', 'tribute'])->findOrFail($itemData['service_id']);
                
                $quantity = (float) $itemData['quantity'];
                $price = (float) $itemData['price'];
                $taxRate = (float) ($itemData['tax_rate'] ?? $service->tax_rate ?? 0);
                
                $subtotal = $quantity * $price;
                $itemTaxAmount = $subtotal * ($taxRate / 100);
                $itemTotal = $subtotal + $itemTaxAmount;
                
                $grossValue += $subtotal;
                $taxAmount += $itemTaxAmount;
                
                ElectronicInvoiceItem::create([
                    'electronic_invoice_id' => $invoice->id,
                    'tribute_id' => $service->tribute_id,
                    'standard_code_id' => $service->standard_code_id,
                    'unit_measure_id' => $service->unit_measure_id,
                    'code_reference' => $service->code_reference,
                    'name' => $service->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTaxAmount,
                    'discount_rate' => 0,
                    'is_excluded' => false,
                    'total' => $itemTotal,
                ]);
            }
            
            $total = $grossValue + $taxAmount;
            
            $invoice->update([
                'gross_value' => $grossValue,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);
            
            // Send to Factus
            $this->sendToFactus($invoice);
            
            DB::commit();
            
            return $invoice->fresh(['customer', 'items']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating electronic invoice from form', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Send invoice to Factus API.
     *
     * @param ElectronicInvoice $invoice
     * @return void
     * @throws \Exception
     */
    private function sendToFactus(ElectronicInvoice $invoice): void
    {
        $company = CompanyTaxSetting::first();
        if (!$company) {
            throw new \Exception('La configuración fiscal de la empresa no está completa.');
        }
        
        $payload = $this->buildPayload($invoice, $company);
        
        try {
            $response = $this->apiService->post('/v1/bills', $payload);
            
            $status = $this->mapStatusFromResponse($response);
            
            $updateData = [
                'status' => $status,
                'payload_sent' => $payload,
                'response_dian' => $response,
            ];
            
            if (isset($response['cufe']) && !empty($response['cufe'])) {
                $updateData['cufe'] = $response['cufe'];
            }
            
            if (isset($response['qr']) && !empty($response['qr'])) {
                $updateData['qr'] = $response['qr'];
            }
            
            if (isset($response['number']) && !empty($response['number'])) {
                $updateData['document'] = $response['number'];
            }
            
            if (isset($response['pdf_url']) && !empty($response['pdf_url'])) {
                $updateData['pdf_url'] = $response['pdf_url'];
            }
            
            if (isset($response['xml_url']) && !empty($response['xml_url'])) {
                $updateData['xml_url'] = $response['xml_url'];
            }
            
            if (isset($response['validated_at']) && !empty($response['validated_at'])) {
                $updateData['validated_at'] = $response['validated_at'];
            }
            
            $invoice->update($updateData);
        } catch (\Exception $e) {
            Log::error('Error sending invoice to Factus', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Error al enviar la factura a Factus: ' . $e->getMessage());
        }
    }

    private function buildPayload(ElectronicInvoice $invoice, CompanyTaxSetting $company): array
    {
        $customer = $invoice->customer;
        $taxProfile = $customer->taxProfile;
        $identificationDocument = $taxProfile->identificationDocument;

        // Cargar la relación de municipio si no está cargada
        if (!$company->relationLoaded('municipality')) {
            $company->load('municipality');
        }

        if (!$company->municipality) {
            throw new \Exception('El municipio de la empresa no está configurado. Por favor, configure el municipio en la configuración fiscal.');
        }

        // Construir el objeto issuer
        // Si tenemos factus_company_id, podemos usarlo, pero también debemos enviar los datos
        // porque Factus puede requerir ambos para validación
        $issuer = [
                'nit' => $company->nit,
                'dv' => $company->dv,
                'company_name' => $company->company_name,
                'email' => $company->email,
                'municipality_id' => $company->municipality->factus_id,
            'economic_activity' => $company->economic_activity ?? null,
        ];
        
        // Si tenemos factus_company_id, agregarlo también
        if ($company->factus_company_id) {
            $issuer['id'] = $company->factus_company_id;
        }

        // Determine names and company based on document type
        $isJuridicalPerson = $identificationDocument->code === 'NIT';
        $customerNames = $isJuridicalPerson 
            ? ($taxProfile->company ?? $customer->name)
            : ($taxProfile->names ?? $customer->name);
        
        $customerData = [
            'identification_document_id' => $identificationDocument->id,
                'identification' => $taxProfile->identification,
            'dv' => $taxProfile->dv ?? null,
                'municipality_id' => $taxProfile->municipality->factus_id,
        ];
        
        // Add names or company based on document type
        if ($isJuridicalPerson) {
            if (!empty($taxProfile->company)) {
                $customerData['company'] = $taxProfile->company;
            }
            if (!empty($taxProfile->trade_name)) {
                $customerData['trade_name'] = $taxProfile->trade_name;
            }
        } else {
            if (!empty($customerNames)) {
                $customerData['names'] = $customerNames;
            }
        }
        
        // Add optional contact information
        if (!empty($taxProfile->address)) {
            $customerData['address'] = $taxProfile->address;
        }
        if (!empty($taxProfile->email)) {
            $customerData['email'] = $taxProfile->email;
        }
        if (!empty($taxProfile->phone)) {
            $customerData['phone'] = $taxProfile->phone;
        }
        
        // Add legal organization and tribute if available
        if (!empty($taxProfile->legal_organization_id)) {
            $customerData['legal_organization_id'] = $taxProfile->legal_organization_id;
        }
        if (!empty($taxProfile->tribute_id)) {
            $customerData['tribute_id'] = $taxProfile->tribute_id;
        }
        
        return [
            'issuer' => $issuer,
            'customer' => $customerData,
            'document_type' => $invoice->documentType->code,
            'operation_type' => $invoice->operationType->code,
            'reference_code' => $invoice->reference_code,
            'numbering_range_id' => $invoice->numberingRange->factus_id,
            'items' => $invoice->items->map(function($item) {
                return [
                    'code_reference' => $item->code_reference,
                    'name' => $item->name,
                    'quantity' => (float) $item->quantity,
                    'price' => (float) $item->price,
                    'unit_measure_id' => $item->unitMeasure->factus_id,
                    'tax_rate' => (float) $item->tax_rate,
                    'tax_amount' => (float) $item->tax_amount,
                    'discount_rate' => (float) $item->discount_rate,
                    'is_excluded' => $item->is_excluded ? 1 : 0,
                    'standard_code_id' => $item->standard_code_id,
                    'tribute_id' => $item->tribute ? $item->tribute->code : null,
                    'total' => (float) $item->total,
                ];
            })->toArray(),
            'gross_value' => (float) $invoice->gross_value,
            'tax_amount' => (float) $invoice->tax_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'total' => (float) $invoice->total,
            'payment_method_code' => $invoice->payment_method_code,
            'payment_form_code' => $invoice->payment_form_code,
        ];
    }

    private function mapStatusFromResponse(array $response): string
    {
        if (isset($response['status'])) {
            $status = strtolower($response['status']);
            if (in_array($status, ['accepted', 'rejected', 'pending', 'error'])) {
                return $status;
            }
        }

        if (isset($response['cufe']) && !empty($response['cufe'])) {
            return 'accepted';
        }

        return 'pending';
    }
}
