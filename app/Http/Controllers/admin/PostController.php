<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeilaoController;
use App\Http\Controllers\wp\ApiWpController;
use App\Http\Requests\StorePostRequest;
use Illuminate\Http\Request;
use stdClass;
use App\Models\Post;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class PostController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $post_type;
    public $tab;
    public $sec;
    public $i_wp;//integração com wp
    public $wp_api;//integração com wp
    public function __construct()
    {
        $this->middleware('auth');
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $type = false;
        if($seg2){
            $type = substr($seg2,0,-1);
        }
        $this->post_type = trim($seg2);
        $this->sec = $seg2;
        $user = Auth::user();
        $this->user = $user;
        $this->routa = $this->sec;
        $this->label = 'Posts';
        $this->tab = 'posts';
        $this->view = 'admin.posts';
        $this->i_wp = Qlib::qoption('i_wp');//indegração com Wp s para sim
        //$this->wp_api = new ApiWpController();
        $this->wp_api = false;

    }
    public function queryPost($get=false,$config=false,$post_type=false)
    {

        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        //$todasFamilias = Post::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        $post_type = $post_type?$post_type:$this->post_type;
        // dd($post_type,$get);
        if($post_type){
            if(Qlib::is_frontend()){
                $seg1 = request()->segment(1); //link da página em questão
                $urlB = Qlib::get_slug_post_by_id(37); //link da pagina para cosulta de leiloes no site.
                if($seg1==$urlB){
                    //Exibir apenas leiloes publicos
                    $post =  Post::where('post_status','!=','inherit')->where('post_status','=','publish')->where('post_type','=',trim($post_type))->where('config','LIKE','%"status":"publicado"%')->orderBy('id',$config['order']);
                    //começar aqui
                }else{
                    $post =  Post::where('post_author','=',Auth::id())->where('post_status','!=','inherit')->where('post_status','!=','trash')->where('post_type','=',trim($post_type))->orderBy('id',$config['order']);
                }

            }else{
                $post =  Post::where('post_status','!=','inherit')->where('post_status','!=','trash')->where('post_type','=',trim($post_type))->orderBy('id',$config['order']);
            }
        }else{
            $post =  Post::where('post_status','!=','inherit')->where('post_status','!=','trash')->orderBy('id',$config['order']);
        }
        //$post =  DB::table('posts')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $post_totais = new stdClass;
        $campos = $this->campos(false,$post_type);
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        // dump($get);
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                $get['filter']['post_status'] = isset($get['filter']['post_status']) ? $get['filter']['post_status'] : 'publish';
                // if(isset($get['filter']['post_status'])){
                // }else{
                    // if(isset($get['origem'])=='site'){
                    //     $get['filter']['post_status'] = 'publish';
                    // }else{
                    //     $get['filter']['post_status'] = 'pending';
                    // }
                // }
                // dump($get['filter']);
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id' || $key=='ID'){
                            $post->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            if(is_array($value)){
                                foreach ($value as $kb => $vb) {
                                    if(!empty($vb)){
                                        if($key=='tags'){
                                            $post->where($key,'LIKE', '%"'.$vb.'"%' );
                                        }else{
                                            $post->where($key,'LIKE', '%"'.$kb.'":"'.$vb.'"%' );
                                        }
                                    }
                                }
                            }else{
                                $post->where($key,'LIKE','%'. $value. '%');
                                if(isset($campos[$key]['type']) && $campos[$key]['type']=='select'){
                                    $value = isset($campos[$key]['arr_opc'][$value]) ? $campos[$key]['arr_opc'][$value] : null;
                                }
                                @$arr_titulo[@$campos[$key]['label']] = $value;
                                $titulo_tab .= 'Todos com *'. @$campos[$key]['label'] .'% = '.$value.'& ';
                            }
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $post;
                // dd($post->get());
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
        // dump($post);

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
    public function queryPostLeilao($get=false,$config=false,$post_type=false)
    {

        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        //$todasFamilias = Post::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        $post_type = $post_type?$post_type:$this->post_type;
        // dd($post_type,$get);
        if($post_type){
            if(Qlib::is_frontend()){
                $seg1 = request()->segment(1); //link da página em questão
                $urlB = Qlib::get_slug_post_by_id(37); //link da pagina para cosulta de leiloes no site.
                if($seg1==$urlB){
                    //Exibir apenas leiloes publicos
                    $post =  Post::select('posts.*','postmeta.meta_key')->
                    join('postmeta','posts.ID','=','postmeta.post_id')->
                    where('posts.post_status','!=','inherit')->
                    where('posts.post_status','=','publish')->where('posts.post_type','=',trim($post_type))->where('config','LIKE','%"status":"publicado"%')->orderBy('id',$config['order']);
                    //começar aqui
                }else{
                    $post =  Post::select('posts.*','postmeta.meta_key')->
                    join('postmeta','posts.ID','=','postmeta.post_id')->
                    where('posts.post_author','=',Auth::id())->where('posts.post_status','!=','inherit')->where('post_status','!=','trash')->where('post_type','=',trim($post_type))->orderBy('id',$config['order']);
                }

            }else{
                $post =  Post::select('posts.*','postmeta.meta_key')->
                join('postmeta','posts.ID','=','postmeta.post_id')->
                where('posts.post_status','!=','inherit')->where('posts.post_status','!=','trash')->where('posts.post_type','=',trim($post_type))->orderBy('id',$config['order']);
            }
        }else{
            $post =  Post::select('posts.*','postmeta.meta_key')->
            join('postmeta','posts.ID','=','postmeta.post_id')->
            where('posts.post_status','!=','inherit')->where('posts.post_status','!=','trash')->orderBy('id',$config['order']);
        }
        if(isset($get['situacao'])){
            $post->where('postmeta.meta_key','=','situacao_leilao')->
            where('postmeta.meta_value',$get['situacao']);
            if(isset($get['contrato']) && $get['contrato']=='s'){
                $post->where('posts.config','LIKE','%status":"publicado%')->
                where('posts.config','LIKE','%contrato":"%');
            }
        }
        //$post =  DB::table('posts')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $post_totais = new stdClass;
        $campos = $this->campos(false,$post_type);
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                if(isset($get['filter']['post_status'])){
                    $get['filter']['post_status'] = 'publish';
                }else{
                    if(isset($get['origem'])=='site'){
                        $get['filter']['post_status'] = 'publish';
                    }else{
                        $get['filter']['post_status'] = 'pending';
                    }
                }
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id' || $key=='ID'){
                            $post->where('posts.'.$key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            if(is_array($value)){
                                foreach ($value as $kb => $vb) {
                                    if(!empty($vb)){
                                        if($key=='tags'){
                                            $post->where('posts.'.$key,'LIKE', '%"'.$vb.'"%' );
                                        }else{
                                            $post->where('posts.'.$key,'LIKE', '%"'.$kb.'":"'.$vb.'"%' );
                                        }
                                    }
                                }
                            }else{
                                $post->where('posts.'.$key,'LIKE','%'. $value. '%');
                                if(isset($campos[$key]['type']) && $campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                                @$arr_titulo[@$campos[$key]['label']] = $value;
                                $titulo_tab .= 'Todos com *'. @$campos[$key]['label'] .'% = '.$value.'& ';
                            }
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
        // dd($post);

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

    public function campos_produtos($post_id=false){
        $hidden_editor = '';
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        if($post_id){
            $data = Post::Find($post_id);
            if($data->count()){
                $data = $data->toArray();
                if(isset($data['token'])){
                    $data['leilao'] = (new LeilaoController)->is_linked_leilao($data['token'],true);
                }
            }
        }
        // $event_divide = 'onkeyup=divideHoras(this)';
        $event_divide = '';
        $ret = [
            'ID'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'config[cliente]'=>[
                'label'=>'Nome do cliente*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT * FROM users WHERE ativo='s' AND id_permission>'4'",'name','id','email',' | Email: '),'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
                'exibe_busca'=>true,
                'option_select'=>true,
                'class'=>'select2',
                'value'=>@$_GET['config']['cliente'],
                'cp_busca'=>'config][cliente',
            ],
            'post_title'=>['label'=>'Número do contrato','active'=>true,'placeholder'=>'Ex.: 5621.11.2023','type'=>'text','exibe_busca'=>'d-block','event'=>'required onkeyup=lib_typeSlug(this)','tam'=>'12','title'=>'Identificador do contrado pode ser nome ou número'],
            'config[total_horas]'=>['label'=>'Qtd. Horas','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'required '.$event_divide,'tam'=>'4','cp_busca'=>'config][total_horas','title'=>'Número total de horas que serão leiloadas'],
            'config[valor_r]'=>['label'=>'Valor da Rescisão','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required '.$event_divide,'tam'=>'4','cp_busca'=>'config][valor_r','title'=>'Valor da rescisão, este também será o valor de lance inicial quando o cliente criar o leilão'],
            'config[incremento]'=>['label'=>'Incremento','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'4','cp_busca'=>'config][incremento','class'=>'','title'=>'Valor de incremento em cada lançe do Leilão'],
            // 'config[valor_h]'=>['label'=>'Valor Hora','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'3','cp_busca'=>'config][valor_h','title'=>'Valor do hora no contrato'],
            // 'config[lance_unit]'=>['label'=>'Lance por hora','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required onkeyup=multiplicaHorasLance(this)','tam'=>'3','cp_busca'=>'config][lance_unit','title'=>'Valor unitário do Lançe'],
            // 'config[lance_total]'=>['label'=>'Lance total','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'3','cp_busca'=>'config][lance_total','title'=>'Valor unitário do Lançe mutiplicado pela quantidade de horas'],
            'config[valor_venda]'=>['label'=>'Compre já','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required onchange=verific_cvalor_venda(this.value)','tam'=>'6','cp_busca'=>'config][valor_venda','title'=>'Valor para venda sem leilão'],
            'config[valor_atual]'=>['label'=>'Valor Atual','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'6','cp_busca'=>'config][valor_atual','title'=>'Valor atual do pacote no Aeroclube'],
            // 'post_date_gmt'=>['label'=>'Data do decreto','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'4'],
            'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
            //'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            //'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'post_content'=>['label'=>'Descrição','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor.' required','tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
            'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Publicado','pending'=>'Despublicado']],
        ];
        if(isset($data['post_status']) && $data['post_status']=='publish'){
            $ret['add_leilao'] = [
                'label'=>__('sessao para adicionar leilão'),
                'type'=>'html',
                'active'=>false,
                'script'=>'admin.leilao.contratos.add_leilao',
                'script_show'=>'admin.leilao.contratos.add_leilao',
                'dados'=>$data,
            ];
        }
        return $ret;
    }
    public function campos_componentes($post_id=false){
        $hidden_editor = '';
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        if($post_id){
            $data = Post::Find($post_id);
            if($data->count()){
                $data = $data->toArray();
                if(isset($data['token'])){
                    $data['leilao'] = (new LeilaoController)->is_linked_leilao($data['token'],true);
                }
            }
        }
        // $event_divide = 'onkeyup=divideHoras(this)';
        $event_divide = '';
        $ret = [
            'ID'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'config[cliente]'=>[
                'label'=>'Nome do cliente*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT * FROM users WHERE ativo='s' AND id_permission>'4'",'name','id','email',' | Email: '),'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
                'exibe_busca'=>true,
                'option_select'=>true,
                'class'=>'select2',
                'value'=>@$_GET['config']['cliente'],
                'cp_busca'=>'config][cliente',
            ],
            'post_title'=>['label'=>'Número do contrato','active'=>true,'placeholder'=>'Ex.: 5621.11.2023','type'=>'text','exibe_busca'=>'d-block','event'=>'required onkeyup=lib_typeSlug(this)','tam'=>'12','title'=>'Identificador do contrado pode ser nome ou número'],
            'config[total_horas]'=>['label'=>'Qtd. Horas','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'required '.$event_divide,'tam'=>'4','cp_busca'=>'config][total_horas','title'=>'Número total de horas que serão leiloadas'],
            'config[valor_r]'=>['label'=>'Valor da Rescisão','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required '.$event_divide,'tam'=>'4','cp_busca'=>'config][valor_r','title'=>'Valor da rescisão, este também será o valor de lance inicial quando o cliente criar o leilão'],
            'config[incremento]'=>['label'=>'Incremento','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'4','cp_busca'=>'config][incremento','class'=>'','title'=>'Valor de incremento em cada lançe do Leilão'],
            // 'config[valor_h]'=>['label'=>'Valor Hora','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'3','cp_busca'=>'config][valor_h','title'=>'Valor do hora no contrato'],
            // 'config[lance_unit]'=>['label'=>'Lance por hora','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required onkeyup=multiplicaHorasLance(this)','tam'=>'3','cp_busca'=>'config][lance_unit','title'=>'Valor unitário do Lançe'],
            // 'config[lance_total]'=>['label'=>'Lance total','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'3','cp_busca'=>'config][lance_total','title'=>'Valor unitário do Lançe mutiplicado pela quantidade de horas'],
            'config[valor_venda]'=>['label'=>'Compre já','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required onchange=verific_cvalor_venda(this.value)','tam'=>'6','cp_busca'=>'config][valor_venda','title'=>'Valor para venda sem leilão'],
            'config[valor_atual]'=>['label'=>'Valor Atual','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'6','cp_busca'=>'config][valor_atual','title'=>'Valor atual do pacote no Aeroclube'],
            // 'post_date_gmt'=>['label'=>'Data do decreto','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'4'],
            'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
            //'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            //'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'post_content'=>['label'=>'Descrição','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor.' required','tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
            'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Publicado','pending'=>'Despublicado']],
        ];
        if(isset($data['post_status']) && $data['post_status']=='publish'){
            $ret['add_leilao'] = [
                'label'=>__('sessao para adicionar leilão'),
                'type'=>'html',
                'active'=>false,
                'script'=>'admin.leilao.contratos.add_leilao',
                'script_show'=>'admin.leilao.contratos.add_leilao',
                'dados'=>$data,
            ];
        }
        return $ret;
    }
    public function campos_leilao($post_id=false,$post_type=false,$data=false){
        $hidden_editor = '';
        $seg1 = request()->segment(1);
        $seg3 = request()->segment(3);
        if($seg3=='create'){
            $ac = 'cad';
        }else{
            $ac = 'alt';
        }
        // dd($post_id,$data);
        if(Qlib::is_frontend()){
            if($seg1=='leilao-create'){
                $ac = 'cad';
            }
        }

        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        $user = Auth::user();
        $lc = new LeilaoController;
        if($post_id){
            // $data = Post::where('ID','=',$post_id)->get()->toArray();// Post::Find($leilao_id);
            $data = $lc->get_leilao($post_id);// Post::Find($leilao_id);
            if(isset($data[0])){
                $data = $data[0];
            }
        }
        if($data && isset($data['config']['contrato'])){
            $contrato = (new LeilaoController)->get_data_contrato($data['config']['contrato']);
            if(isset($contrato['config'])){
                foreach ($contrato['config'] as $kc => $vc) {
                    $data['config'][$kc] = $vc;
                }
            }
            // dd($data);
        }
        $post_type = $post_type?$post_type:$this->post_type;
        $name_route = request()->route()->getName();
        $arr_status = [
            'edicao' => 'Em edição',
            'publicado' => 'Publicado',
        ];
        $event_status = 'required';
        $arr_itens=[];
        if($ac=='cad'){
            @$data['token'] = uniqid();
            $data['post_title'] = 'Leilão '.$data['token'];
            $data['post_author'] = isset($_GET['post_author']) ? $_GET['post_author'] : false;
            $contrato = isset($_GET['contrato']) ? $_GET['contrato'] : false;
            if($contrato){
                $dcontra = Qlib::buscaValorDb0('posts','token',$contrato,'config');
                $data['post_content'] = Qlib::buscaValorDb0('posts','token',$contrato,'post_content');
                if($dcontra){
                    $data['config'] = Qlib::lib_json_array($dcontra);
                    $data['config']['contrato'] = $contrato;
                }
            }
        }else{
            // if(count($arr_itens)==0 && isset($data['config']['itens']) && count($data['config']['itens'])){
                //     $arr_itens = $data['config']['itens'];
                // }
            $data['post_author'] = isset($_GET['post_author']) ? $_GET['post_author'] : @$data['post_author'];
        }
        if(Qlib::is_backend()){
            $event_status = ' onchange=exibeStatus(this);';
            // $arr_itens = $lc->array_contratos();
            if($seg3=='create'){
                $ac = 'cad';
                $arr_itens = $lc->array_contratos(@$data['post_author']);
            }else{
                $arr_itens = $lc->array_contratos(@$data['post_author']);
            }
        }else{
            $seg1 = request()->segment(1);
            if($seg1){
                $arr_seg1=explode('-',$seg1);
                if(isset($arr_seg1[1]) && $arr_seg1[1]=='create'){
                    $ac = 'cad';
                }
            }
            if(isset($user->id))
                $arr_itens = $lc->array_contratos($user->id);
        }
        $duracao_max_leilao = Qlib::qoption('duracao_max_leilao')?Qlib::qoption('duracao_max_leilao'):15;
        $max_data = Qlib::CalcularVencimento2(date('d/m/Y'),$duracao_max_leilao,'Y-m-d');
        $ret = [
            'sep1'=>['label'=>'Dados do Leilão','active'=>false,'tam'=>'12','script'=>'<h5 class="pt-1 text-light">'.__('Dados do Leilão').'</h5>','type'=>'html_script','class_div'=>'bg-secondary'],
            'ID'=>['label'=>'Id','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_title'=>['label'=>'Nome do Leilão','active'=>true,'placeholder'=>'Ex.: leilão 55 ','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'required onkeyup=lib_typeSlug(this)','tam'=>'7','title'=>'Identificador do contrado pode ser nome ou número','value'=>@$data['post_title']],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$post_type],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','value'=>@$data['token']],
            'config[origem]'=>['label'=>'origem','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][origem','value'=>$name_route],
            'post_author'=>[
                'label'=>'Autor',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,name FROM users WHERE ativo='s' AND id_permission>'1'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required onchange=select_contrato(this)',
                'tam'=>'12',
                'exibe_busca'=>true,
                'option_select'=>true,
                'class'=>'select2',
                'value'=>@$data['post_author'],
            ],
            'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
            // 'config[itens][]'=>[
                //     'label'=>'Contratos*',
                //     'active'=>true,
                //     'type'=>'select_multiple',
                //     'arr_opc'=>$arr_itens,'exibe_busca'=>'d-block',
                //     'event'=>'required onchange=dataContratos(this)',
                //     'tam'=>'12',
                //     'class'=>'',
                //     'exibe_busca'=>true,
                //     'option_select'=>true,
                //     'cp_busca'=>'config][itens',
                //     'class'=>'select2',
                // ],
            'callback_contrato'=>[],
            'config[contrato]'=>[
                    'label'=>'Contratos*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>$arr_itens,'exibe_busca'=>'d-block',
                'event'=>'required onchange=dataContratos(this)',
                'tam'=>'12',
                'class'=>'',
                'exibe_busca'=>'d-none',
                'option_select'=>true,
                'cp_busca'=>'config][contrato',
                'class'=>'select2',
                'value'=>@$data['config']['contrato'],
            ],
            'config[status]'=>[
                'label'=>'Status*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>$arr_status,'exibe_busca'=>'d-block',
                'event'=>$event_status,
                'tam'=>'12',
                'class'=>'',
                'exibe_busca'=>true,
                'option_select'=>false,
                'cp_busca'=>'config][status',
            ],
            // 'config[total_horas]'=>['label'=>'Qtd. Horas','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'required','tam'=>'3','cp_busca'=>'config][total_horas','title'=>'Número total de horas'],
            'config[total_horas]'=>['label'=>'Qtd. Horas','active'=>false,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','class'=>'text-field-leilao','tam'=>'6','cp_busca'=>'config][total_horas','value'=>@$data['config']['total_horas'],'title'=>'Número total de horas'],
            // 'config[valor_r]'=>['label'=>'Lance inicial','active'=>false,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>'3','cp_busca'=>'config][valor_r','title'=>'Valor do reembolso'],
            'config[valor_r]'=>['label'=>'Lance inicial','active'=>false,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','class'=>'text-field-leilao','tam'=>'6','cp_busca'=>'config][valor_r','value'=>@$data['config']['valor_r'],'title'=>'Valor do reembolso'],
            'config[incremento]'=>['label'=>'Incremento','active'=>false,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','tam'=>'6','cp_busca'=>'config][incremento','class'=>'text-field-leilao','value'=>@$data['config']['incremento'],'title'=>'Valor de incremento em cada lançe'],
            'config[valor_venda]'=>['label'=>'Compre já','active'=>false,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'required','tam'=>'6','cp_busca'=>'config][valor_venda','value'=>@$data['config']['valor_venda'],'title'=>'Valor para venda sem leilão'],
            // 'post_date_gmt'=>['label'=>'Data do decreto','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'4'],
            'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
            'post_content'=>['label'=>'Descrição','active'=>false,'type'=>'hidden_text','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'','placeholder'=>__('Escreva seu conteúdo aqui..'),'value'=>@$data['post_content']],
            'infoPag'=>['label'=>'Formas de Pagamento','active'=>false,'tam'=>'12','script'=>'<h6 class="mt-2">Formas de pagamento</h6><p><label class="pt-1" for="fp"> <input id="fp" class="mr-2" type="checkbox" disabled checked />&nbsp;'.__('Cartão e Pix').' <i class="fa fa-question-circle" data-toggle="tooltip" title="'.__('Permitir usuário realizar o pagamento via '.config('app.name').'STORE na '.config('app.name').'. Quando o usuário realizar pagamento por essa opção, será gerado um pedido no site com todas as funcionalidades da ').config('app.name').'"></i></label>','type'=>'html_script','class_div'=>''],
            'config[termino]'=>['label'=>'Término','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-none','event'=>'required min='.date('Y-m-d').' max='.$max_data,'tam'=>'6','cp_busca'=>'config][termino','title'=>''],
            'config[hora_termino]'=>['label'=>'Hora','active'=>true,'placeholder'=>'','type'=>'time','exibe_busca'=>'d-none','event'=>'required','tam'=>'6','cp_busca'=>'config][hora_termino','title'=>'Hora de Termino'],
            'config[pode_lance]'=>[
                'label'=>'Quem pode dar lances em seu leilão',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='5'",'nome','id'),'exibe_busca'=>'d-block',
                'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
                'exibe_busca'=>true,
                'option_select'=>true,
                'class'=>'select2-',
                'cp_busca'=>'config][pode_lance',
            ],
            // 'post_content'=>['label'=>'Descrição','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
        ];
        if(Qlib::is_backend()){
            $ret['ID']['active'] = true;
            // if($ac=='alt'){
            //     $ret['post_title'] = ['label'=>'Nome do Leilão','active'=>true,'placeholder'=>'Ex.: leilão 55 ','type'=>'text','exibe_busca'=>'d-block','event'=>'required onkeyup=lib_typeSlug(this)','tam'=>'7','title'=>'Identificador do contrado pode ser nome ou número','value'=>@$data['post_title']];
            // }
            if(isset($data['ID'])){
                //Verificar qual a situação interna
                $situacao = Qlib::get_postmeta($data['ID'],);
                // dd($data['ID']);
            }
            $ret['post_status'] = ['label'=>'Liberado','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['publish'=>'Publicado','pending'=>'Despublicado']];
            //Se tem um contrato cadastrado impedir de mudar o proprietário
            if(isset($data['config']['cliente']) && ($author_id = $data['config']['cliente']) && isset($data['config']['contrato']) && !empty($data['config']['contrato'])){
                $d_user = User::Find($author_id);
                if($d_user){
                    // $ret['post_author'] = ['label'=>'Responsável','active'=>false,'type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','value'=>$author_id,'tam'=>'12'];
                    // dd($d_user);
                    $d_user['config'] = @Qlib::lib_json_array($d_user['config']);
                    $link = $lc->get_link_front($post_id);
                    $script = '<p><b>Ver no site: </b><a style="text-decoration:underline" href="'.$link.'" target="_blank">'.$data['post_title'].'</a></p>';
                    $script .= '<p><b>Responsável:</b><span> '.$d_user['name'].'</span><input type="hidden" name="post_author" value="'.$d_user['id'].'"></p>';
                    $script .= '<p><b>Email:</b> '.$d_user['email'].' <b>Celular:</b> '.@$d_user['config']['celular'].'</p>';
                    $script .= '<p><b>CPF:</b> '.$d_user['cpf'].'</p>';
                    $ret['post_author'] = ['label'=>'Responsável','active'=>false,'type'=>'html_script','exibe_busca'=>'d-block','script'=>$script,'script_show'=>$script,'tam'=>'12'];
                    // dd($ret);
                }
            }
        }
        if(Qlib::is_frontend()){
            $value_author = false;
            if(!$data){
                $value_author = Auth::id();
            }
            $ret['config[contrato]']['active'] = false;
            $ret['post_author'] = ['label'=>'Responsável','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','value'=>$value_author,'tam'=>'2'];
        }
        if($ac!='cad' || ($ac=='cad' && isset($data['config']['contrato']))){
            $link = $lc->get_link_front($post_id);
            $script = '<p><b>Ver no site: </b><a style="text-decoration:underline" href="'.$link.'" target="_blank">'.@$data['post_title'].'</a></p>';
            $script = false;
            if(isset($data['config']['itens'][0]) && !empty($data['config']['itens'][0])){
                $ctt = false;
                $nome_contrato = Qlib::buscaValorDb0('posts','token',$data['config']['itens'][0],'post_title');
                $script .= '<b class="pt-1">'.__('Contrato escolhido').':</b> <span id="tk-contrato">'.$nome_contrato.'</span> <button type="button" id="btn-remove-contrato" class="btn btn-outline-secondary" onclick="remove_contrato_leilao();">'.__('Remover Contrato').'</button>';
                $ret['callback_contrato'] = ['label'=>'calback_leilao','active'=>false,'tam'=>'12','script'=>$script,'type'=>'html_script','class_div'=>'mt-2 mb-2'];
                $ret['config[contrato]'] = ['label'=>'token_contrato','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][contrato','value'=>$ctt];
            }
            if(isset($data['config']['contrato']) && !empty($data['config']['contrato'])){
                $ctt = $data['config']['contrato'];
                $nome_contrato = Qlib::buscaValorDb0('posts','token',$ctt,'post_title');
                $btn_edit_contrato = false;
                if(Qlib::isAdmin(2)){
                    $id_contrato = Qlib::get_id_by_token($ctt);
                    $btn_edit_contrato = '<a href="'.url('/').'/admin/produtos/'.$id_contrato.'/edit" class="btn btn-outline-secondary" target="_blank"> '.__('Editar Contrato').'</a>';
                }
                $script .= '<b class="pt-1">'.__('Contrato escolhido').':</b> <span id="tk-contrato">'.$nome_contrato.'</span><br><button type="button" id="btn-remove-contrato" class="btn btn-outline-danger" onclick="remove_contrato_leilao();">'.__('Remover Contrato').'</button> '.$btn_edit_contrato;
                $ret['callback_contrato'] = ['label'=>'calback_leilao','active'=>false,'tam'=>'12','script'=>$script,'type'=>'html_script','class_div'=>'mt-2 mb-2'];
                $ret['config[contrato]'] = ['label'=>'token_contrato','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][contrato','value'=>$ctt];
                $ret['resumo'] = ['label'=>'Resumo','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','script'=>view('admin.leilao.resumo',['d'=>$data]),'script_show'=>view('admin.leilao.resumo',['d'=>$data]),'tam'=>'12'];

            }
            // if(isset($data['config']['itens']) && !empty($data['config']['itens'])){
            //     $ctt = $data['config']['contrato'];
            //     $nome_contrato = Qlib::buscaValorDb0('posts','token',$ctt,'post_title');
            //     $ret['callback_contrato'] = ['label'=>'calback_leilao','active'=>false,'tam'=>'12','script'=>'<b class="pt-1">'.__('Contrato escolhido').':</b> <span id="tk-contrato">'.$nome_contrato.'</span> <button type="button" id="btn-remove-contrato" class="btn btn-outline-secondary" onclick="remove_contrato_leilao();">'.__('Remover Contrato').'</button>','type'=>'html_script','class_div'=>'mt-2 mb-2'];
            //     $ret['config[contrato]'] = ['label'=>'token_contrato','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][contrato','value'=>$ctt];

            // }

        }

        return $ret;
    }
    public function campos_pacotes($sec=false){
        $hidden_editor = '';
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        $ret = [
            'ID'=>['label'=>'Id','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
            'post_title'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Nome do produto ','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'12'],
            // 'config[total_horas]'=>['label'=>'Horas','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][total_horas','title'=>'Número total de horas'],
            // 'post_date_gmt'=>['label'=>'Data do decreto','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
            //'post_excerpt'=>['label'=>'Resumo (Opcional)','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            //'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
            'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Publicado','pending'=>'Despublicado']],
        ];
        return $ret;
    }
    public function campos_paginas($post_id=false){
        $hidden_editor = '';
        $data = false;
        $user = Auth::user();
        if($post_id){
            $data = Post::Find($post_id);
        }
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        $ret = [
            'ID'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'url_view'=>[
                'label'=>__('Link no site'),
                'type'=>'html',
                'active'=>false,
                'script'=>'admin.posts.link_view',
                'script_show'=>'admin.posts.link_view',
                'dados'=>$data,
            ],
            'config[tipo_pagina]'=>[
                'label'=>'Tipo de página*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::get_tipos('tipos_paginas'),'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'4',
                'exibe_busca'=>true,
                'option_select'=>true,
                'class'=>'',
                'value'=>@$_GET['config']['tipo_pagina'],
                'cp_busca'=>'config][tipo_pagina',
            ],
            'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'text','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'8'],
            'post_title'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Nome da página ','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'12'],
            'post_excerpt'=>['label'=>'Descriação curta','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'','placeholder'=>__('Escreva seu conteúdo aqui..')],
            'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
            'config[permission][]'=>[
                'label'=>'Permissão de visualização (Visível para todos se não selecionar)',
                'active'=>true,
                'type'=>'select_multiple',
                'arr_opc'=>Qlib::sql_array("SELECT id,name FROM permissions WHERE active='s' AND id >='".$user->id_permission."'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'12',
                'cp_busca'=>'config][permission'
            ],
            'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Publicado','pending'=>'Despublicado']],
        ];
        return $ret;
    }
    public function campos_orcamentos($post_id=false){
        $ac = 'cad';
        $data = false;
        // $user = Auth::user();
        if($post_id){
            $data = Post::Find($post_id);
            if($data->count()){
                $data = $data->toArray();
                $ac = 'alt';
            }
        }
        if($ac=='cad'){
            $data['post_title'] = 'Novo orçamento';
        }

        $ret = [
            'ID'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'guid'=>['label'=>'cliente','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2'],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
            'post_date'=>['label'=>'Data','active'=>true,'type'=>'hidden_text','exibe_busca'=>'d-none','event'=>'','tam'=>'6'],
            'post_title'=>['label'=>'Titulo','active'=>true,'value'=>@$data['post_title'],'placeholder'=>'Ex.: Nome da página ','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'12'],
            'post_status'=>[
                'label'=>'Status*',
                'active'=>true,
                'busca'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::get_tipos('status_orcamentos'),
                'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
                'option_select'=>true,
                'class'=>'',
                // 'value'=>@$_GET['config']['tipo_pagina'],
            ],

        ];
        if(isset($data['token'])){
            if(request()->segment(4)=='edit'){
                $oc = (new OrcamentoController)->orcamento_html($data['token'],'table_only');
            }else{
                $oc = (new OrcamentoController)->orcamento_html($data['token']);
            }
            $data['orcamento_html'] = $oc;
        }
        if(isset($data['config']) && is_string($data['config'])){
            $data['config'] = json_decode($data['config'], true);
        }

        $ret['config[matricula]']   =   ['label'=>'Matrícula','active'=>true,'placeholder'=>'XXXXX','type'=>'text','exibe_busca'=>'d-block','event'=>'required maxlength=5 style=text-transform:uppercase','tam'=>'12','cp_busca'=>'config][matricula','title'=>'Número da matricula da aeronave'];
        $ret['add_orcamentos'] = [
            'label'=>__('Orçamentos'),
            'type'=>'html',
            'active'=>false,
            'script'=>'admin.orcamentos.add_orcamentos',
            'script_show'=>'admin.orcamentos.show_orcamentos',
            'dados'=>$data,
        ];
        $route = request()->route()->getName();
        if($ac=='cad' && $route != 'orcamentos.index'){
            $ret['post_title']['type'] = 'hidden';
            $ret['post_date']['type'] = 'hidden';
            // $ret['post_date']['value'] = Qlib::dtBanco(Qlib::dataLocal());
            $ret['post_status']['type'] = 'hidden';
            $ret['post_status']['value'] = 'aguardando';
            $rf = 'fornecedores';
            // $userC = new UserController(['route'=>$rf]);
        }
        $consulta = Qlib::get_postmeta($post_id,'consulta_rab',true);
        $ret['config[consulta]']         =   ['label'=>'Consulta:','active'=>false,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','event'=>'required ','tam'=>'12','cp_busca'=>'config][consulta','value'=>$consulta];
        if($ac=='alt'){
            $ret['config[ddi]']         =   ['label'=>'ddi','active'=>true,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','event'=>'required ','tam'=>'12','cp_busca'=>'config][ddi','title'=>''];
            $ret['config[whatsapp]']     =   ['label'=>'whatsapp','active'=>true,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','event'=>'required ','tam'=>'12','cp_busca'=>'config][whatsapp','title'=>''];

        }
        $ret['config[servicos]'] = [
            'label'=>'Serviços*',
            'active'=>true,
            'type'=>'select',
            'arr_opc'=>Qlib::sql_array("SELECT * FROM posts WHERE post_status='publish' AND post_type='servicos'",'post_title','post_title'),'exibe_busca'=>'d-block',
            'event'=>'required',
            'tam'=>'12',
            'show'=>false,
            'exibe_busca'=>true,
            'option_select'=>true,
            'class'=>'select2',
            // 'value'=>@$_GET['config']['servico'],
            'cp_busca'=>'config][servicos',
        ];
        $userC = new UserController();
        $label1 = 'Nome do solicitante';
        $ret['guid']=[
            'label'=>$label1,
            'active'=>false,
            'type'=>'html_vinculo',
            'exibe_busca'=>'d-none',
            'event'=>'',
            'tam'=>'12',
            'script'=>'',
            'data_selector'=>[
                'campos'=>$userC->campos(),
                'route_index'=>route('users.index'),
                'id_form'=>'frm-users',
                // 'tipo'=>'array', // int para somente um ou array para vários
                'tipo'=>'text', // int para somente um ou array para vários
                'action'=>route('users.store'),
                'campo_id'=>'id',
                'campo_bus'=>'name',
                'campo'=>'guid',
                'value'=>[],
                'label'=>'Informações do cliente',
                'table'=>[
                    //'id'=>['label'=>'Id','type'=>'text'],
                    'name'=>['label'=>'Nome','type'=>'text', //campos que serão motands na tabela
                    'conf_sql'=>[
                        'tab'=>'users',
                        'campo_bus'=>'id',
                        'select'=>'name',
                        'param'=>['name','email'],
                        ]
                    ],
                    'email'=>['label'=>'Email','type'=>'text'], //campos que serão motands na tabela
                ],
                'tab' =>'users',
                'placeholder' =>'Digite somente o nome do '.$label1.'...',
                'janela'=>[
                    'url'=>route('users.create').'',
                    // 'param'=>['name','cnpj','email'],
                    'param'=>[],
                    'form-param'=>'',
                ],
                'salvar_primeiro' =>false,//exigir cadastro do vinculo antes de cadastrar este
            ],
            'script' => false,//'familias.loteamento', //script admicionar
        ];
        if(Qlib::isAdmin(1)){
            $ret['token']['type'] = 'hidden_text';
            $ret['token']['tam'] = '6';
        }
        $ret['obs'] = ['label'=>'Descrição','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12','class_div'=>'','class'=>'editor-padrao summernote','value'=>@$data['post_content'],'show'=>false,'placeholder'=>__('Escreva seu conteúdo aqui..')];
        if($ac=='cad'){
            $ret['add_zap'] = [
                'label'=>__('Enviar para assinaturas'),
                'type'=>'html_script',
                'active'=>false,
                'script'=>'<label><input checked type="checkbox" name="config[enviar_assinatura]" value="s" /> Enviar para assinatura</label>',
                // 'script_show'=>'admin.leilao.contratos.add_leilao',
                'dados'=>$data,
            ];
        }

        return $ret;
    }
    public function campos_menus($sec=false){
        $hidden_editor = '';
        if(Qlib::qoption('editor_padrao')=='laraberg'){
            $hidden_editor = 'hidden';
        }
        $ret = [
            'ID'=>['label'=>'Id','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'post_title'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Nome da página ','type'=>'text','exibe_busca'=>'d-block','event'=>'onkeyup=lib_typeSlug(this)','tam'=>'10'],
            'menu_order'=>['label'=>'Ordem','active'=>true,'type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>'2','class_div'=>'','class'=>''],
            // 'post_name'=>['label'=>'Slug','active'=>false,'placeholder'=>'Ex.: nome-do-post','type'=>'hidden','exibe_busca'=>'d-block','event'=>'type_slug=true','tam'=>'12'],
            'post_name'=>[
                'label'=>'Página*',
                'active'=>true,
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$this->campos_paginas(),
                    'route_index'=>route('paginas.index'),
                    'id_form'=>'frm-paginas',
                    'action'=>route('paginas.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'post_name',
                    'label'=>'Nome da página',
                ],'arr_opc'=>Qlib::sql_array("SELECT ID,post_name FROM posts WHERE post_status='publish' AND post_type='pagina'",'post_name','ID'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'12',
                'class'=>'select2'
            ],
            'post_status'=>['label'=>'Status','active'=>true,'type'=>'chave_checkbox','value'=>'publish','valor_padrao'=>'publish','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['publish'=>'Ativado','pending'=>'Desativado']],
        ];
        return $ret;
    }
    public function campos($post_id=false,$type=false){
        // $sec = $sec?$sec:$this->sec;
        $ret = false;
        $type = $type?$type:$this->post_type;
        if($type=='produtos'){
            $ret = $this->campos_produtos($post_id);

        }elseif($type=='menus'){
            $ret = $this->campos_menus($post_id);
        }elseif($type=='orcamentos'){
            $ret = $this->campos_orcamentos($post_id);
        }elseif($type=='paginas'){
            $ret = $this->campos_paginas($post_id);
        }elseif($type=='pacotes_lance'){
            $ret = $this->campos_pacotes($post_id);
        }elseif($type=='componentes'){
            $ret = $this->campos_componentes($post_id);
        }elseif($type=='leilao' || $type=='leiloes_adm'){
            $ret = $this->campos_leilao($post_id);
        }
        return $ret;
    }
    public function index()
    {
        //$this->authorize('is_admin', $user);
        $this->authorize('ler', $this->routa);
        //Selecionar o tipo de postagem
        $selTypes = $this->selectType($this->sec);
        $title = $selTypes['title'];
        $titulo = $title;
        if($this->post_type=='leiloes_adm' && isset($_GET['situacao'])){
            $queryPost = $this->queryPostLeilao($_GET);
        }else{
            $queryPost = $this->queryPost($_GET);
        }
        $queryPost['config']['exibe'] = 'html';
        $routa = $this->routa;
        //if(isset($queryPost['post']));
        $ret = [
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
        ];

        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
        return view($this->view.'.index',$ret);
    }
    public function selectType($sec=false)
    {
        $ret['exec']=false;
        $ret['title']=false;
        $title = false;
        if($sec){
            $name = request()->route()->getName();
            if($sec=='posts'){
                $title = __('Cadastro de postagens');
            }elseif($sec=='produtos'){
                $title = __('Cadastro de contratos');
                if($name=='produtos.edit'){
                    $title = __('Editar Cadastro de contratos');
                }
            }elseif($sec=='leiloes_adm'){
                $title = __('Cadastro de leilao');
                if($name=='leilao.edit'){
                    $title = __('Editar Cadastro de leilao');
                }
            }elseif($sec=='paginas'){
                $title = __('Cadastro de paginas');
            }elseif($sec=='menus'){
                $title = __('Cadastro de menus');
            }elseif($sec=='orcamentos'){
                $title = __('Cadastro de orçamentos');
            }elseif($sec=='pacotes_lances'){
                $title = __('Cadastro de pacotes');
            }else{
                $title = __('Sem titulo');
            }
        }
        $ret['title'] = $title;
        return $ret;
    }
    public function addMenu($page_id=false){
        $ret = false;
        if($page_id){
            $dPage = Post::Find($page_id);
            if(!$dPage->count()){
                return $ret;
            }
            $page_title = $dPage['post_title'] ? $dPage['post_title'] : null;
            $page_status = $dPage['post_status'] ? $dPage['post_status'] : null;
            $page_author = $dPage['post_author'] ? $dPage['post_author'] : Auth::id();
            $proximo_menu = (int)(Post::max('menu_order'))+1;
            $ds = [
                'post_date_gmt'=>Qlib::dataLocalDb(),
                'post_title'=>$page_title,
                'post_title'=>$page_title,
                'post_name'=>$page_id,
                'post_status'=>$page_status,
                'post_modified'=>Qlib::dataLocalDb(),
                'post_modified_gnt'=>Qlib::dataLocalDb(),
                'post_type'=>'menu',
                'menu_order'=>$proximo_menu,
                'token'=>uniqid(),
            ];
            $ret = Post::create($ds);
        }
        return $ret;
    }
    public function create(User $user)
    {
        $this->authorize('is_admin2', $user);
        //Selecionar o tipo de postagem
        $selTypes = $this->selectType($this->sec);
        $title = $selTypes['title'];
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-posts',
            'route'=>$this->routa,
            'view'=>$this->view,
            'arquivos'=>'jpeg,jpg,png',
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos();
        // dd($campos);
         //REGISTRAR EVENTO CADASTRO
         $regev = Qlib::regEvent(['action'=>'create','tab'=>$this->tab,'config'=>[
            'obs'=>'Abriu tela de cadastro',
            'link'=>$this->routa,
            ]
        ]);

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
        $ret = false;
        if($post_id&&$meta_key&&$meta_value){
            $verf = Qlib::totalReg('wp_postmeta',"WHERE post_id='$post_id' AND meta_key='$meta_key'");
            if($verf){
                $ret=DB::table('wp_postmeta')->where('post_id',$post_id)->where('meta_key',$meta_key)->update([
                    'meta_value'=>$meta_value,
                ]);
            }else{
                $ret=DB::table('wp_postmeta')->insert([
                    'post_id'=>$post_id,
                    'meta_value'=>$meta_value,
                    'meta_key'=>$meta_key,
                ]);
            }
            //$ret = DB::table('wp_postmeta')->storeOrUpdate();
        }
        return $ret;
    }
    public function store(StorePostRequest $request)
    {
        $dados = $request->all();
        if(Qlib::is_backend()){
            $this->authorize('create', $this->routa);
        }else{
            if(isset($dados['post_type']) && $dados['post_type']=='leiloes_adm'){
                $this->authorize('is_logado');
                $dados['post_status'] = 'pending';
            }else{
                $this->authorize('create', $this->routa);
            }
        }
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        //$dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        $userLogadon = Auth::id();
        $dados['post_author'] = $userLogadon;
        $dados['token'] = !empty($dados['token'])?$dados['token']:uniqid();
        $dados['post_status'] = isset($dados['post_status']) ? $dados['post_status']:'pending';
        $dados['config'] = isset($dados['config']) ? $dados['config']:[];
        if($dados['post_date']==null || $dados['post_date']==false){
            unset($dados['post_date']);
        }
        // $dados['post_date'] = isset($dados['post_date'])?$dados['post_date']:Qlib::dtBanco(Qlib::dataLocal());
        $origem = isset($dados['config']['origem'])?$dados['config']['origem']:false;
        if($this->routa=='orcamentos'){
            $validatedData = $request->validate([
                'guid' => ['required'],
            ],[
                'guid.required'=>__('O nome do solicitante é obrigatório'),
            ]);
            $id_cliente = isset($dados['guid']) ? $dados['guid'] : false;
            $obs = isset($dados['obs']) ? $dados['obs'] : false;
            $token = $dados['token'];
            // dd($dados['config']);
            $ret = (new OrcamentoController)->salvarOrcamento($id_cliente,[
                'token'=>$token,
                'obs'=>$obs,
            ],$dados['config']);
            if(isset($ret['exec']) && isset($ret['idCad'])){
                // $ret['redirect'] = route('orcamentos.show',['id'=>$ret['idCad']]);
                $ret['redirect'] = route('orcamentos.index').'?idCad='.$ret['idCad'];
            }else{

            }
            return $ret;

        }else{
            $salvar = Post::create($dados);
        }
        $sm = false;
        if(isset($salvar->id) && $salvar->id){
            $mens = $this->label.' cadastrado com sucesso!';
            $color = 'success';
            $idCad = $salvar->id;
            //REGISTRAR EVENTO STORE
            if($salvar->id){
                //SALVAR MENU QUANDO ADICIONA UMA PÁGINA
                if($dados['post_type'] == 'paginas'){
                    $sm = $this->addMenu($idCad);
                }
                if($dados['post_type'] == 'leiloes_adm'){
                    $post_title = __('Leilão').' '.Qlib::zerofill($idCad,4);
                    $sm = Post::where('id',$idCad)->update([
                            'post_title'=>$post_title,
                            'post_name'=>Qlib::createSlug($post_title),
                        ]);
                }
                $regev = Qlib::regEvent(['action'=>'store','tab'=>$this->tab,'config'=>[
                    'obs'=>'Cadastro guia Id '.$salvar->id,
                    'link'=>$this->routa,
                    ]
                ]);
                if(isset($dados['post_type']) && $dados['post_type']=='leiloes_adm'){
                    $meta_notific = 'notifica_email_moderador';
                    //verifica se ja foi enviado um email e não foi aten
                    // $verific_eviado
                    $liberar_norificacao = Qlib::update_postmeta($idCad,$meta_notific,'n');
                    if($liberar_norificacao){
                        //Enviar notificação para o moderador
                        $send_notific = (new LeilaoController)->notific_update_admin($idCad);
                    }
                }
            }
        }else{
            $mens = 'Erro ao salvar '.$this->label.'';
            $color = 'danger';
            $idCad = 0;
        }
        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'id'=>@$salvar->id,'this'=>$this]);

        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$mens,
            'color'=>$color,
            'idCad'=>$idCad,
            'exec'=>true,
            'dados'=>$dados
        ];
        if(@$sm)
            $ret['sm'] = $sm;
        if($ajax=='s'){
            $r_redirect = $this->routa;
            if($dados['post_type']=='leiloes_adm' && Qlib::is_frontend()){
                //requisição proveniente do site pora o post_type leiloes_adm
                $ret['redirect'] = url('/').'/'.Qlib::get_slug_post_by_id('18');
                $ret['return'] = url('/').'/'.Qlib::get_slug_post_by_id('18').'?idCad='.@$salvar->id;
            }else{
                $ret['return'] = route($route).'?idCad='.$idCad;
                $ret['redirect'] = route($r_redirect.'.edit',['id'=>$idCad]);
            }
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function show($id)
    {
        $lc = new LeilaoController;
        if($this->routa=='leiloes_adm'){
            $dados = $lc->get_leilao($id);
        }else{
            $dados = Post::findOrFail($id);
        }
        $this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = 'Visualização de orçamentos';
            if($this->routa=='leiloes_adm'){
                //list lances
            //    dd($dados);
            }
            $titulo = $title;
            //dd($dados);
            $dados['ac'] = 'alt';
            if(isset($dados['config'])){
                $dados['config'] = Qlib::lib_json_array($dados['config']);
            }
            $listFiles = false;
            //$dados['renda_familiar'] = number_format($dados['renda_familiar'],2,',','.');
            $campos = $this->campos($id);
            if(isset($dados['token'])){
                $listFiles = _upload::where('token_produto','=',$dados['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-familias',
                'route'=>$this->routa,
                'view'=>$this->view,
                'id'=>$id,
                'class_card1'=>'col-md-8',
                'class_card2'=>'col-md-4',
            ];
            if($this->routa == 'orcamentos'){
                $orc = new OrcamentoController;
                $assinatura = Qlib::get_postmeta($id,$orc->campo_assinatura,true);
                // $config['ttassinado'] = Qlib::get_postmeta($id,$orc->campo_ttassinado);
                $gerado = Qlib::get_postmeta($id,$orc->campos_gerado,true);
                $assinado = Qlib::get_postmeta($id,$orc->link_termo_assinado,true);
                // $enviado_zapsing = Qlib::get_postmeta($id,'enviado_zapsing',true);
                $enviado_zapsing = $orc->get_status_zapsing($id);
                $config['assinatura'] = Qlib::lib_json_array($assinatura);
                $config['gerado'] = Qlib::lib_json_array($gerado);
                $config['assinado'] = Qlib::lib_json_array($assinado);
                $config['zapsing'] = $enviado_zapsing;
                $config['status_sing'] = isset($enviado_zapsing['response']['status']) ? $enviado_zapsing['response']['status'] : 'pending';
                $config['assinantes'] = isset($enviado_zapsing['response']['signers']) ? $enviado_zapsing['response']['signers'] : [];

            }
            // if(!isset$dados['matricula'])
            //     $config['display_matricula'] = 'd-none';
            if(isset($dados['config']) && is_array($dados['config'])){
                foreach ($dados['config'] as $key => $value) {
                    if(is_array($value)){

                    }else{
                        $dados['config['.$key.']'] = $value;
                    }
                }
            }
            $subdomain = Qlib::get_subdominio();
            if(Gate::allows('is_admin2', [$this->routa]) && $subdomain !='cmd'){
                $config['eventos'] = (new EventController)->listEventsPost(['post_id'=>$id]);
            }else{
                $config['class_card1'] = 'col-md-12';
                $config['class_card2'] = 'd-none';
            }
            $ret = [
                'value'=>$dados,
                'dados'=>$dados,
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'routa'=>$this->routa,
                'exec'=>true,
            ];
            //REGISTRAR EVENTOS
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            return view($this->view.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
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
                    $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
                }
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-posts',
                'route'=>$this->routa,
                'view'=>$this->view,
                'sec'=>$this->sec,
                'id'=>$id,
            ];
            if($this->routa=='orcamentos'){
                $config['arquivos'] = 'docx,PDF,pdf,jpg,xlsx,png,jpeg';
                $config['tam_col1']='col-md-6';
                $config['tam_col2']='col-md-6';
            }
            $config['media'] = [
                'files'=>'jpeg,jpg,png,pdf,PDF',
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
                    $dados[0]['imagem_destacada'] = Qlib::qoption('storage_path'). '/'.$imgd[0]['guid'];
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

    public function update(StorePostRequest $request, $id)
    {
        $dados = $request->all();
        $lc = new LeilaoController;
        if(Qlib::is_backend()){
            $this->authorize('update', $this->routa);
        }else{
            if(isset($dados['post_type']) && $dados['post_type']=='leiloes_adm'){
                $this->authorize('is_logado');
                $dados['post_status'] = 'pending';
            }else{
                $this->authorize('update', $this->routa);
            }
        }
        if(isset($dados['post_type']) && $dados['post_type']=='leiloes_adm' && ($id=$dados['ID'])){
            //Impedir altualização em leilões que ja foram pagos
            $pago = $lc->is_paid($id);
            if($pago){
                //
                $mens = 'Não é permitido editar um leilão que já foi pago';
                $ret = [
                    'exec'=>true,
                    'id'=>$id,
                    'mens'=>$mens,
                    'mensa'=>$mens,
                    'color'=>'danger',
                ];
                return response()->json($ret);
            }
            //verificar se está finalizado
            $finalizado = $lc->is_end($id);
            if($finalizado){
                //
                $mens = 'Para editar leilão finalizado use a função de Reciclagem antes';
                $ret = [
                    'exec'=>true,
                    'id'=>$id,
                    'mens'=>$mens,
                    'mensa'=>$mens,
                    'color'=>'danger',
                ];
                return response()->json($ret);
            }

            //se estiver finalizado pede para usar a função reciclar para liberar o leilao e ser salvo

        }
        $data = [];
        $mens=false;
        $color=false;
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $d_meta = false;
        if(isset($dados['d_meta'])){
            $d_meta = $dados['d_meta'];
            if(isset($dados['ID'])){
                $d_meta['post_id'] = $dados['ID'];

            }
            unset($dados['d_meta']);
        }
        //carrega config
        $dados['config'] = isset($dados['config'])?$dados['config']:[];
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
        $data['post_status'] = isset($data['post_status'])?$data['post_status']:'pending';
        $userLogadon = Auth::id();
        $data['post_author'] = isset($data['post_author'])?$data['post_author']: $userLogadon;
        $data['token'] = !empty($data['token'])?$data['token']:uniqid();
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(!empty($data)){
            // $contrato = $lc->get_data_contrato(@$data['config']['contrato']);
            // if(isset($contrato['post_title'])){
            //     if(isset($contrato['config']['total_horas'])){
            //         $data['post_title'] = $contrato['config']['total_horas'].' '.__('horas de voo');
            //     }else{
            //         $data['post_title'] = $contrato['post_title'];
            //     }
            // }
            if($this->routa=='orcamentos'){
                $validatedData = $request->validate([
                    'guid' => ['required'],
                ],[
                    'guid.required'=>__('O nome do solicitante é obrigatório'),
                ]);
                $id_cliente = isset($dados['guid']) ? $dados['guid'] : false;
                $obs = isset($dados['obs']) ? $dados['obs'] : false;
                $token = $dados['token'];
                $cfg = $dados['config'];
                if(is_string($cfg)){
                    if(json_validate($cfg)){
                        $cfg = Qlib::lib_json_array($cfg);
                    }
                }
                $atualizar = (new OrcamentoController)->salvarOrcamento($id_cliente,$dados,$cfg);
                if($atualizar['exec']){
                    unset($atualizar['redirect']);
                    return $atualizar;
                }
            }else{
                $atualizar=Post::where('id',$id)->update($data);
            }
            if($atualizar){
                $mens = $this->label.' cadastrado com sucesso!';
                $color = 'success';
                $id = $id;

                //REGISTRAR EVENTOS
                (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
                if(isset($dados['post_type']) && $dados['post_type']=='leiloes_adm'){
                    //Enviar notificação para o moderador
                    $meta_notific = 'notifica_email_moderador';
                    $liberar_norificacao = Qlib::update_postmeta($id,$meta_notific,'n');
                    if($liberar_norificacao){
                        $send_notific = (new LeilaoController)->notific_update_admin($id);
                    }
                    //Atualizar o situação
                    if($dados['post_status']=='publish'){
                        if(is_string(@$dados['config'])){
                            $arr_c = Qlib::lib_json_array($dados['config']);
                            if(@$arr_c['status'] == 'publicado'){
                                $stus = (new LeilaoController)->atualiza_situacao($id,'ea'); //em andamento
                            }else{
                                $stus = (new LeilaoController)->atualiza_situacao($id,'a'); //aguardando publicação
                            }
                        }
                    }
                }
            }else{
                $mens = 'Erro ao salvar '.$this->label.'';
                $color = 'danger';
                $id = 0;
            }
            //}

            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>$mens,
                'color'=>$color,
                'idCad'=>$id,
                'return'=>$route,
            ];
            if($atualizar && $d_meta && $this->i_wp=='s'){
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
            if($data['post_type']=='leiloes_adm' && Qlib::is_frontend()){
                //requisição proveniente do site pora o post_type leiloes_adm
                $ret['return'] = url('/').'/'.Qlib::get_slug_post_by_id('18').'?idCad='.@$id;
            }else{
                $ret['return'] = route($route).'?idCad='.$id;
            }
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function destroy($id,Request $request)
    {
        $config = $request->all();

        $routa = $this->routa;
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        if (!$post = Post::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }
        if(Qlib::is_backend()){
            $this->authorize('delete', $this->routa);
        }else{
            if(isset($post['post_type']) && $post['post_type']=='leiloes_adm'){
                $this->authorize('is_logado');
            }else{
                $this->authorize('delete', $this->routa);
            }
        }

        $color = 'success';
        $mens = 'Registro deletado com sucesso!';
        // if($this->i_wp=='s'){
        //     $endPoint = 'post/'.$id;
        //     $delete = $this->wp_api->exec2([
        //         'endPoint'=>$endPoint,
        //         'method'=>'DELETE'
        //     ]);
        //     if($delete['exec']){
        //         $mens = 'Registro '.$id.' deletado com sucesso!';
        //         $color = 'success';
        //     }else{
        //         $color = 'danger';
        //         $mens = 'Erro ao excluir!';
        //     }
        // }else{
            // Post::where('id',$id)->delete();
            $atualizar=Post::where('id',$id)->update(['post_status' => 'trash']);
            if($atualizar){

                $mens = __('Registro ').$id.__(' deletado com sucesso!');
                $color = 'success';
                //REGISTRAR EVENTO
                $regev = Qlib::regEvent(['action'=>'delete','tab'=>$this->tab,'config'=>[
                    'obs'=>'Exclusão de cadastro Id '.$id,
                    'link'=>$this->routa,
                    ]
                ]);
            }else{
                $mens = __('Erro ao atualizar ').$this->label.'';
                $color = 'danger';
                $id = 0;
            }

        // }
        if($ajax=='s'){
            if($post['post_type']=='leiloes_adm' && Qlib::is_frontend()){
                $return = url('/').'/'.Qlib::get_slug_post_by_id('18');
            }else{
                $return = $this->routa;

            }
            $ret['return'] = url('/').'/'.Qlib::get_slug_post_by_id('18').'?idCad='.@$id;
            $ret = response()->json(['mens'=>__($mens),'color'=>$color,'return'=>$return]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>$mens,'color'=>$color]);
        }
        return $ret;
    }
}
