<?php

use App\Http\Controllers\admin\EventController;
use App\Http\Controllers\admin\OrcamentoController;
use App\Http\Controllers\admin\PdfController;
use App\Http\Controllers\admin\QuickCadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BairroController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\EtapaController;
use App\Http\Controllers\EstadocivilController;
use App\Http\Controllers\SulAmericaController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RelatoriosController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::fallback(function () {
    return view('erro404');
});
// if (env('APP_ENV') == 'production') {
//     URL::forceSchema('https');
// }




Auth::routes();
Auth::routes(['register' => false]);
Route::get('/solicitar-autorizacao', [SulAmericaController::class, 'solicitarAutorizacao']);
Route::get('/consultar-guia', [SulAmericaController::class, 'consultarGuia']);
Route::prefix('admin')->group(function(){
    Route::prefix('quick-cad')->group(function(){
        Route::get('/leilao',[QuickCadController::class,'leilao'])->name('quick.add.leilao');
    });
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
    Route::prefix('estado-civil')->group(function(){
        Route::get('/',[EstadocivilController::class,'index'])->name('estado-civil.index');
        Route::get('/create',[EstadocivilController::class,'create'])->name('estado-civil.create');
        Route::post('/',[EstadocivilController::class,'store'])->name('estado-civil.store');
        Route::get('/{id}/show',[EstadocivilController::class,'show'])->name('estado-civil.show');
        Route::get('/{id}/edit',[EstadocivilController::class,'edit'])->name('estado-civil.edit');
        Route::put('/{id}',[EstadocivilController::class,'update'])->where('id', '[0-9]+')->name('estado-civil.update');
        Route::post('/{id}',[EstadocivilController::class,'update'])->where('id', '[0-9]+')->name('estado-civil.update-ajax');
        Route::delete('/{id}',[EstadocivilController::class,'destroy'])->where('id', '[0-9]+')->name('estado-civil.destroy');
    });
    Route::prefix('relatorios')->group(function(){
        Route::get('/',[RelatoriosController::class,'index'])->name('relatorios.index');
        Route::get('/social',[RelatoriosController::class,'realidadeSocial'])->name('relatorios.social');
        Route::get('/acessos',[EventController::class,'listAcessos'])->name('relatorios.acessos');
        Route::get('export/filter', [RelatoriosController::class, 'exportFilter'])->name('relatorios.export_filter');
        //Route::post('/',[RelatoriosController::class,'store'])->name('relatorios.store');
        //Route::get('/{id}/show',[RelatoriosController::class,'show'])->name('relatorios.show');
        //Route::get('/{id}/edit',[RelatoriosController::class,'edit'])->name('relatorios.edit');
        //Route::put('/{id}',[RelatoriosController::class,'update'])->where('id', '[0-9]+')->name('relatorios.update');
        //Route::post('/{id}',[RelatoriosController::class,'update'])->where('id', '[0-9]+')->name('relatorios.update-ajax');
        //Route::delete('/{id}',[RelatoriosController::class,'destroy'])->where('id', '[0-9]+')->name('relatorios.destroy');
    });
    Route::prefix('sistema')->group(function(){
        Route::get('/pefil',[UserController::class,'perfilShow'])->name('sistema.perfil');
        Route::get('/perfil/edit',[UserController::class,'perfilEdit'])->name('sistema.perfil.edit');
        Route::post('/perfil/store',[UserController::class,'perfilStore'])->name('sistema.perfil.store');
        Route::get('/config',[EtapaController::class,'config'])->name('sistema.config');
        Route::post('/{id}',[EtapaController::class,'update'])->where('id', '[0-9]+')->name('sistema.update-ajax');
    });
    Route::prefix('uploads')->group(function(){
        Route::get('/',[uploadController::class,'index'])->name('uploads.index');
        Route::get('/create',[UploadController::class,'create'])->name('uploads.create');
        Route::post('/',[UploadController::class,'store'])->name('uploads.store');
        Route::get('/{id}/show',[UploadController::class,'show'])->name('uploads.show');
        Route::get('/{id}/edit',[UploadController::class,'edit'])->name('uploads.edit');
        Route::put('/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('uploads.update');
        Route::post('/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('uploads.update-ajax');
        Route::post('/{id}',[UploadController::class,'destroy'])->where('id', '[0-9]+')->name('uploads.destroy');
        Route::get('export/all', [UploadController::class, 'exportAll'])->name('uploads.export_all');
        Route::get('export/filter', [UploadController::class, 'exportFilter'])->name('uploads.export_filter');
    });
    Route::get('/', [App\Http\Controllers\admin\homeController::class, 'index'])->name('home');
    // Route::get('menu/{id}', [App\Http\Controllers\HomeController::class, 'menu'])->name('menu');
    Route::prefix('teste')->group(function(){
        Route::get('/',[App\Http\Controllers\TesteController::class,'index'])->name('teste');
        Route::get('/ajax',[App\Http\Controllers\TesteController::class,'ajax'])->name('teste.ajax');
    });

    Route::resource('produtos','\App\Http\Controllers\admin\PostController',['parameters' => [
        'produtos' => 'id'
    ]]);
    Route::resource('orcamentos','\App\Http\Controllers\admin\PostController',['parameters' => [
        'orcamentos' => 'id'
    ]]);
    Route::prefix('orcamentos')->group(function(){
        Route::get('/ta/{id}',[OrcamentoController::class,'termo_aceito'])->name('termo.aceito');
        // Route::get('/ajax',[App\Http\Controllers\TesteController::class,'ajax'])->name('teste.ajax');
    });

    Route::resource('media','\App\Http\Controllers\admin\mediaController',['parameters' => [
        'media' => 'id'
    ]]);
    Route::prefix('media')->group(function(){
        Route::post('/store-parent',['\App\Http\Controllers\admin\mediaController','storeParent'])->name('store.parent.media');
        Route::post('/trash',['\App\Http\Controllers\admin\mediaController','trash'])->name('trash.media');
        // Route::get('/ajax',[App\Http\Controllers\TesteController::class,'ajax'])->name('teste.ajax');
    });
    // Route::resource('pacotes_lances','\App\Http\Controllers\admin\PostController',['parameters' => [
    //     'pacotes_lances' => 'id'
    // ]]);
    Route::resource('paginas','\App\Http\Controllers\admin\PostController',['parameters' => [
        'paginas' => 'id'
    ]]);
    Route::resource('categorias','\App\Http\Controllers\admin\categoryController',['parameters' => [
        'categorias' => 'id'
    ]]);
    // Route::resource('leiloes_adm','\App\Http\Controllers\admin\PostController',['parameters' => [
    //     'leiloes_adm' => 'id'
    // ]]);
    Route::resource('componentes','\App\Http\Controllers\admin\PostController',['parameters' => [
        'componentes' => 'id'
    ]]);
    Route::resource('documentos','\App\Http\Controllers\DocumentosController',['parameters' => [
        'documentos' => 'id'
    ]]);
    Route::resource('qoptions','\App\Http\Controllers\admin\QoptionsController',['parameters' => [
        'qoptions' => 'id'
    ]]);
    Route::resource('tags','\App\Http\Controllers\admin\TagsController',['parameters' => [
        'tags' => 'id'
    ]]);
    Route::resource('permissions','\App\Http\Controllers\admin\UserPermissions',['parameters' => [
        'permissions' => 'id'
    ]]);
    Route::resource('menus','\App\Http\Controllers\admin\PostController',['parameters' => [
        'menus' => 'id'
    ]]);
    //rotas para testes
    Route::get('/salvar-pdf', [PdfController::class, 'salvarPdf'])->middleware('auth')->name('salvar.pdf');
    Route::get('/gerar-pdf', [PdfController::class, 'gerarPdf'])->middleware('auth')->name('gerar.pdf');

});
Route::get('/', [App\Http\Controllers\siteController::class, 'index'])->name('index');

Route::resource('/leiloes','\App\Http\Controllers\LeilaoController',['parameters' => [
    'leiloes' => 'id'
]]);
Route::resource('/users_site','\App\Http\Controllers\UserController',['parameters' => [
    'users_site' => 'id'
]]);
Route::get('/leiloes/get-data-contrato/{token}', [App\Http\Controllers\LeilaoController::class, 'view_data_contrato'])->name('data.contrato');
Route::get('/leiloes/list-contratos/{id}', [App\Http\Controllers\LeilaoController::class, 'view_list_contrato'])->name('list.contrato');

// Route::get('/test-email',[EmailController::class,'sendEmailTest']);
Route::get('/suspenso',[UserController::class,'suspenso'])->name('cobranca.suspenso');
Route::prefix('cobranca')->group(function(){
    Route::get('/fechar',[UserController::class,'pararAlertaFaturaVencida'])->name('alerta.cobranca.fechar');
});
// Route::get('/seed-database', function(){
//     DB::unprepared(
//         file_get_contents(base_path() . './laravel8.sql')
//     );
// });
Route::get('envio-mails',function(){
    $user = new stdClass();
    $user->name = 'Fernando Queta';
    $user->email = 'ger.maisaqui3@gmail.com';
    return new \App\Mail\dataBrasil($user);
    // $enviar = Mail::send(new \App\Mail\dataBrasil($user));
    // return $enviar;
});
Route::resource('lances','\App\Http\Controllers\LanceController',['parameters' => [
    'lances' => 'id'
]]);
// Route::prefix('user')->group(function(){
//     Route::get('/login',[App\Http\Controllers\UserController::class,'user_login_jwt'])->name('user.login.jwt');
// });
Route::prefix('ajax')->group(function(){
    // Route::post('/excluir-reserva-lance',[App\Http\Controllers\LanceController::class,'excluir_reserva']);
    // Route::post('/ger-seguidores',[App\Http\Controllers\LeilaoController::class,'ger_seguidores']);
    Route::post('/notification',[App\Http\Controllers\NotificationController::class,'receive_ajax']);
    // Route::post('/session-m',[App\Http\Controllers\admin\sessionController::class,'sessionManagerAction']);
    // Route::post('/reciclar-leilao/{leilao_id}',[LeilaoController::class,'reciclar'])->name('leiloes.reciclar');
    // Route::post('/tornar-vencedor',[LanceController::class,'tornar_vencedor'])->name('leiloes.tornar_vencedor');
    Route::post('/enviar-contato',[ContatoController::class,'enviar_contato'])->name('enviar.contato');
    // Route::post('/pre-cadastro-escola',[UserController::class,'pre_cadastro_escola'])->name('user.pre_cadastro_escola');
    Route::post('/get-rab',[OrcamentoController ::class,'get_rab'])->name('ajax.orcamento.rab');
    Route::get('/get-aeronave/{matricula}',[OrcamentoController ::class,'get_info_by_matricula'])->name('ajax.get_aeronave');
    Route::post('/enviar-agendamento',[OrcamentoController ::class,'enviar_orcamento'])->name('orcamento.enviar');
    Route::post('/send-to-zapsing',[OrcamentoController ::class,'sendToZapsing'])->name('send.zapsing');
});
Route::prefix('notification')->group(function(){
    Route::get('/index',[App\Http\Controllers\NotificationController::class,'index'])->name('notification.index');
    // Route::post('/notification',[App\Http\Controllers\NotificationController::class,'receive_ajax']);
});
//inicio Rotas de verificação
Route::get('/email/verify', function () {
    // return view('auth.verify');
    return view('site.index');
})->middleware('auth')->name('verification.notice');
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message-very', 'enviado');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

//Routa para postar o pagamento.
Route::post('/payment',[PaymentController::class,'init'])->name('payment');

//Fim rotas de verificação

Route::get('/{slug}', [App\Http\Controllers\siteController::class, 'index'])->name('site.index');
Route::get('/{slug}/{id}', [App\Http\Controllers\siteController::class, 'index'])->name('site.index2');
Route::get('/{slug}/{id}/{sec}', [App\Http\Controllers\siteController::class, 'index'])->name('site.index3');
Route::get('/{slug}/{id}/{sec}/{token}', [App\Http\Controllers\siteController::class, 'index'])->name('site.index4');
