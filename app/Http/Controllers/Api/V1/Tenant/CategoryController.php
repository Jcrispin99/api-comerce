<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

/**
 * @group Categorías (Tenant)
 * @authenticated
 *
 * Gestión de categorías de productos dentro del tenant.
 */
final class CategoryController extends ApiController
{
    /**
     * Listar categorías.
     *
     * Obtiene todas las categorías registradas en el tenant actual.
     */
    public function index(): JsonResponse
    {
        return $this->success(Category::all());
    }

    /**
     * Crear categoría.
     *
     * Registra una nueva categoría en el tenant.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $this->created($category, 'Categoría creada exitosamente.');
    }

    /**
     * Mostrar categoría.
     */
    public function show(Category $category): JsonResponse
    {
        return $this->success($category);
    }

    /**
     * Actualizar categoría.
     */
    public function update(StoreCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return $this->success($category, 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar categoría.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->success(message: 'Categoría eliminada exitosamente.');
    }
}
