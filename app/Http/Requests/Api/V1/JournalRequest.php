<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $journalId = $this->route('journal') ? $this->route('journal')->id : null;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'code' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('journals')->ignore($journalId)
            ],
            'type' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                Rule::in(['sale', 'purchase', 'cash', 'bank', 'general'])
            ],
            'is_fiscal' => ['nullable', 'boolean'],
            'document_type_code' => ['nullable', 'string', 'max:2'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ];
    }
}
