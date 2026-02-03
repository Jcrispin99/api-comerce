<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Journal;
use App\Models\Sequence;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainCompany = Company::whereNull('parent_id')->first() ?? Company::first();

        if (!$mainCompany) {
            $this->command->warn('⚠️ No se encontró ninguna compañía. Saltando seed de diarios.');
            return;
        }

        $journals = [
            ['name' => 'NOTA DE VENTA',          'type' => 'sale',           'code' => 'NV',   'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'FACTURA DE VENTA',       'type' => 'sale',           'code' => 'F004', 'document_type_code' => '01', 'is_fiscal' => true],
            ['name' => 'BOLETA DE VENTA',        'type' => 'sale',           'code' => 'B004', 'document_type_code' => '03', 'is_fiscal' => true],
            ['name' => 'Nota de Crédito Factura', 'type' => 'sale',          'code' => 'FC04', 'document_type_code' => '07', 'is_fiscal' => true],
            ['name' => 'Nota de Crédito Boleta', 'type' => 'sale',           'code' => 'BC04', 'document_type_code' => '07', 'is_fiscal' => true],
            ['name' => 'Nota de Débito Factura', 'type' => 'sale',           'code' => 'FD04', 'document_type_code' => '08', 'is_fiscal' => true],
            ['name' => 'Nota de Débito Boleta',  'type' => 'sale',           'code' => 'BD04', 'document_type_code' => '08', 'is_fiscal' => true],
            ['name' => 'Compras',                'type' => 'purchase',       'code' => 'COMP', 'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'Cuadre de Caja',         'type' => 'cash',           'code' => 'CAJA', 'document_type_code' => null, 'is_fiscal' => false],
        ];

        // Limpiar tipos no deseados
        Journal::whereIn('type', ['quote', 'purchase-order'])->delete();

        foreach ($journals as $journalData) {
            // Verificar si el journal ya existe para no duplicar secuencia
            $existingJournal = Journal::where('code', $journalData['code'])
                ->where('company_id', $mainCompany->id)
                ->first();

            if ($existingJournal) {
                // Si existe, solo actualizamos datos, no tocamos la secuencia
                $existingJournal->update([
                    'name'               => $journalData['name'],
                    'type'               => $journalData['type'],
                    'document_type_code' => $journalData['document_type_code'],
                    'is_fiscal'          => $journalData['is_fiscal'] ?? false,
                ]);
                continue;
            }

            // Si es nuevo, creamos la secuencia manualmente o dejamos que el modelo lo haga
            // NOTA: Como implementaremos la creación automática en el modelo,
            // aquí podríamos simplemente crear el Journal sin sequence_id y dejar que el hook actúe.
            // Pero para el seeder explícito que pediste, mantendré tu lógica original o usaré el modelo.

            // Opción A: Crear explícitamente como en tu ejemplo
            $sequence = Sequence::create([
                'sequence_size' => 8,
                'step'          => 1,
                'next_number'   => 1,
            ]);

            Journal::create([
                'name'               => $journalData['name'],
                'type'               => $journalData['type'],
                'code'               => $journalData['code'],
                'document_type_code' => $journalData['document_type_code'],
                'is_fiscal'          => $journalData['is_fiscal'] ?? false,
                'sequence_id'        => $sequence->id,
                'company_id'         => $mainCompany->id,
            ]);
        }

        $this->command->info('✅ Se procesaron ' . count($journals) . ' diarios/journals.');
    }
}
