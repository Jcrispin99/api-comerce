<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $saleId = $this->route('sale')?->id ?? $this->route('sale');

        return [
            'serie' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'correlative' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'journal_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:journals,id'],
            'date' => ['nullable', 'date'],

            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'warehouse_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:warehouses,id'],
            'company_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:companies,id'],

            'original_sale_id' => ['nullable', 'integer', Rule::exists('sales', 'id')->whereNot('id', $saleId)],
            'pos_session_id' => ['nullable', 'integer'],

            'user_id' => ['nullable', 'integer', 'exists:users,id'],

            'status' => ['nullable', Rule::in(['draft', 'posted', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'partial', 'paid'])],

            'sunat_status' => ['nullable', 'string', 'max:50'],
            'sunat_response' => ['nullable', 'array'],
            'sunat_sent_at' => ['nullable', 'date'],

            'notes' => ['nullable', 'string'],

            'items' => [$isUpdate ? 'nullable' : 'required', 'array', 'min:1'],
            'items.*.product_product_id' => ['required', 'integer', 'exists:product_products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.unit_of_measure_id' => ['nullable', 'integer', 'exists:unit_of_measures,id'],
            'items.*.tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'serie' => 'serie',
            'correlative' => 'correlativo',
            'journal_id' => 'diario',
            'partner_id' => 'cliente',
            'warehouse_id' => 'almacén',
            'company_id' => 'compañía',
            'original_sale_id' => 'venta original',
            'pos_session_id' => 'sesión POS',
            'user_id' => 'vendedor',
            'items' => 'productos',
            'items.*.product_product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'items.*.price' => 'precio',
            'items.*.unit_of_measure_id' => 'unidad de medida',
        ];
    }
}
