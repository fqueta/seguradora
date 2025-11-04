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
                'categoria'=>'MÓDULOS',
                'description'=>'Postagens do Site',
                'icon'=>'fas fa-globe',
                'actived'=>true,
                'url'=>'cad-conteudo',
                'route'=>'',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Notícias',
                'icon'=>'fas fa-blog',
                'actived'=>true,
                'url'=>'posts',
                'route'=>'posts.index',
                'pai'=>'cad-conteudo'
            ],
            [
                'categoria'=>'',
                'description'=>'Documentos',
                'icon'=>'fas fa-file-word',
                'actived'=>true,
                'url'=>'documents',
                'route'=>'',
                'pai'=>'',
            ],
            [
                'categoria'=>'',
                'description'=>'Leis',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'leis',
                'route'=>'leis.index',
                'pai'=>'documents',
            ],
            [
                'categoria'=>'',
                'description'=>'Concursos',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'concursos',
                'route'=>'concursos.index',
                'pai'=>'documents',
            ],
            [
                'categoria'=>'',
                'description'=>'Decretos',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'decretos',
                'route'=>'decretos.index',
                'pai'=>'documents',
            ],
            [
                'categoria'=>'',
                'description'=>'Diários Oficiais',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'diarios',
                'route'=>'diarios.index',
                'pai'=>'documents',
            ],
            [
                'categoria'=>'',
                'description'=>'Portarias',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'portarias',
                'route'=>'portarias.index',
                'pai'=>'documents',
            ],
            [
                'categoria'=>'',
                'description'=>'Convênios',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'convenios',
                'route'=>'convenios.index',
                'pai'=>'documents',
            ],
            [
                'categoria'=>'',
                'description'=>'Paginas',
                'icon'=>'fas fa-file',
                'actived'=>true,
                'url'=>'pages',
                'route'=>'pages.index',
                'pai'=>'cad-conteudo'
            ],
            [
                'categoria'=>'',
                'description'=>'Arquivos',
                'icon'=>'fas fa-archive',
                'actived'=>true,
                'url'=>'box-archives',
                'route'=>'',
                'pai'=>'',
            ],
            [
                'categoria'=>'',
                'description'=>'Todos Arquivos',
                'icon'=>'fas fa-file',
                'actived'=>true,
                'url'=>'archives',
                'route'=>'archives.index',
                'pai'=>'box-archives',
            ],
            [
                'categoria'=>'',
                'description'=>'Categorias de arquivos',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'archives_category',
                'route'=>'archives_category.index',
                'pai'=>'box-archives',
            ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Sic',
            //     'icon'=>'fas fa-info',
            //     'actived'=>true,
            //     'url'=>'admin.sic',
            //     'route'=>'',
            //     'pai'=>''
            // ],
            [
                'categoria'=>'',
                'description'=>'Solicitações',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'sic',
                'route'=>'admin.sic.index',
                'pai'=>'admin.sic'
            ],
            [
                'categoria'=>'',
                'description'=>'Relatórios',
                'icon'=>'fas fa-chart-line',
                'actived'=>true,
                'url'=>'sic',
                'route'=>'admin.sic.relatorios',
                'pai'=>'admin.sic'
            ],
            [
                'categoria'=>'',
                'description'=>'Configurações',
                'icon'=>'fas fa-cog',
                'actived'=>true,
                'url'=>'sic',
                'route'=>'admin.sic.config',
                'pai'=>'admin.sic'
            ],
            [
                'categoria'=>'',
                'description'=>'Licitações',
                'icon'=>'fas fa-gavel',
                'actived'=>true,
                'url'=>'licitacoes',
                'route'=>'',
                'pai'=>''
            ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Processos',
            //     'icon'=>'fas fa-list',
            //     'actived'=>true,
            //     'url'=>'biddings',
            //     'route'=>'biddings.index',
            //     'pai'=>'licitacoes'
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Categorias',
            //     'icon'=>'fas fa-list',
            //     'actived'=>true,
            //     'url'=>'biddings_categories',
            //     'route'=>'biddings_categories.index',
            //     'pai'=>'licitacoes'
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Fases',
            //     'icon'=>'fas fa-list',
            //     'actived'=>true,
            //     'url'=>'biddings_phases',
            //     'route'=>'biddings_phases.index',
            //     'pai'=>'licitacoes'
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Modalidade',
            //     'icon'=>'fas fa-list',
            //     'actived'=>true,
            //     'url'=>'biddings_genres',
            //     'route'=>'biddings_genres.index',
            //     'pai'=>'licitacoes'
            // ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Tipos',
            //     'icon'=>'fas fa-list',
            //     'actived'=>true,
            //     'url'=>'biddings_types',
            //     'route'=>'biddings_types.index',
            //     'pai'=>'licitacoes'
            // ],
            [
                'categoria'=>'',
                'description'=>'Financeiro',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'financeiro',
                'route'=>'',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Extrato',
                'icon'=>'fas fa-file-excel',
                'actived'=>true,
                'url'=>'extrato',
                'route'=>'financeiro.extrato',
                'pai'=>'financeiro'
            ],
            [
                'categoria'=>'',
                'description'=>'Receitas',
                'icon'=>'fas fa-arrow-up',
                'actived'=>true,
                'url'=>'receitas',
                'route'=>'financeiro.receitas',
                'pai'=>'financeiro'
            ],
            [
                'categoria'=>'',
                'description'=>'Despesas',
                'icon'=>'fas fa-arrow-down',
                'actived'=>true,
                'url'=>'despesas',
                'route'=>'financeiro.despesas',
                'pai'=>'financeiro'
            ],
            // [
            //     'categoria'=>'',
            //     'description'=>'Configurações',
            //     'icon'=>'fas fa-cog',
            //     'actived'=>true,
            //     'url'=>'financeiro_cadastros',
            //     'route'=>'financeiro.index',
            //     'pai'=>'financeiro'
            // ],
            [
                'categoria'=>'SISTEMA',
                'description'=>'Configurações',
                'icon'=>'fas fa-cogs',
                'actived'=>true,
                'url'=>'config',
                'route'=>'sistema.config',
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
                'route'=>'perfil.show',
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
                'description'=>'Fornecedores',
                'icon'=>'fas fa-users',
                'actived'=>true,
                'url'=>'fornecedores',
                'route'=>'fornecedores.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Categorias de Receitas',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'cat_receitas',
                'route'=>'cat_receitas.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Categorias de Despesas',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'cat_despesas',
                'route'=>'cat_despesas.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Tipos de Receitas',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'tipo_receitas',
                'route'=>'tipo_receitas.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Tipos de Despesas',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'tipo_despesas',
                'route'=>'tipo_despesas.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Formas de pagamentos',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'f_pagamento',
                'route'=>'f_pagamento.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Contas',
                'icon'=>'fas fa-cash-register',
                'actived'=>true,
                'url'=>'contas',
                'route'=>'contas.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Permissões',
                'icon'=>'fas fa-list',
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
            [
                'categoria'=>'',
                'description'=>'Dados da Empresa',
                'icon'=>'fas fa-industry',
                'actived'=>true,
                'url'=>'enterprise',
                'route'=>'enterprise.index',
                'pai'=>'config'
            ],
        ]);
    }
}
