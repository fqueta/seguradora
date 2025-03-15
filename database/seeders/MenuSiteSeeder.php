<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu_sites')->truncate();
        DB::table('menu_sites')->insert([
            [
                'categoria'=>'',
                'description'=>'Home',
                'icon'=>'fa fa-home',
                'actived'=>true,
                'permission'=>'public',
                'url'=>'/',
                'ordenar'=>1,
                'page_id'=>0,
                'pai'=>''
            ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Leilões',
            //     'icon'=>'fa fa-gavel',
            //     'actived'=>true,
            //     'permission'=>'public',
            //     'url'=>'leiloes-publicos',
            //     'ordenar'=>2,
            //     'page_id'=>37,
            //     'pai'=>''
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Seguindo',
            //     'icon'=>'fa fa-gavel',
            //     'actived'=>true,
            //     'permission'=>'private',
            //     'url'=>'seguindo',
            //     'ordenar'=>3,
            //     'page_id'=>11,
            //     'pai'=>''
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Meus Lances',
            //     'icon'=>'fa fa-gavel',
            //     'actived'=>true,
            //     'permission'=>'private',
            //     'url'=>'lances-list',
            //     'ordenar'=>4,
            //     'page_id'=>0,
            //     'pai'=>''
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Como Funciona',
            //     'icon'=>'fa fa-book',
            //     'actived'=>true,
            //     'permission'=>'public',
            //     'url'=>'',
            //     'ordenar'=>5,
            //     'page_id'=>4,
            //     'pai'=>''
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Contato',
            //     'icon'=>'fa fa-contacts',
            //     'actived'=>true,
            //     'permission'=>'public',
            //     'url'=>'/contato',
            //     'ordenar'=>5,
            //     'page_id'=>0,
            //     'pai'=>''
            // ],
        ]);
    }
}
