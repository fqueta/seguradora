<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class financeiro extends Model
{
    use HasFactory;
    protected $table = 'financeiro';
    protected $fillable = [
        'ano',
        'numero',
        'valor',
        'valor_pago',
        'obs',
        'obs_pagamento',
        'conta',
        'autor',
        'categoria',
        'vencimento',
        'data_pagamento',
        'emissao',
        'id_cliente',
        'id_responsavel',
        'pago',
        'data',
        'token',
        'atualizado',
        'repetir',
        'vezes',
        'nome',
        'tipo',
        'tag',
        'descricao',
        'historico_estorno',
        'fixa',
        'dividi',
        'dividir',
        'parcela',
        'prazo',
        'periodo_repete',
        'config',
        'id_fatura_fixa',
        'token_fatura_dividir',
        'forma_pagameto',
        'token_transf',
        'ref_compra',
        'local',
        'reg_asaas',
        'data_recorrencia',
        'cobrar',
    ];
}
