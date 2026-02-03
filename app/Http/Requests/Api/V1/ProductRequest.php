<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'category_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
            
            // Variants logic
            'sku' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            
            'attributeLines' => ['nullable', 'array'],
            'attributeLines.*.attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'attributeLines.*.values' => ['required', 'array', 'min:1'],
            
            'generatedVariants' => ['nullable', 'array'],
            'generatedVariants.*.sku' => ['nullable', 'string', 'max:255'],
            'generatedVariants.*.barcode' => ['nullable', 'string', 'max:255'],
            'generatedVariants.*.price' => ['nullable', 'numeric', 'min:0'],
            'generatedVariants.*.attributes' => ['required_with:generatedVariants', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre del producto',
            'price' => 'precio',
            'category_id' => 'categoría',
            'attributeLines' => 'líneas de atributos',
            'generatedVariants' => 'variantes generadas',
        ];
    }
}
