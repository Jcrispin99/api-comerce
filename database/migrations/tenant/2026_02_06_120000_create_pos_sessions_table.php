<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('pos_config_id')->constrained('pos_configs');

            $table->decimal('opening_balance', 10, 2);
            $table->text('opening_note')->nullable();

            $table->decimal('closing_balance', 10, 2)->nullable();
            $table->text('closing_note')->nullable();

            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();

            $table->string('status');

            $table->timestamps();

            $table->index(['pos_config_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};

