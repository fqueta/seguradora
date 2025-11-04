<?php

declare(strict_types=1);

use App\Http\Controllers\admin\AttachmentsController;
use App\Http\Controllers\admin\ClienteController;
use App\Http\Controllers\admin\ConfigController;
use App\Http\Controllers\admin\ContratoController;
use App\Http\Controllers\admin\FinanceiroController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\ImportController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ClientesController;
use App\Http\Controllers\api\SulAmericaController;
use App\Http\Controllers\EtapaController;
use App\Http\Controllers\portal\sicController;
use App\Http\Controllers\portalController;
use App\Http\Controllers\preview\PreviewController;
use App\Http\Controllers\TesteController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/
global $prefixo_admin,$prefixo_site;
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Auth::routes();
    Route::fallback(function () {
        if(Auth::check()){
            return view('erro404');
        }else{
            return view('erro404_site');
        }
    });
    $prefixo_admin = config('app.prefixo_admin');
    $prefixo_site = config('app.prefixo_site');
    Route::get('/', [App\Http\Controllers\site\SiteController::class, 'home'])->name('home');
    Route::get('/test', [App\Http\Controllers\site\SiteController::class, 'test_portal'])->name('teste.site');
    Route::prefix($prefixo_site.'internautas')->group(function(){
        Route::get('/',[App\Http\Controllers\portalController::class, 'index'])->name('internautas.index');
        Route::get('/cadastrar/{tipo}',[portalController::class, 'cadInternautas'])->name('cad.internautas');
        Route::post('/cadastrar',[portalController::class,'storeInternautas'])->name('internautas.store');
        Route::get('/cadastrar/ac/{tipo}/{id}',[portalController::class,'acaoInternautas'])->name('internautas.acao.get');
        Route::get('/login',[portalController::class,'loginInternautas'])->name('internautas.login');
        Route::get('/quick-login',[portalController::class,'quick_login'])->name('internautas.quick_login');
        Route::get('/logout',[portalController::class,'logoutInternautas'])->name('internautas.logout');
        Route::get('/send-verific-user',[portalController::class,'send_verific_user'])->name('send_verific_user');
        Route::resource('sic','\App\Http\Controllers\portal\sicController',['parameters' => [
            'sic' => 'id'
        ]])->middleware('auth');
        Route::get('sics',[sicController::class,'relatorios'])->name('sic.internautas.relatorios');
    });

    Route::prefix('/'.$prefixo_admin)->group(function(){
        Route::get('/', [HomeController::class,'index'])->name('home.admin');
        Route::prefix('users')->group(function(){
            Route::get('/',[UserController::class,'index'])->name('users.index');

            Route::get('/ajax',[UserController::class,'paginacaoAjax'])->name('users.ajax');
            Route::get('/lista.ajax',function(){
                return view('users.index_ajax');
            });

            Route::get('/create',[UserController::class,'create'])->name('users.create');
            Route::post('/',[UserController::class,'store'])->name('users.store');
            Route::get('/{id}/show',[UserController::class,'show'])->where('id', '[0-9]+')->name('users.show');
            Route::get('/{id}/edit',[UserController::class,'edit'])->where('id', '[0-9]+')->name('users.edit');
            Route::put('/{id}',[UserController::class,'update'])->where('id', '[0-9]+')->name('users.update');
            Route::delete('/{id}',[UserController::class,'destroy'])->where('id', '[0-9]+')->name('users.destroy');
        });
        Route::resource('fornecedores','\App\Http\Controllers\UserController',['parameters' => [
            'fornecedores' => 'id'
        ]]);
        Route::resource('posts','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'posts' => 'id'
        ]]);
        Route::resource('cat_receitas','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'cat_receitas' => 'id'
        ]]);
        Route::resource('cat_despesas','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'cat_despesas' => 'id'
        ]]);
        Route::resource('tipo_receitas','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'tipo_receitas' => 'id'
        ]]);
        Route::resource('tipo_despesas','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'tipo_despesas' => 'id'
        ]]);
        Route::resource('contas','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'contas' => 'id'
        ]]);
        Route::resource('f_pagamento','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'f_pagamento' => 'id'
        ]]);
        Route::resource('api-wp','\App\Http\Controllers\wp\ApiWpController',['parameters' => [
            'api-wp' => 'id'
        ]]);
        Route::resource('pages','\App\Http\Controllers\admin\PostsController',['parameters' => [
            'pages' => 'id'
        ]]);
        Route::resource('documentos','\App\Http\Controllers\DocumentosController',['parameters' => [
            'documentos' => 'id'
        ]]);
        Route::resource('qoptions','\App\Http\Controllers\admin\QoptionsController',['parameters' => [
            'qoptions' => 'id'
        ]]);
        Route::resource('permissions','\App\Http\Controllers\admin\UserPermissions',['parameters' => [
            'permissions' => 'id'
        ]]);
        Route::resource('tags','\App\Http\Controllers\admin\TagsController',['parameters' => [
            'tags' => 'id'
        ]]);
        //inicio Rotas módulo Sic
        // Route::resource('sic','\App\Http\Controllers\admin\sicController',['as'=>'admin','parameters' => ['sic' => 'id']]);
        // Route::get('sics/relatorios', ['\App\Http\Controllers\admin\sicController', 'relatorios'])->name('admin.sic.relatorios');
        // Route::get('sics/config', ['\App\Http\Controllers\admin\sicController', 'config'])->name('admin.sic.config');
        // Route::get('sics/config/{url}', ['\App\Http\Controllers\admin\sicController', 'config'])->name('admin.sic.config.edit');
        //Fim Rotas módulo Sic
        Route::prefix('uploads')->group(function(){
            Route::get('/',[uploadController::class,'index'])->name('uploads.index');
            Route::get('/create',[UploadController::class,'create'])->name('uploads.create');
            Route::post('/',[UploadController::class,'store'])->name('uploads.store');
            Route::get('/{id}/show',[UploadController::class,'show'])->name('uploads.show');
            Route::get('/{id}/edit',[UploadController::class,'edit'])->name('uploads.edit');
            Route::put('/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('uploads.update');
            Route::post('/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('uploads.update-ajax');
            Route::post('/{id}/delete',[UploadController::class,'destroy'])->where('id', '[0-9]+')->name('uploads.destroy');
            Route::get('export/all', [UploadController::class, 'exportAll'])->name('uploads.export_all');
            Route::get('export/filter', [UploadController::class, 'exportFilter'])->name('uploads.export_filter');
        });
        Route::prefix('ajax')->group(function(){
            Route::post('/attachments/{id}',[AttachmentsController::class,'update'])->where('id', '[0-9]+')->name('attachments.update-ajax');
            Route::post('/chage_status',[ConfigController::class,'chage_status'])->name('chage_status');
            Route::post('/cliente/reativar/{token}',[ContratoController::class,'reativar'])->name('cliente.reativar');
            Route::post('/cliente/cancelar/{token}',[ContratoController::class,'cancelar'])->name('cliente.cancelar');
            Route::post('/cliente/delete/{id}',[ClienteController::class,'destroy'])->name('cliente.delete');
            // Route::post('/attachments/{id}',[AttachmentsController::class,'update'])->where('id', '[0-9]+')->name('attachments.destroy-ajax');
        });
        Route::fallback(function () {
            return view('erro404');
        });
        Route::resource('media','\App\Http\Controllers\admin\mediaController',['parameters' => [
            'media' => 'id'
        ]]);
        Route::prefix('media')->group(function(){
            Route::post('/store-parent',['\App\Http\Controllers\admin\mediaController','storeParent'])->name('store.parent.media');
            Route::post('/trash',['\App\Http\Controllers\admin\mediaController','trash'])->name('trash.media');
            // Route::get('/ajax',[App\Http\Controllers\TesteController::class,'ajax'])->name('teste.ajax');
        });
        Route::prefix('/financeiro')->group(function(){
            Route::resource('receitas', '\App\Http\Controllers\admin\FinanceiroController', ['parameters'=>[
                'receitas' => 'id'
            ]]);
            Route::resource('despesas', '\App\Http\Controllers\admin\FinanceiroController', ['parameters'=>[
                'despesas' => 'id'
            ]]);
            Route::resource('extrato', '\App\Http\Controllers\admin\FinanceiroController', ['parameters'=>[
                'extrato' => 'id'
            ]]);
            // Route::get('receitas', [FinanceiroController::class,'receitas'])->name('financeiro.receitas');
            Route::get('despesas', [FinanceiroController::class,'despesas'])->name('financeiro.despesas');
            Route::get('extrato', [FinanceiroController::class,'extrato'])->name('financeiro.extrato');
        });

        Route::get('/test', [TesteController::class,'index']);
        // Route::resource('/docfile', '\App\Http\Controllers\admin\DocfileController',['parameters' => [
        //     'docfile' => 'id'
        // ]]);
        Route::resource('/menus', '\App\Http\Controllers\admin\PostsController',['parameters' => [
            'menus' => 'id'
        ]]);
        Route::resource('/paginas', '\App\Http\Controllers\admin\PostsController',['parameters' => [
            'paginas' => 'id'
        ]]);
        Route::resource('/componentes', '\App\Http\Controllers\admin\PostsController',['parameters' => [
            'componentes' => 'id'
        ]]);
        Route::resource('/categorias', '\App\Http\Controllers\admin\PostsController',['parameters' => [
            'categorias' => 'id'
        ]]);
        // Route::resource('/portarias', '\App\Http\Controllers\admin\PostsController',['parameters' => [
        //     'portarias' => 'id'
        // ]]);
        // Route::resource('/secretarias', '\App\Http\Controllers\admin\PostsController',['parameters' => [
        //     'secretarias' => 'id'
        // ]]);
        // Route::resource('/servidores', '\App\Http\Controllers\UserController',['parameters' => [
        //     'servidores' => 'id'
        // ]]);
        Route::resource('/convenios', '\App\Http\Controllers\admin\PostsController',['parameters' => [
            'convenios' => 'id'
        ]]);
        Route::resource('/clientes', '\App\Http\Controllers\admin\ClienteController',['parameters' => [
            'clientes' => 'id'
        ]]);
        Route::get('/clientes/importar',[ImportController ::class,'form_import'])->name('clientes.import');
        Route::post('/clientes/importar',[ClienteController::class,'importar'])->name('clientes.post_import');
        Route::resource('/contratos', '\App\Http\Controllers\admin\PostsController',['parameters' => [
            'contratos' => 'id'
        ]]);
        Route::resource('/archives_category', '\App\Http\Controllers\admin\DefaultController',['parameters' => [
            'archives_category' => 'id'
        ]]);
        Route::resource('/enterprise', '\App\Http\Controllers\admin\ConfigController',['parameters' => [
            'enterprise' => 'id'
        ]]);
        Route::get('/perfil',[UserController::class,'perfilShow'])->name('perfil.index');
        Route::get('/perfil/show',[UserController::class,'perfilShow'])->name('perfil.show');
        Route::get('/perfil/create',[UserController::class,'perfilShow'])->name('perfil.create');
        Route::get('/perfil/edit/{id}',[UserController::class,'perfilEdit'])->name('perfil.edit');
        Route::put('/perfil/update/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('perfil.update');

        Route::get('/config',[EtapaController::class,'config'])->name('sistema.config');
        // Route::post('/{id}',[EtapaController::class,'update'])->where('id', '[0-9]+')->name('sistema.update-ajax');
        Route::post('/import', [ImportController::class, 'import'])->name('import');

    });

    Route::get('/suspenso',[UserController::class,'suspenso'])->name('cobranca.suspenso');
    Route::prefix('sistema')->group(function(){
        Route::get('/pefil',[UserController::class,'perfilShow'])->name('sistema.perfil');
        Route::get('/perfil/edit',[UserController::class,'perfilEdit'])->name('sistema.perfil.edit');
        Route::post('/perfil/store',[UserController::class,'perfilStore'])->name('sistema.perfil.store');
        // Route::post('/perfil/index',[UserController::class,'perfilIndex'])->name('perfil.index');
        // Route::get('/config',[EtapaController::class,'config'])->name('sistema.config');
        // Route::post('/{id}',[EtapaController::class,'update'])->where('id', '[0-9]+')->name('sistema.update-ajax');
    });
    // Route::prefix('preview')->group(function(){
    //     Route::get('/posts/{id}',[PreviewController::class,'posts'])->name('preview.posts');
    //     Route::get('/noticias',[PreviewController::class,'noticias'])->name('preview.noticias');
    // });
    Route::prefix('cobranca')->group(function(){
        Route::get('/fechar',[UserController::class,'pararAlertaFaturaVencida'])->name('alerta.cobranca.fechar');
    });

        //inicio Rotas de verificação
    Route::get('/email/verify', function () {
        // return view('auth.verify');
        return view('site.index');
    })->middleware('auth')->name('verification.notice');


    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function ( Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message-very', 'enviado');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
});

