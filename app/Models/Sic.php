<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Sic extends Model
{
    use HasFactory, Notifiable;
    protected $casts = [
        'config' => 'array',
        'meta' => 'array',
    ];

    protected $fillable = [
        'token',
        'nome',
        'ativo',
        'status',
        'situacao',
        'recurso',
        'motivo',
        'type',
        'autor',
        'id_requerente',
        'obs',
        'config',
        'mensagem',
        'arquivo',
        'meta',
        'excluido',
        'reg_excluido',
        'deletado',
        'reg_deletado'
    ];
}
