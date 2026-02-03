<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\TaxRequest;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;

/**
 * @group Impuestos (Tenant)
 * @authenticated
 *
 * Gestión de impuestos (IGV, ISC, etc.) aplicables a los productos del tenant.
 */
final class TaxController extends ApiController
{
    /**
     * Listar impuestos.
     *
     * Obtiene todos los impuestos registrados en el tenant.
     */
    public function index(): JsonResponse
    {
        return $this->success(Tax::all());
    }

    /**
     * Crear impuesto.
     *
     * Registra un nuevo tipo de impuesto.
     */
    public function store(TaxRequest $request): JsonResponse
    {
        $tax = Tax::create($request->validated());

        return $this->created($tax, 'Impuesto creado exitosamente.');
    }

    /**
     * Mostrar impuesto.
     *
     * Obtiene los detalles de un impuesto específico.
     */
    public function show(Tax $tax): JsonResponse
    {
        return $this->success($tax);
    }

    /**
     * Actualizar impuesto.
     *
     * Actualiza la información de un impuesto existente.
     */
    public function update(TaxRequest $request, Tax $tax): JsonResponse
    {
        $tax->update($request->validated());

        return $this->success($tax, 'Impuesto actualizado exitosamente.');
    }

    /**
     * Eliminar impuesto.
     *
     * Elimina un impuesto del sistema.
     */
    public function destroy(Tax $tax): JsonResponse
    {
        $tax->delete();

        return $this->success(message: 'Impuesto eliminado exitosamente.');
    }
}
