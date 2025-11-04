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
        'config',
        'ativo',
        'autor',
        'token',
        'excluido',
        'reg_excluido',
        'deletado',
        'reg_excluido'
    ];
}
