<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\api\SulAmericaController;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Qoption;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    /**
     * Exibe dos dados do cadastro bem como o array para integração
     */
    public $campo_meta1;
    public $campo_meta2;
    public $campo_meta3;
    public $campo_meta4;
    public $campo_meta5;
    public function __construct()
    {
        $this->campo_meta1 = 'contrato';
        $this->campo_meta2 = 'numOperacao';
        $this->campo_meta3 = 'status_contrato';
        $this->campo_meta4 = 'premioSeguro';
        $this->campo_meta5 = 'numCertificado';
    }
    public function dc($token=false){
        $dc = Contrato::select(
            'users.*',
            'contratos.inicio',
            'contratos.fim',
            'contratos.id as id_contrato',
            'contratos.id_plano',
            'contratos.config as config_contrato',
        )
        ->join('users','contratos.id_cliente','users.id')
        ->where('contratos.token',$token)
        ->orderBy('contratos.id','DESC')
        ->get();
        $dcc = [];
        if($dc->count()){
            $dc = $dc->toArray();
            $dcc = isset($dc[0]) ? $dc[0] : [] ;
            // dd($dc);
            if(!empty($dcc['config_contrato'])){
                $dcc['config_contrato'] = Qlib::lib_json_array($dcc['config_contrato']);
            }
            $integracao = [
                'planoProduto'=>@$dcc['id_plano'] ? $dcc['id_plano'] : Qlib::qoption('planoPadrao'),
                'operacaoParceiro'=>$token,
                'produto'=>isset($dcc['config']['id_produto']) ? $dcc['config']['id_produto'] : '',
                'nomeSegurado'=>$dcc['name'],
                'dataNascimento'=>isset($dcc['config']['nascimento']) ? $dcc['config']['nascimento'] : '',
                'premioSeguro'=>isset($dcc['config_contrato']['premioSeguro']) ? $dcc['config_contrato']['premioSeguro'] : '',
                'sexo'=>strtoupper($dcc['genero']),
                'documento'=>$dcc['cpf'],
                'inicioVigencia'=>$dcc['inicio'],
                'fimVigencia'=>$dcc['fim'],
                'uf'=>isset($dcc['config']['uf']) ? $dcc['config']['uf'] : '',
            ];
            $integracao['premioSeguro'] = str_replace(' ','',$integracao['premioSeguro']);
            $integracao['premioSeguro'] = str_replace('R$','',$integracao['premioSeguro']);
            $integracao['premioSeguro'] = Qlib::precoBanco($integracao['premioSeguro']);

            $integracao['documento'] = str_replace('.','',$integracao['documento']);
            $integracao['documento'] = str_replace('-','',$integracao['documento']);
            $dcc['integracao_sulamerica'] = $integracao;

            $campo_meta2 = 'numOperacao';
            $numOperacao = Qlib::get_usermeta($dcc['id'],$campo_meta2,true);
            if($numOperacao){
                $dcc['numOperacao'] = $numOperacao;
            }
        }
        return $dcc;
    }
    /**
     * Gerenciar as contratações do sulamerica seguradora
     */
    public function sulamerica_contratar($token=false){
        $dc = $this->dc($token);
        if(isset($dc['integracao_sulamerica']) && ($config = $dc['integracao_sulamerica'])){
            //Requisitar contratação sulamerica
            $id_cliente = isset($dc['id']) ? $dc['id'] : null;
            $campo_meta1 = $this->campo_meta1;
            $campo_meta2 = $this->campo_meta2;
            $campo_meta3 = $this->campo_meta3;
            $numOperacao = Qlib::get_usermeta($id_cliente,$campo_meta2,true);
            if($numOperacao){
               return false;
            }
            $ret = (new SulAmericaController)->contratacao($config);
            $salvar = false;
            // dump($config);
            if(isset($ret['exec']) && isset($ret['data'])){
                //salvar resultado do processamento
                $numOperacao = isset($ret['data']['numOperacao']) ? $ret['data']['numOperacao'] : null;
                if($numOperacao){
                    $salvar = Qlib::update_usermeta($id_cliente,$campo_meta1,Qlib::lib_array_json($ret));
                    $salvar2 = Qlib::update_usermeta($id_cliente,$campo_meta2,$numOperacao);
                    $status_aprovdo = 'Aprovado';
                    $salvar3 = Qlib::update_usermeta($id_cliente,$campo_meta3,$status_aprovdo);
                    //salvar no campo config da tabela users
                    $salv_json_fiels = Qlib::update_json_fields('users','id',$id_cliente,'config',$campo_meta2,$numOperacao);
                    // $salv_json_fiels = Qlib::update_json_fields('users','id',$id_cliente,'config',$campo_meta3,$status_aprovdo);
                    $update_status = $this->status_update($token,$status_aprovdo,$ret);
                    if( Qlib::isAdmin(1)){
                        $ret['config'] = $config;
                        $ret['salvar'] = $salvar;
                        $ret['salvar2'] = $salvar2;
                        $ret['salv_json_fiels'] = $salv_json_fiels;
                        $ret['update_status'] = $update_status;
                        // $ret['dc'] = $dc;
                    }
                }
                // $salvar_contrado = Qlib::update_tab('contratos',[
                //     'config'=>Qlib::lib_array_json($ret['data']),
                // ],"WHERE token='$token'");
            }
        }
        return $ret;
    }
    /**
     * Atualiza o status de um contrato
     * @param string $token_contrato é o token de um contrato
     * @param string $status é o status a atual do contrato 'Aprovado' | 'Cancelado'
     * @param array resultado do processamento que gerou o status.
     */
    public function status_update($token_contrato,$status,$ret=[]){
        $dc = $this->dc($token_contrato);
        $ret = ['exec'=>false,'mens'=>'','color'=>'danger'];
        try {
            if(isset($dc['id']) && ($id_cliente=$dc['id'])){
                $salvar3 = Qlib::update_usermeta($id_cliente,$this->campo_meta3,$status);
                $salv_json_fiels = Qlib::update_json_fields('users','id',$id_cliente,'config',$this->campo_meta3,$status);
                if( Qlib::isAdmin(1)){
                    // $ret['config'] = $config;
                    // $ret['salvar'] = $salvar;
                    $ret['salvar3'] = $salvar3;
                    $ret['salv_json_fiels'] = $salv_json_fiels;
                    // $ret['salvar_contrado'] = $salvar_contrado;
                    $ret['dc'] = $dc;
                }
            }
            if(isset($ret['data'])){
                $ret['salvar_contrado'] = Qlib::update_tab('contratos',[
                    'config'=>Qlib::lib_array_json($ret['data']),
                ],"WHERE token='$token_contrato'");

            }else{
                if(isset($id_cliente)){
                    $ret['json_update_tab'] = Qlib::json_update_tab('users','id',$id_cliente,'config',[
                           'status_contrato'=>$status,
                    ]);
                }
            }
            $ret['exec'] = true;
            $ret['mens'] = 'Status atualização com sucesso!';
        } catch (\Throwable $th) {
            $ret['exec'] = false;
            $ret['mens'] = 'Erro ao atualizar o status!';
            $ret['error'] = $th->getMessage();
            //throw $th;
        }
        return $ret;
    }
    /**
     * Atualiza o token para um novo, e tambem remove o numero de operação antigo para que um contrato seja reativado na sulamerica
     */
    public function reativar($token){
        $new_token = uniqid();
        $ret['exec'] = false;
        $ret['color'] = 'danger';
        $ret['mens'] = __('Erro ao iniciar o precesso de reativação');
        $up_contrato = Contrato::where('token','=',$token)->update(['token'=>$new_token]);
        $up_user = User::where('token','=',$token)->update(['token'=>$new_token]);
        if(Qlib::isAdmin(1)){
            $ret['up_contrato'] = $up_contrato;
            $ret['up_user'] = $up_user;
        }
        if($up_contrato && $up_user){
            $dc = $this->dc($new_token);
            $status = 'Reativando';
            $delete1 = false;
            $update_status = false;
            $salv_json_fiels = false;
            if(isset($dc['id']) && ($user_id=$dc['id'])){
                $delete1 = Qlib::delete_usermeta($user_id,$this->campo_meta2);
                $salv_json_fiels = Qlib::update_json_fields('users','id',$user_id,'config',$this->campo_meta2,'');
                $update_status = $this->status_update($new_token,$status,[]);
                $ret['dc'] = $dc;
                $ret['mens'] = __('Retivação inciada com sucesso!!');
                $ret['color'] = 'success';
            }
            $ret['delete1'] = $delete1;
            $ret['salv_json_fiels'] = $salv_json_fiels;
            $ret['exec'] = true;
            $ret['update_status'] = $update_status;
            $ret['old_token'] = $token;
            $ret['new_token'] = $new_token;
        }
        return $ret;

        // $token = Qlib::buscaValorDb0('users','token',$id,'token');
    }
    /**
     * Metodo para recuperar o numero do certificado com o id_do cliente
     */
    public function get_certificado($id_cliente=null){
        $campo_meta1 = $this->campo_meta1;
        $contrato = Qlib::get_usermeta($id_cliente,$campo_meta1,true);
        $arr_contrato = Qlib::lib_json_array($contrato);
        $ret = isset($arr_contrato['data']['numCertificado']) ? $arr_contrato['data']['numCertificado']:0;
        return $ret;
    }
    /**
     * Canelar um contrato.
     */
    public function cancelar($numOperacao=false)
    {
        $ret = ['exec'=>false,'mens'=>'Erro ao cancelar'];
        if($numOperacao){
            $ret = (new sulAmericaController)->cancelamento(['numeroOperacao'=>$numOperacao]);
        }

        return $ret;
    }
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Contrato $contrato)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contrato $contrato)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contrato $contrato)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contrato $contrato)
    {
        //
    }
}
