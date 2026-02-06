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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('serie');
            $table->string('correlative');
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->timestamp('date')->useCurrent();

            $table->foreignId('partner_id')
                ->nullable()
                ->constrained('partners')
                ->onDelete('set null');

            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');

            $table->foreignId('original_sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->unsignedBigInteger('pos_session_id')->nullable();

            $table->foreignId('user_id')->constrained('users');

            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);

            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('paid');

            $table->string('sunat_status')->default('pending');
            $table->json('sunat_response')->nullable();
            $table->timestamp('sunat_sent_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['date']);
            $table->index(['pos_session_id']);
            $table->index('original_sale_id');
            $table->index('sunat_status');

            $table->unique(
                ['company_id', 'serie', 'correlative'],
                'sale_unique_company_serie_corr'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
