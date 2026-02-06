<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PosConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'company_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:companies,id'],
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'warehouse_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:warehouses,id'],
            'default_customer_id' => ['nullable', 'integer', 'exists:partners,id'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'apply_tax' => ['nullable', 'boolean'],
            'prices_include_tax' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],

            'journals' => ['nullable', 'array'],
            'journals.*.journal_id' => ['required', 'integer', 'exists:journals,id'],
            'journals.*.document_type' => ['required', 'string', 'max:50'],
            'journals.*.is_default' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_id' => 'compañía',
            'name' => 'nombre',
            'warehouse_id' => 'almacén',
            'default_customer_id' => 'cliente por defecto',
            'tax_id' => 'impuesto',
            'apply_tax' => 'aplica impuesto',
            'prices_include_tax' => 'precios incluyen impuesto',
            'is_active' => 'activo',
            'journals' => 'diarios',
            'journals.*.journal_id' => 'diario',
            'journals.*.document_type' => 'tipo de documento',
            'journals.*.is_default' => 'por defecto',
        ];
    }
}

