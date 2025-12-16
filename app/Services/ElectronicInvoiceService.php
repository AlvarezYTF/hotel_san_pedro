<?php

namespace App\Services;

// TODO: Adaptar cuando se implementen las reservas
// use App\Models\Reservation;
use App\Models\ElectronicInvoice;
use App\Models\ElectronicInvoiceItem;
use App\Models\CompanyTaxSetting;
use App\Models\DianDocumentType;
use App\Models\DianOperationType;
use App\Models\DianPaymentMethod;
use App\Models\DianPaymentForm;
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
     * @deprecated Este método será reemplazado por createFromReservation cuando se implementen las reservas
     */
    public function createFromSale($sale): ElectronicInvoice
    {
        throw new \Exception('Este método está deprecado. Se adaptará cuando se implementen las reservas.');
        
        // Código comentado - se adaptará para reservas
        /*
        $sale->load(['customer.taxProfile.identificationDocument', 'saleItems.product']);

        if (!$sale->requiresElectronicInvoice()) {
            throw new \Exception('El cliente no requiere facturación electrónica.');
        }

        if (!$sale->customer->hasCompleteTaxProfileData()) {
            $missingFields = $sale->customer->getMissingTaxProfileFields();
            $message = 'El cliente no tiene datos fiscales completos. ';
            if (!empty($missingFields)) {
                $message .= 'Campos faltantes: ' . implode(', ', $missingFields);
            }
            throw new \Exception($message);
        }
        */

        $company = CompanyTaxSetting::with('municipality')->first();
        if (!$company) {
            throw new \Exception('La configuración fiscal de la empresa no existe. Por favor, configure los datos fiscales de la empresa primero.');
        }

        if (!$company->isConfigured()) {
            $missingFields = $company->getMissingFields();
            $message = 'La configuración fiscal de la empresa no está completa. ';
            if (!empty($missingFields)) {
                $message .= 'Campos faltantes: ' . implode(', ', $missingFields);
            }
            throw new \Exception($message);
        }

        // Validar que el municipio tenga factus_id
        if (!$company->municipality || !$company->municipality->factus_id) {
            throw new \Exception('El municipio configurado no tiene un factus_id válido. Por favor, sincroniza los municipios desde Factus.');
        }

        if ($sale->hasElectronicInvoice()) {
            throw new \Exception('La venta ya tiene una factura electrónica asociada.');
        }

        return DB::transaction(function () use ($sale, $company) {
            $documentType = DianDocumentType::where('code', '01')->firstOrFail();
            $operationType = DianOperationType::where('code', '10')->firstOrFail();
            
            // Usar el método de pago de la venta, o usar valores por defecto si no está configurado
            $paymentMethodCode = $sale->payment_method_code ?? '10'; // Por defecto: Efectivo
            $paymentFormCode = $sale->payment_form_code ?? '1'; // Por defecto: Contado
            
            $paymentMethod = DianPaymentMethod::where('code', $paymentMethodCode)->firstOrFail();
            $paymentForm = DianPaymentForm::where('code', $paymentFormCode)->firstOrFail();

            $range = $this->numberingRangeService->getValidRangeForDocument('Factura de Venta');
            if (!$range) {
                throw new \Exception('No hay un rango de numeración válido disponible.');
            }

            $invoice = ElectronicInvoice::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'factus_numbering_range_id' => $range->factus_id,
                'document_type_id' => $documentType->id,
                'operation_type_id' => $operationType->id,
                'payment_method_code' => $paymentMethod->code,
                'payment_form_code' => $paymentForm->code,
                'reference_code' => 'FV-' . str_pad($sale->id, 8, '0', STR_PAD_LEFT),
                'document' => $range->prefix ? ($range->prefix . str_pad($range->current, 8, '0', STR_PAD_LEFT)) : str_pad($range->current, 8, '0', STR_PAD_LEFT),
                'status' => 'pending',
                'total' => $sale->total,
                'tax_amount' => $sale->tax_amount ?? 0,
                'gross_value' => $sale->subtotal ?? $sale->total,
                'discount_amount' => $sale->discount_amount ?? 0,
                'surcharge_amount' => 0,
            ]);

            $defaultTribute = \App\Models\DianCustomerTribute::first();
            $defaultStandard = \App\Models\DianProductStandard::first();
            
            if (!$defaultTribute) {
                throw new \Exception("No se encontró un régimen tributario por defecto. Por favor, ejecuta los seeders de catálogos DIAN.");
            }
            
            if (!$defaultStandard) {
                throw new \Exception("No se encontró un estándar de producto por defecto. Por favor, ejecuta los seeders de catálogos DIAN.");
            }

            foreach ($sale->saleItems as $saleItem) {
                $product = $saleItem->product;
                $unitMeasure = \App\Models\DianMeasurementUnit::where('code', '94')->first();
                
                if (!$unitMeasure) {
                    throw new \Exception("No se encontró unidad de medida por defecto (código 94).");
                }

                // Verificar si el producto está excluido de IVA
                // Si la venta tiene tax_amount = 0, todos los items están excluidos de IVA
                $isExcluded = ($sale->tax_amount == 0);
                
                $taxRate = $isExcluded ? 0.0 : 19.0;
                $subtotal = $saleItem->quantity * $saleItem->unit_price;
                $taxAmount = $isExcluded ? 0.0 : ($subtotal * ($taxRate / 100));
                $total = $subtotal + $taxAmount;

                ElectronicInvoiceItem::create([
                    'electronic_invoice_id' => $invoice->id,
                    'tribute_id' => $defaultTribute->id,
                    'standard_code_id' => $defaultStandard->id,
                    'unit_measure_id' => $unitMeasure->factus_id,
                    'code_reference' => $product->sku ?? 'PROD-' . $product->id,
                    'name' => $product->name,
                    'quantity' => $saleItem->quantity,
                    'price' => $saleItem->unit_price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => 0,
                    'is_excluded' => $isExcluded,
                    'total' => $total,
                ]);
            }

            $invoice->load([
                'items.unitMeasure',
                'items.tribute',
                'items.productStandard',
                'customer.taxProfile.identificationDocument',
                'numberingRange'
            ]);

            $payload = $this->buildPayload($invoice, $company);

            try {
                $response = $this->apiService->post('/v1/bills/validate', $payload);

                // Extraer el número de documento de la respuesta si está disponible
                $documentNumber = $response['number'] ?? $response['document'] ?? null;

                $invoice->update([
                    'status' => $this->mapStatusFromResponse($response),
                    'document' => $documentNumber ?? $invoice->document, // Actualizar con el número real de Factus si está disponible
                    'cufe' => $response['cufe'] ?? null,
                    'qr' => $response['qr'] ?? null,
                    'payload_sent' => $payload,
                    'response_dian' => $response,
                    'validated_at' => now(),
                    'pdf_url' => $response['pdf_url'] ?? null,
                    'xml_url' => $response['xml_url'] ?? null,
                ]);

                Log::info('Factura electrónica creada y validada exitosamente', [
                    'invoice_id' => $invoice->id,
                    'sale_id' => $sale->id,
                    'cufe' => $invoice->cufe,
                ]);

            } catch (\Exception $e) {
                Log::error('Error al enviar factura a Factus', [
                    'invoice_id' => $invoice->id,
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);

                $invoice->update([
                    'status' => 'rejected',
                    'response_dian' => ['error' => $e->getMessage()],
                ]);

                throw $e;
            }

            return $invoice;
        });
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
