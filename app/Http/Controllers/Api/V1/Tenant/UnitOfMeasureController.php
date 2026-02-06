<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UnitOfMeasureRequest;
use App\Models\UnitOfMeasure;
use Illuminate\Http\JsonResponse;

/**
 * @group Unidades de Medida (Tenant)
 * @authenticated
 *
 * Gestión de unidades de medida (Kilos, Metros, Unidades, etc.) y sus conversiones.
 */
final class UnitOfMeasureController extends ApiController
{
    /**
     * Listar unidades de medida.
     *
     * Obtiene todas las unidades de medida registradas en el tenant.
     */
    public function index(): JsonResponse
    {
        return $this->success(
            UnitOfMeasure::with('baseUnit')->get()
        );
    }

    /**
     * Crear unidad de medida.
     *
     * Registra una nueva unidad de medida.
     */
    public function store(UnitOfMeasureRequest $request): JsonResponse
    {
        $unit = UnitOfMeasure::create($request->validated());

        return $this->created($unit, 'Unidad de medida creada exitosamente.');
    }

    /**
     * Mostrar unidad de medida.
     *
     * Obtiene los detalles de una unidad específica.
     */
    public function show(UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        return $this->success(
            $unitOfMeasure->load(['baseUnit', 'subUnits'])
        );
    }

    /**
     * Actualizar unidad de medida.
     *
     * Actualiza la información de una unidad existente.
     */
    public function update(UnitOfMeasureRequest $request, UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        $unitOfMeasure->update($request->validated());

        return $this->success($unitOfMeasure, 'Unidad de medida actualizada exitosamente.');
    }

    /**
     * Eliminar unidad de medida.
     *
     * Elimina una unidad del sistema.
     */
    public function destroy(UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        // Opcional: Validar si tiene hijos antes de eliminar para evitar inconsistencias lógicas
        // aunque la FK tiene set null on delete.
        
        $unitOfMeasure->delete();

        return $this->success(message: 'Unidad de medida eliminada exitosamente.');
    }
}
