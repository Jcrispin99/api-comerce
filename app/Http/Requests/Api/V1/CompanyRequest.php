<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
        $companyId = $this->route('company')?->id ?? $this->route('company');

        return [
            'business_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'ruc' => [$isUpdate ? 'sometimes' : 'required', 'string', 'size:11'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:100'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'ubigeo' => ['nullable', 'string', 'size:6'],
            'urbanization' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:50'],
            'district' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:companies,id'],
            'branch_code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('companies', 'branch_code')->ignore($companyId)
            ],
            'is_main_office' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Obtener los nombres de los atributos para los errores de validación.
     */
    public function attributes(): array
    {
        return [
            'business_name' => 'razón social',
            'trade_name' => 'nombre comercial',
            'ruc' => 'RUC',
            'address' => 'dirección',
            'phone' => 'teléfono',
            'email' => 'correo electrónico',
            'logo_url' => 'URL del logo',
            'ubigeo' => 'ubigeo',
            'urbanization' => 'urbanización',
            'department' => 'departamento',
            'province' => 'provincia',
            'district' => 'distrito',
            'active' => 'estado activo',
            'parent_id' => 'compañía padre',
            'branch_code' => 'código de sucursal',
            'is_main_office' => 'es oficina principal',
        ];
    }
}
