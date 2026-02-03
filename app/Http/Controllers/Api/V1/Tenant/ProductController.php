<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\ProductRequest;
use App\Models\AttributeValue;
use App\Models\ProductTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Productos (Tenant)
 * @authenticated
 *
 * Gestión de productos maestros (Templates) y sus variantes específicas dentro del tenant.
 */
final class ProductController extends ApiController
{
    /**
     * Listar productos.
     */
    public function index(): JsonResponse
    {
        return $this->success(ProductTemplate::with(['category', 'variants.attributeValues'])->get());
    }

    /**
     * Crear producto.
     * 
     * Crea un producto maestro y sus variantes (con o sin atributos).
     */
    public function store(ProductRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            
            // 1. Crear el Producto Maestro (Template)
            $productTemplate = ProductTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'category_id' => $data['category_id'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2. Manejar Variantes
            if (empty($data['generatedVariants'])) {
                // Caso 1: Producto SIN variantes (Crea una sola variante principal)
                $productTemplate->variants()->create([
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'price' => $data['price'],
                    'is_principal' => true,
                ]);
            } else {
                // Caso 2: Producto CON variantes
                foreach ($data['generatedVariants'] as $index => $variantData) {
                    $productProduct = $productTemplate->variants()->create([
                        'sku' => $variantData['sku'] ?? null,
                        'barcode' => $variantData['barcode'] ?? null,
                        'price' => $variantData['price'] ?? $productTemplate->price,
                        'is_principal' => $index === 0,
                    ]);

                    // Asociar valores de atributos
                    foreach ($variantData['attributes'] as $attributeId => $valueName) {
                        $attributeValue = AttributeValue::where('attribute_id', $attributeId)
                            ->where('value', $valueName)
                            ->first();

                        if ($attributeValue) {
                            $productProduct->attributeValues()->syncWithoutDetaching([$attributeValue->id]);
                        }
                    }
                }
            }

            return $this->created(
                $productTemplate->load(['variants.attributeValues', 'category']),
                'Producto creado exitosamente.'
            );
        });
    }

    /**
     * Mostrar producto.
     */
    public function show(ProductTemplate $product): JsonResponse
    {
        return $this->success($product->load(['variants.attributeValues', 'category']));
    }

    /**
     * Actualizar producto.
     * 
     * Actualiza el producto maestro y sus variantes.
     */
    public function update(ProductRequest $request, ProductTemplate $product): JsonResponse
    {
        return DB::transaction(function () use ($request, $product) {
            $data = $request->validated();

            // 1. Actualizar Producto Maestro
            $product->update([
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'category_id' => $data['category_id'] ?? $product->category_id,
                'is_active' => $data['is_active'] ?? $product->is_active,
            ]);

            // 2. Manejar Variantes (Lógica de actualización)
            if (isset($data['generatedVariants'])) {
                // Si se envían variantes, sincronizamos (esto es un flujo de "reemplazo" común en plantillas)
                // En una implementación más avanzada podrías querer comparar IDs, pero aquí
                // si el usuario regenera variantes, solemos recrearlas.
                
                // Por simplicidad en esta etapa de desarrollo:
                $product->variants()->delete();

                foreach ($data['generatedVariants'] as $index => $variantData) {
                    $productProduct = $product->variants()->create([
                        'sku' => $variantData['sku'] ?? null,
                        'barcode' => $variantData['barcode'] ?? null,
                        'price' => $variantData['price'] ?? $product->price,
                        'is_principal' => $index === 0,
                    ]);

                    if (isset($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attributeId => $valueName) {
                            $attributeValue = AttributeValue::where('attribute_id', $attributeId)
                                ->where('value', $valueName)
                                ->first();

                            if ($attributeValue) {
                                $productProduct->attributeValues()->syncWithoutDetaching([$attributeValue->id]);
                            }
                        }
                    }
                }
            } elseif (isset($data['sku']) || isset($data['barcode'])) {
                // Si es un producto simple y se actualiza SKU/Barcode, actualizamos la variante principal
                $principal = $product->variants()->where('is_principal', true)->first();
                if ($principal) {
                    $principal->update([
                        'sku' => $data['sku'] ?? $principal->sku,
                        'barcode' => $data['barcode'] ?? $principal->barcode,
                        'price' => $data['price'] ?? $principal->price,
                    ]);
                }
            }

            return $this->success(
                $product->load(['variants.attributeValues', 'category']),
                'Producto actualizado exitosamente.'
            );
        });
    }

    /**
     * Eliminar producto.
     */
    public function destroy(ProductTemplate $product): JsonResponse
    {
        $product->delete();
        return $this->success(message: 'Producto eliminado exitosamente.');
    }
}