Route::name('api.')->prefix('api/v1')->middleware([
    'api',
    // 'auth:sanctum',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::fallback(function () {
        return view('erro404_site');
    });
    Route::post('/contratar',[ SulAmericaController::class,'contratar'])->name('contratar');
    Route::post('/cancelar',[ SulAmericaController::class,'cancelar'])->name('cancelar');
    // Route::resource('/contratacao', '\App\Http\Controllers\api\SulAmericaController', ['only' => ['index']]);
    // Route::get('/contratacao',[SulAmericaController::class,'contratacao'])->name('contataocao.sulamerica');
    // Route::resource('/documents', '\App\Http\Controllers\api\PostController', ['only' => ['index','show']]);
    Route::post('/login',[AuthController::class,'login']);
    // Route::middleware('auth:sanctum')->get('/user', [AuthController::class,'user']);
    Route::middleware('auth:sanctum')->post('/clientes', [ClientesController::class,'store']);
    Route::middleware('auth:sanctum')->post('/clientes-update/{cpf}', [ClientesController::class,'update']);
    // Route::middleware('auth:sanctum')->put('/clientes/{cpf}', [ClientesController::class,'update']);
    Route::middleware('auth:sanctum')->get('/clientes/{cpf}', [ClientesController::class,'show']);
    Route::middleware('auth:sanctum')->delete('/clientes/{id}', [ClientesController::class,'destroy']);
    Route::middleware('auth:sanctum')->post('/clientes-cancelar/{id}', [ClientesController::class,'cancelar_contrato']);
});
