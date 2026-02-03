<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'company_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:companies,id'],
        ];
    }

    /**
     * Obtener los nombres de los atributos para los errores de validación.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del almacén',
            'location' => 'ubicación',
            'company_id' => 'compañía',
        ];
    }
}
