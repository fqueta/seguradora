<?php

namespace App\Http\Controllers\admin;
use stdClass;
use App\Models\admin\Biddings;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\admin\attachment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BiddingsController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $tab;
    public function __construct()
    {
        $this->middleware('auth');
        $this->user     = Auth::user();
        $this->routa    = 'biddings';
        $this->label    = 'processos';
        $this->view     = 'admin.biddings';
        $this->tab      = 'biddings';
    }
    public function queryBiddings($get=false,$config=false)
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
        $tpc = 'a';
        if(isset($get['dataI'],$get['dataF']) && !empty($get['dataI']) && !empty($get['dataF']) && $get['dataI']!='0000-00-00' && $get['dataF']!='0000-00-00'){
            $tpc = 'p';unset($get['ano']);
        }else{
            $tpc = isset($_GET['tpc']) ? $_GET['tpc'] : 'a';
        }

        if(isset($get['ano']) && !empty($get['ano'])){
            $biddings = Biddings::where('excluido','=','n')->where('year',$get['ano'])->orderBy('id',$config['order']);
        }elseif($tpc=='p' && isset($get['dataI'],$get['dataF']) && !empty($get['dataI']) && !empty($get['dataF'])){
            $biddings = Biddings::where('excluido','=','n')->orderBy('id',$config['order']);
        }else{
            $biddings = Biddings::where('excluido','=','n')->orderBy('id',$config['order']);
        }

        //$biddings =  DB::table('biddings')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        $biddings_totais = new stdClass;
        $campos = $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        unset($get['ano']);
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                $campoJoin = '';
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $biddings->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }elseif(is_array($value)){
                            foreach ($value as $kb => $vb) {
                                if(!empty($vb)){
                                    if($key=='tags'){
                                        $biddings->where($campoJoin.$key,'LIKE', '%"'.$vb.'"%' );
                                    }else{
                                        $biddings->where($campoJoin.$key,'LIKE', '%"'.$kb.'":"'.$vb.'"%' );
                                    }
                                }
                            }
                        }else{
                            $biddings->where($key,'LIKE','%'. $value. '%');
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
        }
        $registros = clone $biddings;
        $ativos = clone $biddings;
        $inativos = clone $biddings;
        $novos = clone $biddings;
        if($config['limit']=='todos'){
            $biddings = $biddings->get();
        }else{
            $biddings = $biddings->paginate($config['limit']);
        }
        $biddings_totais->todos = $registros->count();
        $biddings_totais->esteMes = $novos->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->count();
        $biddings_totais->ativos = $ativos->where('active','=','s')->count();
        $biddings_totais->inativos = $inativos->where('active','=','n')->count();
        $ret['Biddings'] = $biddings;
        $ret['biddings_totais'] = $biddings_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$biddings_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$biddings_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$biddings_totais->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$biddings_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function campos($id=null){
        $biddings_categories = new BiddingCategoriesController();
        $biddings_phases = new DefaultController(['route'=>'biddings_phases']);
        $biddings_genres = new DefaultController(['route'=>'biddings_genres']);
        $biddings_types = new DefaultController(['route'=>'biddings_types']);
        $tambcampos = 12; //6
        $tambcampos1 = 12;
        $ret = [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            // 'config[NumLicitacao]'=>['label'=>'Número da licitação','cp_busca'=>'config][NumLicitacao','active'=>true,'type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>4,'placeholder'=>''],
            'indentifier'=>['label'=>'Número do processo','active'=>true,'placeholder'=>'Processo','type'=>'tel','exibe_busca'=>'d-block','event'=>'required','tam'=>6,'validate'=>['required']],
            'year'=>['label'=>'Ano','active'=>true,'placeholder'=>'Ex: '.date('Y'),'type'=>'number','exibe_busca'=>'d-block','event'=>'required','tam'=>6,'validate'=>['required']],
            'title'=>['label'=>'Título','active'=>true,'placeholder'=>'Título','type'=>'text','exibe_busca'=>'d-block','event'=>'required','tam'=>$tambcampos,'validate'=>['required','string', Rule::unique($this->tab)->ignore($id)]],
            'subtitle'=>['label'=>'Subtítulo (opcional)','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>$tambcampos],
            'bidding_category_id'=>[
                'label'=>'Categoria',
                'active'=>true,
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$biddings_categories->campos(),
                    'route_index'=>route('biddings_categories.index'),
                    'id_form'=>'frm-biddings_categories',
                    'action'=>route('biddings_categories.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'name',
                    'label'=>'Categoria',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,name FROM bidding_categories WHERE ativo='s'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required',
                //'event'=>'onchange=carregaMatricula($(this).val())',
                'tam'=>$tambcampos,
                'value'=>@$_GET['bidding_category_id'],
            ],
            'phase_id'=>[
                'label'=>'Fase',
                'active'=>true,
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$biddings_phases->campos(),
                    'route_index'=>route('biddings_phases.index'),
                    'id_form'=>'frm-biddings_phases',
                    'action'=>route('biddings_phases.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'name',
                    'label'=>'Fase',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,name FROM bidding_phases WHERE ativo='s'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required',
                //'event'=>'onchange=carregaMatricula($(this).val())',
                'tam'=>$tambcampos,
                'value'=>@$_GET['phase_id'],
            ],
            'genre_id'=>[
                'label'=>'Modalidade',
                'active'=>true,
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$biddings_genres->campos(),
                    'route_index'=>route('biddings_genres.index'),
                    'id_form'=>'frm-biddings_genres',
                    'action'=>route('biddings_genres.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'name',
                    'label'=>'Modalidade',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,name FROM bidding_genres WHERE ativo='s'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required',
                //'event'=>'onchange=carregaMatricula($(this).val())',
                'tam'=>$tambcampos,
                'value'=>@$_GET['genre_id'],
            ],
            'type_id'=>[
                'label'=>'Tipo',
                'active'=>false,
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$biddings_types->campos(),
                    'route_index'=>route('biddings_types.index'),
                    'id_form'=>'frm-biddings_types',
                    'action'=>route('biddings_types.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'name',
                    'label'=>'Tipo',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,name FROM bidding_types WHERE ativo='s'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required',
                //'event'=>'onchange=carregaMatricula($(this).val())',
                'tam'=>$tambcampos,
                'value'=>@$_GET['genre_id'],
            ],
            'opening'=>['label'=>'Data de abertura','active'=>true,'placeholder'=>'','type'=>'datetime-local','exibe_busca'=>'d-block','event'=>'','tam'=>$tambcampos,'validate'=>['required']],
            'object'=>['label'=>'Objeto','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'required','tam'=>$tambcampos1],
        ];
        //Pode ser usado para personalizar campos exstras de cada prefeitura....
        $campos_extras = [
            'config[dtPublicacao]'=>['label'=>'	Data da publicação da licitação','cp_busca'=>'config][dtPublicacao','active'=>true,'type'=>'date','exibe_busca'=>'d-block','event'=>'','tam'=>'6','placeholder'=>''],
            'config[NuValorEstimado]'=>['label'=>'Valor estimado da licitação','cp_busca'=>'config][NuValorEstimado','active'=>true,'type'=>'moeda','exibe_busca'=>'d-block','event'=>'','tam'=>'6','placeholder'=>''],
        ];
        //Fim campos extras para prefeituras
        $ret2 = array_merge($ret,$campos_extras);
        $arra_active = [
            'active'=>['label'=>'publicado','tab'=>'biddings','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'4','arr_opc'=>['s'=>'Sim','n'=>'Não']],
        ];
        $ret3 = array_merge($ret2,$arra_active);
        return $ret3;
    }
    public function index()
    {
        $this->authorize('ler', $this->routa);
        $title = 'Cadastro de Processos';
        $titulo = $title;
        $queryBiddings = $this->queryBiddings($_GET);
        $queryBiddings['config']['exibe'] = 'html';
        $routa = $this->routa;
        $anos = Qlib::sql_distinct();
        $ret = [
            'dados'=>$queryBiddings['Biddings'],
            'title'=>$title,
            'titulo'=>$titulo,
            'anos'=>$anos,
            'campos_tabela'=>$queryBiddings['campos'],
            'biddings_totais'=>$queryBiddings['biddings_totais'],
            'titulo_tabela'=>$queryBiddings['tituloTabela'],
            'arr_titulo'=>$queryBiddings['arr_titulo'],
            'config'=>$queryBiddings['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ];
        // dd($ret);
        return view($this->view.'.index',$ret);
    }
    public function create(User $user)
    {
        $this->authorize('create', $this->routa);
        $title = 'Cadastrar '.$this->label;
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-biddings',
            'tam_col1'=>'col-md-10',
            'tam_col2'=>'col-md-2',
            'route'=>$this->routa,
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
    public function store(Request $request)
    {
        $campos = $this->campos();
        $this->authorize('create', $this->routa);
        $arr_validate = array();
        if(is_array($campos)){
            foreach ($campos as $k => $value) {
                if(isset($value['validate'])){
                    $arr_validate[$k] = $value['validate'];
                }
            }
        }
        if(count($arr_validate)>0){
            $validatedData = $request->validate($arr_validate);
        }
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['active'] = isset($dados['active'])?$dados['active']:0;
        $dados['excluido'] = isset($dados['excluido'])?$dados['excluido']:'n';
        // dd($dados);
        $salvar = Biddings::create($dados);
        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$this->label.' cadastrada com sucesso!',
            'color'=>'success',
            'idCad'=>$salvar->id,
            'exec'=>true,
            'dados'=>$dados
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($biddings,User $user)
    {
        $id = $biddings;
        $dados = Biddings::where('id',$id)->get();
        $routa = 'biddings';
        $this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = 'Editar cadastro de '.$this->label;
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = [];
            $campos = $this->campos();
            if(isset($dados[0]['id'])){
                $listFiles = $this->list_files($dados[0]['id']);
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-biddings',
                'route'=>$this->routa,
                'tam_col1'=>'col-md-6',
                'tam_col2'=>'col-md-6',
                'id'=>$id,
                'bidding_id'=>$id,
                'pasta'=>'/biddings/'.date('Y').'/'.date('m'),
                'local'=>'attachments', //a tabela de armazenandmo dos arquivos
                'token_produto'=>$dados[0]['token'],
                'arquivos'=>'docx,doc,PDF,pdf,jpg,xlsx,xls,png,jpeg',
                'compleUrl'=>"+$('#files').serialize()"//complemento de url ao postar via ajax
            ];

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
            return redirect()->route($routa.'.index',$ret);
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', $this->routa);
        $campos = $this->campos($id);
        if(is_array($campos)){
            foreach ($campos as $k => $value) {
                if(isset($value['validate'])){
                    $arr_validate[$k] = $value['validate'];
                }
            }
        }
        if(count($arr_validate)>0){
            $validatedData = $request->validate($arr_validate);
        }
        $data = [];
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        foreach ($dados as $key => $value) {
            if($key!='_method'&&$key!='_token'&&$key!='ac'&&$key!='ajax'){
                if($key=='data_batismo' || $key=='data_nasci'){
                    if($value=='0000-00-00' || $value=='00/00/0000'){
                    }else{
                        $data[$key] = Qlib::dtBanco($value);
                    }
                }elseif($key == 'renda_familiar') {
                    $value = str_replace('R$','',$value);
                    $data[$key] = Qlib::precoBanco($value);
                }else{
                    $data[$key] = $value;
                }
            }
        }
        $userLogadon = Auth::id();

        $data['active'] = isset($data['active'])?$data['active']:'n';
        $data['author_id'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        $d_order = isset($data['order'])?$data['order']:false;
        unset($data['file'],$data['order']);
        if(!empty($data)){
            $atualizar=Biddings::where('id',$id)->update($data);
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
            ];
            if(is_array($d_order)){
                //atualizar ordem dos arquivos
                $ret['order_update'] = (new AttachmentsController)->order_update($d_order);
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
        $routa = $this->routa;
        if (!$post = Biddings::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }

        Biddings::where('id',$id)->delete();
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        return $ret;
    }
    /**
     * Metodo para listar todos os arquivos das licitações
     */
    public function list_files($bidding_id){
        $ret = [];
        if($bidding_id){
            $files = attachment::where('bidding_id','=',$bidding_id)->orderBy('order','asc')->get();
            if($files->count() > 0){
                $files =  $files->toArray();
                foreach ($files as $kf => $vf) {
                    $ret[$kf] = $vf;
                    $arr_c = Qlib::lib_json_array($vf['file_config']);
                    $ret[$kf]['file_path'] = $arr_c['file_path'];
                    $ret[$kf]['extension'] = @$arr_c['extension'];
                }
            }
        }
        return $ret;
    }

}
