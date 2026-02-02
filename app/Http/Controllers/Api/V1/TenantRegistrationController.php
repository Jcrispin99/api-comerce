<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class TenantRegistrationController extends ApiController
{
    /**
     * Registrar un nuevo tenant con su dueño (usuario central).
     *
     * Esto creará:
     * - Un usuario en la base de datos central.
     * - Un registro en la tabla 'tenants' vinculado a ese usuario.
     * - Un dominio asociado.
     * - Una base de datos para el tenant con el mismo usuario replicado.
     *
     * POST /api/v1/tenants
     * Body: { "id": "foo", "domain": "foo", "name": "Admin", "email": "admin@foo.com", "password": "password", "password_confirmation": "password" }
     */
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // 1. Crear el usuario en la base de datos central (Dueño)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        try {
            // 2. Crear el tenant vinculado al usuario
            $tenant = Tenant::create([
                'id' => $validated['id'],
                'user_id' => $user->id,
            ]);

            // 3. Asociar el dominio al tenant
            $domain = $tenant->domains()->create(['domain' => $validated['domain']]);

            // 4. Crear el mismo usuario dentro de la base de datos del tenant
            $tenant->run(function () use ($validated) {
                User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'email_verified_at' => now(),
                ]);
            });

            return $this->created([
                'tenant' => [
                    'id' => $tenant->id,
                    'domain' => $domain->domain,
                    'database' => config('tenancy.database.prefix') . $tenant->id,
                ],
                'owner' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 'Tenant y usuario administrador registrados exitosamente.');
        } catch (\Exception $e) {
            // Si algo falla después de crear el usuario central, podrías optar por eliminarlo
            // o dejarlo para que el usuario intente registrar el tenant de nuevo con el mismo email (si no falla la validación)
            // Por seguridad, si el tenant falla, eliminamos el usuario central creado para permitir reintento limpio.
            $user->delete();
            throw $e;
        }
    }
}
