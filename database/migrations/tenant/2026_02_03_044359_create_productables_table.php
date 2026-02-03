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
        Schema::create('productables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_product_id')
                ->constrained('product_products')
                ->onDelete('cascade');

            $table->morphs('productable');

            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreignId('tax_id')
                ->nullable()
                ->constrained('taxes')
                ->onDelete('set null');

            $table->decimal('tax_rate', 8, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            $table->timestamps();

            // morphs() ya crea su propio índice, solo agregamos index para product_product_id
            $table->index('product_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productables');
    }
};
