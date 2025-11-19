<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations (tenant connection).
     * Cria a tabela `contract_events` para registrar históricos de eventos de contratos.
     */
    /**
     * Create the tenant `contract_events` table if it does not exist.
     */
    public function up(): void
    {
        if (!Schema::hasTable('contract_events')) {
            Schema::create('contract_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contrato_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('event_type', 100);
                $table->text('description')->nullable();
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['contrato_id', 'event_type']);
                // Adiciona FKs somente se as tabelas de referência existirem no tenant.
                if (Schema::hasTable('contratos')) {
                    $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('cascade');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    /**
     * Drop the tenant `contract_events` table.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_events');
    }
};