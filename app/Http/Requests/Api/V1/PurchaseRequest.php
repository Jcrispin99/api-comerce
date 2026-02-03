<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $purchaseId = $this->route('purchase') ? $this->route('purchase')->id : null;

        return [
            // Cabecera
            'serie' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'correlative' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'journal_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:journals,id'],
            'partner_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:partners,id'],
            'warehouse_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:warehouses,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'date' => ['nullable', 'date'],
            'observation' => ['nullable', 'string'],
            'vendor_bill_number' => ['nullable', 'string', 'max:255'],
            'vendor_bill_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['draft', 'posted', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'partial', 'paid'])],

            // Ítems (Productables)
            'items' => [$isUpdate ? 'nullable' : 'required', 'array', 'min:1'],
            'items.*.product_product_id' => ['required', 'integer', 'exists:product_products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'serie' => 'serie',
            'correlative' => 'correlativo',
            'journal_id' => 'diario',
            'partner_id' => 'proveedor',
            'warehouse_id' => 'almacén',
            'items' => 'productos',
            'items.*.product_product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'items.*.price' => 'precio',
        ];
    }
}
