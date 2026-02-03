<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AttributeRequest extends FormRequest
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
            'is_active' => ['nullable', 'boolean'],
            'values' => [$isUpdate ? 'nullable' : 'required', 'array', 'min:1'],
            'values.*.id' => ['nullable', 'integer', 'exists:attribute_values,id'],
            'values.*.value' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Obtener los nombres de los atributos para los errores de validación.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del atributo',
            'is_active' => 'estado activo',
            'values' => 'valores',
            'values.*.value' => 'valor del atributo',
        ];
    }
}
