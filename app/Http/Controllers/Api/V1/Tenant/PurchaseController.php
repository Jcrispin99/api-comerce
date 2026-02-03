<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\PurchaseRequest;
use App\Models\Purchase;
use App\Models\Tax;
use App\Services\KardexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Compras (Tenant)
 * @authenticated
 *
 * Gestión de órdenes de compra y facturas de proveedores.
 */
class PurchaseController extends ApiController
{
    protected $kardexService;

    public function __construct(KardexService $kardexService)
    {
        $this->kardexService = $kardexService;
    }

    public function index(): JsonResponse
    {
        return $this->success(
            Purchase::with(['partner', 'journal', 'warehouse'])->latest()->get()
        );
    }

    public function store(PurchaseRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            // 1. Crear Cabecera de Compra
            $purchase = Purchase::create($data);

            $totalPurchase = 0;

            // 2. Crear Ítems (Productables)
            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];
                $price = (float) $item['price'];

                $taxRate = 0;
                $isPriceInclusive = false;

                if (isset($item['tax_id'])) {
                    $tax = Tax::find($item['tax_id']);
                    if ($tax) {
                        $taxRate = (float) $tax->rate_percent;
                        $isPriceInclusive = (bool) $tax->is_price_inclusive;
                    }
                }

                if ($isPriceInclusive) {
                    // El precio ya incluye el impuesto (Total = Cantidad * Precio)
                    $totalItem = $quantity * $price;
                    $subtotal = $totalItem / (1 + ($taxRate / 100));
                    $taxAmount = $totalItem - $subtotal;
                } else {
                    // El precio es la base imponible (Total = Subtotal + Impuesto)
                    $subtotal = $quantity * $price;
                    $taxAmount = $subtotal * ($taxRate / 100);
                    $totalItem = $subtotal + $taxAmount;
                }

                $purchase->productables()->create([
                    'product_product_id' => $item['product_product_id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'tax_id' => $item['tax_id'] ?? null,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $totalItem,
                ]);

                $totalPurchase += $totalItem;
            }

            // 3. Actualizar Total en Cabecera
            $purchase->update(['total' => $totalPurchase]);

            // 4. Si el estado es 'posted', registrar en Kardex
            if ($purchase->status === 'posted') {
                $this->registerPurchaseInKardex($purchase);
            }

            return $this->created(
                $purchase->load(['productables.productProduct', 'partner', 'journal']),
                'Compra registrada exitosamente.'
            );
        });
    }

    public function show(Purchase $purchase): JsonResponse
    {
        return $this->success(
            $purchase->load(['productables.productProduct', 'partner', 'journal', 'warehouse'])
        );
    }

    public function update(PurchaseRequest $request, Purchase $purchase): JsonResponse
    {
        return DB::transaction(function () use ($request, $purchase) {
            $data = $request->validated();

            // Actualizar cabecera si se envían datos
            $purchase->update($data);

            // Si se envían nuevos ítems, reemplazamos los anteriores (lógica común en edición de documentos)
            if (isset($data['items'])) {
                $purchase->productables()->delete();

                $totalPurchase = 0;
                foreach ($data['items'] as $item) {
                    $quantity = (float) $item['quantity'];
                    $price = (float) $item['price'];

                    $taxRate = 0;
                    $isPriceInclusive = false;

                    if (isset($item['tax_id'])) {
                        $tax = Tax::find($item['tax_id']);
                        if ($tax) {
                            $taxRate = (float) $tax->rate_percent;
                            $isPriceInclusive = (bool) $tax->is_price_inclusive;
                        }
                    }

                    if ($isPriceInclusive) {
                        $totalItem = $quantity * $price;
                        $subtotal = $totalItem / (1 + ($taxRate / 100));
                        $taxAmount = $totalItem - $subtotal;
                    } else {
                        $subtotal = $quantity * $price;
                        $taxAmount = $subtotal * ($taxRate / 100);
                        $totalItem = $subtotal + $taxAmount;
                    }

                    $purchase->productables()->create([
                        'product_product_id' => $item['product_product_id'],
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'tax_id' => $item['tax_id'] ?? null,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'total' => $totalItem,
                    ]);

                    $totalPurchase += $totalItem;
                }
                $purchase->update(['total' => $totalPurchase]);
            }

            return $this->success(
                $purchase->load(['productables.productProduct', 'partner', 'journal']),
                'Compra actualizada exitosamente.'
            );
        });
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        // Al eliminar la compra, se eliminan los productables por la relación MorphMany/DeleteCascade (si aplica)
        // En este caso, el controlador lo maneja manualmente o vía DB.
        $purchase->delete();

        return $this->success(null, 'Compra eliminada exitosamente.');
    }

    /**
     * Confirma la compra y registra los movimientos en el Kardex.
     */
    public function post(Purchase $purchase): JsonResponse
    {
        if ($purchase->status !== 'draft') {
            return $this->error('Solo se pueden confirmar compras en estado borrador.', 422);
        }

        return DB::transaction(function () use ($purchase) {
            $purchase->update(['status' => 'posted']);
            $this->registerPurchaseInKardex($purchase);

            return $this->success(
                $purchase->load(['productables.productProduct', 'inventories']),
                'Compra confirmada y stock actualizado.'
            );
        });
    }

    /**
     * Lógica interna para registrar los ítems de la compra en el Kardex.
     */
    protected function registerPurchaseInKardex(Purchase $purchase): void
    {
        foreach ($purchase->productables as $item) {
            $this->kardexService->registerEntry(
                $purchase,
                [
                    'id' => $item->product_product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ],
                $purchase->warehouse_id,
                "Compra {$purchase->serie}-{$purchase->correlative}"
            );
        }
    }
}
