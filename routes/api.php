<?php

use App\Http\Controllers\admin\OrcamentoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::resource('Webhook/{slug}', WebhookController::class);
Route::post('/webhook/{slug}',[WebhookController::class,'index']);
Route::post('/get-rab',[OrcamentoController ::class,'get_rab'])->name('orcamento.rab');
Route::get('/orcamento-zap',[OrcamentoController ::class,'orcamento_zap'])->name('orcamento.zap');
