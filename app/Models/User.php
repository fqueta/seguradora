<?php

namespace App\Models;

use App\Qlib\Qlib;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // protected $connection = 'tenant';
    protected $fillable = [
        'tipo_pessoa',
        'name',
        'razao',
        'email',
        'password',
        'cpf',
        'cnpj',
        'status',
        'genero',
        'token',
        'foto_perfil',
        'verificado',
        'id_permission',
        'config',
        'preferencias',
        'ativo',
        'autor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'config' => 'array',
        'preferencias' => 'array',
    ];
    // private function dUser(){
    //     $dados = Qlib::dados_tab_SERVER('contas_usuarios');
    //     $usuario = isset($dados[0]['usuario']) ? $dados[0]['usuario']:'demo';
    //     return $usuario;
    // }


}
