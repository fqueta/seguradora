<?php

namespace Database\Seeders;

use App\Models\Contrato;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            [
                'name' => 'Fernando Queta',
                'email' => 'fernando@maisaqui.com.br',
                'password' => Hash::make('ferqueta'),
                'status' => 'actived',
                'verificado' => 'n',
                'id_permission' => '1',
            ],
            [
                'name' => 'Wellington Santos',
                'email' => 'mastertechjf@gmail.com',
                'password' => Hash::make('mudar123'),
                'status' => 'actived',
                'verificado' => 'n',
                'id_permission' => '2',
            ],
            // [
            //     'name' => 'Usuario de teste front',
            //     'email' => 'ger.maisaqui1@gmail.com',
            //     'password' => Hash::make('mudar123'),
            //     'status' => 'actived',
            //     'verificado' => 'n',
            //     'id_permission' => '5',
            // ],
        ];
        User::truncate();
        Contrato::truncate();
        foreach ($arr as $key => $value) {
            User::create($value);
        }
        //Aproveitando para incluir dados padroes para os processos de licitações
        // $arr_t = [
        //     'bidding_categories'=>[
        //         ['name' => 'Saúde'], ['name' => 'Construção'], ['name' => 'Geral'],
        //         ['name' => 'Transporte'], ['name' => 'Informatica'], ['name' => 'Equipamentos Hospitalares'],
        //         ['name' => 'Outros'],
        //     ],
        //     'bidding_phases'=>[
        //         ['name' => 'Em Andamento'], ['name' => 'Adjudicado'],
        //         ['name' => 'Homologado'], ['name' => 'Suspenso'],
        //         ['name' => 'Cancelado'], ['name' => 'Deserta'],
        //         ['name' => 'Concluido'], ['name' => 'Anulado'],
        //         ['name' => 'Revogado'],
        //     ],
        //     'bidding_genres'=>[
        //         ['name' => 'Concorrência'], ['name' => 'Concurso'],
        //         ['name' => 'Convite'], ['name' => 'Dispensa'],
        //         ['name' => 'Inexigibilidade'], ['name' => 'Leilão'],
        //         ['name' => 'Pregão'], ['name' => 'Pregão Eletrônico'],
        //         ['name' => 'Adesão RP'], ['name' => 'Dispensa Eletrônica'],
        //     ],
        //     'bidding_types'=>[
        //         ['name' => 'Menor Preço - Item'], ['name' => 'Menor Preço - Global'],
        //         ['name' => 'Menor Preço - Item - Valor Máximo'], ['name' => 'Menor Preço - Global - Valor Máximo'],
        //         ['name' => 'Menor Preço - Lote'], ['name' => 'Maior Desconto'],
        //         ['name' => 'Maior Lance ou Oferta - Item'], ['name' => 'Maior Lance ou Oferta - Lote'],
        //         ['name' => 'Melhor Técnica'], ['name' => 'Técnica e Preço'],
        //         ['name' => 'Menor Taxa de Administração'],
        //     ]
        // ];
        // foreach ($arr_t as $table => $value) {
        //     DB::table($table)->truncate();
        //     foreach ($value as $kbc => $vbc) {
        //         DB::table($table)->insert($vbc);
        //     }
        // }
    }
}
