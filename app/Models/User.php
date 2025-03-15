<?php

namespace App\Models;

use App\Qlib\Qlib;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    // use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'config' => 'array',
        'preferencias' => 'array',
    ];
    public function getRedirectRoute()
    {
        if($this->id_permission>=5){
            //Clientes
            $routa = url('/').'/' . Qlib::get_slug_post_by_id(37);
        }else{
            $routa = url('/').'/admin';
            //Admins
        }
        if(isset($_REQUEST['r']) && !empty($_REQUEST['r'])){
            $routa = $_REQUEST['r'];
        }
        return $routa;
        // return match((int)$this->role_id) {
        //     1 => 'student.dashboard',
        //     2 => 'teacher.dashboard',
        //     // ...
        // };
    }
}
