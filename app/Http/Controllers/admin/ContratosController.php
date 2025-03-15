<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Jobs\AddContratosJob;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Notifications\notificaNewUser;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
class ContratosController extends Controller
{
    private $url_api;
    private $url_webhook;
    private $token_api;
    public function __construct()
    {
        $this->url_api = 'https://api.aeroclubejf.com.br/api/v1/';
        $this->url_webhook = 'https://api.aeroclubejf.com.br/api/webhook/';
        $this->token_api = '3|XLKG1U2hRFprV2HzavpCZJ9kQ2AbsCx9O9uTAb23';
    }
    public function get_contratos_crm(){

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url_api.'matriculas?status=8&token_externo=',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Authorization: Bearer '.$this->token_api
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $arr = Qlib::lib_json_array($response);
        $data = isset($arr['data']['data']) ? $arr['data']['data'] : [];
        if(is_array($data)){
            foreach($data as $k=>$v){
                foreach ($v as $k1 => $v1) {
                    if($k1=='orc' || $k1=='contrato' || $k1=='totais' || $k1=='rescisao'){
                        $arr['data']['data'][$k][$k1] = Qlib::lib_json_array($v[$k1]);
                    }
                }
            }
        }
        return $arr;
    }
    /**
     * Metodo para atualizar token externo
     */
    public function update_tokenCRM($id,$dados=[]){
        $ret['exec'] = false;
        if($id && is_array($dados)){
            $json_dados = Qlib::lib_array_json($dados);
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url_api.'matriculas/'.$id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS =>$json_dados,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer '.$this->token_api
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $ret = Qlib::lib_json_array($response);
        }
        return $ret;
    }
    /**
     * Metodos para receber posts de plataformas externas
     */
    public function webhook(){
		$ret['exec'] = false;
		@header("Content-Type: application/json");
		$json = file_get_contents('php://input');
        if(!empty($json)){
        //    $ret = AddContratosJob::dispatch($json);
            $ret =  $this->add_cad($json);
        }
		return response()->json($ret);
	}
    /**
     * Metodo para gerenciar a inclusão de contratos atravez da webhook
     * @param string $json,$updat se = true ele vai atualizar o que encontrar
     */
    public function add_cad($json,$update=false){
        $ret['exec']=false;
        $ret['mens']=false;
        $arr_resp = Qlib::lib_json_array($json);
        if(!isset($arr_resp['Cpf'])){
            $ret['mens']='CPF não informado';
            return $ret;
        }
        $ret['arr_resp'] = $arr_resp;
        if(isset($arr_resp['rescisao']['numero']) && $arr_resp['rescisao']['numero'] && isset($arr_resp['Cpf']) && isset($arr_resp['Email'])){
            $ret['data'] = $json;
            $ret['d'] = $arr_resp;
            //Verificar se o cliente está cadastrado atraves do email o cpf informado
            $verf_cad = User::orWhere('email',$arr_resp['Email'])->orWhere('cpf',$arr_resp['Cpf'])->get();
            $dcw = $arr_resp;
            $pass = str_replace('.','',$dcw['Cpf']);
            $pass = str_replace('-','',$pass);

            $dCli = [
                'tipo_pessoa'=>'pf',
                'name'=>@$dcw['nome'].' '.@$dcw['sobrenome'],
                'cpf'=>@$dcw['Cpf'],
                'email'=>@$dcw['Email'],
                'password'=>Hash::make($pass),
                'status'=>'actived',
                'genero'=>'ni',
                'id_permission'=>5,
                'token'=>isset($dcw['token'])?$dcw['token']:uniqid(),
                'config'=>[
                    'celular'=>@$dcw['Celular'],
                    'cep'=>@$dcw['cep'],
                    'endereco'=>@$dcw['Endereco'],
                    'numero'=>@$dcw['Numero'],
                    'bairro'=>@$dcw['Compl'],
                    'complemento'=>@$dcw['Bairro'],
                    'uf'=>@$dcw['Uf'],
                ],
            ];
            if($verf_cad->count() > 0){
                $verf_cad=$verf_cad->toArray();
                $ret['mens']='usuário já existe';
                if(isset($verf_cad[0]['id']) && ($id=$verf_cad[0]['id']) && $update && $dcw){
                    unset($dcw['password']);
                    $ret['up'] = User::where('id', $id)->update($dcw);
                }
                // dd($dcw['rescisao'],$verf_cad[0]);
                if(isset($dcw['rescisao']) && isset($verf_cad[0]['id'])){
                    $ret = $this->add_contratos($verf_cad[0]['id'],$dcw['token'],$dcw['rescisao']);
                }
            }else{
                //cadastrar o cliente
                // return $dCli;
                $salvar = User::create($dCli);
                if($id_cli=$salvar->id){
                    $ret['exec'] = true;
                    $ret['id'] = $id_cli;
                    $s_me = (new UserController)->save_meta($id_cli,[
                        'tag_origem'=>'webwook_crm',
                    ]);
                    $user_cad = User::Find($salvar->id);
                    Notification::send($user_cad,new notificaNewUser($user_cad));
                    $ret = $this->add_contratos($id_cli,$dcw['token'],$dcw['rescisao']);
                }


            }
            $ret['verif_cad'] = $verf_cad;
        }
        return $ret;
    }
    /**
     * Metodo para adicionar contrato a um usuario
     *
     */
    public function add_user($json){
    }
    /**
     * Metodo para adicionar contrato a um usuario
     * @param $user_id, $dados da rescisão
     */
    public function add_contratos($user_id,$token=false,$dados=[]){
        $ret['exec'] = false;
        $ret['mens'] = 'Erro ao adicionar contrato';
        $id = false;
        $idCad = false;
        // dd($user_id);
        if($user_id && isset($dados['numero'])){
            $idn = explode('.',$dados['numero']);
            if(!isset($idn[0])){
                $ret['mens'] = 'número de contrato inválido';
                return $ret;
            }
            $id = $idn[0];
            $post_title = 'Repasse do contrato '.$dados['numero'];
            $post_name = Str::slug($post_title);
            $dsal = [
                'post_author' =>0,
                'post_title' =>$post_title,
                'post_status' =>'publish',//'pending',
                'post_name' =>$post_name,
                'post_type' =>'produtos',
                'post_content' =>isset($dados['descricao'])?$dados['descricao']:'Contrato adicionado pelo CRM ',
                'config' =>[
                    'cliente'=>(string)$user_id,
                    'total_horas'=>$dados['horas'],
                    'valor_r'=>$dados['valor_r'],
                    'valor_venda'=>@$dados['valor_venda'],
                    'valor_atual'=>@$dados['valor_atual'],
                    'incremento'=>@$dados['incremento'],
                ],
                'token' =>$token?$token:uniqid(),
            ];
            $verf_cad = Post::where('post_name', '=',$post_name)->where('post_status','!=','trash')->get();
            // return $verf_cad;
            if($verf_cad->count() > 0){
                $verf_cad = $verf_cad->toArray();
                $idCad = $verf_cad[0]['ID'];
                $ret['exec'] = Post::where('id',$verf_cad[0]['ID'])->update($dsal);
                if($ret['exec']){
                    $ret['mens'] = 'Atualizado com sucesso!!';
                }
            }else{
                $salvo = Post::create($dsal);
                if($salvo->id){
                    $idCad = $salvo->id;
                    $ret['exec'] = true;
                    $ret['mens'] = 'Salvo com sucesso';
                }
            }
            if($ret['exec'] && isset($dsal['token']) && $id){
                $idCad = $idCad?$idCad:$dsal['token'];
                $ret['salv'] = $this->update_tokenCRM($id,[
                    'token_externo' => $idCad,
                ]);
                // $ret['mens'] = 'Salvo com sucesso';
            }
        }
        return $ret;
    }
}
