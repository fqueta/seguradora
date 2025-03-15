<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContatoController extends Controller
{
    public function __construct()
    {

    }
    public function enviar_contato(Request $request){
        $ret['exec'] = false;
        $d = $request->all();
        if(is_array($d)){
            foreach ($d as $key => $value) {
                if($key == 'email'){
                    //validacao email
                }elseif($key == 'nome'){
                    //validacao nome
                    $d[$key] = strip_tags($value);
                }elseif($key == 'obs'){
                    //validacao nome
                    $d[$key] = strip_tags($value);
                }
            }
        }
        $ret['d'] = $d;
        //Sanitizar dados
        //envar para CRM
        //Enviar email
        return $ret;
    }
    public function enviar_CRM($dados){

    }
}
