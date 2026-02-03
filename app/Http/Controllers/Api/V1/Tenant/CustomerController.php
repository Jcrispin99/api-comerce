<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\CustomerRequest;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

/**
 * @group Clientes (Tenant)
 * @authenticated
 *
 * Gestión de clientes dentro del tenant.
 */
class CustomerController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success(Partner::customers()->get());
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_customer'] = true;

        $customer = Partner::create($data);

        return $this->created($customer, 'Cliente creado exitosamente.');
    }

    public function show(Partner $customer): JsonResponse
    {
        if (!$customer->is_customer) {
            return $this->error('El socio no es un cliente.', 404);
        }

        return $this->success($customer);
    }

    public function update(CustomerRequest $request, Partner $customer): JsonResponse
    {
        if (!$customer->is_customer) {
            return $this->error('El socio no es un cliente.', 404);
        }

        $customer->update($request->validated());

        return $this->success($customer, 'Cliente actualizado exitosamente.');
    }

    public function destroy(Partner $customer): JsonResponse
    {
        if (!$customer->is_customer) {
            return $this->error('El socio no es un cliente.', 404);
        }

        $customer->delete();

        return $this->success(null, 'Cliente eliminado exitosamente.');
    }
}
