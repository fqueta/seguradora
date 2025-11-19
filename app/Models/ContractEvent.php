<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractEvent extends Model
{
    /**
     * Modelo de histórico de eventos de contratos.
     * Armazena interações como mudanças de status, reativações e integrações.
     */
    protected $table = 'contract_events';

    protected $fillable = [
        'contrato_id',
        'user_id',
        'event_type',
        'description',
        'from_status',
        'to_status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relacionamento com Contrato.
     */
    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    /**
     * Relacionamento com User (quem realizou a ação).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}