<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class sessionController extends Controller
{
    public function sessionManagerAction(Request $request){
        $ret = false;
        $d = $request->all();
        $ac = isset($d['ac']) ? $d['ac'] : false;
        $key = isset($d['key']) ? $d['key'] : false;
        $value = isset($d['value']) ? $d['value'] : false;
        if($ac && $key){
            $var = $request->session()->exists($key);
            if($ac!='forget'){
                if($var){
                    $ac = 'pull';
                }else{
                    $ac = 'put';
                }
            }
            if($ac=='put'){
                //Adiciona
                $ret = $request->session()->put($key,$value);
            }
            if($ac=='pull'){
                //Atualiza
                $ret = $request->session()->pull($key,$value);
            }
            if($ac=='forget'){
                //remove
                $ret = $request->session()->forget($key);
            }
            dd($var,$key,$value,$ac,$request->session()->all());
        }
        return $ret;
    }
}
