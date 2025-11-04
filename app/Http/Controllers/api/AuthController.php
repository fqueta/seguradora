<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * 1|vohDWlfJFrs2l0mXJFX3SKxcBVcDZDhkkOqjfgtr3aab67aclad
 *
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $key = 'email';
        $credentials = [$key => $request->email, 'password' => $request->password, 'ativo' => 's', 'excluido' => 'n'];
        $logar = Auth::guard('web')->attempt($credentials, $request->filled('remember'));
        // if(Auth::guard('web')->attempt($request->only('email','password'))){
        if($logar){
            return  response()->json(['message'=>'Authorized','status'=>200,'data'=>[
                        'token'=> $request->user()->createToken('developer')->plainTextToken
                    ],
            ]);
        }else{
            return  response()->json(['message'=>'Sem Autorização','status'=>403]);

        }
    }
}
