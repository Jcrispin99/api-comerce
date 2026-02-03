<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\JournalRequest;
use App\Models\Journal;
use Illuminate\Http\JsonResponse;

/**
 * @group Diarios (Tenant)
 * @authenticated
 *
 * Gestión de diarios y sus secuencias dentro del tenant.
 */
class JournalController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success(Journal::with('sequence')->get());
    }

    public function store(JournalRequest $request): JsonResponse
    {
        // La secuencia se crea automáticamente en el modelo Journal (hook creating)
        $journal = Journal::create($request->validated());

        return $this->created(
            $journal->load('sequence'),
            'Diario creado exitosamente.'
        );
    }

    public function show(Journal $journal): JsonResponse
    {
        return $this->success($journal->load('sequence'));
    }

    public function update(JournalRequest $request, Journal $journal): JsonResponse
    {
        $journal->update($request->validated());

        return $this->success(
            $journal->load('sequence'),
            'Diario actualizado exitosamente.'
        );
    }

    public function destroy(Journal $journal): JsonResponse
    {
        // Al eliminar el journal, podrías querer eliminar la secuencia también
        // pero por integridad referencial y auditoría, a veces es mejor dejarla.
        // Aquí seguimos la lógica estándar.
        $journal->delete();

        return $this->success(null, 'Diario eliminado exitosamente.');
    }
}
