<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;

/**
 * @group Almacenes (Tenant)
 * @authenticated
 *
 * Gestión de almacenes físicos vinculados a las empresas del tenant.
 */
final class WarehouseController extends ApiController
{
    /**
     * Listar almacenes.
     *
     * Obtiene todos los almacenes registrados con su empresa vinculada.
     */
    public function index(): JsonResponse
    {
        return $this->success(Warehouse::with('company')->get());
    }

    /**
     * Crear almacén.
     *
     * Registra un nuevo almacén físico.
     */
    public function store(WarehouseRequest $request): JsonResponse
    {
        $warehouse = Warehouse::create($request->validated());

        return $this->created($warehouse->load('company'), 'Almacén creado exitosamente.');
    }

    /**
     * Mostrar almacén.
     *
     * Obtiene los detalles de un almacén específico.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        return $this->success($warehouse->load('company'));
    }

    /**
     * Actualizar almacén.
     *
     * Actualiza la información de un almacén existente.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $warehouse->update($request->validated());

        return $this->success($warehouse->load('company'), 'Almacén actualizado exitosamente.');
    }

    /**
     * Eliminar almacén.
     *
     * Elimina un almacén físico.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();

        return $this->success(message: 'Almacén eliminado exitosamente.');
    }
}
