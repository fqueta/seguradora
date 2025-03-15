<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Qlib\Qlib;
use App\Rules\FullName;
use App\Rules\RightCpf;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    public function init(Array $data){
        return $this->create($data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(Array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255',new FullName],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cpf'   =>[new RightCpf, 'unique:users']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $dados)
    {
         //REGISTRAR EVENTO
        //  $regev = Qlib::regEvent(['action'=>'create','tab'=>'user','config'=>[
        //      'obs'=>'Usuario se cadastrou pelo site',
        //      'link'=>'',
        //      ]
        // ]);
        // $ds = [
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        //     'status' => 'pre_registred',
        //     'cpf' => @$data['cpf'],
        //     'id_permission' => '5',
        //     'tipo_pessoa' => 'pf',
        // ];
        // dd($ds);
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'s';
        $dados['id_permission'] = 5;
        if(isset($dados['cpf']) && !empty($dados['cpf'])){
            $cpf = str_replace('.','',$dados['cpf']);
            $cpf = str_replace('-','',$cpf);
            $dados['password'] = $cpf;
        }
        if(isset($dados['password']) && !empty($dados['password'])){
            $dados['password'] = Hash::make($dados['password']);
        }else{
            if(empty($dados['password'])){
                unset($dados['password']);
            }
        }
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        // dd($dados);
        $salvar = User::create($dados);
        $dados['id'] = $salvar->id;
        // $route = $this->routa.'.index';
        // $ret = User::create($ds);
        $ret = [
            'mens'=>'Usuário cadastrada com sucesso!',
            'color'=>'success',
            'idCad'=>$salvar->id,
            'exec'=>true,
            'dados'=>$dados,
        ];
        if($ret){
           // $request->user()->sendEmailVerificationNotification();
        }
        if($ajax=='s'){
            // $ret['reg'] = $this->register(Request $request);
            return true;
            return response()->json($ret);
        }else{
            return $ret;
        }
    }
}
