<?php

namespace App\Models;

use App\Events\lanceLeilao;
use App\Events\LanceLeilaoEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lance extends Model
{
    use HasFactory;
    protected $casts = [
        'config' => 'array',
    ];
    protected $fillable = [
        'token',
        'leilao_id',
        'author',
        'valor_lance',
        'ativo',
        'obs',
        'type',
        'config',
        'excluido',
        'reg_excluido',
        'deletado',
        'reg_deletado',
    ];
    protected $dispatchesEvents = [
        'created' => LanceLeilaoEvent::class
    ];
}
