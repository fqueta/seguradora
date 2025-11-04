<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\wp\ApiWpController;
use App\Http\Requests\StorePostRequest;
use Illuminate\Http\Request;
use stdClass;
use App\Models\Post;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use App\Models\Documento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class PostsController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $post_type;
    public $sec;
    public $tab;
    public $ac; //acao da requsição $ac = 'cad' para create $ac = 'alt' para edit
    public $i_wp;//integração com wp
    public $wp_api;//integração com wp
    public $d_pagina;//integração com wp
    public function __construct($config=[])
    {
        $this->middleware('auth');
        $seg1 = request()->segment(2);
        $seg2 = request()->segment(3);
        $type = isset($config['post_type']) ? $config['post_type'] : false;
        if($seg1 && !$type){
            // $type = substr($seg1,0,-1);
            $type = $seg1;
        }
        $user = Auth::user();
        $this->post_type = $type;
        $this->sec = $this->post_type;
        $this->user = $user;
        if($seg2=='create'){
            $this->ac = 'cad';
        }elseif(request()->segment(4)){
            $this->ac = 'alt';
        }
        $this->routa = $this->sec;
        if($this->sec=='posts'){
            $this->label = 'Notícias';

        }elseif($this->sec=='pages'){
            $this->label = 'Páginas';

        }
        if($this->sec=='pages' || $this->sec=='posts'){
            $this->view = 'posts';
        }else{
            $this->view = 'admin.padrao';
        }
        $this->tab = 'posts';
        $this->i_wp = Qlib::qoption('i_wp');//indegração com Wp s para sim
        // $this->wp_api = new ApiWpController();
        // $this->d_pagina = $d_pagina;

    }
    public function queryPost($get=false,$config=false)
    {

        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        //$todasFamilias = Familia::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        if($this->post_type){
            $post =  Post::where('post_status','!=','inherit')->where('post_type','=',$this->post_type)->orderBy('id',$config['order']);
        }else{
            $post =  Post::where('post_status','!=','inherit')->orderBy('id',$config['order']);
        }
        //$post =  DB::table('posts')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $post_totais = new stdClass;
        $campos = isset($_SESSION['campos_posts_exibe']) ? $_SESSION['campos_posts_exibe'] : $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $post->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $post->where($key,'LIKE','%'. $value. '%');
                            if($campos[$key]['type']=='select'){
                                $value = $campos[$key]['arr_opc'][$value];
                            }
                            $arr_titulo[$campos[$key]['label']] = $value;
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $post;
                if($config['limit']=='todos'){
                    $post = $post->get();
                }else{
                    $post = $post->paginate($config['limit']);
                }
        }else{
            $fm = $post;
            if($config['limit']=='todos'){
                $post = $post->get();
            }else{
                $post = $post->paginate($config['limit']);
            }
        }
        $post_totais->todos = $fm->count();
        $post_totais->esteMes = $fm->whereYear('post_date', '=', $ano)->whereMonth('post_date','=',$mes)->count();
        $post_totais->ativos = $fm->where('post_status','=','publish')->count();
        $post_totais->inativos = $fm->where('post_status','!=','publish')->count();
        $ret['post'] = $post;
        $ret['post_totais'] = $post_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['post_type'] = $this->post_type;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$post_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$post_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$post_totais->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$post_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function campos($id=false){
        $sec = $this->sec;
        $hidden_editor = '';
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        $d = [];
        if($id){
            $d = Post::find($id);
        }
        $d_pagina = $this->pagina();
        if(isset($d_pagina['config']) && !empty($d_pagina['config'])){
            $ret = $d_pagina['config'];
            if($this->post_type=='archives'){
                $route_category = 'archives_category';
                $archives_category = new DefaultController(['route'=>$route_category]);
                $id_pai = Qlib::buscaValorDb0('tags','value',$route_category,'id');
                if(isset($ret['guid'])){
                    if($this->ac=='alt'){
                        $ret['html1']=['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h6 class="text-right"><b title="codigo de inserção deste arquivo em um post">Código:</b> <span style="" id="short_code">*|posts-'.$id.'|*</span></h6>'];
                    }
                    $ret['guid'] = [
                        'label'=>'Categoria',
                        'active'=>true,
                        'type'=>'selector',
                        'data_selector'=>[
                            'campos'=>$archives_category->campos(),
                            'route_index'=>route('archives_category.index'),
                            'id_form'=>'frm-archives_category',
                            'action'=>route('archives_category.store'),
                            'campo_id'=>'id',
                            'campo_bus'=>'nome',
                            'label'=>'Categoria',
                        ],'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$id_pai'",'nome','id'),'exibe_busca'=>'d-block',
                        'event'=>'required',
                        //'event'=>'onchange=carregaMatricula($(this).val())',
                        'tam'=>'12',
                        'value'=>@$_GET['archives_category'],
                    ];
                }
            }elseif($this->post_type =='cat_receitas' || $this->post_type =='cat_despesas'){
                $ret['guid']['arr_opc'] = Qlib::sql_array("SELECT ID,post_title FROM posts WHERE post_status='publish' AND post_type='".$this->post_type."'",'post_title','ID');
            }elseif($this->post_type =='tipo_receitas' || $this->post_type =='tipo_despesas'){
                $ret['guid']['arr_opc'] = Qlib::sql_array("SELECT ID,post_title FROM posts WHERE post_status='publish' AND post_type='".$this->post_type."'",'post_title','ID');
            }
        }else{
            $ret = [
                'ID'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
                'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'html1'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>''],
                'post_title'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Nome do post','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'12'],
                'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
                'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                // 'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
                'post_status'=>['label'=>'Publicar','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'6','arr_opc'=>['publish'=>'Publicado','pending'=>'Pendente'],'tab'=>'posts'],
            ];
            if($this->post_type=='posts'){
                if($this->ac=='alt'){
                    $ret['html1']['script'] = '<div class="col-12 text-right">Id: '.@$d['ID'].' Data: '. Qlib::dataExibe( @$d['post_date']).'</div>';
                    $ret['html1']['script'] .= '<div class="col-12"><a class="underline" target="_BLANK" href="'.$this->lib_preview($d['post_name']).'">Visualizar Conteúdo púbilco</a></div>';
                }
                $ret['config[capa_artigo]']=['label'=>'Capa no artigo','cp_busca'=>'config][capa_artigo','active'=>false,'','type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'6','arr_opc'=>['s'=>'Sim','n'=>'Não'],'title'=>'Função para exibir imagem de capa dentro do artigo.'];
                // $ret['post_name']['type'] = 'text';
            }
        }
        // dump($ret);
        return $ret;
    }
    /**
     * Metodo para gerar um lino de preview de posts
     * @param string $post_id
     */
    public function lib_preview($post_id){
        return url('/preview/posts/' . $post_id);
    }
    /**
     * Metodo para montar um array que configura a página de acordo com a tabela documentos dessa forma essa area será dinamica
     */
    public function pagina($sec=false){
        $sec = $this->post_type ? $this->post_type : false;
        if(!$sec){
            return false;
        }
        $d = Documento::where('token', '=', $sec)->get();
        if($d->count()){
            $d = $d[0];
            if(isset($d['config'])){
                $d['config'] = Qlib::lib_json_array($d['config']);
            }
            return $d->toArray();
        } else{
            return false;
        }
    }
    public function index(User $user)
    {
        $this->authorize('is_admin', $user);
        //buscar os dados da página
        $d_pagina = $this->pagina();
        if(!$d_pagina){
            if($this->sec=='posts'){
                $title = 'Cadastro de '.$this->label;
            }elseif($this->sec=='pages'){
                $title = 'Cadastro de paginas';
            }else{
                $title = 'Cadastro';
            }
        }else{
            $title = 'Cadastro de '.$d_pagina['nome'];
        }

        $titulo = $title;
        $queryPost = $this->queryPost($_GET);
        $queryPost['config']['exibe'] = 'html';
        $routa = $this->routa;
        //if(isset($queryPost['post']));
        return view($this->view.'.index',[
            'dados'=>$queryPost['post'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryPost['campos'],
            'post_totais'=>$queryPost['post_totais'],
            'titulo_tabela'=>$queryPost['tituloTabela'],
            'arr_titulo'=>$queryPost['arr_titulo'],
            'config'=>$queryPost['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ]);
    }
    public function create(User $user)
    {
        $this->authorize('is_admin', $user);
        $selTypes = $this->selectType($this->sec);
        $title = $selTypes['title'];
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-posts',
            'route'=>$this->routa,
            'view'=>$this->view,
            'arquivos'=>false,
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos();
        return view($this->view.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);
    }
    public function salvarPostMeta($config = null)
    {
        $post_id = isset($config['post_id'])?$config['post_id']:false;
        $meta_key = isset($config['meta_key'])?$config['meta_key']:false;
        $meta_value = isset($config['meta_value'])?$config['meta_value']:false;
        $tab = isset($config['tab'])?$config['tab']:'postmeta';
        $ret = false;
        if($post_id&&$meta_key&&$meta_value){
            $verf = Qlib::totalReg($tab,"WHERE post_id='$post_id' AND meta_key='$meta_key'");
            if($verf){
                $ret=DB::table($tab)->where('post_id',$post_id)->where('meta_key',$meta_key)->update([
                    'meta_value'=>$meta_value,
                ]);
            }else{
                $ret=DB::table($tab)->insert([
                    'post_id'=>$post_id,
                    'meta_value'=>$meta_value,
                    'meta_key'=>$meta_key,
                ]);
            }
            //$ret = DB::table($tab)->storeOrUpdate();
        }
        return $ret;
    }
    /**Metodo para criar e verificar se um slug ja está cadastrdo
     * @params string $title é o titulo do post, $id é id da postagem
     * @ret é o post valido para ser salvo
     * uso $slug = (new PostController)->str_slug($title,$id=false);
     *
    */
    public function str_slug($title,$id=false){
        $slug = Str::slug($title, '-');
        if($id){
            $verifica = Post::where('ID', '=',$id)->get();
            if($verifica->count() > 0){
                //verifica se o titulo atual e diferente deste titulo
                if($verifica[0]['post_title'] == $title){
                    //se for igual returna o mesmo slug
                    if(!empty($verifica[0]['post_name'])){
                        $ret = $verifica[0]['post_name'];
                        return $ret;
                    }
                }
            }
            $verifica = Post::where('post_name', 'LIKE','%'.$slug.'%')->where('ID','!=',$id)->get();
        }else{
            $verifica = Post::where('post_name', 'LIKE','%'.$slug.'%')->get();
        }
        if($tot=$verifica->count()){
            if($tot>1){
                $tot++;
            }
            $ret=$slug.'-'.$tot;
            return $ret;
        }else{
            return $slug;
        }
    }
    public function store(StorePostRequest $request)
    {
        $this->authorize('create', $this->routa);
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['token'] = !empty($dados['token'])?$dados['token']:uniqid();
        $dados['post_status'] = isset($dados['post_status'])?$dados['post_status']:'pending';
        if($this->i_wp=='s' && isset($dados['post_type'])){
            //$endPoint = isset($dados['endPoint'])?$dados['endPoint']:$dados['post_type'].'s';
            $endPoint = 'post';
            $params = $this->geraParmsWp($dados);

            if($params){
                $salvar = $this->wp_api->exec2([
                    'endPoint'=>$endPoint,
                    'method'=>'POST',
                    'params'=>$params
                ]);
                if(isset($salvar['arr']['id']) && $salvar['arr']['id']){
                    $mens = $this->label.' cadastrado com sucesso!';
                    $color = 'success';
                    $idCad = $salvar['arr']['id'];
                }else{
                    $mens = 'Erro ao salvar '.$this->label.'';
                    $color = 'danger';
                    $idCad = 0;
                    if(isset($salvar['arr']['status'])&&$salvar['arr']['status']==400 && isset($salvar['arr']['message']) && !empty($salvar['arr']['message'])){
                        $mens = $salvar['arr']['message'];
                    }
                }
            }else{
                $color = 'danger';
                $mens = 'Parametros invalidos!';
            }
        }else{
            $dados['post_author'] = isset($dados['post_author']) ? $dados['post_author'] : Auth::id();
            if(($this->post_type == 'posts' || $this->post_type == 'pages') && isset($dados['post_title'])){
                // $dados['post_name'] = isset($dados['post_name']) ? $dados['post_name'] : $this->str_slug($dados['post_title']);
                $dados['post_name'] = $this->str_slug($dados['post_title']);
            }
            // dd($dados);
            $salvar = Post::create($dados);
            if(isset($salvar->id) && $salvar->id){
                $mens = $this->label.' cadastrado com sucesso!';
                $color = 'success';
                $idCad = $salvar->id;
            }else{
                $mens = 'Erro ao salvar '.$this->label.'';
                $color = 'danger';
                $idCad = 0;
            }
        }
        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$mens,
            'color'=>$color,
            'idCad'=>$idCad,
            'exec'=>true,
            'dados'=>$dados
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$idCad;
            $ret['redirect'] = route($this->routa.'.edit',['id'=>$idCad]);
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function show($id)
    {
        //
    }
    public function geraParmsWp($dados=false)
    {
        $params=false;
        if($dados && is_array($dados)){

            $arr_parm = [
                'post_name'=>'post_name',
                'post_title'=>'post_title',
                'post_content'=>'post_content',
                'post_excerpt'=>'post_excerpt',
                'post_status'=>'post_status',
                'post_type'=>'post_type',
            ];
            foreach ($dados as $kp => $vp) {
                if(isset($arr_parm[$kp])){
                    $params[$kp] = $dados[$kp];
                }
            }
        }
        return $params;
    }
    public function selectType($sec=false)
    {
        $ret['exec']=false;
        $ret['title']=false;
        $title = false;
        if($sec){
            $name = request()->route()->getName();
            // if($sec=='posts'){
            //     $title = __('Cadastro de postagens');
            // }elseif($sec=='produtos'){
            //     $title = __('Cadastro de contratos');
            //     if($name=='produtos.edit'){
            //         $title = __('Editar Cadastro de contratos');
            //     }
            // }elseif($sec=='leiloes_adm'){
            //     $title = __('Cadastro de leilao');
            //     if($name=='leilao.edit'){
            //         $title = __('Editar Cadastro de leilao');
            //     }
            // }elseif($sec=='paginas'){
            //     $title = __('Cadastro de paginas');
            // }elseif($sec=='menus'){
            //     $title = __('Cadastro de menus');
            // }elseif($sec=='pacotes_lances'){
            //     $title = __('Cadastro de pacotes');
            // }else{
            //     $title = __('Sem titulo');
            // }
            $d_pagina = $this->pagina();
            if(!$d_pagina){
                if($this->sec=='posts' || $this->sec=='pages'){
                    $title = 'Cadastro de '.$this->label;
                }else{
                    $title = __('Sem titulo');
                }
            }else{
                $title = 'Cadastro de '.$d_pagina['nome'];
            }

        }
        $ret['title'] = $title;
        return $ret;
    }
    public function edit($post,User $user)
    {
        $id = $post;
        $dados = Post::where('id',$id)->where('post_type',$this->post_type)->get();
        $routa = 'posts';
        $this->authorize('ler', $this->routa);
        if($dados->count()){
            $selTypes = $this->selectType($this->sec);
            $title = $selTypes['title'];
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            if(isset($dados[0]['post_date_gmt'])){
                $dExec = explode(' ',$dados[0]['post_date_gmt']);
                if(isset($dExec[0])){
                    $dados[0]['post_date_gmt'] = $dExec[0];
                }
            }
            //dd($dados[0]['config']['numero']);
            $listFiles = false;
            $campos = $this->campos($id);
            if($this->i_wp=='s' && !empty($dados[0]['post_name'])){
                $dadosApi = $this->wp_api->list([
                    'params'=>'/'.$dados[0]['post_name'].'?_type='.$dados[0]['post_type'],
                ]);
                if(isset($dadosApi['arr']['arquivos'])){
                    $listFiles = $dadosApi['arr']['arquivos'];
                }
            }else{
                if(isset($dados[0]['token'])){
                    $listFiles = $this->list_files($dados[0]['token']);
                }
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-posts',
                'route'=>$this->routa,
                'view'=>$this->view,
                'sec'=>$this->sec,
                'id'=>$id,
                'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg',
                'tam_col1'=>'col-md-6',
                'tam_col2'=>'col-md-6',

            ];
            if($this->post_type =='posts'){
                $config['tam_col1'] = 'col-md-7';
                $config['tam_col2'] = 'col-md-5';
            }elseif($this->post_type =='cat_receitas' || $this->post_type =='cat_despesas' || $this->post_type =='contas' || $this->post_type =='f_pagamento'){
                //cadastros do financeiro
                $config['tam_col1'] = 'col-md-10';
                $config['tam_col2'] = 'col-md-2';
                unset($config['arquivos']);
            }
            $config['media'] = [
                'files'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg,JPG',
                'select_files'=>'unique',
                'field_media'=>'post_parent',
                'post_parent'=>$id,
            ];
            //IMAGEM DESTACADA
            if(isset($dados[0]['ID']) && $this->i_wp=='s'){
                $imagem_destacada = DB::table('wp_postmeta')->
                where('post_id',$dados[0]['ID'])->
                where('meta_key','imagem_destacada')->get();
                if(isset($imagem_destacada[0])){
                    $dados[0]['imagem_destacada'] = $imagem_destacada[0];
                }
            }elseif(isset($dados[0]['post_parent'])){
                // $link_img = Qlib::buscaValorDb([
                //     'tab'=>'posts',
                //     'campo_bus'=>'ID',
                //     'valor'=>$dados[0]['post_parent'],
                //     'select'=>'guid',
                //     'compleSql'=>''
                // ]);

                $imgd = Post::where('ID', '=', $dados[0]['post_parent'])->where('post_status','=','publish')->get();
                if( $imgd->count() > 0 ){
                    // dd($imgd[0]['guid']);
                    $dados[0]['imagem_destacada'] = tenant_asset($imgd[0]['guid']);
                }
            }
            //REGISTRAR EVENTOS
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'listFilesCode'=>Qlib::encodeArray($listFiles),
                'campos'=>$campos,
                'exec'=>true,
            ];
            return view($this->view.'.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route('home',$ret);
        }
    }
    /**
     * Metodo para listar todos os arquivos das licitações
     */
    public function list_files($token_produto){
        $ret = [];
        if($token_produto){
            $files = _upload::where('token_produto','=',$token_produto)->orderBy('ordem','asc')->get();
            if($files->count() > 0){
                $files =  $files->toArray();
                foreach ($files as $kf => $vf) {
                    $ret[$kf] = $vf;
                    $arr_c = Qlib::lib_json_array($vf['config']);
                    $ret[$kf]['file_path'] = $ret[$kf]['pasta'];
                    $ret[$kf]['extension'] = @$arr_c['extenssao'];
                    $ret[$kf]['extenssao'] = @$arr_c['extenssao'];
                }
            }
        }
        return $ret;
    }
    public function update(StorePostRequest $request, $id)
    {
        $this->authorize('update', $this->routa);
        $data = [];
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['post_status'] = isset($dados['post_status'])?$dados['post_status']:'pending';
        $d_meta = false;
        if(isset($dados['d_meta'])){
            $d_meta = $dados['d_meta'];
            if(isset($dados['ID'])){
                $d_meta['post_id'] = $dados['ID'];

            }
            unset($dados['d_meta']);
        }
        foreach ($dados as $key => $value) {
            if($key!='_method'&&$key!='_token'&&$key!='ac'&&$key!='ajax'){
                /*if($key=='data_batismo' || $key=='data_nasci'){
                    if($value=='0000-00-00' || $value=='00/00/0000'){
                    }else{
                        $data[$key] = Qlib::dtBanco($value);
                    }
                }else{*/
                    $data[$key] = $value;
                //}
            }
        }
        // $userLogadon = Auth::id();
        //$data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        $data['token'] = !empty($data['token'])?$data['token']:uniqid();
        //$data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        $d_ordem = isset($data['ordem'])?$data['ordem']:false;
        unset($data['file'],$data['ordem']);
        if(!empty($data)){
            if(($this->post_type == 'posts' || $this->post_type == 'pages') && isset($data['post_title'])){
                // $data['post_name'] = isset($data['post_name']) ? $data['post_name'] : $this->str_slug($data['post_title']);
                $data['post_name'] = $this->str_slug($data['post_title'],$id);
                $data['config']['capa_artigo'] = isset($data['config']['capa_artigo']) ? $data['config']['capa_artigo'] : 'n';
            }
            if($this->i_wp=='s' && isset($dados['post_type'])){
                $endPoint = 'post/'.$id;
                $arr_parm = [
                    'post_name'=>'post_name',
                    'post_title'=>'post_title',
                    'post_content'=>'post_content',
                    'post_excerpt'=>'post_excerpt',
                    'post_status'=>'post_status',
                    'post_type'=>'post_type',
                ];
                $params = $this->geraParmsWp($dados);
                if($params){
                    $atualizar = $this->wp_api->exec2([
                        'endPoint'=>$endPoint,
                        'method'=>'PUT',
                        'params'=>$params
                    ]);
                    if(isset($atualizar['exec']) && $atualizar['exec']){
                        $mens = $this->label.' cadastrado com sucesso!';
                        $color = 'success';
                        $id = $id;
                    }else{
                        $mens = 'Erro ao salvar '.$this->label.'';
                        $color = 'danger';
                        $id = 0;
                        if(isset($atualizar['arr']['status'])&&$atualizar['arr']['status']==400 && isset($atualizar['arr']['message']) && !empty($atualizar['arr']['message'])){
                            $mens = $atualizar['arr']['message'];
                        }
                    }
                }else{
                    $color = 'danger';
                    $mens = 'Parametros invalidos!';
                }
            }else{
                $atualizar=Post::where('id',$id)->update($data);
                if(isset($atualizar) && $atualizar){
                    $mens = $this->label.' atualizada com sucesso!';
                    $color = 'success';
                    $id = $id;
                }else{
                    $mens = 'Erro ao salvar '.$this->label.'';
                    $color = 'danger';
                    $id = 0;
                    // if(isset($atualizar['arr']['status'])&&$atualizar['arr']['status']==400 && isset($atualizar['arr']['message']) && !empty($atualizar['arr']['message'])){
                    //     $mens = $atualizar['arr']['message'];
                    // }
                }
            }
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>$mens,
                'color'=>$color,
                'idCad'=>$id,
                'return'=>$route,
            ];
            if(is_array($d_ordem)){
                //atualizar ordem dos arquivos
                $ret['order_update'] = (new AttachmentsController)->order_update($d_ordem,'uploads');
            }
            if($atualizar && $d_meta){
                $ret['salvarPostMeta'] = $this->salvarPostMeta($d_meta);
            }

        }else{
            $route = $this->routa.'.edit';
            $ret = [
                'exec'=>false,
                'id'=>$id,
                'mens'=>'Erro ao receber dados',
                'color'=>'danger',
            ];
        }
        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$id;
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function destroy($id,Request $request)
    {
        $this->authorize('delete', $this->routa);
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = 'posts';
        if (!$post = Post::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }
        $color = 'success';
        $mens = 'Registro deletado com sucesso!';
        if($this->i_wp=='s'){
            $endPoint = 'post/'.$id;
            $delete = $this->wp_api->exec2([
                'endPoint'=>$endPoint,
                'method'=>'DELETE'
            ]);
            if($delete['exec']){
                $mens = 'Registro '.$id.' deletado com sucesso!';
                $color = 'success';
            }else{
                $color = 'danger';
                $mens = 'Erro ao excluir!';
            }
        }else{
            Post::where('id',$id)->delete();
            $mens = 'Registro '.$id.' deletado com sucesso!';
            $color = 'success';
        }
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__($mens),'color'=>$color,'return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>$mens,'color'=>$color]);
        }
        return $ret;
    }
    /**
     * Metodo para exibir os dados de um post
     */
    public function get_post($post_id){
        $d=Post::find($post_id);
        if($d->count()>0){
            $dimd = Post::where('ID', '=', $d['post_parent'])->where('post_status','=','publish')->get();
            if($dimd->count()>0){
                $d['imagem_destacada'] = tenant_asset($dimd[0]['guid']);
            }else{
                $d['imagem_destacada'] = '';
            }
        }
        return $d;
    }
    /**
     * Metodo para exibir a imagem destacada
     */
    public function get_imgd($post_id,$type=false){
        $d=$this->get_post($post_id);
        $imgd=false;
        if(isset($d['imagem_destacada']) && ($imgd = $d['imagem_destacada'])){
            if($type == 'html'){
                $imgd = str_replace('{img}',$imgd,'<div class="row"><div class="col-md-12 imagem-destacada mb-3"><img src="{img}" class="w-100"/></div></div>');
            }
        }
        return $imgd;
    }
    /**
     * Metodo para rederisar um contedo de uma postagem atraves de um codigo de $code=[posts+_+ID]
     * @return string $ret
     */
    public function short_code($code){
        $ret = '';
        $dcod = explode('-', $code);
        $tab = isset($dcod[0])?$dcod[0]:false;
        $id = isset($dcod[1])?$dcod[1]:false;
        if($tab && $id){

            $d = DB::table($tab)->find($id);
            if($d->ID > 0){
                // dump($d);
                $h1 = '<h1 class="h1-cms">{conteudo}</h1>';
                $dconf = Qlib::lib_json_array($d->config);
                $imgd = false;
                if(isset($dconf['capa_artigo']) && $dconf['capa_artigo']=='s'){
                    //Opção para exibir a imagem destacado no topo da noticia
                    $imgd = $this->get_imgd($id,'html');
                    // dd($imgd);
                }
                $style_video = '<style>
                                    iframe.note-video-clip {
                                        width: 100%;
                                        /*height: auto;*/
                                    }
                                </style>';
                $page = '{style_video}{ret}';
                $h2 = '<h2 class="h2-cms">{conteudo}</h2>';
                $p = '<p class="p-cms">{conteudo}</p>';
                $row = '<div class="row">{conteudo}</div>';
                $col12 = '<div class="col-md-12">{conteudo}</div>';
                $ret = str_replace('{conteudo}',$d->post_title,$h1);
                $ret .= $imgd;
                if($d->post_excerpt)
                    $ret .= str_replace('{conteudo}',str_replace('{conteudo}',$d->post_excerpt,$col12),$row);
                $ret .= str_replace('{conteudo}',str_replace('{conteudo}',$d->post_content,$col12),$row);
                $galeria = $this->list_galeria($d->token);
                $ret .= $galeria;
                $ret = str_replace('{ret}',$ret,$page);
                $ret = str_replace('{style_video}',$style_video,$ret);
            }

        }
        return $ret;
    }
    /**
     * Metodoa para listar uma galeria
     * @param string $token é o tokem do post
     * @return string $ret retorna uma html da galeria montado
     */
    public function list_galeria($token){
        $ret = false;
        if(!$token){
            return $ret;
        }
        $list_files = (new UploadController)->list_files($token);
        if(is_array($list_files)){
            $tema_gal1 = '<div class="row">{gal}</div>';
            $tema_gal2 = '<div class="col-md-{tam_file} text-center"><a class="venobox" href="{link}" {target}><img src="{link_img}" class="w-100"/></a><br>{name}</div>';
            $gal = false;
            $tam_file = 2;
            foreach ($list_files as $kf => $vf) {
                $ex = isset($vf['extension']) ? $vf['extension'] : '';
                $target = 'target="_BLANK"';
                if($ex == 'jpeg' || $ex == 'png' || $ex == 'PNG' || $ex == 'jpg'){
                    $link_img = tenant_asset($vf['link']);
                    $target = '';
                }elseif($ex == 'xls' || $ex == 'xlsx'){
                    $link_img = asset('/images/excel.png');
                }elseif($ex == 'doc' || $ex == 'docx'){
                    $link_img = asset('/images/word.png');
                }elseif($ex == 'pdf' || $ex == 'PDF'){
                    $link_img = asset('/images/pdf.png');
                }else{
                    $link_img = asset('/images/file.png');
                }
                $gal .= str_replace('{link}',tenant_asset($vf['link']),$tema_gal2);
                $gal = str_replace('{name}',$vf['name'],$gal);
                $gal = str_replace('{link_img}',$link_img,$gal);
                $gal = str_replace('{tam_file}',$tam_file,$gal);
                $gal = str_replace('{target}',$target,$gal);
            }
            $ret = str_replace('{gal}',$gal,$tema_gal1);
        }

        return $ret;
    }
    public function get_id_by_slug($slug){
        $post = Post::where('post_name',$slug)->select('id')->first();
        if($post){
            $ret = $post['id'];
        }else{
            $ret = 0;
        }
        return $ret;
    }
}
