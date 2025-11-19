<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\ContractEvent;

class ContractEventLogger
{
    /**
     * Registra um evento genérico associado a um contrato.
     *
     * @param Contrato $contrato Instância do contrato alvo.
     * @param string $type Tipo do evento (ex.: status_update, reativacao, integracao_sulamerica).
     * @param string|null $description Texto descritivo opcional.
     * @param array $metadata Metadados adicionais para auditoria.
     * @param int|null $userId ID do usuário que realizou a ação (opcional).
     * @return ContractEvent Evento persistido.
     */
    public static function log(Contrato $contrato, string $type, ?string $description = null, array $metadata = [], ?int $userId = null): ContractEvent
    {
        return ContractEvent::create([
            'contrato_id' => $contrato->id,
            'user_id' => $userId,
            'event_type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Registra uma mudança de status do contrato.
     *
     * @param Contrato $contrato Instância do contrato alvo.
     * @param string|null $from Status anterior (pode ser null).
     * @param string|null $to Novo status (pode ser null).
     * @param string|null $description Texto descritivo opcional.
     * @param array $metadata Metadados adicionais.
     * @param int|null $userId ID do usuário que realizou a ação.
     * @return ContractEvent Evento persistido.
     */
    public static function logStatusChange(Contrato $contrato, ?string $from, ?string $to, ?string $description = null, array $metadata = [], ?int $userId = null): ContractEvent
    {
        return ContractEvent::create([
            'contrato_id' => $contrato->id,
            'user_id' => $userId,
            'event_type' => 'status_update',
            'description' => $description,
            'from_status' => $from,
            'to_status' => $to,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Helper: registra evento buscando o contrato pelo token.
     *
     * @param string $tokenContrato Token do contrato.
     * @param string $type Tipo do evento.
     * @param string|null $description Descrição.
     * @param array $metadata Metadados.
     * @param int|null $userId Usuário (opcional).
     * @return ContractEvent|null Evento ou null se não encontrar o contrato.
     */
    public static function logByToken(string $tokenContrato, string $type, ?string $description = null, array $metadata = [], ?int $userId = null): ?ContractEvent
    {
        $contrato = Contrato::where('token', $tokenContrato)->first();
        if (!$contrato) {
            return null;
        }
        return self::log($contrato, $type, $description, $metadata, $userId);
    }

    /**
     * Helper: registra mudança de status buscando o contrato pelo token.
     *
     * @param string $tokenContrato Token do contrato.
     * @param string|null $from Status anterior.
     * @param string|null $to Novo status.
     * @param string|null $description Descrição.
     * @param array $metadata Metadados.
     * @param int|null $userId Usuário (opcional).
     * @return ContractEvent|null Evento ou null se não encontrar o contrato.
     */
    public static function logStatusChangeByToken(string $tokenContrato, ?string $from, ?string $to, ?string $description = null, array $metadata = [], ?int $userId = null): ?ContractEvent
    {
        $contrato = Contrato::where('token', $tokenContrato)->first();
        if (!$contrato) {
            return null;
        }
        return self::logStatusChange($contrato, $from, $to, $description, $metadata, $userId);
    }
}