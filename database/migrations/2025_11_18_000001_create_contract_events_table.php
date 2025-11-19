<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Cria a tabela `contract_events` para registrar históricos de eventos de contratos.
     */
    /**
     * Create the central `contract_events` table if it does not exist.
     */
    public function up(): void
    {
        if (!Schema::hasTable('contract_events')) {
            Schema::create('contract_events', function (Blueprint $table) {
                $table->id();
                // Referência ao contrato
                $table->unsignedBigInteger('contrato_id');
                // Usuário que gerou a interação (opcional)
                $table->unsignedBigInteger('user_id')->nullable();
                // Tipo de evento (ex.: status_update, reativacao, integracao_sulamerica)
                $table->string('event_type', 100);
                // Descrição livre do evento
                $table->text('description')->nullable();
                // Mudança de status (quando aplicável)
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                // Metadados adicionais em JSON
                $table->json('metadata')->nullable();
                $table->timestamps();

                // Índices e chaves estrangeiras
                $table->index(['contrato_id', 'event_type']);
                $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Remove a tabela `contract_events`.
     */
    /**
     * Drop the central `contract_events` table.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_events');
    }
};