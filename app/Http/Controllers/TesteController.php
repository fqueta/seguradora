<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\AsaasController;
use App\Http\Controllers\admin\CobrancaController;
use App\Http\Controllers\admin\ContratosController;
use App\Http\Controllers\admin\OrcamentoController;
use App\Http\Controllers\admin\RdstationController;
use App\Http\Controllers\admin\PostController;
use App\Http\Controllers\admin\ZapsingController;
use App\Http\Controllers\BacklistController;
use App\Jobs\NotificWinnerJob;
use App\Jobs\RdstationJob;
use App\Jobs\SendEmailJob;
use App\Mail\EnviaMail;
use App\Mail\leilao\lancesNotific;
use App\Models\Familia;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use PHPUnit\Util\Blacklist;
use stdClass;

class TesteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {

        // echo (int)(Post::max('menu_order'))+1;
        // $ret = (new PostController)->addMenu(20);
        // $slug = Qlib::createSlug('leilão 12');
        // $p = (new LeilaoController)->get_data_contrato([24,4],true);
        // $p = (new LeilaoController)->is_linked_leilao('64a5a2b17a602');
        // $tempo  = Qlib::diffDate('2014-12-01 15:00:00',Qlib::dataLocalDb(),'H');
        // dd($tempo);
        // $ret = (new LeilaoController)->notific_update_admin(48,'admin');
        // $ret = (new AsaasController)->cadastrarCliente(['id_cliente'=>6],true);
        // $ret = (new AsaasController)->deletarCliente('cus_000005435290');
        // $ret = (new AsaasController)->deletarCliente(6);
        //   $conf=[
        //     'compra' => [
        //         'token' =>'64b0769e47002',
        //         'id_cliente' =>6,
        //         'valor' =>'',
        //         'forma_pagamento' =>'cred_card',
        //         'descricao' =>'Pagamento de leilao 14',
        //     ],
        //     'cartao' => [
        //         'valor' =>'02X25.36',
        //         'nome_no_cartao' =>'Jose Onifarsio',
        //         'numero_cartao' =>'000000000000000000001',
        //         'validade_mes' =>'05',
        //         'validade_ano' =>'2025',
        //         'codigo_seguranca' =>'123',
        //     ],
        //     // 'responsavel' => [],
        // ];

        // $ret = (new AsaasController)->integraCompraAsaas($conf);
        // dd($ret);
        // $post_id = Qlib::get_id_by_token('64b6e7630678d');
        // dd($post_id);
        // $post_id = 36;
        // $proximo_lance1 = (new LanceController)->proximo_lance(60,false,1);
        // dd($proximo_lance1);
        // $dlt = $this->get_leilao($seg2);
    // $ret = (new LeilaoController)->get_leilao(58,false,[
    //     'desconto'=>true
    // ]);
        // $ret = (new LeilaoController)->who_won(36);
        // $ret = (new LeilaoController)->get_all_finalized();
        // $ret = (new LeilaoController())->get_seguidores(66);
        // $ret = (new LeilaoController())->list_winner(Auth::id());
        // $ret = (new LeilaoController())->lista_leilao_terminado(Auth::id());
        // $ret = (new LeilaoController())->list_alert_winners();
        // $ret = (new BlacklistController())->add(47,[
        //     'description' =>'Não pagamento',
        //     'leilao_id' =>'65',
        // ]);
        // $ret = (new LeilaoController)->get_ranking(41);
        // $ret = (new LeilaoController)->finaliza_leilao(90);
        // $ret = (new LeilaoController)->situacao_html(54);
        // $ret = (new BlacklistController())->send_to_blacklist(false);
        // $ret = (new BlacklistController())->remove(1);
        // $ret = (new QuickCadController())->link_step2(5);
        // $ret = (new LeilaoController())->finalizados_nao_pagos();
        // // $ret = (new BlacklistController())->is_blacklist(3);
        // $ret = (new BlacklistController())->get_blacklist();
        // $leilao_id = 66;
        // $meta_notific = 'notifica_termino_leilao_ganhador';
        // $ret['remove_notific'] = Qlib::update_postmeta($leilao_id,$meta_notific,'n');
        // //Notificar o cliente caso seja permitido
        // $ret['notify'] = (new LeilaoController())->notifica_termino($leilao_id,'ganhador');
        // $ret = (new UserController)->ger_select_ddi();

