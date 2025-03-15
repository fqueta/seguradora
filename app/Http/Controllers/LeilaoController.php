<?php

namespace App\Http\Controllers;
use App\Http\Controllers\admin\EventController;
use App\Http\Controllers\admin\PostController;
use App\Http\Controllers\UserController;
use App\Http\Requests\StorePostRequest;
use App\Models\lance;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use stdClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use App\Models\Post;
use App\Notifications\EmailDonoLeilaoNotification;
use App\Notifications\ganhadorPainelNotification;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use JeroenNoten\LaravelAdminLte\Components\Tool\Modal;

class LeilaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $post_type;
    /**
     * o metodo responsavel pro salvar a situalção leilão finalizado f é $this->info_termino();
     */
    public $c_meta_situacao;
    public $c_meta_detalhes_situacao;
    public $c_meta_lance_vencedor;
    public function __construct()
    {
        $this->post_type                 = 'leiloes_adm';
        $this->c_meta_situacao           = 'situacao_leilao'; //f=Finalizado, a=em andamento
        $this->c_meta_detalhes_situacao  = 'situacao_leilao_detalhes'; //salva um json contendo detalhes daquela situação
        $this->c_meta_lance_vencedor     = 'id_lance_vencedor'; //salva o id do lance vencedor
    }
    /**Metodo para gerar o formulario no front pode ser iniciado com o short_de [sc ac="form_leilao"] */
    public function form_leilao($post_id=false,$dados=false,$leilao_id=false){
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        if(Gate::allows('is_admin2')||Gate::allows('is_customer_logado')){
        }else{
            return false;
        }
        if($seg1 == Qlib::get_slug_post_by_id(37)){
            //Verifica se a página é de exição
            return false;
        }
        $leilao_id = $leilao_id ? $leilao_id : $seg2;
        if($leilao_id){
            $ac = 'alt';
            $leilao_id = Qlib::buscaValorDb0('posts','token',$leilao_id,'ID');
            $dadosLeilao = Post::Find($leilao_id);
            // $dadosLeilao = $this->get_leilao($leilao_id);
            if($dadosLeilao){
                if($dadosLeilao->count() > 0){
                    $dadosLeilao['id'] = $dadosLeilao['ID'];
                    $dadosLeilao = $dadosLeilao->toArray();
                }
            }
            // dd($dadosLeilao);
        }else{
            $dadosLeilao = false;
            $ac = 'cad';
        }
        if(!$dados && $post_id){
            $dados = Post::Find($post_id);
        }
        $route = $this->post_type;
        $title = __('Cadastro de Leilão');
        $titulo = $title;
        $config = [
            'ac'=>$ac,
            'frm_id'=>'frm-posts',
            'route'=>$route,
            'view'=>'site.leiloes',
            'file_submit'=>'site.leiloes.js_submit',
            'arquivos'=>'jpeg,jpg,png',
            'redirect'=>url('/'.Qlib::get_slug_post_by_id(12)),
            'title'=>$title,
            'titulo'=>$titulo,
        ];
        if(isset($dadosLeilao['ID'])){
            $config['id'] = $dadosLeilao['ID'];
        }
        $config['media'] = [
            'files'=>'jpeg,jpg,png,pdf,PDF',
            'select_files'=>'unique',
            'field_media'=>'post_parent',
            'post_parent'=>$post_id,
        ];
        $pst = new PostController;
        $listFiles = false;
        $post_type = $this->post_type;
        $campos = $pst->campos_leilao($leilao_id,$post_type,$dadosLeilao);

        $ret = [
            'value'=>$dadosLeilao,
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'listFiles'=>$listFiles,
            'campos'=>$campos,
            'exec'=>true,
        ];
        return view('site.leiloes.edit',$ret);
    }
    /**Metodo para listar os leilões no console do usuario do site no front pode ser iniciado com o short_de [sc ac="list_leilao"] */

    public function list_leilao($dados=false){
        if(Gate::allows('is_admin2')||Gate::allows('is_customer_logado')){
            $pst = false;
        }else{
            return false;
        }
        $pst = new PostController;
        $queryPost = $pst->queryPost($_GET,$dados,$this->post_type);
        $queryPost['config']['exibe'] = 'html';
        $route = $this->post_type;
        $title = 'Leilão';
        $titulo = $title;
        $view   = '/'.Qlib::get_slug_post_by_id(18);
        //if(isset($queryPost['post']));
        // dd($queryPost);
        $ret = [
            'dados'=>$queryPost['post'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryPost['campos'],
            'post_totais'=>$queryPost['post_totais'],
            'titulo_tabela'=>$queryPost['tituloTabela'],
            'arr_titulo'=>$queryPost['arr_titulo'],
            'config'=>$queryPost['config'],
            'routa'=>$route,
            'view'=>$view,
            'i'=>0,
            'ganhos'=>$this->lista_leilao_terminado(Auth::id()),
            // 'ganhos'=>self::list_winner(Auth::id()),
        ];
        // dd($ret);
        //REGISTRAR EVENTOS
        // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);

        return view('site.leiloes.list',$ret);
    }
    /**
     * Metodo para listar os leiloes publicados no site
     */
    public function index()
    {
        // $pst = new PostController;
        // $queryPost = $pst->queryPost($_GET,false,$this->post_type);
        // $queryPost['config']['exibe'] = 'html';
        // $route = $this->post_type;
        // $title = 'Leilão';
        // $titulo = $title;
        // $view   = '/'.Qlib::get_slug_post_by_id(18);
        // //if(isset($queryPost['post']));
        // $ret = [
        //     'dados'=>$queryPost['post'],
        //     'title'=>$title,
        //     'titulo'=>$titulo,
        //     'campos_tabela'=>$queryPost['campos'],
        //     'post_totais'=>$queryPost['post_totais'],
        //     'titulo_tabela'=>$queryPost['tituloTabela'],
        //     'arr_titulo'=>$queryPost['arr_titulo'],
        //     'config'=>$queryPost['config'],
        //     'routa'=>$route,
        //     'view'=>$view,
        //     'i'=>0,
        // ];
        // return view('site.index',$ret);
        return redirect('/leiloes-publicos');
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
    public function store(StorePostRequest $request)
    {
        return (new PostController)->store($request);
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
    public function update(StorePostRequest $request, $id)
    {
        return (new PostController)->update($request,$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        return (new PostController)->destroy($id,$request);
    }
    /**
     * Metodo para retornar o valor de um contrato
     * @param int $id or array $array
     * @retunt array
     */
    public function get_data_contrato($token=false,$somar=false){
        $ret['exec'] = false;
        $id = false;
        if($token){
            if(is_array($token)){
                foreach ($token as $kto => $vto) {
                    if($this->is_linked_leilao($vto)){
                        return $ret;
                    }else{
                        $id[$kto] = Qlib::buscaValorDb0('posts','token',$vto,'ID');
                    }
                }
            }else{
                $id = Qlib::buscaValorDb0('posts','token',$token,'ID');
                // dd($id);
            }
        }
        $ret['config'] = false;
        if($id){
            if(is_array($id)){
                if($somar){
                    $ret['total_horas'] = Null;
                    $ret['valor_r'] = Null;
                }
                foreach ($id as $k => $v) {
                    $post = Post::Find($v);
                    if($post->count() > 0){
                        if(isset($post['config'])){
                            $ret['config'] = $post['config'];
                            if($somar){
                                if(isset($post['config']['valor_r'])){
                                    $total_horas = (int)$post['config']['total_horas'];
                                    $valor_r = Qlib::precoBanco($post['config']['valor_r']);
                                    $valor_atual = Qlib::precoBanco(@$post['config']['valor_atual']);
                                    $incremento = Qlib::precoBanco(@$post['config']['incremento']);
                                    @$ret['horas'] += @$post['config']['horas'];
                                    $ret['valor_r'] += $valor_r;
                                    $ret['total_horas'] += $total_horas;
                                    $ret['description'] = $post['post_content'];
                                    $ret['valor_atual'] = $valor_atual;
                                    $ret['incremento'] = $incremento;
                                    $ret['exec'] = true;
                                }
                            }else{
                                $ret[$v] = $post['config'];
                            }
                        }
                    }
                }
            }else{
                $post = Post::Find($id);
                if($post->count() > 0){
                    if(isset($post['config'])){
                        $ret = $post['config'];
                        $ret['config'] = $post['config'];
                        $ret['id'] = $id;
                        $ret['exec']=true;
                        $ret['post_title']=@$post['post_title'];
                        $ret['post_content']=$post['post_content'];
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para exibir o valor e horas dos contratos atraves de uma routa /leiloes/get-data-contrato/{token}
     */
    public function view_data_contrato($token=false){
        if(isset($_REQUEST['config']['itens'])&&is_array($_REQUEST['config']['itens'])){
            $arr = $_REQUEST['config']['itens'];
        }else{
            $arr = explode("_",$token);
        }
        $ret = [];
        if(is_array($arr)){
            $ret =  $this->get_data_contrato($arr,true);
        }elseif(!empty($token)){
            $ret =  $this->get_data_contrato($token);
        }
        return response()->json($ret);
    }
    /**
     * Metodo para verificar vinculo de contrato com um leilão
     * @param string $token //token do contrato
     * @return boolean $ret
     */
    public function is_linked_leilao($token=false,$exibe_tudo=false){
        $ret = false;
        if($token){
            $d = Post::where('config','LIKE', '%"'.$token.'"%')->where('post_type','=',$this->post_type)->where('post_status','!=','trash')->get()->toArray();
            if($d){
                if($exibe_tudo){
                    $ret = $d[0];
                }else{
                    $ret = $d[0]['post_title'];
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para verificar montar um array com os contratos disponiveis
     * @param int $id_cliente, boolean $is_linked
     * @return array $ret
     */
    public function array_contratos($id_cliente=false, $is_linked=true){
        $ret = array();
        $r = [];
        if($id_cliente){
            $r = Qlib::sql_array("SELECT token,post_title FROM posts WHERE post_status='publish'  AND post_type='produtos' AND config LIKE '%\"cliente\":\"".$id_cliente."\"%'",'post_title','token');
        }else{
            $r = Qlib::sql_array("SELECT token,post_title FROM posts WHERE post_status='publish' AND post_type='produtos'",'post_title','token');
        }
        if($is_linked && is_array($r)){
            foreach ($r as $k => $v) {
                if($leiao=$this->is_linked_leilao($k)){
                    $ret[$k] = ['label'=>$v.' Cadastrado no '.$leiao,'attr_option'=>'disabled'] ;
                }else{
                    $ret[$k] = ['label'=>$v,'attr_option'=>''] ;
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para finalizar o leilao
     * @param int $leilao_id, $dl
     */
    public function finaliza_leilao($leilao_id,$dl=false){
        $ret['exec'] = false;
        if(!$dl && $leilao_id){
            $dl = Post::Find($leilao_id);
        }
        if($dl){
            $d1 = @$dl['config']['termino'].' '.@$dl['config']['hora_termino'];
            $d2 = Qlib::dataLocalDb();
            $sd1 = strtotime($d1);
            $sd2 = strtotime($d2);
            if($sd1<$sd2){
                //Pegar e salvar o ultimo lance
                $lv = (new LanceController)->ultimo_lance($leilao_id);
                $ret['lance_vencedor']=$lv;
                $ret['s_s'] = Qlib::update_postmeta($leilao_id,$this->c_meta_situacao,'f');
                $ret['s_d'] = Qlib::update_postmeta($leilao_id,$this->c_meta_detalhes_situacao,Qlib::lib_array_json([
                    'data_situacao'=>Qlib::dataBanco(),
                    'label'=>'Finalizado',
                    'color'=>'danger',
                ]));
                if($ret['s_s']){
                    //Salver o id do lance vencedor..

                    $ret['exec'] = true;
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo de display de terminio de lelao
     */
    public function info_termino($leilao_id=false,$dl=false){
        $ret['exec'] = false;
        $ret['termino'] = false;
        $ret['html'] = false;
        $ret['time'] = false;
        $ret['data'] = false;
        $ret['color'] = 'text-success';
        $ret['situacao_leilao'] = false;
        if(!$dl && $leilao_id){
            $dl = Post::Find($leilao_id);
        }
        if($dl){
            $d1 = @$dl['config']['termino'].' '.@$dl['config']['hora_termino'];
            $d2 = Qlib::dataLocalDb();
            $sd1 = strtotime($d1);
            $sd2 = strtotime($d2);
            if($sd1<$sd2){
                //marcar leilão como terminado caso não tenha sido feito ainda ou se está na reciclagem
                $situacao_leilao = Qlib::get_postmeta($leilao_id,$this->c_meta_situacao,true);
                if($situacao_leilao!='f' && $situacao_leilao!='r'){
                    $ret = $this->finaliza_leilao($leilao_id,$dl);
                }
                $ret['exec'] = true;
                $ret['situacao_leilao'] = $situacao_leilao;
                $ret['termino'] = true;
                $ret['time'] = Qlib::dataExibe(@$dl['config']['termino']);
                $ret['html'] = 'Finalizado ('.Qlib::dataExibe(@$dl['config']['termino']).' '.@$dl['config']['hora_termino'].')';
                $ret['quase_termino'] = Qlib::quase_termino($d1,$d2,3);
                $ret['data0'] = Qlib::dataExibe(@$dl['config']['termino']);
                $ret['hora'] = $dl['config']['hora_termino'];
                $ret['data'] = Qlib::dataExibe(@$dl['config']['termino']).' às '.$dl['config']['hora_termino'];
            }else{
                $termino = Qlib::diffDate2($d1,$d2,false,true,true);
                $ret['quase_termino'] = Qlib::quase_termino($d1,$d2,3);
                $ret['html'] = $termino;
                $ret['time'] = Qlib::diffDate2($d1,$d2,false,true);
                $ret['data0'] = Qlib::dataExibe(@$dl['config']['termino']);
                $ret['hora'] = $dl['config']['hora_termino'];
                $ret['data'] = Qlib::dataExibe(@$dl['config']['termino']).' às '.$dl['config']['hora_termino'];
                $ret['color'] = @$ret['quase_termino']['color'];
                $ret['exec'] = true;
            }
        }
        return $ret;
    }
    /**
     * Metodo para atualização a situalão de um leilão de acorto com a necessidade
     * @param integer $leilao_id, string $situacao
     */
    public function atualiza_situacao($leilao_id,$situacao){
        $ret = Qlib::update_postmeta($leilao_id,$this->c_meta_situacao,$situacao);
        return $ret;
    }
    /**
     * Retorna um arra com todos status de situacação do leilão
     */
    public function arr_situacao($val=false,$type=1){
        if($type==2){
            $arr = [
                'ea'=>'<span class="text-success">Em Andamento</span>',
                'f'=>'<span class="text-danger">Leilão Finalizado</span>',
                'a'=>'<span class="text-warning">Aguardando Publicação</span>',
                'r'=>'<span class="text-info">Reciclagem solicitada</span>',
            ];
        }else{
            $arr = [
                'ea'=>'Em Andamento',
                'f'=>'Leilão Finalizado',
                'a'=>'Aguardando Publicação',
                'r'=>'Reciclagem solicitada',
            ];
        }
        if($val){
            return $arr[$val];
        }else{
            return $arr;
        }
    }
    /**
     * Metodo Mostrar o lance vencedor
     * @param integer $leilao_id, array $dl=dados dos leilão, string $get_meta_tipo=tipo de dados para trazer junto
    */
    public function get_lance_vencedor($leilao_id=false,$dl=false,$get_meta_tipo=false){
        $ret=false;
        if(!$dl && $leilao_id){
            $dl = $this->get_leilao($leilao_id);
        }
        if($dl){
            $termino = $this->info_termino($leilao_id,$dl);
            if(isset($termino['termino']) && $termino['termino']){
                $lv = (new LanceController)->ultimo_lance($leilao_id,true);
                if(isset($lv['valor_lance']) && ($vl=$lv['valor_lance'])){
                    if($get_meta_tipo){
                        if($get_meta_tipo=='ultimo_lance'){
                            $ret['valor'] = Qlib::valor_moeda($vl,'R$ ').' ('.Qlib::getNickName(@$lv['author']).') ';
                            $ret[$get_meta_tipo] = $lv;
                        }else{
                            $ret = Qlib::valor_moeda($vl,'R$ ').' ('.Qlib::getNickName(@$lv['author']).') ';
                        }
                    }else{
                        $ret = Qlib::valor_moeda($vl,'R$ ').' ('.Qlib::getNickName(@$lv['author']).') ';
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para listar detalhes do leiloes publicado no front
     */
    public function leiloes_publicos($dados=false){
        $pst = new PostController;
        $seg1 = request()->segment(1); //link da página em questão
        $seg2 = request()->segment(2); //link da página em questão
        $logado = Auth::check();
        if($logado){
            //checar se a conta destá verifcadada
            $uc = new UserController;
            $iv=$uc->is_verified();
            if(!$iv){
                // dd(url('/email/verify'));
                // return redirect()->to(url('/email/verify'));
                $route = route('verification.notice');
                // return redirect()->route('verification.notice');
                echo header('Location: '.$route);
                exit;
            }
            $user_id = Auth::id();
            //Se usuario não aceitou os termmos e não for um administrador
            if(!$uc->aceito_termo($user_id) && !Qlib::isAdmin()){
                $me = 'É necessário aceitar os termos para continuar';
                $url = url('/meu-cadastro?rbase='.base64_encode(Qlib::UrlAtual()).'&mbase='.base64_encode($me));
                // dd(session()->all());
                echo header('Location: '.$url);
                // return Redirect::to($url)->with('alert-danger', $me);
                exit;
            }
        }
        if($seg2){
            if($logado && $user_id){
                if(isset($_GET['like']) && $_GET['like'] == 's'){
                    //Seguir este leilão
                    $seguir = $this->seguidor_update($seg2,$user_id,true);
                    if(isset($seguir['exec']) && $seguir['exec']){
                        $route = Qlib::UrlAtual();
                        $route = str_replace('?like=s','',$route);
                        // return redirect()->route('verification.notice');
                        echo header('Location: '.$route);
                        exit;
                    }
                }
            }
            $_GET['filter']['ID'] = $seg2;
            $_GET['filter']['post_status'] = 'publish';
            $dlt = $this->get_leilao($seg2);
            // dd($dlt);
            if(isset($dlt['post_status']) && $dlt['post_status']=='publish'){
                $dl[0] = $dlt;
                //Verificar se estão devidamente publicados
                if(!isset($dl[0]['config']['status'])){
                    $title = __('Página não encontrada');
                    $titulo = $title;
                    $ret = [
                        'dados'=>[],
                        'config'=>[
                            'title'=>$title,
                            'titulo'=>$titulo,
                            'exec'=>false,
                            'mens'=>Qlib::formatMensagemInfo('Página não encontrada!','danger')
                        ],
                    ];
                    return view('site.leiloes.list',$ret);
                }
                if((isset($dl[0]['config']['status']) && isset($dl[0]['post_status'])) && ($dl[0]['config']['status']!='publicado' || $dl[0]['post_status']!='publish')){
                    $title = __('Página aguardando publicação ou não encontrada');
                    $titulo = $title;
                    $ret = [
                        'dados'=>[],
                        'config'=>[
                            'title'=>$title,
                            'titulo'=>$titulo,
                            'exec'=>false,
                            'mens'=>Qlib::formatMensagemInfo('Página em edição ou não encontrada!','danger')
                        ],
                    ];
                    return view('site.leiloes.list',$ret);

                }
                $title = $dl[0]['post_title'];
                $titulo = $title;
                $c_l = isset($dl[0]['config'])?$dl[0]['config']:[]; //config leilao
                // $d1 = @$dl[0]['config']['termino'].' '.@$dl[0]['config']['hora_termino'];
                $it = isset($dlt['termino']) ? $dlt['termino'] : $this->info_termino($dl[0]['ID']);
                $dl[0]['finalizado'] = @$it['termino']; //inform para a view se o leilão termniou ou não
                $termino = $it['html'];
                $lc = new LanceController;
                $ultimoLance = $lc->ultimo_lance($seg2);
                // $arr_lances = self::arr_lances($seg2,$dl[0],20);
                if($ultimoLance){
                    $lance_atual = Qlib::valor_moeda($ultimoLance,'R$ ');
                }else{
                    $lance_atual = '<h6>SEM LANCES</h6>';
                }
                //list lances
                $ll = $lc->get_lances($dl[0]['ID'],true);
                $dl[0]['link_thumbnail'] = Qlib::get_thumbnail_link($dl[0]['ID']);
                $dl[0]['the_permalink'] = Qlib::get_the_permalink($dl[0]['ID']);
                $dl[0]['termino'] = $termino;
                $dl[0]['lance_atual'] = $lance_atual;
                $dl[0]['info_termino'] = $it;
                $dl[0]['list_lances'] = $ll['list'];
                $dl[0]['total_lances'] = $ll['total'];
                $dl[0]['lance_vencedor'] = $this->get_lance_vencedor($dl[0]['ID'],$dl[0]);
                $dl[0]['arr_lances'] = $this->arr_lances($dl[0]['ID'],$dl[0]);
                $dl[0]['nome_contrato'] = Qlib::buscaValorDb0('posts','token',@$c_l['contrato'],'post_title');
                $dl[0]['nome_responsavel'] = Qlib::buscaValorDb0('users','id',$dl[0]['post_author'],'name');
                //Marcar visualização
                $views = $this->update_views($dl[0]['ID']);

                $ret = [
                    'dados'=>$dl[0],
                    'config'=>[
                        'title'=>$title,
                        'titulo'=>$titulo,
                        'views'=>$views,
                        'exec'=>true,
                        'mens'=>false,
                    ],
                ];

            }else{
                $title = __('Página não encontrada');
                $titulo = $title;
                $ret = [
                    'dados'=>[],
                    'config'=>[
                        'title'=>$title,
                        'titulo'=>$titulo,
                        'exec'=>false,
                        'mens'=>Qlib::formatMensagemInfo('Página não encontrada!','danger'),
                    ],
                ];
            }
            return view('site.leiloes.list',$ret);
        }else{
            $queryPost = $pst->queryPost($_GET,$dados,$this->post_type);
            $dados = [];
            if(isset($queryPost['post']) && $queryPost['post']->count()>0 && is_object($queryPost['post'])){
                $arrPost = $queryPost['post']->toArray();
                // dd($arrPost['data']);
                $meta_pago = Qlib::qoption('meta_pago') ? Qlib::qoption('meta_pago') : 'pago';
                foreach ($arrPost['data'] as $kp => $vp) {
                    $pago = Qlib::get_postmeta(@$vp['ID'],$meta_pago,true);
                    $tmno = $this->info_termino($vp['ID']);
                    if($pago!='s' && !@$tmno['termino']){
                        //Listar no site apenas leiloes não pagos e que não foram terminados
                        if(isset($vp['config']['itens']) && is_array($vp['config']['itens']) && count($vp['config']['itens'])>0){
                            $dados[$kp] = $vp;
                            $src = Qlib::get_thumbnail_link($vp['ID']);
                            $dados[$kp]['src'] = $src;
                            $dados[$kp]['proximo_lance'] = (new LanceController) ->proximo_lance($vp['ID'],$vp);
                            $dados[$kp]['link'] = Qlib::get_the_permalink($vp['ID'],$vp);
                            $dados[$kp]['link_edit_admin'] = $this->get_link_edit_admin($vp['ID'],$vp);
                        }elseif(isset($vp['config']['contrato']) && !empty($vp['config']['contrato'])){
                            $dados[$kp] = $vp;
                            $src = Qlib::get_thumbnail_link($vp['ID']);
                            $dados[$kp]['proximo_lance'] = (new LanceController) ->proximo_lance($vp['ID'],$vp);
                            // Qlib::lib_print($src);
                            $dados[$kp]['link'] = Qlib::get_the_permalink($vp['ID'],$vp);
                            if(empty($src) && $vp['config']['contrato']){
                                $id_contrato = Qlib::buscaValorDb0('posts','token',$vp['config']['contrato'],'ID');
                                $src = Qlib::get_thumbnail_link($id_contrato);
                            }
                            $dados[$kp]['src'] = $src;
                            $dados[$kp]['link'] = Qlib::get_the_permalink($vp['ID'],$vp);
                            $dados[$kp]['link_edit_admin'] = $this->get_link_edit_admin($vp['ID'],$vp);
                        }
                    }
                }
            }
            // dd($dados);
        }
        $queryPost['config']['exibe'] = 'html';
        $route = $this->post_type;
        $title = 'Leilão';
        $titulo = $title;

        $view   = '/'.Qlib::get_slug_post_by_id(18);
        //if(isset($queryPost['post']));
        $ret = [
            'dados'=>$dados,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryPost['campos'],
            'post_totais'=>$queryPost['post_totais'],
            'titulo_tabela'=>$queryPost['tituloTabela'],
            'arr_titulo'=>$queryPost['arr_titulo'],
            'config'=>$queryPost['config'],
            'routa'=>$route,
            'view'=>$view,
            'i'=>0,
        ];
        //REGISTRAR EVENTOS
        // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
        return view('site.leiloes.list',$ret);
    }
    /**
     * Metodo para recupaerar todos os dados de um leilao
     * @param int $leilao_id, array $data = caso esse parametro for informado sistema entende que pode aproveitar como cache, array $config = lista de solicitações para informar junto.
     */
    public function get_leilao($leilao_id=false,$data=false,$config=[]){
        $ret = false;
        if(!$data && $leilao_id){
            // $data = Post::where('ID','=',$leilao_id)->where('post_status', '=', 'publish')->get()->toArray();// Post::Find($leilao_id);
            $data = Post::where('ID','=',$leilao_id)->get()->toArray();// Post::Find($leilao_id);
            if(isset($data[0])){
                $data = $data[0];
            }
        }
        if(isset($data['ID']) && ($leilao_id=$data['ID'])){
            $data['id'] = $data['ID'];
        }else{
            return $ret;
        }
        $lc = new LeilaoController;
        $lac = new LanceController;
        $user_id = Auth::id();
        if($data && isset($data['config']['contrato'])){
            $contrato = $lc->get_data_contrato($data['config']['contrato']);
            if(isset($contrato['post_content']) && !empty($contrato['post_content'])){
                $data['post_content'] = $contrato['post_content'];
            }

            if(isset($contrato['config'])){
                foreach ($contrato['config'] as $kc => $vc) {
                    $data['config'][$kc] = $vc;
                }
            }
        }
        //Verifica se o leilao ja terminou e informa dos dados do ga
        // $data['dg'] = $lc->get_lance_vencedor($leilao_id,$data,'ultimo_lance');
        $data['termino']            = $lc->info_termino($leilao_id);
        $data['proximo_lance']      = $lac->proximo_lance($leilao_id,$data);
        $data['exibe_btn_comprar']  = false;
        $data['link_btn_comprar']  = false;
        if($data['proximo_lance'] && ($pl=$data['proximo_lance']) && isset($data['config']['valor_atual']) && !empty($data['config']['valor_atual'])){
            //Exibição do preço da hora no proximo lance.
            if(isset($data['config']['total_horas']) && !empty($data['config']['total_horas'])){
                $data['total_horas_lance'] = round($data['proximo_lance']/$data['config']['total_horas']);
            }
            //Solicitação para exibição de desconto sobre o preço atual
            $va = Qlib::precoBanco($data['config']['valor_atual']);
            $data['valor_atual'] = Qlib::valor_moeda($va,'R$');
            if($pl<$va){
                //tudo se realiza apenas se o proximo lance for menor que valor atual do pacote
                $data['desconto_s_atual']['valor'] = 0;
                $data['desconto_s_atual']['html'] = 0;
                $data['desconto_s_atual']['porcento'] = 0;
                $desc = $va - $pl;
                if($desc){
                    $porc = ($desc * 100)/$va;
                    $data['desconto_s_atual']['valor'] = $desc;
                    $data['desconto_s_atual']['html'] = Qlib::valor_moeda($desc,'R$');
                    $data['desconto_s_atual']['porcento'] = round($porc,2);
                }
            }
        }
        //Exibir um botão para seguir este leilão
        if(Auth::check()){
            //link add
            if($lc->is_liked($data['ID'],$user_id)){
                $data['link_seguir'] = 'javascript:seguir_leilao(\''.$data['ID'].'\',\''.$user_id.'\',\'remove\');';
                $data['link_seguir_color'] = 'danger';
                $data['link_seguir_title'] = __('Parar de seguir este leilão');
                $data['link_seguir_label'] = __('Seguindo');
            }else{
                $data['link_seguir'] = 'javascript:seguir_leilao(\''.$data['ID'].'\',\''.$user_id.'\',\'add\');';
                $data['link_seguir_color'] = 'primary';
                $data['link_seguir_title'] = __('Começar a seguir este leilao');
                $data['link_seguir_label'] = __('Seguir');
            }
        }else{
            $data['link_seguir'] = 'javascript:alerta_modal_login_seguir(\''.$data['ID'].'\');';
            $data['link_seguir_color'] = 'primary';
            $data['link_seguir_title'] = __('Começar a seguir este leilao');
            $data['link_seguir_label'] = __('Seguir');
        }
        $data['total_seguidores'] = $lc->total_seguidores($data['ID']);
        $data['link_thumbnail'] = Qlib::get_thumbnail_link($data['ID']);
        $data['link_leilao'] = $lc->get_link_front($data['ID']);
        $data['total_views'] = $lc->get_total_views($data['ID']);
        $data['situacao_html'] = $lc->situacao_html($data['ID']);
        $data['situacao'] = $lc->situacao($data['ID']);
        if($data['situacao']=='f'){
            $ranking = $lc->get_ranking($data['ID']);
            $data['ranking'] = $ranking;
        }
        if($data['proximo_lance'] && ($pl=$data['proximo_lance']) && isset($data['config']['valor_venda']) && !empty($data['config']['valor_venda'])){
            //Exibir botão comprar
            $vv = Qlib::precoBanco($data['config']['valor_venda']);
            if($pl<$vv){
                //Se proximo lance for menor que o valor de venda e tambem o primeiro lance
                if(isset($data['config']['valor_r']) && !empty($data['config']['valor_r'])){
                    //valor de rescição
                    $vr = Qlib::precoBanco($data['config']['valor_r']);
                    //incremento
                    $inc = Qlib::precoBanco(@$data['config']['incremento']);
                    //verifica se é o primeiro lance valor de rescisão + incremento = valor do proximo lance
                    // if($pl==($vr+$inc)){
                    //     $data['exibe_btn_comprar'] = true;
                    // }else{
                        //Se proximo lance for menor que o valor de venda aparece o botão aparece de forma randomica
                        $value = rand(0,1) == 1;
                        $data['exibe_btn_comprar'] = $value;
                    //}
                }else{
                    //Se proximo lance for menor que o valor de venda aparece o botão aparece de forma randomica
                    $value = rand(0,1) == 1;
                    $data['exibe_btn_comprar'] = $value;
                }
                if($data['exibe_btn_comprar']){
                    $link_btn_comprar = route('login').'?r='.Qlib::UrlAtual();
                    if(Auth::check()){
                        $link_btn_comprar = $lc->get_link_pagamento($leilao_id,'02');
                        //type=2 para gerar um pagamento mediagem pagameno por valor de compra
                    }
                    $data['link_btn_comprar'] = $link_btn_comprar;
                }
            }
        }
        //Historico de lances..
        $ll = $lac->get_lances($data['ID'],true);
        if(isset($ll['total'])){
            $data['list_lances'] = @$ll['list'];
            $data['total_lances'] = $ll['total'];

        }
        $ret = $data;
        // dd($ret);
        return $ret;
    }
    public function arr_lances($leilao_id=false,$data=false,$total=10){
        $ret = [];
        $dl = $this->get_leilao($leilao_id,$data);
        $campo_valor = 'valor_r';
        if(isset($dl['config']['incremento']) && isset($dl['config'][$campo_valor])){
            $inc=Qlib::precoBanco($dl['config']['incremento']);
            $li=Qlib::precoBanco($dl['config'][$campo_valor]);
            $l_at=(new LanceController)->ultimo_lance($leilao_id);//lance atual
            // dump($li,$l_at,$inc);
            $primeiro_lance_com_incremento = true;  // o sistema considera o primeiro lance como sendo o valor de rescisão + incremento
            if($primeiro_lance_com_incremento){
                if($l_at>0){
                    $li=$l_at+$inc;
                }else{
                    $li=$li+$inc;
                }
            }else{
                if($l_at>0){
                    $li=$l_at+$inc;
                }
            }
            if($inc>0 && $li>0){
                $vl = $li;
                foreach (range(1,$total) as $k => $v) {
                    // echo $vl.'<br>';
                    $ret[$k] = ['valor'=>$vl];
                    $vl = $vl+$inc;
                }
            }
        }
        return $ret;
    }
    public function get_link_edit_admin($post_id,$post=false){
        if(!$post && $post_id){
            $post = post::Find($post_id);
        }
        $ret = config('app.url').'/admin/leiloes_adm/'.$post_id.'/edit?redirect='.Qlib::UrlAtual().'';
        return $ret;
    }
    /**
     * Metodo para pegar o link do leilao no painel de admin
     * @param int $post_id
     * @return string $ret
     */
    public function get_link_front($post_id){
        $ret = config('app.url').'/leiloes-publicos/'.$post_id;
        return $ret;
    }
    /**
     * Metodo para pegar o link do leilao no painel de admin
     * @param int $post_id
     * @return string $ret
     */
    public function get_link_admin($post_id){
        $ret = config('app.url').'/admin/leiloes_adm/'.$post_id.'/edit';
        return $ret;
    }
    /**
     * Metodo para pegar o link publico do leilao
     * @param int $post_id
     * @return string $ret
     */
    public function get_link($post_id){
        $ret = config('app.url').'/leiloes-publicos/'.$post_id;
        return $ret;
    }
    /**
     * Metodo para pegar o link de pagamento do leilão
     * @param int $post_id,string $type 01 para pagamento de leilao ganho 02 para pagmento direto
     */
    public function get_link_pagamento($post_id,$type='01'){
        $type = '-'.$type;
        $token = Qlib::buscaValorDb0('posts','id',$post_id,'token');
        $ret = config('app.url').'/payment/'.$token.$type;
        return $ret;
    }
    /**
     * Metodo Notificar o termino do leilao ao ganhador ou ao Dono doleilao
     * @param int $post_id o id do leilão, string $tipo_responsavel pode ser ganhador ou autor para o dono do leilao
     */
    public function notifica_termino($post_id,$tipo_responsavel='ganhador',$dl=false){
        $ret['exec'] = false;
        if($post_id &&!$dl){
            // $dl = Post::Find($post_id) ; //dados do leilao
            $dl = $this->get_leilao($post_id) ; //dados do leilao
        }
        if($dl){
            $greeting = false;
            if($tipo_responsavel=='ganhador'){
                $meta_notific = 'notifica_email_termino_leilao';
                $mensagem = '
                <h1>Parabéns {nome} </h1>
                <p>Seu lance de <b>{valor_lance}</b> para o <b>{nome_leilao}</b> foi vencedor</p>
                <p>para efetuar o pagamento use o botão abaixo!</p>
                ';
            }
            $meta_notific = 'notifica_termino_leilao_'.$tipo_responsavel;
            $status_leilao = false;
            if($tipo_responsavel=='responsavel'){
                $greeting = 'Olá {nome}';
                $mensagem = '
                <p>Seu leilão <b>{nome_leilao}</b> está finalizado.<br>{status_leilao}
                ';
            }elseif($tipo_responsavel=='admin'){
                $mensagem = '
                <p>Leilão <b>{nome_leilao}</b> está finalizado.<br>{status_leilao}
                ';
            }
            $meta_pago = Qlib::qoption('meta_pago') ? Qlib::qoption('meta_pago') : 'pago';
            //Verifica quem é o ganhador
            $dg = $this->get_lance_vencedor($post_id,$dl,'ultimo_lance');//dados do ganhador
            //Verifica se ja foi enviado a notificação antes
            $verifica_notific = Qlib::get_postmeta($post_id,$meta_notific,true);
            $pago = Qlib::get_postmeta($post_id,$meta_pago,true);
            if($verifica_notific=='s'){
                $ret['mens'] = 'E-mail ja foi enviado';
                return $ret;
            }
            if($pago=='s'){
                $ret['mens'] = 'Não é ncessário enviar E-mail para leilão pago';
                return $ret;
            }
            if(isset($dg['ultimo_lance']['id']) && ($id_lance=$dg['ultimo_lance']['id']) && $verifica_notific!='s'){
                $ul = $dg['ultimo_lance'];
                $user_id = $ul['author'];
                $no = explode(' ',Qlib::buscaValorDb0('users','id',$user_id,'name'));
                $nome = @$no[0];
                $leilao_id = $dl['ID'];
                $nome_leilao = $dl['post_title'];//Qlib::buscaValorDb0('posts','id',$ul['leilao_id'],'post_title');
                $link_pagamento = $this->get_link_pagamento($leilao_id);
                $valor_lance = $ul['valor_lance'];
                if($tipo_responsavel=='responsavel' || $tipo_responsavel=='admin'){
                    $status_leilao = '<p>O contrato foi arrematado por <b>{cliente}</b>, no valor de <b>{valor_lance}</b> estamos aguardando o pagamento</p>';
                    $status_leilao = str_replace('{cliente}',$nome,$status_leilao);
                    $status_leilao = str_replace('{valor_lance}',Qlib::valor_moeda($valor_lance,'R$ '),$status_leilao);

                }
                // $greeting = str_replace('{nome}',$nome,$greeting);
                $mensagem = str_replace('{nome}',$nome,$mensagem);
                $mensagem = str_replace('{valor_lance}',Qlib::valor_moeda($valor_lance,'R$ '),$mensagem);
                $mensagem = str_replace('{nome_leilao}',$nome_leilao,$mensagem);
                $mensagem = str_replace('{link_pagamento}',$link_pagamento,$mensagem);
                $mensagem = str_replace('{status_leilao}',$status_leilao,$mensagem);

                $arr_notification = [
                    'type' => 'notifica_finalizado',
                    'lance_id' => $id_lance,
                    'subject' => 'Leilão Finalizado',
                    // 'link_pagamento' => $link_pagamento,
                    'mensagem' => $mensagem,
                    'dleilao' => $dl,
                    'tipo_responsavel' => $tipo_responsavel,
                    'link_leilao' => $dl['link_leilao'],
                ];
                if($tipo_responsavel=='responsavel'){
                    $user_id = isset($dl['config']['cliente'])?$dl['config']['cliente']:false;
                }elseif($tipo_responsavel=='admin'){
                    $user_id = isset($dl['config']['admin'])?$dl['config']['admin']:1;
                }
                if(!$user_id){
                    $ret['mens'] = 'ID de usuário não inválido';
                    return $ret;
                }
                $user = User::find($user_id);
                if($user){
                    $ret['notifica_painel'] = $user->notify(new ganhadorPainelNotification($arr_notification));
                }
                if($tipo_responsavel=='ganhador'){
                    $ret = (new LeilaoController)->enviar_email($arr_notification);
                }elseif($tipo_responsavel=='responsavel'){
                    $user->notify(new EmailDonoLeilaoNotification($arr_notification));
                    $ret['exec'] = true;
                }elseif($tipo_responsavel=='admin'){
                    $user->notify(new EmailDonoLeilaoNotification($arr_notification));
                    $ret['exec'] = true;
                }
                // return $ret;
                if($ret['exec']){
                    $ret['save'] = Qlib::update_postmeta($post_id,$meta_notific,'s');
                }
            }else{

            }
            // if(isset($dg['ultimo_lance']['valor_lance']) && isset($dg['ultimo_lance']['author'])){
            //     $valor_lance = $dg['ultimo_lance']['valor_lance'];
            //     $autor_lance = $dg['ultimo_lance']['author'];
            //     $autor_leilao = $dl['post_author'];
            //     $subject = 'Leilão Finalizado';
            //     // $arr = implode($valor_lance, $autor_lance, $autor_leilao);
            //     dd($valor_lance);
            // }
        }
        return $ret;
    }
    /**
     * Metodo Notificar o adminstrador moderador toda vez leilão precisar de publicação no site
     * @param int $post_id o id do leilão, string $tipo_responsavel pode ser ganhador ou autor para o dono do leilao
     */
    public function notific_update_admin($post_id,$tipo_responsavel='admin'){
        //Uso $ret = (new LeilaoController)->notific_update_admin($post_id,'admin');
        $ret['exec'] = false;
        $dl = Post::Find($post_id); //dados do leilao
        if($dl && $tipo_responsavel=='admin'){
            $meta_notific = 'notifica_email_moderador';
            // //Verifica se ja foi enviado a notificação antes
            $verifica_notific = Qlib::get_postmeta($post_id,$meta_notific,true);
            if($verifica_notific=='s'){
                $ret['mens'] = 'E-mail ja foi enviado';
                return $ret;
            }
            //Verifica se qual o status
            $dl = $dl->toArray();
            if(isset($dl['config']['status']) && $dl['config']['status']=='publicado' && isset($dl['post_status']) && $dl['post_status']!='publish' ){
                //Enviar notificação

                // $ul = $dg['ultimo_lance'];
                // dd($ul);
                $mensagem = '
                <h1>Atenção Sr. Moderador</h1>
                <p>O <b>{nome_leilao}</b> foi publicado por <b>{nome}</b>, que é o responsável no site, e aguarda sua conferência e liberação.</p>
                <p>Use o botão abaixo para acessar o painel de administração dele!</p>';
                $no = explode(' ',Qlib::buscaValorDb0('users','id',$dl['post_author'],'name'));
                $nome = @$no[0];
                $nome_leilao = $dl['post_title'];
                // $valor_lance = $ul['valor_lance'];
                $mensagem = str_replace('{nome}',$nome,$mensagem);
                // $mensagem = str_replace('{valor_lance}',Qlib::valor_moeda($valor_lance,'R$ '),$mensagem);
                $mensagem = str_replace('{nome_leilao}',$nome_leilao,$mensagem);
                $ret = (new LeilaoController)->enviar_email([
                    'type' => 'notific_update_admin',
                    'leilao_id' => $dl['ID'],
                    'dados_leilao' => $dl,
                    'subject' => 'Leilão aguardando publicação',
                    'mensagem' => $mensagem,
                ]);
                //Salvar meta trava de notificaçao

                // return $ret;
                if($ret['exec']){
                    $ret['save'] = Qlib::update_postmeta($post_id,$meta_notific,'s');
                }

            }
        }
        return $ret;
    }
    /**
     * Metodo para preparar o disparar o email
     * @param array $config
     * @return array $ret
     */
    public function enviar_email($config=[]){
        $ret['exec'] = false;
        if(is_array($config)){
            $lance_id = isset($config['lance_id']) ? $config['lance_id'] : false;
            $type = isset($config['type']) ? $config['type'] : false;
            $leilao_id = isset($config['leilao_id']) ? $config['leilao_id'] : false;
            $subject = isset($config['subject']) ? $config['subject'] : false;
            $mensagem = isset($config['mensagem']) ? $config['mensagem'] : false;
            if($type=='notific_update_admin'){
                $dados_leilao = isset($config['dados_leilao']) ? $config['dados_leilao'] : false;
                if(!$dados_leilao && $leilao_id){
                    $dados_leilao = Post::Find($leilao_id);
                }
                $user = new stdClass();
                // dd($dados_leilao);
                $user->name = 'Admin';
                $user->email = Qlib::qoption('email_gerente');
                $user->subject = $subject;
                $user->type = $type;
                $user->leilao_id = $leilao_id;
                // $user->link_pagamento = $link_pagamento;
                $user->mensagem = $mensagem;
                $user->nome_leilao = $dados_leilao['post_title'];
                $user->link_leilao_admin = (new LeilaoController)->get_link_admin($user->leilao_id);
            }else{
                if(!$lance_id){
                    return $ret;
                }
                $link_pagamento = isset($config['link_pagamento']) ? $config['link_pagamento'] : $this->get_link_pagamento($leilao_id);
                $dl = isset($config['dados_lance'])?$config['dados_lance']: lance::Find($lance_id); //dados do lance.
                if($dl){
                    // $leilao_id = isset($dl['leilao_id']) ? $dl['leilao_id'] : false;
                    $id_user = isset($dl['author']) ? $dl['author'] : false;
                    $nome_leilao = isset($config['nome_leilao']) ? $config['nome_leilao'] : Qlib::buscaValorDb0('posts','id',$leilao_id,'post_title');
                    $type = isset($config['type']) ? $config['type'] : false;
                    $d_user = isset($config['d_user']) ? $config['d_user'] : User::Find($id_user);
                    if(isset($d_user['email']) && !empty($d_user['email'])){
                        $user = new stdClass();
                        $n = explode(' ',$d_user['name']);
                        if(!isset($n[0])){
                            return $ret;
                        }
                        // $title_leilao = Qlib::buscaValorDb0('posts','id',$leilao_id,'post_title');
                        $user->name = ucwords($n[0]);
                        $user->email = $d_user['email'];
                        $user->subject = $subject;
                        $user->type = $type;
                        $user->leilao_id = $leilao_id;
                        $user->link_pagamento = $link_pagamento;
                        $user->mensagem = $mensagem;
                        $user->nome_leilao = $nome_leilao;
                        $user->link_leilao = (new LeilaoController)->get_link($user->leilao_id);

                    }else{
                        $ret['mens'] = __('Usuário não encontrado');
                    }
                }
            }
            if($user){
                $reder_em = new \App\Mail\leilao\lancesNotific($user);
                // return $reder_em;
                try {
                    Mail::send($reder_em);
                    $ret['exec'] = true;
                    $ret['mens'] = "Sem erros, enviado com sucesso! ".$user->email;

                } catch (\Throwable $e) {
                    $ret['mens'] = "Houve um ou mais erros. Segue abaixo: <br />";
                    $ret['mens'] .= $e->getMessage();

                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para listar informações do(s) vencedore(s) do leilao que terminou
     * @param int $id_user
     * @return array $ret
     */
    public function list_winner($id_user=false){
        $ret = false;
        $list = Post::where('post_type',$this->post_type)->
        where('post_status','publish')->
        where('config','LIKE','%status":"publicado%')->
        where('config','LIKE','%contrato":"%')->
        get();
        $lc = new LanceController;
        $cal = [];
        if($list){
            $arr_l = $list->toArray();
            foreach ($arr_l as $key => $value) {
                $df = $this->info_termino($value['ID'],$value);
                $ultimo_lance = $lc->ultimo_lance($value['ID'],true);
                if(isset($df['termino']) && $df['termino']){
                    if($id_user){
                        if(isset($ultimo_lance['author']) && $ultimo_lance['author']==$id_user){
                            $cal['finalizado'][$key]['id_leilao'] = $value['ID'];
                            // $cal['finalizado'][$key]['ultimo'] = $value['ID'];
                            // $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                            $cal['finalizado'][$key]['pago'] = Qlib::get_postmeta($value['ID'],'pago',true);
                            $cal['finalizado'][$key]['ul'] = $ultimo_lance;
                            $cal['finalizado'][$key]['exec'] = true;
                            $cal['finalizado'][$key]['df'] = $df;
                            $cal['finalizado'][$key]['link_pagamento'] = url('/').'/payment/'.$value['token'].'-01';
                        }
                    }else{
                        if(isset($df['termino']) && $df['termino']){
                            $cal['finalizado'][$key]['id_leilao'] = $value['ID'];
                            // $cal['finalizado'][$key]['ultimo'] = $value['ID'];
                            // $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                            $cal['finalizado'][$key]['ul'] = $ultimo_lance;
                            $cal['finalizado'][$key]['exec'] = true;
                        }
                    }
                }//else{
                //     $cal['finalizado'][$key]['id_leilao'] = $value['ID'];
                //     $cal['finalizado'][$key]['exec'] = false;
                // }
            }
        }
        return $cal;
    }
    /**
     * Metodo para listar e eviar notificação para os vencedores do leilao que terminou
     * sem parametros
     * @return array $ret
     */
    public function list_alert_winners(){
        $ret = 'false';
        $cal = false;
        //listar todos os leiloes terminados que não foram emitidos notificações
        $list = Post::where('post_type',$this->post_type)->
        where('post_status','publish')->
        where('config','LIKE','%status":"publicado%')->
        where('config','LIKE','%contrato":"%')->
        get();
        //Enviar a notificação.
        if($list->count()){
            $id_admin = (new UserController)->get_first_admin();
            $arr_l = $list->toArray();
            foreach ($arr_l as $key => $value) {
                $df = $this->info_termino($value['ID'],$value);
                if(isset($df['termino']) && $df['termino']){
                    $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                    $cal[$value['ID']] = $this->notifica_termino($value['ID'],'responsavel');
                    $cal[$value['ID']] = $this->notifica_termino($id_admin,'admin');
                    $ret = 'true';
                }
            }
        }
        // if($ret == 'true'){
        //     $arquivo = fopen(dirname(__FILE__).'/teste_notific.txt','a');
        //     $json = Qlib::lib_array_json($cal);
		// 	fwrite($arquivo, $json.',');
        // 	//Fechamos o arquivo após escrever nele
    	// 	fclose($arquivo);
        // }
        return $ret;
    }
    /**
     * Metodo para listar todos os leilãos finalizados
     * @param integer $user_id se for verdadeiro e estiver no front lista todos os leiloes ganhos do usuario,$status_pago=filtar os que tem status pago
     * @return array $ret
     */
    public function lista_leilao_terminado($user_id=false,$status_pago=false){
        $ret = false;
        $cal = false;
        $meta_status_pagamento = Qlib::qoption('meta_status_pagamento')?Qlib::qoption('meta_status_pagamento'):'pago';
        //listar todos os leiloes terminados que não foram emitidos notificações
        $list = Post::where('post_type',$this->post_type)->
        where('post_status','publish')->
        where('config','LIKE','%status":"publicado%')->
        where('config','LIKE','%contrato":"%')->
        orderBy('ID','desc')->
        get();
        // dd($list->toArray());
        $pc = new PaymentController();
        if($list->count()){
            $arr_l = $list->toArray();
            foreach ($arr_l as $key => $value) {
                $df = $this->info_termino($value['ID'],$value);
                $leilao_id = $value['ID'];
                if(isset($df['termino']) && $df['termino']){
                    $venc = $this->get_lance_vencedor($value['ID'],false,'ultimo_lance');
                    $venc = isset($venc['ultimo_lance'])?$venc['ultimo_lance']:false;
                    $sp = $this->is_paid($leilao_id,'string');
                    if($status_pago){
                        // dd($sp,$status_pago);
                        if($status_pago==$sp){
                            if($user_id){
                                if(isset($venc['author']) && $venc['author']==$user_id){
                                    // $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                                    $ret[$key] = $value;
                                    $ret[$key]['venc'] = $venc;
                                    $ret[$key]['term'] = $df;
                                    $link_leilao_front = $this->get_link_front($leilao_id);
                                    $situacao_pagamento = $pc->get_status_payment($leilao_id);
                                    $ret[$key]['status_pago'] = $sp;
                                    $ret[$key]['situacao_pagamento'] = $situacao_pagamento;
                                    $ret[$key]['link_leilao_front'] = $link_leilao_front;
                                    $ret[$key]['pago'] = $this->is_paid($leilao_id);
                                    $ret[$key]['link_pagamento'] = $this->get_link_pagamento($leilao_id);
                                }
                            }else{
                                // $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                                $leilao_id = $value['ID'];
                                $ret[$key] = $value;
                                $venc = isset($venc['ultimo_lance'])?$venc['ultimo_lance']:$venc;
                                $ret[$key]['venc'] = $venc;
                                $ret[$key]['term'] = $df;

                                $link_leilao_front = $this->get_link_front($leilao_id);
                                $sp = Qlib::get_postmeta($leilao_id,$meta_status_pagamento,true);
                                $situacao_pagamento = $pc->get_status_payment($leilao_id);
                                $ret[$key]['status_pago'] = $sp;
                                $ret[$key]['situacao_pagamento'] = $situacao_pagamento;
                                $ret[$key]['link_leilao_front'] = $link_leilao_front;
                                $ret[$key]['link_pagamento'] = $this->get_link_pagamento($leilao_id);
                                // dd($ret);
                            }
                        }
                    }else{
                        if($user_id){
                            if(isset($venc['author']) && $venc['author']==$user_id){
                                // $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                                $ret[$key] = $value;
                                $ret[$key]['venc'] = $venc;
                                $ret[$key]['term'] = $df;
                                $link_leilao_front = $this->get_link_front($leilao_id);
                                // if($sp=='s'){
                                //     $situacao_pagamento = '<span class="text-success">Pago</span>';
                                // }elseif($sp=='a'){
                                //     $situacao_pagamento = '<span class="text-warning">Pix Gerado</span>';
                                // }else{
                                //     $situacao_pagamento = '<span class="text-danger">Aguardando pagamento</span>';
                                // }
                                $situacao_pagamento = $pc->get_status_payment($leilao_id);
                                $ret[$key]['status_pago'] = $sp;
                                $ret[$key]['situacao'] = $this->situacao($leilao_id);
                                $ret[$key]['situacao_pagamento'] = $situacao_pagamento;
                                $ret[$key]['link_leilao_front'] = $link_leilao_front;
                                $ret[$key]['pago'] = $this->is_paid($leilao_id);
                                $ret[$key]['link_pagamento'] = $this->get_link_pagamento($leilao_id);
                            }
                        }else{
                            // $cal[$value['ID']] = $this->notifica_termino($value['ID'],'ganhador');
                            $leilao_id = $value['ID'];
                            $ret[$key] = $value;
                            $venc = isset($venc['ultimo_lance'])?$venc['ultimo_lance']:$venc;
                            $ret[$key]['venc'] = $venc;
                            $ret[$key]['term'] = $df;

                            $link_leilao_front = $this->get_link_front($leilao_id);
                            $sp = Qlib::get_postmeta($leilao_id,$meta_status_pagamento,true);
                            $situacao_pagamento = $pc->get_status_payment($leilao_id);
                            $ret[$key]['status_pago'] = $sp;
                            $ret[$key]['situacao'] = $this->situacao($leilao_id);
                            $ret[$key]['situacao_pagamento'] = $situacao_pagamento;
                            $ret[$key]['link_leilao_front'] = $link_leilao_front;
                            $ret[$key]['link_pagamento'] = $this->get_link_pagamento($leilao_id);
                            // dd($ret);
                        }
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo que retodos todos os contratos disponivel de um usuario para o cadastro de lelões
     * @param int $id = id do usuario
     * @return array
     */
    public function view_list_contrato($id) {
        //verificar que é administrador
        $ret['exec'] = false;
        $ret['arr_itens'] = false;
        if(Qlib::isAdmin()){
            $ret['id'] = $id;
            $arr_itens = $this->array_contratos($id);
        }else{
            if(Auth::check()){
                $id = Auth::id();
                $ret['id'] = $id;
                $arr_itens = $this->array_contratos($id);
            }
        }
        if(isset($arr_itens)){
            $ret['arr_itens'] = $arr_itens;
            $ret['campo']['config[contrato]'] = [
                'label'=>'Contratos*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>$arr_itens,'exibe_busca'=>'d-block',
                'event'=>'required onchange=dataContratos(this)',
                'tam'=>'12',
                'class'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][contrato',
                'class'=>'select2',
                ];
        }
        //se for lista todos do contrario somento os dele
        return response()->json($ret);
    }
    /**
     * Metodo para verificar se um leilao ja está pago
     * @param $leilao_id
     * @return boolean true || false
     */
    public function is_paid($leilao_id,$type='boolean') {
        $meta_pago = Qlib::qoption('meta_pago') ? Qlib::qoption('meta_pago') : 'pago';
        $st = Qlib::get_postmeta($leilao_id,$meta_pago,true);
        $ret = false;
        if($type=='string'){
            if(!$st){
                $st='n';
            }
            return $st;
        }else{
            if($st == 's'){
                $ret = true;
            }
        }
        return $ret;
    }
    /**
     * Metodo para verificar se um leilao está finalizado
     * @param $leilao_id
     * @return boolean true || false
     */
    public function is_end($leilao_id,$type='boolean') {
        $meta_finalizado = $this->c_meta_situacao;
        $st = Qlib::get_postmeta($leilao_id,$meta_finalizado,true);
        $ret = false;
        if($type=='string'){
            if(!$st){
                $st='n';
            }
            return $st;
        }else{
            if($st == 'f'){
                $ret = true;
            }
        }
        return $ret;
    }
    /**
     * Metodo que responde a routa para adicionar ou remover usuarios como seguidores de um leilao via ajax
     * @param integer $leilao_id,$user_id, boolern $type true para adicionar false para remover
     * @return array $ret
     */
    public function ger_seguidores(Request $request){
        $d = $request->all();
        $ret['exec'] = false;
        if(isset($d['ac']) && isset($d['leilao_id']) && isset($d['user_id'])){
            if($d['ac']=='add'){
                $ret = (new LeilaoController)->seguidor_update($d['leilao_id'],$d['user_id'],true);
            }
            if($d['ac']=='remove'){
                $ret = (new LeilaoController)->seguidor_update($d['leilao_id'],$d['user_id'],false);
            }
        }
        return response()->json($ret);
    }
    /**
     * Metodo para adicionar ou remover usuarios como seguidores de um leilao
     * @param integer $leilao_id,$user_id, boolern $type true para adicionar false para remover
     * @return array $ret
     */
    public function seguidor_update($leilao_id,$user_id,$type=true){
        $ret['exec'] = false;
        $ret['mens'] = false;
        if($leilao_id && $user_id){
            //verifica o type de interação
            $nome_campo = 'seguidor';
            $mens = false;
            if($type){
                //adiciona
                //uso: $ret = (new LeilaoController)->seguidor_update(60,1,true);
                $seguidores = Qlib::get_postmeta($leilao_id,$nome_campo,true);
                if($seguidores){
                    $seguidores = Qlib::lib_json_array($seguidores);
                    if(is_array($seguidores)){
                        if(!isset($seguidores[$user_id])){
                            $seguidores[$user_id] = ['seguidor'=>$user_id,'data'=>Qlib::dataLocal()];
                            $ret['exec'] = Qlib::update_postmeta($leilao_id,$nome_campo,Qlib::lib_array_json($seguidores));
                        }
                    }
                }else{
                    $ret['exec'] = Qlib::update_postmeta($leilao_id,$nome_campo,Qlib::lib_array_json([
                        $user_id=>['seguidor'=>$user_id,'data'=>Qlib::dataLocal()]
                    ]));
                }
                if($ret['exec']){
                    $mens = 'Seguidor <b>adicionado</b> com sucesso!!';
                }
            }else{
                //remove
                //uso: $ret = (new LeilaoController)->seguidor_update(60,1,false);
                $seguidores = Qlib::get_postmeta($leilao_id,$nome_campo,true);
                if($seguidores){
                    $seguidores = Qlib::lib_json_array($seguidores);
                    // $seguidores[$user_id] = ['seguidor'=>$user_id,'data'=>Qlib::dataLocal()];
                    if(is_array($seguidores)){
                        unset($seguidores[$user_id]);
                        $ret['exec'] = Qlib::update_postmeta($leilao_id,$nome_campo,Qlib::lib_array_json($seguidores));
                    }
                }
                if($ret['exec']){
                    $mens = 'Seguidor <b>removido</b> com sucesso!!';
                }
            }
            $seguidores = Qlib::get_postmeta($leilao_id,$nome_campo,true);
            $arr = Qlib::lib_json_array($seguidores);
            if($ret['exec'] && $mens){
                $ret['mens'] = Qlib::formatMensagemInfo($mens,'success');
            }
            if(Qlib::isAdmin()){
                $ret['seguidores'] = $arr;
            }

        }
        return $ret;
    }
    /**
     * Metodo para verificar se um usuario está seguindo um leilao
     * @param int $leilao_id, $user_id
     * @return boolean true|false
     */
    public function is_liked($leilao_id,$user_id){
        $nome_campo = 'seguidor';
        $ret = false;
        if($leilao_id && $user_id){
            $seguidores = Qlib::get_postmeta($leilao_id,$nome_campo,true);
            if($seguidores){
                $seguidores = Qlib::lib_json_array($seguidores);
                if(is_array($seguidores)){
                    if(isset($seguidores[$user_id])){
                        $ret = true;
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para contar os seguidores de um leilão
     * @param int $leilao_id
     * @return int
     */
    public function total_seguidores($leilao_id){
        $nome_campo = 'seguidor';
        $ret = 0;
        $seguidores = Qlib::get_postmeta($leilao_id,$nome_campo,true);
        if($seguidores){
            $seguidores = Qlib::lib_json_array($seguidores);
            if(is_array($seguidores)){
                $ret = count($seguidores);
            }
        }
        return $ret;
    }
    /**
     * Metodo para listar todos os seguidores de um leilao
     */
    public function get_seguidores($leilao_id){
        //uso $seguidores = (new LeilaoController()->get_seguidores($leilao_id);
        $nome_campo = 'seguidor';
        $ret = [];
        $seguidores = Qlib::get_postmeta($leilao_id,$nome_campo,true);
        if($seguidores){
            $seguidores = Qlib::lib_json_array($seguidores);
            if(is_array($seguidores)){
                foreach ($seguidores as $ks => $vs) {
                    $ret[$ks] = User::Find($ks);
                    if($ret[$ks]){
                        $ret[$ks]['seguindo_desde'] = $vs['data'];
                    }
                }
            }
        }
        return $ret;
    }
     /**
     * Metodo para listar todos os leilão sendo seguigos pelo usurio
     */
    public function list_seguindo($user_id=false){
        $this->middleware('auth');
        $user_id = $user_id ? $user_id : Auth::id();
        $seguindo = Post::select('posts.*')->join('postmeta','posts.id','=','postmeta.post_id')->
            where('posts.post_type','=',$this->post_type)->
            where('postmeta.meta_key','=','seguidor')->
            where('postmeta.meta_value','LIKE','%"'.$user_id.'"%')->
            orderBy('posts.ID','Asc')->
            get();
        if($seguindo->count() > 0){
            $seguindo = $seguindo->toArray();
            if(is_array($seguindo)){
                foreach ($seguindo as $ks => $vs) {
                    $seguindo[$ks] = $this->get_leilao($vs['ID']);
                }
            }
        }
        return $seguindo;
    }
     /**
     * Metodo para eviar para view todos os leilão sendo seguigos pelo usurio
     */
    public function leilao_list_seguindo($post_id_pagina){
        $this->middleware('auth');
        $user_id = Auth::id();
        $seguindo = $this->list_seguindo($user_id);
        global $post;
        // dd($post);
        $dados_pg = $post;
        // $dados_pg = Post::where('ID','=',$user_id)->get()->toArray();
        return view('site.leiloes.list_seguindo',['seguindo' => $seguindo,'dados_pg'=>$dados_pg]);
    }
    /**
     * Metodo mostrar o nome de um leilao apartir de um id
     */
    public function nome_leilao($leilao_id){
        return Qlib::buscaValorDb0('posts','id',$leilao_id,'post_title');
    }
    /**
     * Metodo para totalizar visualizações
     */
    public function get_total_views($leilao_id){
        return Qlib::get_postmeta($leilao_id,'views',true)?Qlib::get_postmeta($leilao_id,'views',true):0;
    }
    /**
     * Metodo para atualizar visualizações
     */
    public function update_views($leilao_id,$dl=false){

        // dd($dl);
        $total_views = $this->get_total_views($leilao_id);
        $total_views = $total_views?$total_views:0;
        $total_views++;
        $ret = Qlib::update_postmeta($leilao_id,'views',$total_views);
        return $ret;
    }
    /**
     * Metodo para listar todos os finalizados ate o momento
     * @return array $ret
     */
    public function get_all_finalized(){
        $ret = false;
        $d = Post::select('posts.*','postmeta.meta_value')->
        join('postmeta', 'postmeta.post_id', 'posts.ID')->
        where('posts.post_status', '=', 'publish')->
        where('postmeta.meta_key','=', $this->c_meta_situacao)->
        where('postmeta.meta_value','=', 'f')->
        where('posts.post_type', '=', $this->post_type)->get();
        if($d->count() > 0){
            $data = $d->toArray();
            // if(is_array($data)){
            // }
            $ret['data'] = $data;
        }
        return $ret;
    }
    /**
     * Metodo para informa que é o ganhador, ou quem ganhou de um determinado leilao apartir do id do leilao
     * @param integer $leilao_id, array| bool $dl pode ser informado um array com a consulta do leilao
     * @return array $ret
     */
    public function who_won($leilao_id,$dl=false){
        $dg = $this->get_lance_vencedor($leilao_id,$dl,'ultimo_lance');
        $ret = $dg;
        if(isset($dg['ultimo_lance']['author']) && ($user_id = $dg['ultimo_lance']['author'])){
            $duser = User::find($user_id);
            if($duser->count() > 0){
                $duser = $duser->toArray();
            }
            $ret['duser'] = $duser;
        }
        return $ret;
    }
    /**
     * Metodo responsavel por buscar o total dos leilões que finalizaram
     *
     */
    public function total_finalizados(){
       $ret = Post::join('postmeta','posts.id','=','postmeta.post_id')->
       where('postmeta.meta_key','=','situacao_leilao')->
       Where('postmeta.meta_value','=','f')->
    //    orWhere('postmeta.meta_value','=','f')->
    //    orWhere('postmeta.meta_value','=','r')->
       where('posts.post_type',$this->post_type)->
       where('posts.post_status','publish')->
       where('posts.config','LIKE','%status":"publicado%')->
       where('posts.config','LIKE','%contrato":"%')->
       orderBy('posts.ID','desc')->
       count();
    //    get();
    // dd($ret->toArray());
       return $ret;
    }
    /**
     * Retorna total de leilão em determinada situacao
     * @param $status
     */
    public function total_situacao($situacao){
       $ret = Post::join('postmeta','posts.id','=','postmeta.post_id')->
       where('postmeta.meta_key','=','situacao_leilao')->
       where('postmeta.meta_value',$situacao)->
       where('posts.post_type',$this->post_type)->
       where('posts.post_status','!=','trash')->
    //    where('posts.config','LIKE','%status":"publicado%')->
       where('posts.config','LIKE','%contrato":"%')->
       count();
       return $ret;
    }
    /**
     * Retorna os dados de todos os leilões publicados finalizados e não pagos
     * @return array $ret
     */
    public function finalizados_nao_pagos(){
        //(new LeilaoController())->finalizados_nao_pagos();
        $ret = false;
        $d = Post::join('postmeta','posts.id','=','postmeta.post_id')->
        where('postmeta.meta_key','=','situacao_leilao')->
        where('postmeta.meta_value','f')->
        where('posts.post_type',$this->post_type)->
        where('posts.post_status','publish')->
        where('posts.config','LIKE','%status":"publicado%')->
        where('posts.config','LIKE','%contrato":"%')->
        get();
        $dados = [];
        $total_pago = NULL;
        $total_apagar = NULL;
        if($d->count()){
            $d = $d->toArray();
            foreach ($d as $k => $v) {
                if(isset($v['ID'])){
                    $ul = $this->get_lance_vencedor($v['ID'],$v,'ultimo_lance');
                    if(isset($ul['ultimo_lance'])){
                        $ul = @$ul['ultimo_lance']->toArray();
                        //verificar se está pago
                        if($is_payd = $this->is_paid($v['ID'])){
                            $total_pago += isset($ul['valor_lance']) ? $ul['valor_lance'] : 0;
                        }else{
                            $total_apagar += isset($ul['valor_lance']) ? $ul['valor_lance'] : 0;
                            $v['is_payd'] = $is_payd;
                            $v['ul'] = $ul;
                            $dados[$k] = $v;

                        }
                    }
                }
            }
            $ret['dados'] = $dados;
            $ret['total_pago'] = $total_pago;
            $ret['total_apagar'] = $total_apagar;
            $ret['d'] = $d;
        }
        return $ret;
    }
    /**
     * Metodo para reciclar um leilão finalizado
     * @param int $leilao_id
     */
    public function reciclar($leilao_id){
        $ret['exec'] = false;
        //Verificar se está finalizado
        if($this->is_end($leilao_id)){
            //atualizar o situação de r de reciclar
            $situacao = 'r';
            $ret['exec'] = Qlib::update_postmeta($leilao_id,$this->c_meta_situacao,$situacao);
            //Salvar evento para historico.
            if($ret['exec']){
                $user = Auth::user();
                $regev = Qlib::regEvent(['action'=>'update','tab'=>'Posts','post_id'=>$leilao_id,'config'=>[
                        'obs'=>'Solicitação de reciclagem feita por '.$user['name'],
                        'data_solicitacao'=>Qlib::dataLocal(),
                    ]
                ]);
                $ret['evento'] = $regev;
                //excluir lances anteriores.
                $removeLances = lance::where('leilao_id',$leilao_id)->update([
                    'excluido'=>'s',
                    'reg_excluido'=>Qlib::lib_array_json(['excluido_por'=>$user['id'],'data_excluido'=>Qlib::dataLocal()]),
                ]);
            }
        }
        return response()->json($ret);
    }
    /**
     * Metodo para retornar a situação de um leilão com uma letra
     * @param integer $leilao_id
     * @return string
     */
    public function situacao($leilao_id){
        return Qlib::get_postmeta($leilao_id,$this->c_meta_situacao,true);
    }
    /**
     * Metodo para retornar a situação de um leilão em html
     * @param integer $leilao_id
     * @return string $ret
     */
    public function situacao_html($leilao_id){
        $situacao =  Qlib::get_postmeta($leilao_id,$this->c_meta_situacao,true);
        if($situacao){
            $sh = $this->arr_situacao($situacao,2);
            $tm = '<label>'.__('Situação').':</label> <span>{sh}</span>';
            $ret = str_replace('{sh}',$sh,$tm);
        }else{
            $ret = false;
        }
        return $ret;
    }
    /**
     * Metodo para listar o ranking do leilão
     * @param int $leilao_id
     * @return array $ret
     */
    public function get_ranking($leilao_id=false){
        $ret['exec'] = false;
        if($leilao_id){
            $d = lance::select('lances.*','users.name','users.cpf','users.email','users.config')
            ->join('users','users.id','=','lances.author')
            ->where('lances.leilao_id',$leilao_id)
            ->where('lances.type','=','lance')
            ->where('lances.excluido','=','n')
            ->orderBy('lances.valor_lance','desc')
            ->get();
            $competidores = lance::select('author','id','valor_lance','type')
                        ->distinct()
                        ->groupBy('author')
                        ->where('leilao_id',$leilao_id)
                        ->where('type','=','lance')
                        ->where('excluido','=','n')
                        ->orderBy('id','desc')
                        ->get();
            // $d = DB::select("SELECT DISTINCT author,id,valor_lance,type FROM lances WHERE leilao_id='$leilao_id' AND type='lance' GROUP BY author ORDER BY id DESC");
            if($d->count() && $competidores->count()){
                $ret['total_competidores'] = $competidores->count();
                $ret['competidores'] = $competidores->toArray();
                $ret['exec'] = true;
                $d = $d->toArray();
                if($competidores->count() > 1){
                    $arr_g = [1,2,3];
                    $arr = [];
                    foreach ($arr_g as $kg => $vg) {
                        foreach ($d as $key => $value) {
                            if($vg==1){
                                //Pegando o 1 ganhador
                                // if($key==0 && $value['superado']=='n'){
                                if($key==0){
                                    $arr[$vg] = $value;
                                    if($this->is_paid($leilao_id)){
                                        $arr[$vg]['color'] = 'success';
                                    }else{
                                        $arr[$vg]['color'] = 'danger';
                                    }

                                }
                            }
                            if($vg==2){
                                //Pegando o 2 ganhador
                                if(!isset($arr[$vg]) && isset($arr[1]['author']) && $value['author'] != $arr[1]['author']){
                                    $arr[$vg] = $value;
                                    $arr[$vg]['color'] = 'info';
                                }
                            }
                            if($vg==3){
                                //Pegando o 3 ganhador
                                if(!isset($arr[$vg]) && isset($arr[2]['author']) && ($value['author'] != $arr[1]['author'] && $value['author'] != $arr[2]['author'])){
                                    $arr[$vg] = $value;
                                    $arr[$vg]['color'] = 'warning';
                                }
                            }
                        }
                    }
                }else{
                    foreach ($d as $key => $value) {
                        if($key==0 && $value['superado']=='n'){
                            $vg = 1;
                            $arr[$vg] = $value;
                            if($this->is_paid($leilao_id)){
                                $arr[$vg]['color'] = 'success';
                            }else{
                                $arr[$vg]['color'] = 'danger';
                            }
                        }
                    }
                }
                $ret['ganhadores'] = $arr;
            }
            $ret['d'] = $d;
        }
        // dd($ret);
        return $ret;
    }
}
