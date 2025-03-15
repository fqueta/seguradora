<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\PostController;
use App\Models\lance;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use stdClass;

class LanceController extends Controller
{
    public $routa;
    public $label;
    public $view;
    public $tab;
    public function __construct()
    {
        $this->middleware('auth');
        $this->routa = 'lances';
        $this->label = 'Lance';
        $this->view = 'padrao';
        $this->tab = $this->routa;
        //$this->listarEvent();
    }
    public function queryLances($get=false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        //$todasFamilias = Familia::where('excluido','=','n')->where('deletado','=','n');
        $type = isset($get['type']) ? $get['type'] : 'lance';
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
            'type'=>$type,
            'acao_massa'=>[['link'=>'javascript:lib_abrirListaOcupantes();','event'=>false,'icon'=>'fa fa-list','label'=>__('Lista de ocupantes')]],
        ];

        if(isset($get['term'])){
            //Autocomplete
            if(isset($get['leilao_id']) && !empty($get['leilao_id']) && isset($get['author']) && !empty($get['author'])){
               $sql = "SELECT * FROM lances WHERE (nome LIKE '%".$get['term']."%') AND leilao_id=".$get['leilao_id']." AND author=".$get['author']." AND ".Qlib::compleDelete();
            }elseif(isset($get['leilao_id']) && !empty($get['leilao_id'])){
                $sql = "SELECT * FROM lances WHERE (nome LIKE '%".$get['term']."%') AND leilao_id=".$get['leilao_id']." AND ".Qlib::compleDelete();
            // }else{
            //     $sql = "SELECT l.*,q.nome author_valor FROM lances as l
            //     JOIN authors as q ON q.id=l.author
            //     WHERE (l.nome LIKE '%".$get['term']."%' OR q.nome LIKE '%".$get['term']."%' ) AND ".Qlib::compleDelete('l');
            }
            // $lance = DB::select($sql);
            // if(isset($get['familias'])&&$get['familias']=='s' && is_array($lance)){
            //     foreach ($lance as $k => $v) {
            //         $sqlF = "SELECT f.*,b.nome,b.cpf FROM familias As f
            //         JOIN beneficiarios As b ON b.id=f.id_beneficiario
            //         WHERE f.lanceamento LIKE '%\"".$v->id."\"%' AND ".Qlib::compleDelete('f')." AND ".Qlib::compleDelete('b');
            //         $lance[$k]->familias = DB::select($sqlF);
            //     }
            // }
            // $ret['lance'] = $lance;
            // return $ret;
        }else{
            $lance =  lance::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        }

        //$lance =  DB::table('lances')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        $lance_totais = new stdClass;
        $campos = $this->campos();
        $tituloTabela = 'Lista de lances';
        $arr_titulo = false;
        // Adiciona um filtro para que fora do backend os usuarios so podem ver os seus lances.
        if(Qlib::is_backend()){

        }else{
            $lance->where('author','LIKE', Auth::id());
        }
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id' || $key=='author'){
                            $lance->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            if(is_array($value)){
                                // dd($value);
                            }else{
                                $lance->where($key,'LIKE','%'. $value. '%');
                                if($campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                                $arr_titulo[$campos[$key]['label']] = $value;
                                $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            }
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $lance;
                if($config['limit']=='todos'){
                    $lance = $lance->get();
                }else{
                    $lance = $lance->paginate($config['limit']);
                }
        }else{
            $fm = $lance;
            if($config['limit']=='todos'){
                $lance = $lance->get();
            }else{
                $lance = $lance->paginate($config['limit']);
            }
        }
        $lance_totais->todos = $fm->count();
        $lance_totais->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->get()->count();
        $lance_totais->ativos = $fm->where('ativo','=','s')->get()->count();
        $lance_totais->inativos = $fm->where('ativo','=','n')->get()->count();

