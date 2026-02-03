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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();

            // Relación con company
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Relación OPCIONAL con user (si el partner tiene login)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            // TIPOS DE PARTNER (Un partner puede ser múltiples tipos)
            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);

            // ========================================
            // CAMPOS COMPARTIDOS (todos los partners)
            // ========================================

            // Documentos
            $table->enum('document_type', ['DNI', 'RUC', 'CE', 'Passport'])
                ->default('DNI');
            $table->string('document_number', 20);

            // Nombre (puede ser persona o empresa)
            $table->string('business_name', 200)->nullable(); // Para empresas/proveedores
            $table->string('first_name', 100)->nullable();     // Para personas
            $table->string('last_name', 100)->nullable();      // Para personas

            // Contacto
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('photo_url')->nullable();

            // Dirección completa
            $table->text('address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('department', 100)->nullable();

            // ========================================
            // CAMPOS ESPECÍFICOS DE CUSTOMERS
            // ========================================

            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'Other'])->nullable();

            // Info médica (solo customers)
            $table->string('blood_type', 5)->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('allergies')->nullable();

            // ========================================
            // CAMPOS ESPECÍFICOS DE PROVIDERS
            // ========================================

            // Términos comerciales
            $table->integer('payment_terms')->nullable(); // Días de crédito
            $table->decimal('credit_limit', 10, 2)->nullable();
            $table->string('tax_id', 50)->nullable(); // RUC para facturación
            $table->string('business_license', 100)->nullable();

            // Categoría de proveedor
            $table->string('provider_category', 50)->nullable(); // 'equipment', 'supplements', 'services'

            // ========================================
            // CAMPOS COMUNES
            // ========================================

            // Estado general
            $table->enum('status', ['active', 'inactive', 'suspended', 'blacklisted'])
                ->default('active');

            // Notas generales
            $table->text('notes')->nullable();

            $table->timestamps();

            // ========================================
            // ÍNDICES Y CONSTRAINTS
            // ========================================

            // Documento único por compañía (Se asume que en un tenant, el documento es único)
            // Nota: El usuario sugirió unique(['document_type', 'document_number'])
            $table->unique(['document_type', 'document_number']);

            // Índices para búsquedas rápidas
            $table->index('is_customer');
            $table->index('is_supplier');
            $table->index('email');
            $table->index('status');
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
