<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

final class PaymentMethodController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success(PaymentMethod::latest()->get());
    }

    public function store(PaymentMethodRequest $request): JsonResponse
    {
        $paymentMethod = PaymentMethod::create($request->validated());

        return $this->created($paymentMethod, 'Método de pago creado exitosamente.');
    }

    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return $this->success($paymentMethod);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->update($request->validated());

        return $this->success($paymentMethod, 'Método de pago actualizado exitosamente.');
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->delete();

        return $this->success(message: 'Método de pago eliminado exitosamente.');
    }
}