        $token = $request->get('token') ? $request->get('token') : '675855ed0b876';
        $titulo = $request->get('titulo') ? $request->get('titulo') : 'Meu teste';
        $opc = $request->get('opc') ? $request->get('opc') : 1;
        if($opc==2){
            $link = (new OrcamentoController)->orcamento_html($token,'whatsapp');
            $tm =$link.'<br>';
            // $ret = $link;
            $ret = $tm.'<a href="'.$link.'" target="_blank">Acessar</a>';
        }elseif($opc==3){
            $ret = (new OrcamentoController)->send_to_zapSing($token);
            // $ret = (new OrcamentoController)->gerar_termo_orcamento($token);

        }elseif($opc==4){
            //download file
            $url = "https://zapsign.s3.amazonaws.com/sandbox/dev/2024/12/pdf/72d30d89-da1f-4e10-9025-3689b03ef3d4/7a773057-05d3-4843-be1d-0fe6bffdb730.pdf?AWSAccessKeyId=AKIASUFZJ7JCTI2ZRGWX&Signature=oRLj2PALoDs1JEkx%2FHm4TV1ZM%2BQ%3D&Expires=1734026017";
            $external_id = Qlib::createSlug('11/12/2024 07');;
            $caminhoSalvar = 'pdf/termos_assinados/'.$external_id.'/arquivo.pdf';
            $ret = Qlib::download_file($url,$caminhoSalvar);
        }elseif($opc==5){
            return view('teste');
        }elseif($opc==6){
            $ret = (new ZapsingController)->status_doc_remoto($token);
        }elseif($opc==7){
            // $ret = (new RdstationController)->add_rd_negociacao($token);
            $ret = RdstationJob::dispatch($token);
        }else{
            $subject = 'SOLICITAÇÃO DE AGENDAMENTO DE MANUTENÇÃO';
            $dc = User::find(1);
            $mensagem =  'Antenção foi solicitado um orçamento por <b>'.$dc['name'].'</b> em '.Qlib::dataLocal();
            $mensagem .= (new OrcamentoController)->orcamento_html($token,'markdown');
            $nome = 'Fernando Queta';
            $cc = 'suporte@maisaqui.com.br';
            $from = 'suporte@aeroclubejf.com.br';
            $email = 'contato@aeroclubejf.com.br';
            $details = [
                'email' => $email,
                'cc' => $cc,
                // 'from' => $from,
                'name' => $nome,
                'subject' => $subject,
                'message' => $mensagem
            ];
            if($request->get('envia')==1){
                if(isset($details['cc'])){
                    $ret = Mail::to($details['email'])->cc($details['cc'])->send(new EnviaMail($details));
                }else{
                    $ret = Mail::to($details['email'])->send(new EnviaMail($details));
                }
            }elseif($request->get('envia')==2){
                $ret = SendEmailJob::dispatch($details);
                dd(config('app.name'));
            }else{
                $ret = (new OrcamentoController)->orcamento_html($token);
                // $ret = new EnviaMail($details);
            }
        }
        return $ret;
        // dd($ret);
        // dd(NotificWinnerJob::dispatch());
        // session(['user'=>'s']);
        // $request->session()->put('close_popup','s');
        // $request->session()->keep('close_popup');
        // $value = $request->session()->all();
        // dd($value);
        // echo (new LeilaoController)->get_link_edit_admin(71);
        // $ret = Qlib::get_postmeta($post_id,'pago');
        // $ret = (new PaymentController) -> get_info_pagamento($post_id);
        // session()->forget('ganhador');
        // dd(session()->get('ganhador'));
        // return $ret;
        // dd(Auth::user());
        // $ret = env('APP_NAME');
        // $ret = (new LeilaoController)->enviar_email([
        //     'type' => 'notifica_finalizado',
        //     'lance_id' => 213,
        //     'subject' => 'Leilão Finalizado',
        //     'mensagem' => 'Ola mensagem',
        //     // 'link_pagamento' => $link_pagamento,
        // ]);
        // $ret['salv'] = (new ContratosController)->update_tokenCRM(5550,[
        //     'token_externo' => '54233',
        // ]);
        // // $notific = (new LanceController)->notifica_superado($leilao_id,$id_a);
        // $ret = (new LeilaoController)->notifica_termino(62,'admin');
        // dd($ret);
    //    echo  (new UserController)->get_first_admin();
        // $ret['list'] = (new ContratosController)->get_contratos_crm();
        // $ret = Qlib::createSlug('Fernando Teste programador aatiça ação 200,.52');
        // return $ret;
        // $up = Qlib::update_postmeta(45,'notifica_termino_leilao','n');
        // $me = Qlib::get_postmeta(36,'notifica_termino_leilao',true);
        // dd($me);
        // dd(config('app.debug'));
        // $p = (new LanceController)->marca_lance_superado(36);
        // dd($p);
        // // dd($ret);
        // return false;
        // //     echo Qlib::get_subdominio();
        // $dados = (new FamiliaController($user))->rendaFamiliar(3145);
        // dd($dados);
            // $host = request()->getHttpHost();
        // echo $host ."<br/>";
        // $getHost = request()->getHost();
        // echo $getHost ."<br/>";
        // $hostwithHttp = request()->getSchemeAndHttpHost();
        // echo $hostwithHttp ."<br/>";
        // $subdomain = $route->getParameter('subdomain');
        //dd($route);
        // return view('teste',$config);
    }
    public function ajax(){
        $limit = isset($_GET['limit']) ?$_GET['limit'] : 50;
        $page = isset($_GET['page']) ?$_GET['page'] : 1;
        $site=false;

        $urlApi = $site?$site: 'https://po.presidenteolegario.mg.gov.br';
        $link = $urlApi.'/api/diaries?page='.$page.'&limit='.$limit;
        $link_html = dirname(__FILE__).'/html/front.html';
        $dir_img = $urlApi.'/uploads/posts/image_previews/{id}/thumbnail/{image_preview_file_name}';
        $dir_file = $urlApi.'/uploads/diaries/files/{id}/original/{file_file_name}';

        //$arquivo = $this->carregaArquivo($link_html);
        //$temaHTML = explode('<!--separa--->',$arquivo);
        $api = file_get_contents($link);
        $arr_api = Qlib::lib_json_array($api);
        /*
        $tema1 = '<ul id="conteudo" class="list-group">{tr}</ul>';
        $tema2 = '<li class="list-group-item" itemprop="headline"><a href="{link_file}" target="_blank">{file_file_name} – {date}</a></li>';
        $tr=false;
        if(isset($arr_api['data']) && !empty($arr_api['data'])){
          foreach ($arr_api['data'] as $key => $value) {
              $link = false;
              $link_file = str_replace('{id}',$value['id'],$dir_file);
              $link_file = str_replace('{file_file_name}',$value['file_file_name'],$link_file);


              $conteudoPost = isset($value['content'])?:false;
              $date = false;
              $time = false;
              $datetime = str_replace(' ','T',$value['date']);
              $d = explode(' ',$value['date']);

              if(isset($d[0])){
                $date = Qlib::dataExibe($d[0]);
              }
              if(isset($d[1])){
                $time = $d[1];
              }
              $file_name = str_replace('.pdf','',$value['file_file_name']);
              $file_name = str_replace('.PDF','',$file_name);
              $tr .= str_replace('{file_file_name}',$file_name,$tema2);
              $tr = str_replace('{link}',$link,$tr);
              $tr = str_replace('{link_file}',$link_file,$tr);
              $tr = str_replace('{time}',$time,$tr);
              $tr = str_replace('{date}',$date,$tr);
              $tr = str_replace('{description}',$value['description'],$tr);
              $tr = str_replace('{datetime}',$datetime,$tr);
          }
        }
        $link_veja_mais = '/diario-oficial';
        $ret = str_replace('{tr}',$tr,$tema1);
        //$ret = str_replace('{id_sec}',$id_sec,$ret);
        $ret = str_replace('{link_veja_mais}',$link_veja_mais,$ret);
        */
        return response()->json($arr_api);
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
