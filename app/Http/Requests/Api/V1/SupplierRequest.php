<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $partnerId = $this->route('supplier') ? $this->route('supplier')->id : null;

        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'document_type' => [$isUpdate ? 'sometimes' : 'required', Rule::in(['DNI', 'RUC', 'CE', 'Passport'])],
            'document_number' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:20',
                Rule::unique('partners')->where(function ($query) {
                    return $query->where('document_type', $this->document_type);
                })->ignore($partnerId)
            ],
            'business_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:200'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'district' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'integer', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'business_license' => ['nullable', 'string', 'max:100'],
            'provider_category' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended', 'blacklisted'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
