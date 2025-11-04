<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->truncate();
        DB::table('menus')->insert([
            [
                'categoria'=>'',
                'description'=>'Painel',
                'icon'=>'fa fa-tachometer-alt',
                'actived'=>true,
                'url'=>'painel',
                'route'=>'home.admin',
                'pai'=>''
            ],
            [
                'categoria'=>'CADASTROS',
                'description'=>'Clientes',
                'icon'=>'fas fa-users',
                'actived'=>true,
                'url'=>'cad-clientes',
                'route'=>'',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Todos Clientes',
                'icon'=>'fas fa-user',
                'actived'=>true,
                'url'=>'clientes',
                'route'=>'clientes.index',
                'pai'=>'cad-clientes'
            ],
            [
                'categoria'=>'',
                'description'=>'Importar Clientes',
                'icon'=>'fas fa-file',
                'actived'=>true,
                'url'=>'import_clientes',
                'route'=>'clientes.import',
                'pai'=>'cad-clientes'
            ],
            [
                'categoria'=>'SITE',
                'description'=>'Gerenciar site',
                'icon'=>'fas fa-globe',
                'actived'=>true,
                'url'=>'ger-site',
                'route'=>'',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Páginas',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'paginas',
                'route'=>'paginas.index',
                'pai'=>'ger-site'
            ],
            [
                'categoria'=>'',
                'description'=>'Menus',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'menus',
                'route'=>'menus.index',
                'pai'=>'ger-site'
            ],
            [
                'categoria'=>'',
                'description'=>'Componentes',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'componentes',
                'route'=>'componentes.index',
                'pai'=>'ger-site'
            ],
            [
                'categoria'=>'',
                'description'=>'Categorias',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'categorias',
                'route'=>'categorias.index',
                'pai'=>'ger-site'
            ],
            [
                'categoria'=>'SISTEMA',
                'description'=>'Configurações',
                'icon'=>'fas fa-cogs',
                'actived'=>true,
                'url'=>'config',
                'route'=>'',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Documentos',
                'icon'=>'fas fa-file-word',
                'actived'=>true,
                'url'=>'documentos',
                'route'=>'documentos.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Perfil',
                'icon'=>'fas fa-user',
                'actived'=>true,
                'url'=>'sistema',
                'route'=>'sistema.perfil',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Usuários',
                'icon'=>'fas fa-users',
                'actived'=>true,
                'url'=>'users',
                'route'=>'users.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Permissões',
                'icon'=>'far fa-list-alt ',
                'actived'=>true,
                'url'=>'permissions',
                'route'=>'permissions.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Listas do sistema (Tags)',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'tags',
                'route'=>'tags.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Avançado (Dev)',
                'icon'=>'fas fa-user',
                'actived'=>true,
                'url'=>'qoptions',
                'route'=>'qoptions.index',
                'pai'=>'config'
            ],
        ]);
    }
}
