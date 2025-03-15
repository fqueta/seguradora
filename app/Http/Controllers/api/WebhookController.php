<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\admin\AsaasController;
use App\Http\Controllers\admin\ContratosController;
use App\Http\Controllers\admin\ZapsingController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request){
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $seg3 = request()->segment(3);
        $ret = false;
        if($seg3=='asaas'){
            $ret = (new AsaasController)->webhook($request->all());
        }elseif($seg3=='contratos'){
            $ret = (new ContratosController)->webhook($request->all());
        }elseif($seg3=='zapsing'){
            $ret = (new ZapsingController)->webhook($request->all());
        }
        return $ret;
    }
}
