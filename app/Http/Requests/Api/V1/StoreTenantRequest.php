<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTenantRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'string',
                'max:255',
                'unique:tenants,id',
                'regex:/^[a-z0-9-]+$/',
            ],
            'domain' => [
                'required',
                'string',
                'max:255',
                'unique:domains,domain',
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Obtener los mensajes de error personalizados para las reglas definidas.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'El ID del tenant es requerido.',
            'id.unique' => 'Este tenant ID ya existe.',
            'id.regex' => 'El tenant ID solo puede contener letras minúsculas, números y guiones.',
            'domain.required' => 'El dominio es requerido.',
            'domain.unique' => 'Este dominio ya está registrado.',
        ];
    }

    /**
     * Preparar los datos para la validación.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('domain') && ! str_contains($this->domain, '.')) {
            $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
            $this->merge([
                'domain' => $this->domain . '.' . $centralDomain,
            ]);
        }
    }
}
