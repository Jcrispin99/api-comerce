<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $taxId = $this->route('tax')?->id ?? $this->route('tax');

        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')->ignore($taxId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'invoice_label' => ['nullable', 'string', 'max:255'],
            'tax_type' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:50'],
            'affectation_type_code' => ['nullable', 'string', 'max:10'],
            'rate_percent' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:100'],
            'is_price_inclusive' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Obtener los nombres de los atributos para los errores de validación.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del impuesto',
            'tax_type' => 'tipo de impuesto',
            'rate_percent' => 'porcentaje de tasa',
            'is_price_inclusive' => 'precio incluye impuesto',
            'is_active' => 'estado activo',
            'is_default' => 'impuesto por defecto',
        ];
    }
}
