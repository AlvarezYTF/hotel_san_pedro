<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyTaxSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'nit' => 'required|string|max:20',
            'dv' => 'required|string|size:1',
            'email' => 'required|email|max:255',
            'municipality_id' => [
                'required',
                function ($_attribute, $value, $fail) {
                    if (!\App\Models\DianMunicipality::where('factus_id', $value)->exists()) {
                        $fail('El municipio seleccionado no es válido.');
                    }
                },
            ],
            'economic_activity' => 'nullable|string|max:10',
            'logo_url' => 'nullable|url|max:255',
        ];
    }
}
