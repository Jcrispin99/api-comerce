<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productables', function (Blueprint $table) {
            $table->foreignId('unit_of_measure_id')
                ->nullable()
                ->after('product_product_id')
                ->constrained('unit_of_measures')
                ->nullOnDelete();

            $table->decimal('quantity_uom', 10, 2)
                ->nullable()
                ->after('quantity')
                ->comment('Cantidad original ingresada por el usuario en la unidad seleccionada');

            $table->decimal('uom_factor', 20, 8)
                ->default(1)
                ->after('quantity_uom')
                ->comment('Factor de conversión usado al momento de la transacción');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productables', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropColumn(['unit_of_measure_id', 'quantity_uom', 'uom_factor']);
        });
    }
};
