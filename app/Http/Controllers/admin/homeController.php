<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LeilaoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Qlib\Qlib;

class homeController extends Controller
{
    protected $user;
    public function __construct(User $user)
    {
        $this->middleware('auth');
        $this->user = $user;
    }

    public function index()
    {
        /*$controlerFamilias = new FamiliaController(Auth::user());
        $dadosFamilias = $controlerFamilias->queryFamilias();
        */

        // $blacklist=(new BlacklistController())->get_blacklist();
        $lc = new LeilaoController;
        $orc = new OrcamentoController;
        $uc = new UserController;
        $fnp = $lc->finalizados_nao_pagos();
        $card_top = [
            'finalizados' => ['label' => __('Orçamentos aguardando'),'icon' => 'fas fa-gavel','link' => url('/admin/orcamentos?limit=100&order=desc&filter[ID]=&filter[post_status]=aguardando'),'value' => $orc->total_orcamentos('aguardando'),'color' => 'bg-info','title'=>''],
            'andamento' => ['label' => __('Orçamentos em aprovado'),'icon' => 'fas fa-gavel','link' => url('/admin/orcamentos?limit=100&order=desc&filter[ID]=&filter[post_status]=orcamento_aprovado'),'value' => $orc->total_orcamentos('orcamento_aprovado'),'color' => 'bg-success','title'=>''],
            'cadastrados' => ['label' => __('Usuários cadastrados'),'icon' => 'fas fa-users','link' => route('users.index'),'value' => $uc->total(),'color' => 'bg-warning','title'=>''],
            'total' => ['label' => __('Orçamentos não pagos'),'icon' => 'fa fa-cash','event'=>'','link' => url('/admin#list-finalizados'),'value' => Qlib::valor_moeda(@$fnp['total_apagar'],'R$'),'color' => 'bg-danger','title'=>'Contratos de leilões arrematados e não pagos'],
        ];
        // dd($card_top);
        $config = [
            'card_top' => $card_top,
            'lista_leilao_terminado' => $lc->lista_leilao_terminado(),
            'blacklist'=>$ret = (new BlacklistController())->get_blacklist(),
        ];
        // dd($config);
        return view('home',[
            'config'=>$config,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