        $ret['lance'] = $lance;
        $ret['lance_totais'] = $lance_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$lance_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$lance_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$lance_totais->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$lance_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function index($config=false)
    {
        $ajax = isset($_GET['ajax'])?$_GET['ajax']:'n';
        $view = isset($_GET['view'])?$_GET['view']:$this->view;
        $this->authorize('ler', $this->routa);
        $title = 'lances Cadastrados';
        $titulo = $title;
        $queryLance = $this->querylance($_GET);
        $ret = [
            'dados'=>$queryLance['lote'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryLance['campos'],
            'lote_totais'=>$queryLance['lote_totais'],
            'titulo_tabela'=>$queryLance['tituloTabela'],
            'arr_titulo'=>$queryLance['arr_titulo'],
            'config'=>$queryLance['config'],
            'routa'=>$this->routa,
            'view'=>$view,
            'i'=>0,
        ];
        if($ajax=='s'){
            return response()->json($ret);
        }else{
            return view($view.'.index',$ret);
        }
    }
    /**
     * Metodo para listar lances
     * @param integer || array $param,boolean $total para retoranr um array contendo o total
     * @return array $ret
     */
    public function get_lances($param=false,$total=false){
        $ret = false;
        if(is_array($param)){
            $author = Auth::id();
            $ret = DB::table('lances')->
            select('users.nome','lances.*')->
            join('users','users.id','=','lances.author')->
            where('lances.author',$author)->
            where('lances.excluido','n')->
            where('lances.ativo','s')->
            orderBy('lances.created_at','Asc')->
            get()->toArray();
        }elseif(is_integer($param)){
            $leilao_id = $param;
            $ret = DB::table('lances')->
            select('users.*','lances.*')->
            join('users','users.id','=','lances.author')->
            where('lances.leilao_id',$leilao_id)->
            where('lances.type','lance')->
            where('lances.excluido','n')->
            where('lances.ativo','s')->
            orderBy('lances.created_at','Asc')->
            get()->toArray();
            if(is_array($ret)){
                foreach ($ret as $kl => $vlo) {
                    // $dt = explode('T',$vl['created_at']);
                    $dt = explode(' ',$vlo->created_at);
                    $ret[$kl]->data = Qlib::dataExibe(@$dt[0]);
                    if(isset($dt[1])){
                        $ret[$kl]->data .= ' às '.$dt[1];
                    }
                }
                if($total){
                    $r['total'] = count($ret);
                    $r['list'] = $ret;
                    $ret = $r;
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para gerar campos para edição de lances
     */
    public function campos($id=false,$data=false){
        $user = Auth::user();
        if($id && !$data){
            $data = Post::Find($id);
        }

        $post = new PostController();
        // $arr_opc_ocupantes = Qlib::qoption('opc_declara_posse','array');
        // $bairro = new BairroController($user);

        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'created_at'=>['label'=>'Data e hora','active'=>true,'type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'12','value'=>@$data['bairro']],
            'leilao_id'=>[
                'label'=>'Leilão',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT ID,post_type FROM posts WHERE post_status='publish' AND post_type='leiloes_adm'",'post_type','ID'),'exibe_busca'=>'d-block',
                'event'=>'',
                //'event'=>'onchange=carregaMatricula($(this).val())',
                'tam'=>'12',
                // 'value'=>@$_GET['bairro'],
            ],
            'author'=>[
                'label'=>'Responsável',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,name FROM users WHERE ativo='s' AND id_permission>'1'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
                'exibe_busca'=>true,
                'option_select'=>true,
                'class'=>'select2',
            ],
            'valor_lance'=>['label'=>'Valor do lance','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'6','title'=>'Valor do reembolso'],
            'type'=>['label'=>'Type','active'=>true,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','event'=>'required','tam'=>'6','title'=>''],
            'obs'=>['label'=>'Descrição','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],

        ];
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
    /**
     * Metodo para gravar e gerenciar todos os lances.
     * @param array $d, boolean $autolance, array $ul
     */
    public function gravar_lance($d=false,$autolance=true){
        $ret['exec'] = false;
        if($d){
            $d['token'] = isset($d['token']) ? $d['token'] :uniqid();
            //antes de gravar um lance verifica se ja existe um lance naquele valor
            $verifica = Lance::where('valor_lance','=',$d['valor_lance'])
                ->where('leilao_id','=',$d['leilao_id'])
                ->where('excluido','=','n')
                ->where('type','=',$d['type'])
                ->count();
            if($verifica>0){
                $ret['exec'] = false;
                $ret['code_mens'] = 'enc';
                $ret['redirect'] = 'self';
                // $ret['mens'] = Qlib::formatMensagemInfo('<b>Erro</b> Valor de lance, <b>'.Qlib::valor_moeda($d['valor_lance']).'</b> já foi superado tente novamente com um valor mais alto','warning');
                $ret['mens'] = Qlib::formatMensagemInfo('<b>Erro</b> Lance já foi superado tente com um valor mais alto','warning');
                return $ret;
            }
            //salvar o lance
            $s = lance::create($d);
            if($s->id && isset($d['leilao_id']) && ($leilao_id = $d['leilao_id'])){
                $ret['exec'] = true;
                $ret['id'] = $s->id;
                $lc = new LeilaoController;
                // $marca_lance_superado = $this->marca_lance_superado($leilao_id);
                if(!$lc->is_liked($leilao_id,Auth::id())){
                    //Se não é seguidor deste leilão incluir ele como seguidor
                    $ret['add_seguidor'] = $lc->seguidor_update($leilao_id,Auth::id(),true);
                }
                //ativa os lances automaticos
                if($autolance)
                    $ret['auto_lance'] = $this->lance_automatico($leilao_id);

            }
        }
        return $ret;
    }
    /**
     * Metodo para gravar o lance
     */
    public function store(Request $request)
    {
        $d = $request->all();
        $origem = isset($d['origem']) ? $d['origem'] : false;
        $leilao_id = isset($d['leilao_id']) ? $d['leilao_id'] : false;
        $type = isset($d['type']) ? $d['type'] : 'lance';  // serve para marcar os tipos de lances existem 2 tipos de lances type=lance ou type=reserva
        $d['token'] = isset($d['token']) ? $d['token'] : uniqid();
        $d_user = Auth::user();
        $d['author'] = $d_user->id;
        $d['type'] = $type;
        $ret['exec'] = false;
        $ret['code_mens'] = false;
        $ret['mens'] = Qlib::formatMensagemInfo('Erro ao gravar o lance, por favor entre em contato com o nosso suporte','danger');
        //Verifica se que está dando o lance é o dono do leilão
        $dono_leilao = Qlib::buscaValorDb0('posts','ID',$leilao_id,'post_author');
        if($dono_leilao){
            if($d['author']==$dono_leilao){
                $ret['mens'] = Qlib::formatMensagemInfo('<b>Atenção</b> Não é permitido dar lance eu seu próprio leilão','danger');
                return $ret;
            }
        }else{
            $ret['mens'] = Qlib::formatMensagemInfo('Leilão sem responsável por favor entre em contato com o nosso suporte','danger');
            return $ret;
        }
        //Verifica se o leilão ja terminou
        $dg = (new LeilaoController)->get_lance_vencedor($leilao_id,false,'ultimo_lance');
        if($dg){
            $ret['mens'] = Qlib::formatMensagemInfo('Não é possível dar lances em leilão finalizado','danger');
            return $ret;
        }
        if(!isset($d['valor_lance']) || !isset($d['author']) || !isset($d['leilao_id'])){
            return $ret;
        }
        if($d['valor_lance']<0){
            $ret['mens'] = Qlib::formatMensagemInfo('Erro o valor do lance é nulo, por favor selecione outro','danger');
            return $ret;
        }
        //Verifica se usuario tem permissão para dar o lance
        $al = $this->autoriza_lance($leilao_id);
        if(!$al['exec']){
            $ret['mens'] = Qlib::formatMensagemInfo(@$al['mens'],'danger');
            return $ret;
        }
        //antes de gravar um lance verifica se ja existe um lance naquele valor
        $verifica = Lance::where('valor_lance','=',$d['valor_lance'])
            ->where('leilao_id','=',$d['leilao_id'])
            ->where('excluido','=','n')
            ->where('type','=',$d['type'])
            ->count();
        if($verifica>0){
            $ret['exec'] = false;
            $ret['code_mens'] = 'enc';
            $ret['mens'] = Qlib::formatMensagemInfo('<b>Erro</b> Valor de lance, <b>'.Qlib::valor_moeda($d['valor_lance']).'</b> já foi encontrado tente novamente com outro valor','danger');
            return $ret;
        }
        if($origem=='front'){
            //Verificar
            $v_reserva = $this->salvar_reserva($d,$d['leilao_id']);
            $ret['v_reserva'] = $v_reserva;
            if($v_reserva['exec'] && isset($v_reserva['proximo_lance']) && $v_reserva['proximo_lance']>0){
                //Nesse caso salvou a reserva e resgatamos o proximo lance
                $d['valor_lance'] = $v_reserva['proximo_lance'];
            }
            //Antes de gravar novo lance verifica se o ultimo lance foi do que está dando lance atualmente
            $d_ultimo_lance = $this->ultimo_lance($leilao_id,true);//dados do ultimo lance
            if(isset($d_ultimo_lance['author']) && ($dono_ultimo_l = $d_ultimo_lance['author'])){
                if($dono_ultimo_l==$d['author']){
                    if(isset($v_reserva['exec'])){
                        if($v_reserva['exec']){
                            $vlar = isset($v_reserva['ds']['valor_lance'])?$v_reserva['ds']['valor_lance']:0;
                            // dd($vlar);
                            $ret['code_mens'] = 'dulance';
                            $ret['mens'] = Qlib::formatMensagemInfo('<b>Sucesso</b> Como você é o autor do último lance, o valor de <b>'.Qlib::valor_moeda($vlar,'R$').'</b> é aceito como reserva para os próximos lances e serão feitos de forma automatica.','success');
                            return $ret;
                        }
                    }
                }
            }
            $ret = $this->gravar_lance($d);
            if($ret['exec']){
                $ret['idCad'] = @$ret['id'];
                $ret['mens'] = Qlib::formatMensagemInfo('Lance cadastrado com sucesso','success',70000);
                $ret['redirect'] = url('/').'/leiloes-publicos/'.$leilao_id;
            }
        }
        return $ret;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\lance  $lance
     * @return \Illuminate\Http\Response
     */
    public function show(lance $lance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\lance  $lance
     * @return \Illuminate\Http\Response
     */
    public function edit(lance $lance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\lance  $lance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, lance $lance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\lance  $lance
     * @return \Illuminate\Http\Response
     */
    public function destroy(lance $lance)
    {
        //
    }
    /**
     * Metodo para pegar o penultimo lance
     * @param integer $leilao_id,integer $id_ultimo_lance
     * @return integer $ret
     */

    public function penultimo_lance($leilao_id=false){
        $d = Lance::where('leilao_id',$leilao_id)->
            where('type','lance')->
            where('excluido','n')->
            orderBy('id', 'desc')->
            take(2)->
            get()->toArray();
        $ret = false;
        if(isset($d[1]['id'])){
            $ret = $d[1];
        }
        return $ret;
    }
        /**
     * Metodo para marcar uma reseva como superada
     * @param integer $leilao_id,integer $id_ultimo_lance
     * @return integer $ret
     */
    public function marca_lance_superado($leilao_id=false){
        $ret['exec'] = false;
        $d = $this->penultimo_lance($leilao_id);
        if(isset($d['id'])){
            $ds = [
                'superado'=>'s'
            ];
            $r = Lance::where('id',$d['id'])->update($ds);
            // $d = $this->penultimo_lance($leilao_id);
            if($r){
                $ret['exec'] = true;
                $ret['dados'] = $this->penultimo_lance($leilao_id);
                //Enviar notificação
                // $id_a = Qlib::buscaValorDb0('lances','id',$d['id'],'author');
                // $notific = $this->notifica_superado($leilao_id,$id_a);
                // $nome_leilao = Qlib::buscaValorDb0('posts','id',$leilao_id,'post_title');
                $notific = (new LeilaoController)->enviar_email([
                    'type' => 'notifica_superado',
                    'lance_id' => $d['id'],
                    'subject' => 'Seu lance foi superado',
                    'mensagem' => '<p>Esta é a mesagem</p>',
                    // 'nome_leilao' => $nome_leilao,
                ]);
                $ret['notific'] = $notific;
            }
        }
        return $ret;
    }
    /**
     * Metodo para avisar um usuario que o lance dele foi superado
     */
    public function notifica_superado($leilao_id,$id_user_notific){
        $ret['exec'] = false;
        $ret['mens'] = false;
        if($leilao_id && $id_user_notific){
            // dd($d_user['email']);
            $d_user = User::Find($id_user_notific);
            if(isset($d_user['email']) && !empty($d_user['email'])){
                $user = new stdClass();
                $n = explode(' ',$d_user['name']);
                if(!isset($n[0])){
                    return $ret;
                }
                $title_leilao = Qlib::buscaValorDb0('posts','id',$leilao_id,'post_title');
                $user->name = ucwords($n[0]);
                $user->email = $d_user['email'];
                $user->subject = 'Lance superado';
                $user->type = 'notifica_superado';
                $user->leilao_id = $leilao_id;
                $user->nome_leilao = $title_leilao;
                $user->link_leilao = (new LeilaoController)->get_link($user->leilao_id);
                // return new \App\Mail\leilao\lancesNotific($user);
                $enviar = Mail::send(new \App\Mail\leilao\lancesNotific($user));
                if( count(Mail::failures()) > 0 ) {

                    $ret['mens'] = "Houve um ou mais erros. Segue abaixo: <br />";

                    foreach(Mail::failures() as $email_address) {
                        $ret['mens'] .= " - $email_address <br />";
                    }

                } else {
                    $ret['exec'] = true;
                    $ret['mens'] = "Sem erros, enviado com sucesso!";
                }
            }else{
                $ret['mens'] = __('Usuário não encontrado');
                return $ret;
            }
        }
        return $ret;
    }
    /**
     * Metodo para retornar o proximo ultimo lance dado
     * @param integer $leilao_id
     * @return integer $ret
     */
    public function ultimo_lance($leilao_id=false,$exibe_data=false){
        $ret = 0;
        $lc = new LeilaoController;
        $id_lance=false;
        // dd($leilao_id);
        // if($id_lance=Qlib::get_postmeta($leilao_id,$lc->c_meta_lance_vencedor)){
        //     $l = lance::where('id',$id_lance)->where('excluido','n')->orderBy('id', 'desc')->first();
        //     // if($leilao_id==58){
        //     //     dump($exibe_data,$lc->c_meta_lance_vencedor,$l,$leilao_id,$ret);
        //     // }
        // }else{
            $l = lance::where('leilao_id',$leilao_id)->where('type','lance')->where('excluido','n')->orderBy('id', 'desc')->first();
        // }

        if($l){
            //Gravar id do lance
            if(isset($l['id']) && $l['id'] && !$id_lance){
                $ret = Qlib::update_postmeta($leilao_id, $lc->c_meta_lance_vencedor,$l['id']);
            }
            if($exibe_data){
                if(isset($l['author']) && $l['author']!=''){
                    $l['nome'] = Qlib::buscaValorDb0('users','id',$l['author'],'name');
                }
                $ret = $l;
            }else{
                $ret = $l['valor_lance'];
            }
        }
        return $ret;
    }
    /**
     * Metodo para tornar um lance vencedor do leilao ignorando o ultimo lance dado
     * é usando no caso que administrador querer passar o direito de compra para outro cliente que deu lance
     * @param integer $lance_id
     * @return integer $ret
     */
    public function tornar_vencedor(Request $request){
        $ret['exec'] = false;
        if(!$request->has('lance_id')){
            $ret['mens'] = Qlib::formatMensagem0('Lance não informado','danger','50000');
            return $ret;
        }
        $lance_id = $request->get('lance_id');
        $notify = $request->get('notify');
        //Localizar o lance
        $dla = Lance::find($lance_id);
        $ret['dla'] = $dla;
        $lc = new LeilaoController;
        $bl = new BlacklistController;
        //verifica se o lance é legitimo
        if(isset($dla['valor_lance']) && $dla['valor_lance'] > 0 && isset($dla['excluido']) && $dla['excluido']=='n'){
            $leilao_id = isset($dla['leilao_id'])?$dla['leilao_id']:null;
            //varifica  é o atual ganhador
            if($id_lance_vencedor=Qlib::get_postmeta($leilao_id,$lc->c_meta_lance_vencedor,true)){
                $ret['ganhador_atual']=$id_lance_vencedor;
                //varifica se o dono deste lance ja é o atual ganhador
                if($id_lance_vencedor==$lance_id){
                    $ret['mens'] = Qlib::formatMensagem0('Este cliente já é o ganhador, por favor selecione outro','warning','50000');
                    return $ret;
                }else{
                    $ret['leilao_id'] = $leilao_id;
                    //Novo ganhador
                    $id_ganhador = @$dla['author'];
                    if($id_ganhador){
                        $user = User::find($id_ganhador);
                        //Verificar se ele está no blacklist
                        if($bl->is_blacklist($id_ganhador)){
                            $ret['mens'] = Qlib::formatMensagem0('Usuário <b>'.@$user['name'].'</b> está no <b>Blacklist</b> por isso não pode receber a preferência de pagamento','danger','90000');
                            return $ret;
                        }
                        //Marcar como vencedor
                        $exec = Qlib::update_postmeta($leilao_id,$lc->c_meta_lance_vencedor,$lance_id);
                        $ret['exec'] = $exec;
                        if($exec){
                            $ret['mens'] = Qlib::formatMensagem0('Preferência de pagamanto transferida com sucesso para <b>'.@$user['name'].'</b>!','success','50000');
                            if($notify=='true'){
                                //remover marcação de notificação anterior
                                $meta_notific = 'notifica_termino_leilao_ganhador';
                                $ret['remove_notific'] = Qlib::update_postmeta($leilao_id,$meta_notific,'n');
                                //Notificar o cliente caso seja permitido
                                $ret['notify'] = $lc->notifica_termino($leilao_id,'ganhador');
                            }
                        }
                    }
                }
            }

        }
        //retorna a resposta
        return $ret;
    }
    /**
     * Metodo para retornar o proximo lance
     * @param integer $leilao_id,array $dl=dados do leilao, int $mult_incremento = se precisar saber de lances futuros
     * @return integer $ret
     */
    public function proximo_lance($leilao_id=false,$dl=false,$mult_incremento=1){
        $ret = 0;
        $lance_atual = $this->ultimo_lance($leilao_id);
        // $dl = Post::Find($leilao_id);
        if($leilao_id && !$dl){
            $dl = (new LeilaoController)->get_leilao($leilao_id);
        }
        $campo_valor = 'valor_r';
        if(isset($dl['config']['incremento']) && ($inc = $dl['config']['incremento'])){
            if($lance_atual==0 && isset($dl['config'][$campo_valor])){
                $lance_atual = Qlib::precoBanco($dl['config'][$campo_valor]);
            }
            $inc = Qlib::precoBanco($inc);
            if($inc>0){
                if($mult_incremento>0){
                    $ret = $lance_atual+($mult_incremento*$inc);
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para salver um lance de reserve
     * @param array $dadosForm, int $leilao_id
     * @return array $ret
     */
    public function salvar_reserva($dadosForm=false,$leilao_id=false){
        $ret['exec'] = false;
        $ret['proximo_lance'] = 0;
        if(isset($dadosForm['valor_lance']) && ($vl=$dadosForm['valor_lance']) && $leilao_id){
            // $dl = (new LeilaoController)->get_leilao($leilao_id);
            $vl = (double)$vl;
            $proximo_lance = $this->proximo_lance($leilao_id,false,1);
            if($vl>$proximo_lance){
                // dd($vl,$proximo_lance);
                //nesse momento o sistema entede que precisa gravar o este lance tbm como reserva
                $ret['proximo_lance'] = $proximo_lance;
                $dadosForm['type'] = 'reserva';
                //verifica se ja tem uma reserva criada se sim ele atualiza com o valor
                $vr = lance::where('leilao_id',$leilao_id)
                    ->where('type','reserva')
                    ->where('excluido','n')
                    ->where('author',$dadosForm['author'])
                    ->get();
                if($vr->count()){
                    //se encontrou vamos atualizar
                    if(isset($vr[0]['id']) && $id=$vr[0]['id']){
                        unset($dadosForm['_token'],$dadosForm['origem'],$dadosForm['ajax']);
                        $salvar = lance::where('id',$id)->update($dadosForm);
                        if($salvar){
                            $ret['exec'] = true;
                            $ret['ds'] = $dadosForm;
                        }
                    }
                }else{
                    //se não encontrou vamos gravar novo
                    $salvar = lance::create($dadosForm);
                    if(isset($salvar->id) && $salvar->id){
                        $ret['exec'] = true;
                        $ret['ds'] = $dadosForm;
                    }
                }
                if($ret['exec']){
                    $auto_lance = $this->lance_automatico($leilao_id);
                    $ret['auto_lance'] = $auto_lance;
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para dar lances automaticos dos registro na reserva
     * @param integer $leilao_id
     * @return array $ret
     */
    public function lance_automatico($leilao_id=false){
        $ret['exec'] = false;
        if($leilao_id){
            $proximo_lance = $this->proximo_lance($leilao_id);
            $reservas = lance::where('leilao_id',$leilao_id)
                ->where('type','reserva')
                ->where('excluido','n')
                ->where('valor_lance','>=',$proximo_lance)
                ->orderBy('id','ASC')
                ->get()
                ->toArray();
            $ret['proximo_lance'] = $proximo_lance;
            $ret['reservas'] = count($reservas);
            $d_ultimo_lance = $this->ultimo_lance($leilao_id,true);//dados do ultimo lance
            if(is_array($reservas) && isset($d_ultimo_lance['author'])){
                foreach ($reservas as $k => $v) {
                    //o autor no momento da inserção não pode ser o autor do ultimo lance
                    if($v['author'] != $d_ultimo_lance['author']){
                        unset($v['id'],$v['token'],$v['created_at'],$v['updated_at']);
                        $v['type'] = 'lance';
                        $r = $v['valor_lance'];
                        $proximo_lance1 = $this->proximo_lance($leilao_id);
                        if((double)$proximo_lance1 < (double)$r){
                            // $v['valor_lance'] = $this->proximo_lance($leilao_id);
                            $v['valor_lance'] = $proximo_lance1;
                            $v['config'] = Qlib::lib_array_json(['type' => 'auto','token_reserva'=>@$reservas[$k]['token']]); //Marca que é um lance automatico
                            // Qlib::lib_print($reservas);
                            // dd($v);
                            $salv[$k] = $this->gravar_lance($v,$autolance=false);
                            $v['count'] = count($reservas);
                            $v['reserva'] = $r;
                            $v['proximo_lance1'] = $proximo_lance1;
                            $debug = $v;
                            // Qlib::lib_print($debug);
                            $d_ultimo_lance = $this->ultimo_lance($leilao_id,true);//dados do ultimo lance
                        }elseif((double)$proximo_lance1==(double)$r){
                            if((double)$r==$reservas[0]['valor_lance']){
                                //Se o lance for igual a reserva verificar se a reserva do atual é igual a reserva do primeiro cliente e prevalece a reserva do primeiro notifica que ele precisa reservar um valor maior para cobrir o lance
                                $v = $reservas[0];
                                $v['type'] = 'lance';
                                // $r = $v['valor_lance'];
                            }
                            $v['valor_lance'] = $proximo_lance1;
                            $v['config'] = Qlib::lib_array_json(['type' => 'auto']); //Marca que é um lance automatico
                            $salv[$k] = $this->gravar_lance($v,$autolance=false);
                            $v['count'] = count($reservas);
                            $v['reserva'] = $r;
                            $v['proximo_lance1'] = $proximo_lance1;
                            $debug = $v;
                            // Qlib::lib_print($debug);
                            $d_ultimo_lance = $this->ultimo_lance($leilao_id,true);//dados do ultimo lance
                        }else{
                            $v['valor_lance'] = $proximo_lance1;
                            // $salv[$k] = $this->gravar_lance($v,$autolance=false);
                            $v['count'] = count($reservas);
                            $v['reserva'] = $r;
                            $v['proximo_lance1'] = $proximo_lance1;
                            $v['res'] = 'Não posso mais';
                            // Qlib::lib_print($v);
                        }
                    }
                }
                if(count($reservas)>1){
                    //Roda o lance automatico novamente se existirem mais de um cliente com lance automatico
                    $ret['auto_lance'] = $this->lance_automatico($leilao_id);
                }
            }
                // Qlib::lib_print($ret);
        }
        return $ret;
    }
    /**
     * Metodo para listar os lances dos usuarios no site, quando o paramentro $leilao_id for informado busca apenas do lances do usuario em um leilao expessifico
     * @param integer $leilao_id = o do leilao, strig $type=superdos ou vencendo
     * @return array $ret= conteudo da consulta
     */
    public function list_lance_user($leilao_id=false,$type='superados'){
        $lances_superados = false;
        $lances_vencendo = false;
        //listar lances superados do cliente
        $ret['exec'] = false;
        $ret['lances_superados'] = false;
        $data = Qlib::CalcularDiasAnteriores(date('d/m/Y'),7);
        $dtBanco = Qlib::dtBanco($data);
        $arr = array();
        // dd($dtBanco);
        //Listar lances distintos superados
        // $ld_s = lance::select('leilao_id','id')->
        //         distinct('leilao_id')->
        //         // getcodes()->
        //         // groupBy('leilao_id')->
        //         where('superado','s')->
        //         where('author',Auth::id())->
        //         where('excluido','n')->
        //         whereDate('created_at','>=',$dtBanco)->

        //         orderBy('id','desc')->
        //         get()->toArray();
        // dd($ld_s);
        // if($ld_s){
            // foreach ($ld_s as $key => $value) {
                $lances_superados = lance::query()->select('lances.*','posts.*')->
                    join('posts','lances.leilao_id','=','posts.ID')->
                    where('lances.superado','s')->
                    where('lances.author',Auth::id())->
                    where('lances.excluido','n')->
                    orderBy('lances.id','asc')->
                    whereDate('lances.created_at','>=',$dtBanco)->
                    get()->toArray();
                    // get();
                DB::enableQueryLog();
                $lances_vencendo = lance::select('lances.*','posts.*')->
                    join('posts','lances.leilao_id','=','posts.ID')->
                    where('lances.superado','!=','s')->
                    where('lances.author','=',Auth::id())->
                    where('lances.excluido','=','n')->
                    where('lances.type','=','lance')->
                    orderBy('lances.id','desc')->
                    whereDate('lances.created_at','>=',$dtBanco)->
                    get()->toArray();
                // dd(Auth::id(),$dtBanco,$lances_vencendo);
                if($lances_superados){
                    foreach ($lances_superados as $k => $v) {
                        // if($k==0){
                            $arr['lances_superados'][$v['leilao_id']] = $v;
                        // }
                    }
                }
                if($lances_vencendo){
                    foreach ($lances_vencendo as $k => $v) {
                        if($k==0){
                            $arr['lances_vencendo'][$k] = $v;
                        }
                    }
                }
            // }
        // }
        if(isset($lances_vencendo)){
            $ret['exec'] = true;
            $ret['lances_vencendo'] = $lances_vencendo;
        }
        if(isset($arr['lances_superados'])){
            $ret['exec'] = true;
            $ret['lances_superados'] = $arr['lances_superados'];
        }
        // if(isset($lances_superados)){
        //     $ret['exec'] = true;
        //     $ret['lances_superados'] = $lances_superados;
        // }
        // if(isset($arr['lances_vencendo'])){
        //     $ret['exec'] = true;
        //     $ret['lances_vencendo'] = $arr['lances_vencendo'];
        // }
        return view('site.leiloes.lances.list_lances',$ret);
    }
    /**
     * Metodo para listar os lances dos usuarios no site o paramentro post_id se refere ai id da áginas e o paramentro dados os dados da página
     * @param integer $post_id = o do leilao, array $dados=dados de configuração da página
     */
    public function list_lances($post_id=false,$dados=false){
        if(Gate::allows('is_admin2')||Gate::allows('is_customer_logado')){
            $pst = false;
        }else{
            return false;
        }
        // $pst = new PostController;
        $get = isset($_GET['get']) ? $_GET['get'] : [];

        $queryLances = $this->queryLances($get);
        $queryLances['config']['exibe'] = 'html';
        $title = 'Lances';
        $titulo = $title;
        $view   = url('/').Qlib::get_slug_post_by_id(3);
        $route = $view;
        //if(isset($queryLances['post']));

        $ret = [
            'dados'=>$queryLances['lance'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryLances['campos'],
            'lance_totais'=>$queryLances['lance_totais'],
            'titulo_tabela'=>$queryLances['tituloTabela'],
            'arr_titulo'=>$queryLances['arr_titulo'],
            'config'=>$queryLances['config'],
            'routa'=>'lances',
            'view'=>$view,
            'i'=>0,
        ];
        //REGISTRAR EVENTOS
        // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);

        return view('site.leiloes.lances.list_lances',$ret);
    }
    /**
     * Metodo para informar ao usuario sobre a reserva que ele fez para lances automaticos
     * @param int $leilao_id
     * @return string $ret
     */
    public function info_reserva($leilao_id=false){
        $ret = false;
        if($leilao_id){
            $dr = Lance::where('author','=',Auth::id())
            ->where('excluido','=','n')
            ->where('leilao_id','=',$leilao_id)
            ->where('type','=','reserva')->
            get()->toArray();
            if(isset($dr[0]['valor_lance']) && ($vl=$dr[0]['valor_lance'])){
                // dd($vl);
                $ret = '<div id="info-reserva"><span class="valor-reserva"><span>'.__('Valor de Reserva').':</span><b> '.Qlib::valor_moeda($vl,'R$ ').'</b></span> <button onclick="excluirReserva(\''.$dr[0]['token'].'\')" class="btn btn-outline-secondary" type="button"><i class="fa fa-trash"></i> '.__('Excluir').'</button></div>';
            }
        }
        return $ret;
    }
    public function excluir_reserva(Request $request){
        $pst = $request->all();
        $ret['exec'] = false;
        $ret['me'] = 'Erro ao excluir entre em contato com o nosso suporte';
        $ret['mens'] = Qlib::formatMensagemInfo($ret['me'],'danger');
        if(isset($pst['token'])){
            $d = [
                'excluido'=>'s',
                'reg_excluido'=>Qlib::lib_array_json(['excluido_por'=>Auth::id(),'data_excluido'=>Qlib::dataLocal()]),
            ];
            $ret['exec'] = Lance::where('token',$pst['token'])->update($d);
            if($ret['exec']){
                $ret['me'] = 'Excluido com sucesso';
                $ret['mens'] = Qlib::formatMensagemInfo($ret['me'],'success');
            }
        }
        return response()->json($ret);
    }
    /**
     * Metodo para verificar se o usuario está liberado para dar um lance
     */
    public function autoriza_lance($leilao_id=false){
        $ret['exec'] = false;
        $ret['mens'] = false;
        if($leilao_id){
            //Verifica se o usuário tem tempo suficiente cadastro
            $d = Post::Find($leilao_id);
            if($d){
                $arr = Qlib::lib_json_array($d['config']);
                if(isset($arr['pode_lance']) && $cf=$arr['pode_lance']){
                    $tgmens = Qlib::buscaValorDb0('tags','id',$cf,'nome');
                    $ret['mens'] = __('Somente '.$tgmens.' podem dar lances.');
                    $arr = [
                        6=>'',
                        7=>48, //48 horas
                        8=>168,//7 dias = 168 horas
                        9=>720,//1 mês = 168 horas
                        10=>2160,//3 meses = 168 horas
                    ];
                    $criterio = $arr[$cf];
                    if(empty($criterio)){
                        $ret['exec'] = true;
                    }else{
                        $user = Auth::user();
                        $dt_created = $user->created_at;
                        $now = Qlib::dataLocalDb();
                        $difdt = Qlib::diffDate($dt_created,$now);
                        if((int)$difdt >= (int)$criterio){
                            $ret['exec'] = true;
                        }
                    }
                }
            }
        }

        return $ret;
    }

}
