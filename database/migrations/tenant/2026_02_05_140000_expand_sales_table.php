<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('serie')->after('id');
            $table->string('correlative')->after('serie');
            $table->foreignId('journal_id')->after('correlative')->constrained('journals')->onDelete('cascade');
            $table->timestamp('date')->after('journal_id')->useCurrent();

            $table->foreignId('partner_id')->nullable()->after('date')->constrained('partners')->onDelete('set null');
            $table->foreignId('warehouse_id')->after('partner_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('company_id')->after('warehouse_id')->constrained('companies')->onDelete('cascade');

            $table->foreignId('original_sale_id')->nullable()->after('company_id')->constrained('sales')->nullOnDelete();
            $table->unsignedBigInteger('pos_session_id')->nullable()->after('original_sale_id');
            $table->foreignId('user_id')->after('pos_session_id')->constrained('users');

            $table->decimal('subtotal', 10, 2)->default(0.00)->after('user_id');
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('subtotal');
            $table->decimal('total', 10, 2)->default(0.00)->after('tax_amount');

            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted')->after('total');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('paid')->after('status');

            $table->string('sunat_status')->default('pending')->after('payment_status');
            $table->json('sunat_response')->nullable()->after('sunat_status');
            $table->timestamp('sunat_sent_at')->nullable()->after('sunat_response');

            $table->text('notes')->nullable()->after('sunat_sent_at');

            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['date']);
            $table->index(['pos_session_id']);
            $table->index(['original_sale_id']);
            $table->index(['sunat_status']);

            $table->unique(['company_id', 'serie', 'correlative'], 'sale_unique_company_serie_corr');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sale_unique_company_serie_corr');
            $table->dropIndex('sales_status_index');
            $table->dropIndex('sales_payment_status_index');
            $table->dropIndex('sales_date_index');
            $table->dropIndex('sales_pos_session_id_index');
            $table->dropIndex('sales_original_sale_id_index');
            $table->dropIndex('sales_sunat_status_index');

            $table->dropForeign(['journal_id']);
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['original_sale_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'serie',
                'correlative',
                'journal_id',
                'date',
                'partner_id',
                'warehouse_id',
                'company_id',
                'original_sale_id',
                'pos_session_id',
                'user_id',
                'subtotal',
                'tax_amount',
                'total',
                'status',
                'payment_status',
                'sunat_status',
                'sunat_response',
                'sunat_sent_at',
                'notes',
            ]);
        });
    }
};
