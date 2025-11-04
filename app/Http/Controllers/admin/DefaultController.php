<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use stdClass;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use App\Models\admin\BiddingCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DefaultController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $tab;
    public $view;
    public function __construct($config=[])
    {
        $user = Auth::user();
        $this->user = $user;
        $routeName = isset($config['route']) ? $config['route'] : false;
        $getName = request()->segment(2);
        // // if(function_exists('getName')){
        // // }
        // // $getName = request()->route()->getName();
        // dd($getName);
        $this->middleware('auth');

        $routeName = $routeName ? $routeName : explode('.',$getName)[0];
        // $routeName = $routeName ? $routeName : '';
        $this->routa = $routeName;
        $arr_cf = [
            'biddings_phases'=>['label'=>'Fases','tab'=>'bidding_phases'],
            'biddings_genres'=>['label'=>'Fases','tab'=>'bidding_genres'],
            'biddings_types'=>['label'=>'Fases','tab'=>'bidding_types'],
            'archives_category'=>['label'=>'Categorias de arquivos','tab'=>'tags'],
        ];
        $this->label = @$arr_cf[$this->routa]['label'];
        $this->tab = @$arr_cf[$this->routa]['tab'];
        // $this->dbtab = DB::table($this->tab);
        $this->view = 'admin.padrao';
    }
    public function queryDefault($get=false,$config=false)
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

        // $biddings_categories =  BiddingCategory::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        if($this->routa=='archives_category'){
            //filtrar as categorias de arquivos que esta armazenado na tabel tags
            $id_pai = Qlib::buscaValorDb0('tags','value',$this->routa,'id');
            $biddings_categories =  DB::table($this->tab)->where('pai',$id_pai)->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        }else{
            $biddings_categories =  DB::table($this->tab)->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        }
        $escolaridade_totais = new stdClass;
        $campos = $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $biddings_categories->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $biddings_categories->where($key,'LIKE','%'. $value. '%');
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
        $registros = clone $biddings_categories;
        $ativos = clone $biddings_categories;
        $inativos = clone $biddings_categories;
        $novos = clone $biddings_categories;
        if($config['limit']=='todos'){
            $biddings_categories = $biddings_categories->get();
        }else{
            $biddings_categories = $biddings_categories->paginate($config['limit']);
        }
        $escolaridade_totais->todos = $registros->count();
        $escolaridade_totais->esteMes = $novos->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->count();
        $escolaridade_totais->ativos = $ativos->where('ativo','=','s')->count();
        $escolaridade_totais->inativos = $inativos->where('ativo','=','n')->count();
        $ret['Defaulf'] = $biddings_categories;
        $ret['escolaridade_totais'] = $escolaridade_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$escolaridade_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$escolaridade_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$escolaridade_totais->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$escolaridade_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    /**
     * Metodo para sanitizar dados nele inicialmente aproveitamos para remover alguns campos que não temos no banco de dados
     */
    public function sanitizeDados($dados){
        $ret = array();
        if(is_array($dados)){
            $arr_val = ['valor'];
            // foreach ($arr_val as $k => $v) {
            //     if(isset($dados[$v])){
            //         $dados[$v] = str_replace( 'R$','', $dados[$v]);
            //         $dados[$v] = (double)trim($dados[$v]);
            //     }
            // }
            // unset($dados['_token'],$dados['ajax'],$dados['_method']);
            foreach ($dados as $key => $value) {
                if($key!='_method'&&$key!='_token'&&$key!='ac'&&$key!='ajax'){
                    if($key == 'valor' || $key == 'valor_pago') {
                        if(empty($value)){
                            $value = (double)0;
                        }else{
                            $value = str_replace('R$','',$value);
                        }
                        $data[$key] = Qlib::precoBanco(trim($value));
                    }else{
                        $data[$key] = $value;
                    }
                }
            }
            $ret = $data;
        }
        return $ret;
    }
    public function campos($id=null){
        if($this->routa=='biddings_phases' || $this->routa=='biddings_types' || $this->routa=='biddings_genres'){
            return [
                'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'autor'=>['label'=>'autor','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'name'=>['label'=>'Nome','active'=>true,'js'=>true,'placeholder'=>'Ex.: Suspenso','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','validate'=>['required','string',Rule::unique($this->tab)->where(fn ($query) => $query->where('excluido', 'n'))->ignore($id)]],
                'ativo'=>['label'=>'Ativado','tab'=>$this->tab ,'active'=>true,'js'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                // 'obs'=>['label'=>'Observação','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            ];
        }elseif($this->routa=='archives_category'){
            $id_pai = Qlib::buscaValorDb0('tags','value',$this->routa,'id');
            return [
                'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'pai'=>['label'=>'pai','active'=>true,'js'=>true,'value'=>$id_pai,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'value'=>['label'=>'Valor','active'=>false,'value'=>$this->routa,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'token'=>['label'=>'token','active'=>false,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'autor'=>['label'=>'autor','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'nome'=>['label'=>'Nome','active'=>true,'js'=>true,'placeholder'=>'Ex.: Contratações','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','validate'=>['required','string',Rule::unique($this->tab)->ignore($id)]],
                'obs'=>['label'=>'Descrição','active'=>false,'js'=>true,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                'ativo'=>['label'=>'Ativado','tab'=>$this->tab,'active'=>true,'js'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            ];
        }else{
            return [
                'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'autor'=>['label'=>'autor','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'name'=>['label'=>'Nome','active'=>true,'js'=>true,'placeholder'=>'Ex.: Suspenso','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','validate'=>['required','string',Rule::unique($this->tab)->ignore($id)]],
                'ativo'=>['label'=>'Ativado','tab'=>$this->tab,'active'=>true,'js'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                // 'obs'=>['label'=>'Observação','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            ];

        }
    }
    public function index(Request $request)
    {
        $this->authorize('ler', $this->routa);
        $title = __('Cadastro de ').$this->label;
        $titulo = $title;
        $queryDefault = $this->queryDefault($_GET);
        $queryDefault['config']['exibe'] = 'html';
        $routa = $this->routa;
        // dump($this->view);
        // dd($queryDefault);
        return view($this->view.'.index',[
            'dados'=>$queryDefault['Defaulf'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryDefault['campos'],
            'escolaridade_totais'=>$queryDefault['escolaridade_totais'],
            'titulo_tabela'=>$queryDefault['tituloTabela'],
            'arr_titulo'=>$queryDefault['arr_titulo'],
            'config'=>$queryDefault['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ]);
    }
    public function create(User $user)
    {
        $this->authorize('create', $this->routa);
        $title = __('Cadastrar categorias');
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-biddings_categories',
            'route'=>$this->routa,
        ];
        $value = [
            'token'=>uniqid(),
            'autor'=>Auth::id(),
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
        // dd($this->routa);
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
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        //remover variaveis que não tenham um campo no banco de dados
        $dados = $this->sanitizeDados($dados);
        $reg_id = DB::table($this->tab)->insertGetId($dados);
        if($reg_id){
            $color = 'success';
            $idCad = $reg_id;
            $exec = true;
            $route = $this->routa.'.index';
        }else{
            $color = 'danger';
            $idCad = 0;
            $exec = false;

        }
        $ret = [
            'mens'=>$this->label.' cadastrada com sucesso!',
            'color'=>$color,
            'idCad'=>$idCad,
            'exec'=>$exec,
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

    public function edit($id)
    {
        $routa = $this->routa;
        $this->authorize('ler', $routa);
        $dados = DB::table($this->tab)->where('id',$id)->get()->toArray();
        if(isset($dados[0]) && !empty($dados[0])){
            $dados[0] = (array)$dados[0];
            // dd($dados[0]);
            $title = __('Editar cadastro d)e categorias');
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            $campos = $this->campos();
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-biddings_categories',
                'route'=>$this->routa,
                'id'=>$id,
            ];

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

        // $validatedData = $request->validate([
        //     'nome' => ['required'],
        // ]);
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
        $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        $data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(!empty($data)){
            $atualizar=DB::table($this->tab)->where('id',$id)->update($data);
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
            ];
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
        $qli = Qlib::qoption('lixeira');
        $lixeira = $qli ? $qli : 's'; // verifica se a lixeira esta ativa s para sim e n para não
        $routa = $this->routa;
        if (!$reg = DB::table($this->tab)->find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }
        if($lixeira =='s'){
            $reg_excluido = ['data'=>date('d-m-Y H:i:s'),'autor'=>$id];
            DB::table($this->tab)->where('id',$id)->update(['excluido'=>'s','reg_excluido'=>Qlib::lib_array_json($reg_excluido)]);
        }else{
            DB::table($this->tab)->where('id',$id)->delete();
        }
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        return $ret;
    }
}
