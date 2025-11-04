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
            ['nome'=>'STATUS DOS CONTRATOS','obs'=>'Todas os status',"value"=>"status_contratos",],
            [
                'nome'=>'Aprovado',
                'pai'=>'status_contratos',
                'obs'=>'Clientes que foram aprovados pela sulamerica direto da API',
                'ordem'=>1,
                'value'=>'Aprovado',
            ],
            [
                'nome'=>'Cancelado',
                'pai'=>'status_contratos',
                'obs'=>'Clientes que foram cancelados através sulamerica direto da API',
                'ordem'=>2,
                'value'=>'Cancelado',
            ],
            [
                'nome'=>'Reativando',
                'pai'=>'status_contratos',
                'obs'=>'Clientes que foram Reativando através sulamerica direto da API',
                'ordem'=>3,
                'value'=>'Reativando',
            ],
            [
                'nome'=>'Plano01',
                'pai'=>'status_contratos',
                'obs'=>'Clientes que foram aprovandos antes da criação do sistema',
                'ordem'=>4,
                'value'=>'Plano01',
            ],
        ];
        Tag::truncate();
        foreach ($arr as $key => $value) {
            $d = $value;
            $d['value']= isset($value['value']) ? $value['value'] :  uniqid();
            Tag::create($d);
        }
    }
}
