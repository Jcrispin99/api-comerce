<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\PosConfigRequest;
use App\Models\PosConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class PosConfigController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->success(
            PosConfig::with(['company', 'warehouse', 'defaultCustomer', 'tax', 'journals'])->latest()->get()
        );
    }

    public function store(PosConfigRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $journals = $data['journals'] ?? null;
            unset($data['journals']);

            $posConfig = PosConfig::create($data);

            if (is_array($journals)) {
                $posConfig->journals()->sync($this->buildJournalSyncData($journals));
            }

            return $this->created(
                $posConfig->load(['company', 'warehouse', 'defaultCustomer', 'tax', 'journals']),
                'Configuración POS creada exitosamente.'
            );
        });
    }

    public function show(PosConfig $posConfig): JsonResponse
    {
        return $this->success(
            $posConfig->load(['company', 'warehouse', 'defaultCustomer', 'tax', 'journals'])
        );
    }

    public function update(PosConfigRequest $request, PosConfig $posConfig): JsonResponse
    {
        return DB::transaction(function () use ($request, $posConfig) {
            $data = $request->validated();
            $journals = $data['journals'] ?? null;
            unset($data['journals']);

            $posConfig->update($data);

            if (is_array($journals)) {
                $posConfig->journals()->sync($this->buildJournalSyncData($journals));
            }

            return $this->success(
                $posConfig->load(['company', 'warehouse', 'defaultCustomer', 'tax', 'journals']),
                'Configuración POS actualizada exitosamente.'
            );
        });
    }

    public function destroy(PosConfig $posConfig): JsonResponse
    {
        $posConfig->delete();

        return $this->success(null, 'Configuración POS eliminada exitosamente.');
    }

    private function buildJournalSyncData(array $journals): array
    {
        $sync = [];

        foreach ($journals as $journal) {
            $journalId = (int) $journal['journal_id'];
            $sync[$journalId] = [
                'document_type' => (string) $journal['document_type'],
                'is_default' => (bool) ($journal['is_default'] ?? false),
            ];
        }

        return $sync;
    }
}

