<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_pos_config', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pos_config_id')
                ->constrained('pos_configs')
                ->cascadeOnDelete();

            $table->foreignId('journal_id')
                ->constrained('journals')
                ->cascadeOnDelete();

            $table->string('document_type');
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique(['pos_config_id', 'journal_id', 'document_type'], 'journal_pos_config_unique');
            $table->index(['pos_config_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_pos_config');
    }
};

