<?php

namespace Database\Seeders;

use App\Models\profissao;
use Illuminate\Database\Seeder;

class ProfissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            'EMPREGADO - SETOR PRIVADO',
            'EMPRESÁRIO/EMPREENDEDOR',
            'ESTUDANTE',
            'JORNALISTA',
            'PESQUISADOR',
            'PROFESSOR',
            'PROFIS.LIBERAL/AUTÔNOMO',
            'SERVIDOR PÚBLICO ESTADUAL',
            'SERVIDOR PÚBLICO FEDERAL',
            'SERVIDOR PÚBLICO MUNICIPAL',
            'NÃO INFORMADO',
            'OUTRA',
        ];
        profissao::truncate();
        foreach ($arr as $key => $value) {
            profissao::create([
                'nome'=>$value,
                'token'=>uniqid(),
            ]);
        }
    }
}
