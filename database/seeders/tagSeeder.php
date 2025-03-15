<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class tagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            ['nome'=>'Tipo de páginas','value'=>'tipo_paginas','obs'=>'Configurar tipos de páginas','ordem'=>1],
            ['nome'=>'Tipo de conteúdos','value'=>'tipo_conteudo','obs'=>'Tipos de conteúdos','ordem'=>2],
            [
                'nome'=>'PÁGINA PRINCIAL',
                'pai'=>1,
                'value'=>'pode_lance',
                'ordem'=>2,
                'obs'=>'Página com banner do tipo da home',
                'config'=>['color'=>'danger','icon'=>'fa fa-times']
            ],
            [
                'nome'=>'PÁGINA SECUNDÁRIA',
                'pai'=>1,
                'ordem'=>3,
                'obs'=>'São imóveis que são cadastrados, mas que não comporão o processo jurídico por já apresentarem registro imobiliário. Devem ser considerados como cadastros completos.',
                'value'=>'pagina_secu',
                'config'=>['color'=>'info','icon'=>'fas fa-calendar-check']
            ],
            [
                'nome'=>'Usuários que podem dar lances',
                'pai'=>0,
                'value'=>'podem_dar_lances',
                'ordem'=>3,
                'obs'=>'Agrupa todas a opções do select de clientes que podem dar lançes',
                'config'=>['color'=>'warning','icon'=>'fas fa-search-minus']
            ],
            [
                'nome'=>'Qualquer usuário',
                'pai'=>5,
                'ordem'=>1,
                'value'=>'qualquer_usuario',
                'obs'=>'',
                'config'=>['color'=>'warning','icon'=>'fa fa-times']
            ],
            [
                'nome'=>'Usuários registrados há pelo menos 48 horas',
                'pai'=>5,
                'ordem'=>2,
                'value'=>'48_horas',
                'obs'=>'',
                'config'=>['color'=>'warning','icon'=>'fa fa-times']
            ],
            [
                'nome'=>'Usuários registrados há pelo menos 7 dias',
                'pai'=>5,
                'ordem'=>3,
                'value'=>'7_dias',
                'obs'=>'',
                'config'=>['color'=>'warning','icon'=>'fa fa-times']
            ],
            [
                'nome'=>'Usuários registrados há pelo menos 1 mês',
                'pai'=>5,
                'ordem'=>4,
                'value'=>'1_mes',
                'obs'=>'',
                'config'=>['color'=>'warning','icon'=>'fa fa-times']
            ],
            [
                'nome'=>'Usuários registrados há pelo menos 3 meses',
                'pai'=>5,
                'ordem'=>5,
                'value'=>'3_meses',
                'obs'=>'',
                'config'=>['color'=>'warning','icon'=>'fa fa-times']
            ],
            [
                'nome'=>'FORMAS DE PAGAMENTO',
                'pai'=>0,
                'ordem'=>5,
                'value'=>'formas_pagamento',
                'obs'=>'',
                'config'=>[]
            ],
            [
                'nome'=>'Catão de Crédito',
                'pai'=>11,
                'ordem'=>1,
                'value'=>'credit_card',
                'obs'=>'',
                'config'=>[]
            ],
            [
                'nome'=>'PIX',
                'pai'=>11,
                'ordem'=>2,
                'value'=>'PIX',
                'obs'=>'',
                'config'=>[]
            ],
            [
                'nome'=>'Boleto',
                'pai'=>11,
                'ordem'=>3,
                'value'=>'BOLETO',
                'obs'=>'',
                'config'=>[]
            ],

        ];
        Tag::truncate();
        foreach ($arr as $key => $value) {
            $d = $value;
            $d['token']=uniqid();
            Tag::create($d);
        }
    }
}
