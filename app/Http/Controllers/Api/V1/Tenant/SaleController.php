<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\SaleRequest;
use App\Models\Sale;
use App\Models\Tax;
use App\Models\UnitOfMeasure;
use App\Services\KardexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Ventas (Tenant)
 * @authenticated
 *
 * Gestión de ventas, notas de crédito y movimientos de salida/retorno de stock.
 */
final class SaleController extends ApiController
{
    public function __construct(private readonly KardexService $kardexService) {}

    public function index(): JsonResponse
    {
        return $this->success(
            Sale::with(['partner', 'journal', 'warehouse', 'user'])->latest()->get()
        );
    }

    public function store(SaleRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $data['user_id'] = $request->user()?->id ?? ($data['user_id'] ?? null);

            $sale = Sale::create($data);

            $subtotalSale = 0;
            $taxAmountSale = 0;
            $totalSale = 0;

            foreach ($items as $item) {
                $quantityUom = (float) $item['quantity'];
                $uomFactor = 1;
                $unitOfMeasureId = $item['unit_of_measure_id'] ?? null;

                if ($unitOfMeasureId) {
                    $uom = UnitOfMeasure::find($unitOfMeasureId);
                    if ($uom) {
                        $uomFactor = (float) $uom->factor;
                    }
                }

                $quantityReal = $quantityUom * $uomFactor;

                $priceUom = (float) $item['price'];
                $priceReal = $uomFactor > 0 ? ($priceUom / $uomFactor) : $priceUom;

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
                    $totalItem = $quantityReal * $priceReal;
                    $subtotal = $totalItem / (1 + ($taxRate / 100));
                    $taxAmount = $totalItem - $subtotal;
                } else {
                    $subtotal = $quantityReal * $priceReal;
                    $taxAmount = $subtotal * ($taxRate / 100);
                    $totalItem = $subtotal + $taxAmount;
                }

                $sale->productables()->create([
                    'product_product_id' => $item['product_product_id'],
                    'quantity' => $quantityReal,
                    'price' => $priceReal,
                    'unit_of_measure_id' => $unitOfMeasureId,
                    'quantity_uom' => $quantityUom,
                    'uom_factor' => $uomFactor,
                    'subtotal' => $subtotal,
                    'tax_id' => $item['tax_id'] ?? null,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $totalItem,
                ]);

                $subtotalSale += $subtotal;
                $taxAmountSale += $taxAmount;
                $totalSale += $totalItem;
            }

            $sale->update([
                'subtotal' => $subtotalSale,
                'tax_amount' => $taxAmountSale,
                'total' => $totalSale,
            ]);

            if ($sale->status === 'posted') {
                $this->registerSaleInKardex($sale);
            }

            return $this->created(
                $sale->load(['productables.productProduct', 'productables.unitOfMeasure', 'partner', 'journal', 'warehouse', 'user']),
                'Venta registrada exitosamente.'
            );
        });
    }

    public function show(Sale $sale): JsonResponse
    {
        return $this->success(
            $sale->load(['productables.productProduct', 'productables.unitOfMeasure', 'partner', 'journal', 'warehouse', 'user', 'inventories'])
        );
    }

    public function update(SaleRequest $request, Sale $sale): JsonResponse
    {
        if ($sale->status !== 'draft') {
            return $this->error('Solo se pueden editar ventas en estado borrador.', 422);
        }

        return DB::transaction(function () use ($request, $sale) {
            $data = $request->validated();

            unset($data['user_id']);
            $sale->update($data);

            if (isset($data['items'])) {
                $sale->productables()->delete();

                $subtotalSale = 0;
                $taxAmountSale = 0;
                $totalSale = 0;

                foreach ($data['items'] as $item) {
                    $quantityUom = (float) $item['quantity'];
                    $uomFactor = 1;
                    $unitOfMeasureId = $item['unit_of_measure_id'] ?? null;

                    if ($unitOfMeasureId) {
                        $uom = UnitOfMeasure::find($unitOfMeasureId);
                        if ($uom) {
                            $uomFactor = (float) $uom->factor;
                        }
                    }

                    $quantityReal = $quantityUom * $uomFactor;

                    $priceUom = (float) $item['price'];
                    $priceReal = $uomFactor > 0 ? ($priceUom / $uomFactor) : $priceUom;

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
                        $totalItem = $quantityReal * $priceReal;
                        $subtotal = $totalItem / (1 + ($taxRate / 100));
                        $taxAmount = $totalItem - $subtotal;
                    } else {
                        $subtotal = $quantityReal * $priceReal;
                        $taxAmount = $subtotal * ($taxRate / 100);
                        $totalItem = $subtotal + $taxAmount;
                    }

                    $sale->productables()->create([
                        'product_product_id' => $item['product_product_id'],
                        'quantity' => $quantityReal,
                        'price' => $priceReal,
                        'unit_of_measure_id' => $unitOfMeasureId,
                        'quantity_uom' => $quantityUom,
                        'uom_factor' => $uomFactor,
                        'subtotal' => $subtotal,
                        'tax_id' => $item['tax_id'] ?? null,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'total' => $totalItem,
                    ]);

                    $subtotalSale += $subtotal;
                    $taxAmountSale += $taxAmount;
                    $totalSale += $totalItem;
                }

                $sale->update([
                    'subtotal' => $subtotalSale,
                    'tax_amount' => $taxAmountSale,
                    'total' => $totalSale,
                ]);
            }

            return $this->success(
                $sale->load(['productables.productProduct', 'productables.unitOfMeasure', 'partner', 'journal', 'warehouse', 'user']),
                'Venta actualizada exitosamente.'
            );
        });
    }

    public function destroy(Sale $sale): JsonResponse
    {
        if ($sale->status !== 'draft') {
            return $this->error('Solo se pueden eliminar ventas en estado borrador.', 422);
        }

        $sale->delete();

        return $this->success(null, 'Venta eliminada exitosamente.');
    }

    public function post(Sale $sale): JsonResponse
    {
        if ($sale->status !== 'draft') {
            return $this->error('Solo se pueden confirmar ventas en estado borrador.', 422);
        }

        return DB::transaction(function () use ($sale) {
            $sale->update(['status' => 'posted']);
            $this->registerSaleInKardex($sale);

            return $this->success(
                $sale->load(['productables.productProduct', 'productables.unitOfMeasure', 'inventories']),
                'Venta confirmada y stock actualizado.'
            );
        });
    }

    protected function registerSaleInKardex(Sale $sale): void
    {
        $detailPrefix = $sale->original_sale_id ? 'Reversión' : 'Venta';

        foreach ($sale->productables as $item) {
            if ($sale->original_sale_id) {
                $lastRecord = $this->kardexService->getLastRecord($item->product_product_id, $sale->warehouse_id);
                $unitCost = (float) ($lastRecord['cost'] ?? 0);

                $this->kardexService->registerEntry(
                    $sale,
                    [
                        'id' => $item->product_product_id,
                        'quantity' => $item->quantity,
                        'price' => $unitCost,
                    ],
                    $sale->warehouse_id,
                    "{$detailPrefix} {$sale->document_number}"
                );

                continue;
            }

            $this->kardexService->registerExit(
                $sale,
                [
                    'id' => $item->product_product_id,
                    'quantity' => $item->quantity,
                ],
                $sale->warehouse_id,
                "{$detailPrefix} {$sale->document_number}"
            );
        }
    }
}
