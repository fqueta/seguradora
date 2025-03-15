<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Master',
                'redirect_login' => '/home',
                'id_menu' => '{"ler":{"painel":"s","cad-produtos":"s","create":"s","produtos":"s","cad-leiloes":"s","leiloes_adm":"s","ger-site":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","config":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"create":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"update":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"delete":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"ler_arquivos":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"}}',
                'config' => NULL,
                'description' => 'Desenvolvedores',
                'guard_name' => 'web',
                'created_at' => NULL,
                'updated_at' => '2024-02-01 14:07:56',
                'active' => 's',
                'autor' => 1,
                'token' => NULL,
                'excluido' => 'n',
                'reg_excluido' => NULL,
                'deletado' => 'n',
                'reg_deletado' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Administrador',
                'redirect_login' => '/home',
                'id_menu' => '{"ler":{"painel":"s","cad-produtos":"s","create":"s","produtos":"s","cad-leiloes":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","config":"s","sistema":"s","users":"s","permissions":"s"},"create":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"update":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"delete":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"ler_arquivos":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"}}',
                'config' => NULL,
                'description' => 'Administradores do sistema',
                'guard_name' => 'web',
                'created_at' => NULL,
                'updated_at' => '2024-02-01 14:20:20',
                'active' => 's',
                'autor' => 1,
                'token' => NULL,
                'excluido' => 'n',
                'reg_excluido' => NULL,
                'deletado' => 'n',
                'reg_deletado' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Gerente',
                'redirect_login' => '/home',
                'id_menu' => '{"ler":{"painel":"s","cad-produtos":"s","create":"s","produtos":"s","cad-leiloes":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","config":"s","sistema":"s"},"create":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"update":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"delete":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"},"ler_arquivos":{"create":"s","produtos":"s","leiloes_adm":"s","paginas":"s","menus":"s","componentes":"s","categorias":"s","documentos":"s","sistema":"s","users":"s","permissions":"s","tags":"s","qoptions":"s"}}',
                'config' => NULL,
                'description' => 'Gerente do sistema menos que administrador secundário',
                'guard_name' => 'web',
                'created_at' => NULL,
                'updated_at' => '2024-02-01 14:21:39',
                'active' => 's',
                'autor' => 1,
                'token' => NULL,
                'excluido' => 'n',
                'reg_excluido' => NULL,
                'deletado' => 'n',
                'reg_deletado' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Escritório',
                'redirect_login' => '/home',
                'id_menu' => '[]',
                'config' => NULL,
                'description' => 'Pessoas do escritório',
                'guard_name' => 'web',
                'created_at' => NULL,
                'updated_at' => NULL,
                'active' => 's',
                'autor' => NULL,
                'token' => NULL,
                'excluido' => 'n',
                'reg_excluido' => NULL,
                'deletado' => 'n',
                'reg_deletado' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Clientes',
                'redirect_login' => '/home',
                'id_menu' => '{"ler":{"create":"s","cad-leiloes":"s","leiloes_adm":"s"},"create":{"create":"s","leiloes_adm":"s"},"update":{"create":"s","leiloes_adm":"s"},"delete":{"create":"s","leiloes_adm":"s"},"ler_arquivos":{"create":"s","leiloes_adm":"s"}}',
                'config' => NULL,
                'description' => 'Somente clientes, Sem privilêgios de administração acesso a área restrita do site',
                'guard_name' => 'web',
                'created_at' => NULL,
                'updated_at' => '2024-02-01 14:22:57',
                'active' => 's',
                'autor' => 1,
                'token' => NULL,
                'excluido' => 'n',
                'reg_excluido' => NULL,
                'deletado' => 'n',
                'reg_deletado' => NULL,
            ),
        ));
        
        
    }
}