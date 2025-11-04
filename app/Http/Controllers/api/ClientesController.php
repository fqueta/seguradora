<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\admin\ClienteController;
use App\Http\Controllers\admin\ContratoController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClientesRequestApi;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateClientesRequest;
use App\Http\Requests\UpdateClientesRequestApi;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function store_b(StoreClientesRequestApi $request)
    {
        // Se chegou aqui, os dados já foram validados
        $dados = $request->validated();

        // Lógica de criação
        return response()->json(['mensagem' => 'Usuário criado com sucesso.'], 201);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientesRequestApi $request)
    {
        // $user = Auth::user();
        $user_id = Auth::id();
        if($user_id){
            $d = $request->all();
            $ret['exec'] = false;
            $ret['status'] = 400;
            $ret['response'] = 'Erro ao salvar';
            $ret['data'] = $d;
            // $ret['user'] = $user;
            // return $ret;
            $produtoParceiro = Qlib::qoption('produtoParceiro') ? Qlib::qoption('produtoParceiro') : 10232;

            $arr_campos = [
                'name'=>'nome',
                'cpf'=>'cpf',
                'genero'=>'sexo',
                'config'=>[
                    'celular'=>'celular',
                    'telefone_residencial'=>'telefone_residencial',
                    'telefone_comercial'=>'telefone_comercial',
                    'rg'=>'rg',
                    'nascimento'=>'nascimento',
                    'cep'=>'cep',
                    'endereco'=>'endereco',
                    'numero'=>'numero',
                    'complemento'=>'complemento',
                    'bairro'=>'bairro',
                    'cidade'=>'cidade',
                    'uf'=>'uf',
                    'inicioVigencia'=>'inicio_vigencia',
                    'fimVigencia'=>'fim_vigencia',
                ],
            ];
            $ds=[];
            foreach ($arr_campos as $key => $val) {
                if(is_array($val)){
                    foreach ($val as $k1 => $va1) {
                        if(isset($d[$va1])){
                            $ds[$key][$k1] = $d[$va1];
                        }
                    }
                }else{
                    if(isset($d[$val])){
                        $ds[$key] = $d[$val];
                    }
                }
            }
            $ds['config']['id_produto'] = isset($ds['id_produto']) ? $ds['id_produto'] : $produtoParceiro;
            $ds['config']['nome_fantasia'] = isset($ds['nome_fantasia']) ? $ds['nome_fantasia'] : null;
            $ds['ativo'] = isset($ds['ativo']) ? $ds['ativo'] : 's';

            // $ret['d'] = $ds;
            if(count($ds)>1){
                $inicioVigencia = isset($ds['config']['inicioVigencia']) ? $ds['config']['inicioVigencia'] : null;
                // dd($inicioVigencia);
                if($inicioVigencia){
                    $str_inicioVigencia = strtoupper($inicioVigencia);
                    $str_hoje = strtoupper(date('Y-m-d'));
                    // dd($str_inicioVigencia,$str_hoje,($str_inicioVigencia<$str_hoje));
                    if($str_inicioVigencia<$str_hoje){
                        $ret['response'] = 'Data de início de vigência inválida.\n';
                        return $ret;
                    }
                }
                $ds['autor'] = $user_id;
                $ds['id_permission'] = Qlib::qoption('id_permission_clientes');
                $ds['token'] = uniqid();
                $salv = (new ClienteController())->salvar_clientes($ds,true);
                if(isset($salv->original)){
                    $ret = $salv->original;
                    unset($ret['data']['valorPremio']);
                    $ret['status'] = 200;
                }
            }


        }else{

        }
        return response()->json($ret);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $cpf)
    {
        $user_id = Auth::id();
        $dados = User::where('autor',$user_id)
        ->where('cpf',$cpf)->get();
        // return $dados;
        $ret = ['status'=>401,'data'=>[]];
        if($dados->count()){
            $arr_campos = [
                'id'=>'id',
                'nome'=>'name',
                'cpf'=>'cpf',
                'sexo'=>'genero',
                // 'id_parceiro'=>'autor',
                'config'=>[
                    'celular'=>'celular',
                    'telefone_residencial'=>'telefone_residencial',
                    'telefone_comercial'=>'telefone_comercial',
                    'rg'=>'rg',
                    'nascimento'=>'nascimento',
                    'cep'=>'cep',
                    'endereco'=>'endereco',
                    'numero'=>'numero',
                    'complemento'=>'complemento',
                    'bairro'=>'bairro',
                    'cidade'=>'cidade',
                    'uf'=>'uf',
                    'inicio_vigencia'=>'inicioVigencia',
                    'fim_vigencia'=>'fimVigencia',
                    // 'numCertificado'=>'numCertificado',
                    // 'numOperacao'=>'numOperacao',
                    'status_contrato'=>'status_contrato',
                ],
            ];
            $dc = $dados[0];
            $ds=[];
            foreach ($arr_campos as $key => $val) {
                if(is_array($val)){
                    foreach ($val as $k1 => $va1) {
                        // dump($val,$dc[$key]);
                        if(isset($dc[$key])){
                            $ds[$k1] = isset($dc[$key][$va1]) ? $dc[$key][$va1] : null;
                        }
                    }
                }else{
                    if(isset($dc[$val])){
                        $ds[$key] = isset($dc[$val]) ? $dc[$val] : null;
                    }
                }
            }
            if(isset($ds['id']) && ($id=$ds['id'])){
                $contrato = (new ClienteController())->get_contrato_sulamerica($id);
                $arr_contrato=[];
                if($contrato){
                    $req_contrato = Qlib::lib_json_array($contrato);
                    $arr_contrato = isset($req_contrato['data']) ? $req_contrato['data'] : [];
                    unset($arr_contrato['valorPremio']);

                }
                $ds['contrato'] = $arr_contrato;
            }
        }else{
            $ret = ['status'=>404,'data'=>[]];
        }
        if(isset($ds['id'])){
            $ret = ['status'=>200,'data'=>$ds];
        }
        // return $dados;
        // return $ds;
        return $ret;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientesRequestApi $request, string $cpf)
    {
        $user_id = Auth::id();
        //Localizar o id do cliente pelo cpf informado levando em conta se ele é o autor ou o parceiro responsavel
        if($user_id){
            $d = $request->all();
            $ret['status'] = 400;
            $ret['response'] = 'Erro ao salvar';
            $ret['data'] = $d;
            // $ret['user'] = $user;
            // return $ret;
            $produtoParceiro = Qlib::qoption('produtoParceiro') ? Qlib::qoption('produtoParceiro') : 10232;

            $arr_campos = [
                'name'=>'nome',
                'cpf'=>'cpf',
                'genero'=>'sexo',
                'config'=>[
                    'celular'=>'celular',
                    'telefone_residencial'=>'telefone_residencial',
                    'telefone_comercial'=>'telefone_comercial',
                    'rg'=>'rg',
                    'nascimento'=>'nascimento',
                    'cep'=>'cep',
                    'endereco'=>'endereco',
                    'numero'=>'numero',
                    'complemento'=>'complemento',
                    'bairro'=>'bairro',
                    'cidade'=>'cidade',
                    'uf'=>'uf',
                    'inicioVigencia'=>'inicio_vigencia',
                    'nome_fantasia'=>'nome_fantasia',
                    'fimVigencia'=>'fim_vigencia',
                ],
            ];
            $ds=[];
            foreach ($arr_campos as $key => $val) {
                if(is_array($val)){
                    foreach ($val as $k1 => $va1) {
                        if(isset($d[$va1])){
                            $ds[$key][$k1] = $d[$va1];
                        }
                    }
                }else{
                    if(isset($d[$val])){
                        $ds[$key] = $d[$val];
                    }
                }
            }
            // return $ds;
            $ds['config']['id_produto'] = isset($ds['id_produto']) ? $ds['id_produto'] : $produtoParceiro;
            // $ds['config']['nome_fantasia'] = isset($ds['nome_fantasia']) ? $ds['nome_fantasia'] : null;
            // $ds['ativo'] = isset($ds['ativo']) ? $ds['ativo'] : 's';

            // $ret['d'] = $ds;
            // dd($ret);
            if(count($ds)>1){
                $tab_user = 'users';
                $id = Qlib::buscaValorDb0($tab_user,'cpf',$cpf,'id');
                $is_owner = (new ClienteController())->is_owner($user_id,$id);
                //Antes de prosseguir com a atualização verificar se o parceiro é o dono do cliente.
                if($is_owner){
                    $ds['autor'] = $user_id;
                    // $ds['id_permission'] = Qlib::qoption('id_permission_clientes');
                    // $ds['token'] = uniqid();
                    if(isset($ds['config']) && ($dconf=$ds['config'])){
                        // //criar uma nova variavel config e remover daqui
                        // unset($ds['config']);
                        // //salvar os dados do config separadamente
                        $salv_array = Qlib::json_generate_tab($tab_user,'id',$id,'config',$dconf);
                        // $ret['salv_array'] = $salv_array;
                        // $config_salvo =
                        if($salv_array){
                            $ds['config'] = $salv_array;
                        }
                        // dd($salv_array,$ds['config']);
                    }
                    $ccnt = new ClienteController();
                    if($request->get('contrato') == 'reativar'){
                        //Verificar se está dentro da vigencia
                        $inicioVigencia = isset($ds['config']['inicioVigencia']) ? $ds['config']['inicioVigencia'] : null;
                        // dd($inicioVigencia);
                        if($inicioVigencia){
                            $str_inicioVigencia = strtoupper($inicioVigencia);
                            $str_hoje = strtoupper(date('Y-m-d'));
                            // dd($str_inicioVigencia,$str_hoje,($str_inicioVigencia<$str_hoje));
                            if($str_inicioVigencia<$str_hoje){
                                $ret['response'] = 'Data de início de vigência inválida.\n';
                                return $ret;
                            }
                        }
                    }
                    $salv = $ccnt->atualizar_clientes($ds,$id,true);
                    // $ret['salv'] = $salv;
                    // return $ret;
                    if(isset($salv->original['exec']) && $salv->original['exec']){
                        $ret = $salv->original;
                        $ret['status'] = 200;
                        $ret['status_contrato'] = '';
                        if($request->get('contrato') == 'reativar'){
                            //Verificar se está dentro da vigencia

                            $status_atual = $ccnt->get_status_contrato($id);
                            $token_contrato = Qlib::buscaValorDb0('contratos','id_cliente',$id,'token');
                            if($token_contrato && $status_atual=='Cancelado'){
                                $ContCont = new ContratoController;
                                $contrato = $ContCont->reativar($token_contrato);
                                if($contrato['exec']){
                                    $st_contrato = $ccnt->get_contrato_sulamerica($id);
                                    $arr_contrato = Qlib::lib_json_array($st_contrato);
                                    unset($arr_contrato['data']['valorPremio']);
                                    $ret['contrato'] = $arr_contrato;
                                    $ret['status_contrato'] = $ccnt->get_status_contrato($id);
                                }
                            }
                        }
                        $ret['status_contrato'] = $ccnt->get_status_contrato($id);
                        unset($ret['dados'],$ret['idCad'],$ret['color']);
                    }
                }
            }

        }
        return $ret;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //verifica se é o dono
        $user_id = Auth::id();
        $is_owner = (new ClienteController())->is_owner($user_id,$id);
        $ret['exec'] = false;
        $ret['status'] = 400;
        $ret['mens'] = 'Erro ao tentar excluir';
        if($is_owner){
            $del = (new ClienteController)->delete_all($id);
            unset($del['color']);
            $ret = $del;
        }else{
            $ret['exec'] = false;
            $ret['status'] = 400;
            $ret['mens'] = 'Registro não encontrado';
            // $ret['data'] = $d;
        }
        return $ret;
    }
    /**
     * cancela um contrato.
     */
    public function cancelar_contrato(string $id)
    {
        //verifica se é o dono

        $user_id = Auth::id();
        $is_owner = (new ClienteController())->is_owner($user_id,$id);
        $ret['exec'] = false;
        $ret['status'] = 400;
        $ret['mens'] = 'Erro ao tentar excluir';
        // dump($user_id);
        if($is_owner){
            //Verifica qual é o numero de operação
            $numOperacao = (new ClienteController())->get_numero_operacao($id);
            if($numOperacao){
                $cc = new ContratoController;
                $ret = $cc->cancelar($numOperacao);
                if(isset($ret['exec'])){
                    unset(
                        $ret['data']['valorPremio'],
                        $ret['data']['parametros']['premioSeguro'],
                        $ret['data']['parametros']['planoProduto'],
                        $ret['color'],
                    );
                    $ret['status'] = 200;
                    $token_contrato = Qlib::buscaValorDb0('contratos','id_cliente',$id,'token');
                    $up_status = $cc->status_update($token_contrato,'Cancelado');
                    $ret['up_status'] = $up_status;
                    // $ret = Qlib::json_update_tab('users','id',$id,'config',[
                    //     'status_contrato'=>'Cancelado',
                    // ]);
                }
            }
        }else{
            $ret['exec'] = false;
            $ret['status'] = 400;
            $ret['mens'] = 'Registro não encontrado';
            // $ret['data'] = $d;
        }
        return $ret;
    }
}
