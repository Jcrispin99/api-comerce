<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

/**
 * @group Compañías (Tenant)
 * @authenticated
 *
 * Gestión de la información de la empresa y sus sucursales dentro del tenant.
 */
final class CompanyController extends ApiController
{
    /**
     * Listar compañías.
     *
     * Obtiene todas las compañías y sucursales registradas en el tenant actual.
     */
    public function index(): JsonResponse
    {
        return $this->success(Company::all());
    }

    /**
     * Crear compañía.
     *
     * Registra una nueva compañía o sucursal en el tenant.
     */
    public function store(CompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());

        return $this->created($company, 'Compañía creada exitosamente.');
    }

    /**
     * Mostrar compañía.
     *
     * Obtiene los detalles de una compañía específica.
     */
    public function show(Company $company): JsonResponse
    {
        return $this->success($company);
    }

    /**
     * Actualizar compañía.
     *
     * Actualiza la información de una compañía existente.
     */
    public function update(CompanyRequest $request, Company $company): JsonResponse
    {
        $company->update($request->validated());

        return $this->success($company, 'Compañía actualizada exitosamente.');
    }

    /**
     * Eliminar compañía.
     *
     * Elimina una compañía del sistema.
     */
    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return $this->success(message: 'Compañía eliminada exitosamente.');
    }
}
