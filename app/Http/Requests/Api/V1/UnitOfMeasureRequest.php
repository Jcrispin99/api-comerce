<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitOfMeasureRequest extends FormRequest
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
        // Cuando es update, el parámetro de ruta suele ser 'unit_of_measure' (por convención de apiResource)
        // pero verificamos si llega como objeto o ID.
        $unitId = $this->route('unit_of_measure')?->id ?? $this->route('unit_of_measure');

        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                // Validación de unicidad compuesta: family + name debe ser único
                Rule::unique('unit_of_measures', 'name')
                    ->where(function ($query) {
                        return $query->where('family', $this->input('family'));
                    })
                    ->ignore($unitId),
            ],
            'symbol' => ['nullable', 'string', 'max:50'],
            'family' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255'
            ],
            'base_unit_id' => [
                'nullable',
                'exists:unit_of_measures,id'
            ],
            'factor' => [
                $isUpdate ? 'sometimes' : 'required',
                'numeric',
                'min:0'
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Obtener los nombres de los atributos para los errores de validación.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre de la unidad',
            'symbol' => 'símbolo',
            'family' => 'familia',
            'base_unit_id' => 'unidad base',
            'factor' => 'factor de conversión',
            'is_active' => 'estado activo',
        ];
    }
}
