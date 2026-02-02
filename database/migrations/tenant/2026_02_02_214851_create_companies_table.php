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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('companies')
                ->onDelete('cascade')
                ->comment('Compañía matriz (null = matriz, valor = sucursal)');
            $table->string('branch_code', 10)
                ->nullable()
                ->unique()
                ->comment('Código de sucursal (ej: SUC-001)');
            $table->boolean('is_main_office')
                ->default(false)
                ->comment('¿Es casa matriz?');
            $table->string('business_name')->comment('Razón Social');
            $table->string('trade_name')->nullable()->comment('Nombre Comercial');
            $table->string('ruc', 11)->comment('RUC');
            $table->text('address')->nullable()->comment('Dirección');
            $table->string('phone', 20)->nullable()->comment('Teléfono');
            $table->string('email', 100)->nullable()->comment('Email');
            $table->string('logo_url')->nullable()->comment('URL del Logo');
            $table->string('ubigeo', 6)->nullable()->comment('Código de Ubigeo');
            $table->string('urbanization', 100)->nullable()->comment('Urbanización');
            $table->string('department', 50)->nullable()->comment('Departamento');
            $table->string('province', 50)->nullable()->comment('Provincia');
            $table->string('district', 50)->nullable()->comment('Distrito');
            $table->boolean('active')->default(true)->comment('Estado Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
