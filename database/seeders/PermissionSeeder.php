<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrPermiss = [
            "master"=>
            array (
                'ler' =>
                array (
                  'painel' => 's',
                  'cad-produtos' => 's',
                  'os' => 's',
                  'produtos' => 's',
                  'cad-orcamentos' => 's',
                  'orcamento' => 's',
                  'orcamentos' => 's',
                  'ger-site' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'config' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ),
                'create' =>
                array (
                  'os' => 's',
                  'produtos' => 's',
                  'orcamento' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ),
                'update' =>
                array (
                  'os' => 's',
                  'produtos' => 's',
                  'orcamento' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ),
                'delete' =>
                array (
                  'os' => 's',
                  'produtos' => 's',
                  'orcamento' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ),
                'ler_arquivos' =>
                array (
                  'os' => 's',
                  'produtos' => 's',
                  'orcamento' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ),
            ),
            "admin"=>
            [
                'ler' => [
                  'painel' => 's',
                  'produtos' => 's',
                  'cad-orcamentos' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'config' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                ],
                'create' => [
                  'produtos' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ],
                'update' => [
                  'produtos' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ],
                'delete' => [
                  'produtos' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ],
                'ler_arquivos' => [
                  'produtos' => 's',
                  'orcamentos' => 's',
                  'paginas' => 's',
                  'menus' => 's',
                  'componentes' => 's',
                  'categorias' => 's',
                  'documentos' => 's',
                  'sistema' => 's',
                  'users' => 's',
                  'permissions' => 's',
                  'tags' => 's',
                  'qoptions' => 's',
                ],
            ]
            ,
            "gerente"=>
            [
                "ler"=>["painel"=>"s","transparencia"=>"s","cad-social"=>"s","cad-topografico"=>"s","familias"=>"s","quadras"=>"s","lotes"=>"s","beneficiarios"=>"s","bairros"=>"s","etapas"=>"s","escolaridades"=>"s","estado-civil"=>"s","relatorios"=>"n","relatorios_social"=>"n","relatorios_acessos"=>"n","documentos"=>"s","config"=>"s","sistema"=>"n","users"=>"s","permissions"=>"s"],
                "ler_arquivos"=>["cad-social"=>"s","cad-topografico"=>"s","familias"=>"s","quadras"=>"s","lotes"=>"s","beneficiarios"=>"s","bairros"=>"s","etapas"=>"s","escolaridades"=>"s","estado-civil"=>"s","relatorios"=>"s","relatorios_social"=>"s","relatorios_acessos"=>"s","documentos"=>"s","qoptions"=>"s","config"=>"s","sistema"=>"s","users"=>"s","permissions"=>"s"],
                "create"=>["familias"=>"s","quadras"=>"s","lotes"=>"s","bairros"=>"s","beneficiarios"=>"s","etapas"=>"s","escolaridades"=>"s","estado-civil"=>"s","relatorios_social"=>"s","relatorios_acessos"=>"s","sistema"=>"s","users"=>"s","permissions"=>"s","documentos"=>"s"],
                "update"=>["familias"=>"s","quadras"=>"s","lotes"=>"s","bairros"=>"s","beneficiarios"=>"s","etapas"=>"s","escolaridades"=>"s","estado-civil"=>"s","relatorios_social"=>"s","relatorios_acessos"=>"s","sistema"=>"s","users"=>"s","permissions"=>"s","documentos"=>"s"],
                "delete"=>["familias"=>"s","quadras"=>"s","lotes"=>"s","bairros"=>"s","beneficiarios"=>"s","etapas"=>"s","escolaridades"=>"s","estado-civil"=>"s","relatorios_social"=>"s","relatorios_acessos"=>"s","sistema"=>"s","users"=>"s","permissions"=>"s","documentos"=>"s"]
            ],
        ];
        DB::table('permissions')->truncate();
        DB::table('permissions')->insert([
            [
                'name'=>'Master',
                'description'=>'Desenvolvedores',
                'redirect_login'=>'/home',
                'active'=>'s',
                'id_menu'=>json_encode($arrPermiss['master']),
            ],
            [
                'name'=>'Administrador',
                'description'=>'Administradores do sistema',
                'redirect_login'=>'/home',
                'active'=>'s',
                'id_menu'=>json_encode($arrPermiss['admin']),
            ],
            [
                'name'=>'Gerente',
                'description'=>'Gerente do sistema menos que administrador secundário',
                'redirect_login'=>'/home',
                'active'=>'s',
                'id_menu'=>json_encode([]),
            ],
            [
                'name'=>'Escritório',
                'description'=>'Pessoas do escritório',
                'redirect_login'=>'/home',
                'active'=>'s',
                'id_menu'=>json_encode([]),
            ],
            [
                'name'=>'Clientes',
                'description'=>'Somente clientes, Sem privilêgios de administração acesso a área restrita do site',
                'redirect_login'=>'/home',
                'active'=>'s',
                'id_menu'=>json_encode([]),
            ],
        ]);
    }
}

