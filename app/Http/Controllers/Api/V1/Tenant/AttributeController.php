<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\AttributeRequest;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Atributos (Tenant)
 * @authenticated
 *
 * Gestión de atributos de productos (ej: Talla, Color) y sus valores dentro del tenant.
 */
final class AttributeController extends ApiController
{
    /**
     * Listar atributos.
     *
     * Obtiene todos los atributos con sus respectivos valores.
     */
    public function index(): JsonResponse
    {
        return $this->success(Attribute::with('values')->get());
    }

    /**
     * Crear atributo.
     *
     * Registra un nuevo atributo y sus valores iniciales.
     */
    public function store(AttributeRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $attribute = Attribute::create($request->only(['name', 'is_active']));
            
            foreach ($request->input('values') as $valueData) {
                $attribute->values()->create($valueData);
            }

            return $this->created($attribute->load('values'), 'Atributo y valores creados exitosamente.');
        });
    }

    /**
     * Mostrar atributo.
     *
     * Obtiene los detalles de un atributo específico y sus valores.
     */
    public function show(Attribute $attribute): JsonResponse
    {
        return $this->success($attribute->load('values'));
    }

    /**
     * Actualizar atributo.
     *
     * Actualiza el nombre del atributo y gestiona sus valores (crear, actualizar o eliminar).
     */
    public function update(AttributeRequest $request, Attribute $attribute): JsonResponse
    {
        return DB::transaction(function () use ($request, $attribute) {
            $attribute->update($request->only(['name', 'is_active']));

            if ($request->has('values')) {
                $incomingIds = collect($request->input('values'))->pluck('id')->filter()->toArray();
                
                // Eliminar valores que no vienen en la petición
                $attribute->values()->whereNotIn('id', $incomingIds)->delete();

                // Actualizar o crear valores
                foreach ($request->input('values') as $valueData) {
                    if (isset($valueData['id'])) {
                        $attribute->values()->where('id', $valueData['id'])->update(['value' => $valueData['value']]);
                    } else {
                        $attribute->values()->create($valueData);
                    }
                }
            }

            return $this->success($attribute->load('values'), 'Atributo actualizado exitosamente.');
        });
    }

    /**
     * Eliminar atributo.
     *
     * Elimina un atributo y todos sus valores asociados.
     */
    public function destroy(Attribute $attribute): JsonResponse
    {
        $attribute->delete();

        return $this->success(message: 'Atributo eliminado exitosamente.');
    }
}
