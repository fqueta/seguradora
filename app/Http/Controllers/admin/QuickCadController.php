<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LeilaoController;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class QuickCadController extends Controller
{
    public function __construct()
    {

    }
    /**
     * Metodo para gerir um cadastro rapido de leilao passo a passo
     */
    public function leilao(Request $request){
        $get = $request->all();
        $step = isset($get['step']) ? $get['step'] : 1;
        $cfg = isset($get['cfg']) ? $get['cfg'] : false;
        if(!$cfg && $step!=1){
            return redirect()->route('leiloes_adm.index');
        }
        $title = __('Cadastro de leilão');
        $title_card1 = false;
        $prev_page = false;
        $next_page = false;
        if($step){
            $titulo = __('Cadastro de leilão passo ').$step;
            if($step==1){
                $title_card1 = __('Informações do dono do leilão');
                $prev_page = route('leiloes_adm.index');
                $next_page = route('quick.add.leilao').'?cfg='.Qlib::encodeArray($cfg);
                $cfg = [
                    'prev_page' => '',
                    'next_page' => '',
                    'base_url' => route('quick.add.leilao'),
                ];
            }
            if($step==2){
                $cfg = Qlib::decodeArray($cfg);
                $title_card1 = __('Informações do contrato');
                $arr_contratos = [];
                if($user_id=@$cfg['user']['id']){
                    $arr_contratos = (new LeilaoController)->array_contratos($user_id);
                }
                $cfg['arr_contratos'] = $arr_contratos;
                $prev_page = route('quick.add.leilao').'?step=1&cfg='.Qlib::encodeArray([
                    'prev_page' => route('leiloes_adm.index'),
                    'next_page' => route('quick.add.leilao').'?step=2&cfg='.Qlib::encodeArray($cfg),
                ]);
                $next_page = route('quick.add.leilao').'?step=3&cfg='.Qlib::encodeArray($cfg);

            }
        }


        $ret = [
            'title'=>$title,
            'titulo'=>$titulo,
            'step'=>$step,
            'title_card1'=>$title_card1,
            'prev_page'=>$prev_page,
            'next_page'=>$next_page,
            'config'=>$cfg,
        ];
        return view('admin.leilao.quick_cad.leilao.create',$ret);
    }
    public function link_step2($user_id){
        //http://127.0.0.1:8000/admin/quick-cad/leilao?step=2&cfg=eyJjcGYiOiIxMjMuNDU2Ljc4OS0wOSIsImJhc2VfdXJsIjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3F1aWNrLWNhZC9sZWlsYW8iLCJ1c2VyIjp7ImlkIjozNywidGlwb19wZXNzb2EiOiJwZiIsIm5hbWUiOiJQYXRyaWNpYSBkZSB0ZXN0ZSIsInJhemFvIjpudWxsLCJjcGYiOiIxMjMuNDU2Ljc4OS0wOSIsImNucGoiOm51bGwsImVtYWlsIjoiZ2VyLm1haXNhcXVpOEBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZF9hdCI6IjIwMjMtMTAtMjRUMjI6MDA6MzguMDAwMDAwWiIsInN0YXR1cyI6ImFjdGl2ZWQiLCJnZW5lcm8iOiJuaSIsInZlcmlmaWNhZG8iOiJuIiwiaWRfcGVybWlzc2lvbiI6NSwiY3JlYXRlZF9hdCI6IjIwMjMtMDktMjNUMTE6NDg6MjYuMDAwMDAwWiIsInVwZGF0ZWRfYXQiOiIyMDIzLTEwLTI3VDIyOjU1OjAzLjAwMDAwMFoiLCJjb25maWciOnsiY2VsdWxhciI6IigxMykyNDU2Ni02MTMzIiwiY2VwIjoiMzYuMDMyLTU4MCIsImVuZGVyZWNvIjoiUnVhIERvdXRvciBDb3N0YSBSZWlzIiwibnVtZXJvIjoiNDUiLCJjb21wbGVtZW50byI6IjEyMiIsImNpZGFkZSI6Ikp1aXogZGUgRm9yYSIsInVmIjoiTUciLCJvcmlnZW0iOiJzaXRlIn0sInByZWZlcmVuY2lhcyI6bnVsbCwiZm90b19wZXJmaWwiOm51bGwsImF0aXZvIjoicyIsImF1dG9yIjozNywidG9rZW4iOiI2NTNjM2YyZGM0OWNiIiwiZXhjbHVpZG8iOiJuIiwicmVnX2V4Y2x1aWRvIjpudWxsLCJkZWxldGFkbyI6Im4iLCJyZWdfZGVsZXRhZG8iOm51bGx9fQ==
        $base_url = route('quick.add.leilao');
        $url = $base_url . '?step=2&cfg=';
        $d= User::find($user_id);
        $cfg=[];
        if($d->count() != 0){
            $d = $d->toArray();
            $cfg=[
                'cpf' => $d['cpf'],
                'base_url' => $base_url,
                'user' => $d,
            ];
        }
        $url .= Qlib::encodeArray($cfg);
        return $url;
    }
}
