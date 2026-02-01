<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class TenantRegistrationController extends ApiController
{
    /**
     * Registrar un nuevo tenant con su dominio.
     *
     * Esto creará:
     * - Un registro en la tabla 'tenants' (BD central)
     * - Un dominio asociado en la tabla 'domains' (BD central)
     * - Una nueva base de datos para el tenant (ej: tenant_foo)
     *
     * POST /api/v1/tenants
     * Body: { "id": "foo", "domain": "foo.localhost" }
     */
    public function store(Request $request): JsonResponse
    {
        // Si el dominio no contiene un punto, asumimos que es un subdominio y le agregamos el dominio central
        $dat = $request->all();
        if (! str_contains($dat['domain'], '.')) {
            $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
            $dat['domain'] = $dat['domain'] . '.' . $centralDomain;
        }

        $validator = Validator::make($dat, [
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
        ], [
            'id.required' => 'El ID del tenant es requerido.',
            'id.unique' => 'Este tenant ID ya existe.',
            'id.regex' => 'El tenant ID solo puede contener letras minúsculas, números y guiones.',
            'domain.required' => 'El dominio es requerido.',
            'domain.unique' => 'Este dominio ya está registrado.',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        // Crear el tenant (esto automáticamente crea la base de datos del tenant)
        $tenant = Tenant::create(['id' => $dat['id']]);

        // Asociar el dominio al tenant
        $domain = $tenant->domains()->create(['domain' => $dat['domain']]);

        return $this->created([
            'tenant' => [
                'id' => $tenant->id,
                'domain' => $domain->domain,
                'database' => "tenant{$tenant->id}",
                'created_at' => $tenant->created_at,
            ],
        ], 'Tenant registrado exitosamente.');
    }
}
