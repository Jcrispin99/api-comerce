<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\SupplierRequest;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

/**
 * @group Proveedores (Tenant)
 * @authenticated
 *
 * Gestión de proveedores dentro del tenant.
 */
class SupplierController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success(Partner::suppliers()->get());
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_supplier'] = true;

        $supplier = Partner::create($data);

        return $this->created($supplier, 'Proveedor creado exitosamente.');
    }

    public function show(Partner $supplier): JsonResponse
    {
        if (!$supplier->is_supplier) {
            return $this->error('El socio no es un proveedor.', 404);
        }

        return $this->success($supplier);
    }

    public function update(SupplierRequest $request, Partner $supplier): JsonResponse
    {
        if (!$supplier->is_supplier) {
            return $this->error('El socio no es un proveedor.', 404);
        }

        $supplier->update($request->validated());

        return $this->success($supplier, 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Partner $supplier): JsonResponse
    {
        if (!$supplier->is_supplier) {
            return $this->error('El socio no es un proveedor.', 404);
        }

        $supplier->delete();

        return $this->success(null, 'Proveedor eliminado exitosamente.');
    }
}
