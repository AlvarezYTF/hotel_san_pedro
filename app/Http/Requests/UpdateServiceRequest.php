<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit_services');
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code_reference' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('services', 'code_reference')->ignore($serviceId),
            ],
            'description' => ['nullable', 'string'],
            'standard_code_id' => ['nullable', 'exists:dian_product_standards,id'],
            'unit_measure_id' => ['required', 'exists:dian_measurement_units,factus_id'],
            'tribute_id' => ['nullable', 'exists:dian_customer_tributes,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del servicio es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'code_reference.unique' => 'El código de referencia ya está en uso.',
            'code_reference.max' => 'El código de referencia no puede exceder 100 caracteres.',
            'unit_measure_id.required' => 'La unidad de medida es obligatoria.',
            'unit_measure_id.exists' => 'La unidad de medida seleccionada no es válida.',
            'standard_code_id.exists' => 'El código estándar seleccionado no es válido.',
            'tribute_id.exists' => 'El tributo seleccionado no es válido.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
            'tax_rate.numeric' => 'La tasa de impuesto debe ser un número válido.',
            'tax_rate.min' => 'La tasa de impuesto no puede ser negativa.',
            'tax_rate.max' => 'La tasa de impuesto no puede exceder 100%.',
        ];
    }
}

