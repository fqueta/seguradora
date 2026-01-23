<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $casts = [
        'config' => 'array',
    ];
    protected $fillable = [
        'id_cliente',
        'id_produto',
        'id_plano',
        'inicio',
        'fim',
        'config',
        'ativo',
        'autor',
        'token',
        'excluido',
        'reg_excluido',
        'deletado',
        'reg_deletado'
    ];

    /**
     * Relação: um contrato possui vários eventos de histórico.
     */
    public function events()
    {
        return $this->hasMany(ContractEvent::class, 'contrato_id');
    }
}
