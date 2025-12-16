<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'requires_electronic_invoice' => 'boolean',
            'identification_document_id' => 'required_if:requires_electronic_invoice,1|nullable|exists:dian_identification_documents,id',
            'identification' => [
                'required_if:requires_electronic_invoice,1',
                'nullable',
                'string',
                'max:20',
            ],
            'municipality_id' => [
                'required_if:requires_electronic_invoice,1',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\DianMunicipality::where('factus_id', $value)->exists()) {
                        $fail('El municipio seleccionado no es válido.');
                    }
                },
            ],
            'dv' => 'nullable|string|max:1',
            'legal_organization_id' => 'nullable|exists:dian_legal_organizations,id',
            'company' => 'nullable|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'names' => 'nullable|string|max:255',
            'tribute_id' => 'nullable|exists:dian_customer_tributes,id',
            'tax_address' => 'nullable|string|max:500',
            'tax_email' => 'nullable|email|max:255',
            'tax_phone' => 'nullable|string|max:20',
        ];

        // Get identification document for specific validations only if electronic invoice is required
        if ($this->boolean('requires_electronic_invoice') && $this->has('identification_document_id')) {
            $identificationDocument = \App\Models\DianIdentificationDocument::find(
                $this->input('identification_document_id')
            );

            // DV required if document requires it and electronic invoice is enabled
            if ($identificationDocument && $identificationDocument->requires_dv) {
                $rules['dv'] = 'required_if:requires_electronic_invoice,1|string|size:1';
            }

            // Company required for juridical persons (NIT) when electronic invoice is enabled
            if ($identificationDocument && $identificationDocument->code === 'NIT') {
                $rules['company'] = 'required_if:requires_electronic_invoice,1|string|max:255';
            }

            // Unique identification validation: combination of identification + identification_document_id must be unique
            if ($this->has('identification') && $this->filled('identification')) {
                $rules['identification'][] = function ($_attribute, $value, $fail) {
                    $exists = \App\Models\CustomerTaxProfile::where('identification', $value)
                        ->where('identification_document_id', $this->input('identification_document_id'))
                        ->exists();

                    if ($exists) {
                        $fail('Ya existe un cliente con este número de identificación y tipo de documento.');
                    }
                };
            }
        }

        return $rules;
    }
}
