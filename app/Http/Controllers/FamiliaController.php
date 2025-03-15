<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use App\Models\User;
use App\Models\_upload;
use App\Rules\FullName;
use App\Rules\RightCpf;

use stdClass;
use App\Http\Requests\StoreFamilyRequest;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Exports\FamiliasExport;
use App\Exports\FamiliasExportView;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Exports\UsersExport;
use App\Http\Controllers\admin\EventController;
use App\Models\Bairro;
use App\Models\Beneficiario;
use App\Models\Etapa;
use App\Models\Tag;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FamiliaController extends Controller
{
    protected $user;
    public $routa;
    public $view;
    public $tab;
    public function __construct(User $user)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->routa = 'familias';
        $this->label = 'Familia';
        $this->view = $this->routa;
        $this->tab = $this->routa;
        //$this->listarEvent($user);
        //$user = auth()->user();
        //dd($user);
    }
    public function listarEvent($user){
        $request = request();
        $routeName = $request->route()->getName();
        $pRoute = explode('.',$routeName);
        $action = @$pRoute[1];
        $acaoObs = false;
        $link = false;
        $user_id = Auth::id();
        dd($user_id);
        $id = $request->route()->parameter('id');
        if($action=='index'){
            $acaoObs = __('Listou cadastros de ').$this->tab;
        }elseif($action=='create'){
            $acaoObs = __('Abriu tela de cadastro de').$this->tab;
        }elseif($action=='store'){
            $acaoObs = __('Criou cadastros de ').$this->tab;
        }elseif($action=='show'){
            $acaoObs = __('Visualizou cadastros de ').$this->tab;
            $link = route($routeName,['id'=>$id]);
        }elseif($action=='edit'){
            $link = route($routeName,['id'=>$id]);
            $acaoObs = __('Abriu tela de Edição de ').$this->tab;
        }elseif($action=='update'){
            $acaoObs = __('Atualizou cadastro de ').$this->tab;
            $link = route($pRoute[0].'.show',['id'=>$id]);
        }
        //dd($request->route()->parameter('id'));
        //REGISTRAR EVENTO DE LISTA
        $cfe = [
            'action'=>$action,
            'tab'=>$this->tab,
            'user_id'=>$user['id'],
            'config'=>[
                'obs'=>$acaoObs,
                'link'=>$link,
            ],
        ];
        $regev = (new EventController)->regEvent($cfe);
        return $regev;
    }
    public function queryFamilias($get=false,$config=false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        //dd($get);
        //$idUltimaEtapa = Etapa::where('ativo','=','s')->where('excluido','=','n')->where('deletado','=','n')->max('id');
        $idUltimaEtapa=false;
        $tags = Tag::where('ativo','=','s')->where('pai','=','1')->where('excluido','=','n')->where('deletado','=','n')->OrderBy('ordem','asc')->get();
        $id_pendencia = 3;
        $id_imComRegistro = 4;
        $id_recusas = 5;
        $id_nLocalizado = 6;
        $completos = 0;
        $pendentes = 0;
        $etapas = Etapa::where('ativo','=','s')->where('excluido','=','n')->OrderBy('ordem','asc')->get();
        //$todasFamilias = Familia::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];

        DB::enableQueryLog();

        if(isset($get['ano']) && !empty($get['ano'])){
            $familia = Familia::where('excluido','=','n')->where('deletado','=','n')->whereYear('data_exec',$get['ano'])->orderBy('id',$config['order']);
            $countFam = Familia::where('excluido','=','n')->where('deletado','=','n')->whereYear('data_exec',$get['ano'])->orderBy('id',$config['order']);
            //Totalizadores.
            $totProcesso['entregue'] = Familia::where('excluido','=','n')->where('deletado','=','n')->whereYear('data_exec',$get['ano'])->orderBy('id',$config['order'])->where('config','LIKE','%"categoria_processo":"processo_entregue"%')->count();
            $totProcesso['certidao'] = Familia::where('excluido','=','n')->where('deletado','=','n')->whereYear('data_exec',$get['ano'])->orderBy('id',$config['order'])->where('config','LIKE','%"categoria_processo":"certidao"%')->count();
        }else{
            $familia =  Familia::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
            $countFam =  Familia::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
            $totProcesso['entregue'] = Familia::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order'])->where('config','LIKE','%"categoria_processo":"processo_entregue"%')->count();
            $totProcesso['certidao'] = Familia::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order'])->where('config','LIKE','%"categoria_processo":"certidao"%')->count();

        }
        //$familia =  DB::table('familias')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $familia_totais = new stdClass;
        //$campos = isset($_SESSION['campos_familias_exibe']) ? $_SESSION['campos_familias_exibe'] : $this->campos();
        $rel = new RelatoriosController($this->user);
        $campos = $rel->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $familia->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }elseif(is_array($value)){
                            foreach ($value as $kb => $vb) {
                                if(!empty($vb)){
                                    if($key=='tags'){
                                        $familia->where($key,'LIKE', '%"'.$vb.'"%' );
                                    }else{
                                        $familia->where($key,'LIKE', '%"'.$kb.'":"'.$vb.'"%' );
                                    }
                                }
                            }
                        }else{
                            if($key=='quadra'){
                                $familia->where($key,'=', $value);
                                if(isset($campos[$key]['type']) && $campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                                $arr_titulo[$campos[$key]['label']] = Qlib::valorTabDb('quadras','id',$value,'nome');
                            }elseif($key=='id_beneficiario'){
                                $familia->where($key,'=', $value);
                                if(isset($campos[$key]['type']) && $campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                                $arr_titulo[$campos[$key]['label']] = Qlib::valorTabDb('beneficiarios','id',$value,'nome');
                            }else{
                                //dd( $campos);exit;
                                $arr_titulo[$campos[$key]['label']] = $value;
                                $familia->where($key,'LIKE','%'. $value. '%');
                                if(isset($campos[$key]['type']) && $campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                            }
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $familia;
                if($config['limit']=='todos'){
                    $familia = $familia->get();
                }else{
                    $familia = $familia->paginate($config['limit']);
                }
                //$query = DB::getQueryLog();
                //$query = end($query);
                //dd($query);

                if($idUltimaEtapa)
                $completos = $familia->where('etapa','=',$idUltimaEtapa)->count();
                $pendentes = $familia->where('tags','LIKE','%"'.$id_pendencia.'"')->count();
                $familia_totais->todos = $fm->count();
                $familia_totais->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->count();
                $familia_totais->idoso = $fm->where('idoso','=','s')->count();
                $familia_totais->criancas = $fm->where('crianca_adolescente','=','s')->count();
        }else{
            $fm = $familia;
            if($idUltimaEtapa){
                $completos = $countFam->where('etapa','=',$idUltimaEtapa)->count();
            }

            if($config['limit']=='todos'){
                $familia = $familia->get();
            }else{
                $familia = $familia->paginate($config['limit']);
            }
            $familia_totais->todos = $fm->count();
            $familia_totais->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->count();
            $familia_totais->idoso = $fm->where('idoso','=','s')->count();
            $familia_totais->criancas = $fm->where('crianca_adolescente','=','s')->count();
        }
        $progresso = [];
        if($etapas){
            foreach ($etapas as $key => $value) {
                $progresso[$key]['label'] = $value['nome'];
                $progresso[$key]['total'] = Familia::where('etapa','=',$value['id'])->where('excluido','=','n')->where('deletado','=','n')->count();
                $progresso[$key]['geral'] = $familia_totais->todos;
                if($progresso[$key]['total']>0 && $progresso[$key]['geral'] >0){
                    $porceto = round($progresso[$key]['total']*100/$progresso[$key]['geral'],2);
                }else{
                    $porceto = 0;
                }
                $progresso[$key]['porcento'] = $porceto;
                $progresso[$key]['color'] = $this->colorPorcento($porceto);
            }
        }
        $familia_totais->completos = $completos;

        $colTabela = $rel->colTabela($familia);
        //$ret['familia'] = $familia;
        $ret['familia'] = $colTabela;
        $ret['familia_totais'] = $familia_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        //$ret['progresso'] = $progresso;
        $ret['link_completos'] = route('familias.index').'?filter[etapa]='.$idUltimaEtapa;
        $ret['link_idosos'] = route('familias.index').'?filter[idoso]=s';
        $cardTags = [];
        $ret['cards_home'] = [];
        $compleLinkVisu = false;
        $compleLinkVisu1 = false;
        if(isset($get['ano'])&&$get['ano']){
            $compleLinkVisu = '&ano='.$get['ano'];
        }
        if($compleLinkVisu){
            $compleLinkVisu1 = '?'.$compleLinkVisu;
        }
        $cards_homeTodos = [
                'label'=>'TODOS OS CADASTROS',
                'valor'=>$familia_totais->todos,
                'obs'=>'São cadastros completos sistematizados por ano de execução do projeto ou programa. Incluem cadastros completos, cadastros com pendências e imóveis com registro anterior junto ao Cartório de Registro de Imóveis.',
                'href'=>route('familias.index').$compleLinkVisu1,
                'icon'=>'fa fa-map-marked-alt',
                'lg'=>'3',
                'xs'=>'6',
                'color'=>'warning',

        ];
        if(!empty($tags)){
            foreach ($tags as $kt => $vt) {
                if(isset($get['ano'])&&!empty($get['ano'])){
                    $countFamTag =  Familia::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order'])->where('tags','LIKE','%"'.$vt['id'].'"%')->whereYear('data_exec',$get['ano'])->count();
                }else{

                    $countFamTag =  Familia::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order'])->where('tags','LIKE','%"'.$vt['id'].'"%')->count();
                }

                $cardTags[$vt['id']] =
                [
                    'label'=>$vt['nome'],
                    'obs'=>$vt['obs'],
                    'valor'=>$countFamTag,
                    'href'=>route('familias.index').'?filter[tags][]='.$vt['id'].$compleLinkVisu,
                    'icon'=>$vt['config']['icon'],
                    'lg'=>'3',
                    'xs'=>'6',
                    'color'=>$vt['config']['color'],
                ];
                array_push($ret['cards_home'],$cardTags[$vt['id']]);
            }
        }
        array_push($ret['cards_home'],$cards_homeTodos);

        $anos = Qlib::sql_distinct();
        $ret['anos'] = $anos;
        $ret['totProcesso'] = $totProcesso;
        $ret['config']['acao_massa'] = [
            //['link'=>'#edit_etapa','event'=>'edit_etapa','icon'=>'fa fa-pencil','label'=>'Editar etapa'],
            ['link'=>'#edit_situacao','event'=>'edit_situacao','icon'=>'fa fa-pencil','label'=>'Editar Situação'],
        ];
        //dd($ret);
        return $ret;
    }
    public function colTabela($familia = null,$campos=false)
    {
        $ret = false;
        if($familia){
            if(!$campos){
                $campos = $this->campos();
            }
            foreach ($familia as $k1 => $v1) {
                foreach ($campos as $k2 => $v2) {
                    if($v2['type']=='text'){
                        if($k2=='lote' && isset($v1['loteamento'])){
                            if(is_array($v1['loteamento'])){
                                foreach ($v1['loteamento'] as $kl => $lote) {
                                    $loteN = Qlib::buscaValorDb([
                                        'tab'=>'lotes',
                                        'campos_bus'=>'id',
                                        'valor'=>$lote,
                                        'select'=>'nome',
                                    ]);

                                    $loteN = $loteN.$v1['complemento_lote'].',';
                                    $familia[$k1][$k2] .= $loteN;
                                }
                                $familia[$k1][$k2] = substr($familia[$k1][$k2], 0, -1);
                            }
                        }
                        if(($k2=='nome' || $k2=='cpf'  || $k2=='cpf_conjuge' || $k2=='escolaridade') && (isset($v1[$v2['valor']]))){
                            $familia[$k1][$k2] = Qlib::buscaValorDb([
                                'tab'=>$v2['tab'],
                                'campos_bus'=>'id',
                                'valor'=>$v1[$v2['valor']],
                                'select'=>$v2['select'],
                            ]);
                        }
                    }elseif($v2['type']=='array' && isset($v1[$v2['valor']])){
                        $familia[$k1][$k2] = Qlib::buscaValorDb([
                            'tab'=>$v2['tab'],
                            'campos_bus'=>'id',
                            'valor'=>$v1[$v2['valor']],
                            'select'=>$v2['select'],
                        ]);
                    }elseif($v2['type']=='chave_checkbox' && isset($v2['arr_opc'])){
                        $familia[$k1][$k2] = $v2['arr_opc'][$v1[$k2]];
                    }elseif($v2['type']=='json'){
                        if((isset($v2['cp_b']))){
                            $ab = explode('][',$v2['cp_b']);
                            if($ab[1]){
                                $valor = Qlib::buscaValorDb([
                                    'tab'=>$v2['tab'],
                                    'campos_bus'=>'id',
                                    'valor'=>$v1[$v2['valor']],
                                    'select'=>$v2['select'],
                                ]);

                                if(Qlib::isJson($valor)){
                                    $valor = Qlib::lib_json_array($valor);
                                }
                                $value = @$valor[$ab[1]];
                                if($ab[1]=='telefone' && !empty(@$valor['telefone2'])){
                                    $value .= ', '.$valor['telefone2'];
                                }
                                if($k2=='escolaridades' || $k2=='estadocivils'){
                                    $value = Qlib::buscaValorDb([
                                        'tab'=>$k2,
                                        'campos_bus'=>'id',
                                        'valor'=>$value,
                                        'select'=>'nome',
                                    ]);
                                }
                                $familia[$k1][$k2] = $value;
                            }
                        }
                    }
                }
            }
            $ret = $familia;
        }
        return $ret;
    }
    public function colorPorcento($val=0){
        $ret = 'bg-danger';
        if($val<=25){
            $ret = 'bg-danger';
        }elseif($val > 25 && $val <= 50){
            $ret = 'bg-warning';
        }elseif($val > 50 && $val <= 85){
            $ret = 'bg-primary';
        }elseif($val > 85){
            $ret = 'bg-success';
        }
        return $ret;
    }
    public function index(User $user)
    {
        //$this->authorize('is_admin', $user);
        $this->authorize('ler', $this->routa);
        $title = 'Famílias Cadastradas';
        $titulo = $title;
        $queryFamilias = $this->queryFamilias($_GET);
        $queryFamilias['config']['exibe'] = 'html';
        $routa = $this->routa;
        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);


        $campos = $this->campos();
        $ret = [
            'dados'=>$queryFamilias['familia'],
            'familias'=>$queryFamilias['familia'],
            'anos'=>$queryFamilias['anos'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryFamilias['campos'],
            'campos'=>$campos,
            'familia_totais'=>$queryFamilias['familia_totais'],
            'titulo_tabela'=>$queryFamilias['tituloTabela'],
            'arr_titulo'=>$queryFamilias['arr_titulo'],
            'config'=>$queryFamilias['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ];
        //dd($ret);
        return view($routa.'.index',$ret);
    }
    public function exportAll(User $user)
    {
        $this->authorize('is_admin', $user);
        $regev = Qlib::regEvent(['action'=>'exportAll','tab'=>$this->tab,'config'=>[
            'obs'=>'Exportou',
            'link'=>$this->routa,
            ]
        ]);

        return Excel::download(new FamiliasExport, 'Familias_'.date('d_m_Y').'.xlsx');
    }
    public function exportFilter(User $user)
    {
        $this->authorize('is_admin', $user);
        $dados = new FamiliasExportView;
        //return $dados->view();
        return Excel::download(new FamiliasExportView, 'Familias_'.date('d_m_Y').'.xlsx');
    }
    public function arrQualificacao($var = null)
    {
        $arr_qualicacao = [
           1=>'REURB (S)',
           2=>'REURB (E)',
        ];
        if($var){
            return $arr_qualicacao[$var];
        }else{
            return $arr_qualicacao;
        }
    }
    /**
     * Responsável por montar as estruturas de exibição dos registros no index, show, edit
     * @return array
     */
    public function campos($dados=false,$local='index'){
        $user = Auth::user();
        $etapa = new EtapaController($user);
        $bairro = new BairroController($user);
        $beneficiario = new BeneficiariosController($user);
        $escolaridade = new EscolaridadeController($user);
        $quadra = new QuadrasController($user);
        $lote = new LotesController($user);
        $data = $dados?$dados:false;
        if(isset($data['bairro'])){
            $arr_opc_quadras = Qlib::sql_array("SELECT id,nome FROM quadras WHERE ativo='s' AND bairro='".$data['bairro']."' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'nome','id');
        }else{
            $arr_opc_quadras = Qlib::sql_array("SELECT id,nome FROM quadras WHERE ativo='s' ORDER BY nome ASC",'nome','id');
        }
        $ret = [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>''],
            /*'etapa'=>[
                'label'=>'Etapa',
                'active'=>true,
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$etapa->campos(),
                    'route_index'=>route('etapas.index'),
                    'id_form'=>'frm-etapas',
                    'action'=>route('etapas.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Etapa',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM etapas WHERE ativo='s' ORDER BY ordem ASC",'nome','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'value'=>@$_GET['etapa'],
            ],*/
            'config[qualificacao]'=>[
                'label'=>'Qualificação*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>$this->arrQualificacao(),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'4',
                'class'=>'',
                'exibe_busca'=>true,
                'option_select'=>false,
                'cp_busca'=>'qualificacao][',
            ],
            'data_exec'=>['label'=>'Data de Execução','active'=>true,'type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>'4'],
            'tipo_residencia'=>[
                'label'=>'Tipo de residência*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='2'",'nome','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'4',
                'class'=>'',
                'exibe_busca'=>true,
                'option_select'=>false,
            ],
            'tags[]'=>[
                'label'=>'Situação',
                'active'=>true,
                'type'=>'select_multiple',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1' ORDER BY nome ASC",'nome','id'),'exibe_busca'=>'d-block',
                'event'=>'onchange=exibeCategoria(this)',
                'class'=>'',
                'option_select'=>false,
                'tam'=>'12',
                'cp_busca'=>'tags]['
            ],
            'config[categoria_pendencia]'=>[
                'label'=>'Tipo de pendências',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT value,nome FROM tags WHERE ativo='s' AND pai='3'",'nome','value'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'id'=>'categoria_pendencia',
                'cp_busca'=>'categoria_pendencia][',
                'class'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
            ],
            'config[categoria_processo]'=>[
                'label'=>'Status do processo',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT value,nome FROM tags WHERE ativo='s' AND pai='10'",'nome','value'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'id'=>'categoria_processo',
                'cp_busca'=>'categoria_processo][',
                'class'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
            ],
            'bairro'=>[
                'label'=>'Área',
                'active'=>true,
                'type'=>'select',
                'campo'=>'bairro',
                'data_selector'=>[
                    'campos'=>$bairro->campos(),
                    'route_index'=>route('bairros.index'),
                    'id_form'=>'frm-bairros',
                    'action'=>route('bairros.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Etapa',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM bairros WHERE ativo='s'",'nome','id'),'exibe_busca'=>'d-block',
                //'event'=>'onchange=carregaMatricula($(this).val(),\'familias\')',
                'event'=>'onchange=carregaQuadras($(this).val())',
                'tam'=>'6',
                'value'=>@$_GET['bairro'],
            ],
            'quadra'=>[
                'label'=>'Quadra',
                'active'=>true,
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$quadra->campos(),
                    'route_index'=>route('quadras.index'),
                    'id_form'=>'frm-quadras',
                    'action'=>route('quadras.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Quadra',
                    'value_transport'=>'bairro',//valor a transportar
                ],
                'arr_opc'=>$arr_opc_quadras,
                'exibe_busca'=>'d-block',
                'event'=>'onchange=lib_abrirModalConsultaVinculo(\'loteamento\',\'fechar\');',
                'tam'=>'3',
                //'class'=>'select2'
                'value'=>@$_GET['quadra'],
            ],
            'matricula'=>['label'=>'Matricula','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>''],
            //'area_alvo'=>['label'=>'Área Alvo','active'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'','tam'=>'2','placeholder'=>''],
            //'endereco'=>['label'=>'Rua','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'10'],
            //'numero'=>['label'=>'Número','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],

            'loteamento'=>[
                'label'=>'Informações do lote',
                'active'=>false,
                'type'=>'html_vinculo',
                'exibe_busca'=>'d-none',
                'event'=>'',
                'tam'=>'12',
                'script'=>'',
                'data_selector'=>[
                    'campos'=>$lote->campos(),
                    'route_index'=>route('lotes.index'),
                    'id_form'=>'frm-loteamento',
                    'tipo'=>'array', // int para somente um ou array para vários
                    'action'=>route('lotes.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'campo'=>'loteamento',
                    'value'=>[],
                    'label'=>'Informações do lote',
                    'table'=>[
                        //'id'=>['label'=>'Id','type'=>'text'],
                        'quadra'=>['label'=>'Quadra','type'=>'arr_tab',
                        'conf_sql'=>[
                            'tab'=>'quadras',
                            'campo_bus'=>'id',
                            'select'=>'nome',
                            'param'=>['bairro','etapa'],
                            ]
                        ],
                        'nome'=>['label'=>'Lote','type'=>'text'],
                    ],
                    'tab' =>'lotes',
                    'placeholder' =>'Digite somente o número do Lote...',
                    'janela'=>[
                        'url'=>route('lotes.create').'',
                        'param'=>['bairro','etapa','quadra'],
                        'form-param'=>'',
                    ],
                    'salvar_primeiro' =>false,//exigir cadastro do vinculo antes de cadastrar este
                ],
                'script' =>'familias.loteamento',
            ],
            'id_beneficiario'=>[
                'label'=>'Proprietário',
                'active'=>false,
                'type'=>'html_vinculo',
                'exibe_busca'=>'d-none',
                'event'=>'',
                'tam'=>'12',
                'script'=>'',
                'data_selector'=>[
                    'campos'=>$beneficiario->campos(),
                    //'route_index'=>route('beneficiarios.index').'?filter[tipo]=1',
                    'route_index'=>route('beneficiarios.index'),
                    'id_form'=>'frm-beneficiario',
                    'action'=>route('beneficiarios.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'campo'=>'id_beneficiario',
                    'value'=>['tipo'=>1],
                    'label'=>'Proprietário',
                    'table'=>[
                        'id'=>['label'=>'Id','type'=>'text'],
                        'nome'=>['label'=>'Nome','type'=>'text'],
                        'cpf'=>['label'=>'CPF','type'=>'text']
                    ],
                    'tab' =>'beneficiarios',
                ],
            ],
            'id_conjuge'=>[
                'label'=>'Cônjuge ou Parceiro(a)',
                'active'=>false,
                'type'=>'html_vinculo',
                'exibe_busca'=>'d-none',
                'event'=>'',
                'tam'=>'12',
                'script'=>'',
                'data_selector'=>[
                    //'campos'=>$beneficiario->campos_parceiro(),
                    'campos'=>$beneficiario->campos(),
                    //'route_index'=>route('beneficiarios.index').'?filter[tipo]=2',
                    'route_index'=>route('beneficiarios.index'),
                    'id_form'=>'frm-conjuge',
                    'action'=>route('beneficiarios.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'campo'=>'id_conjuge',
                    'value'=>['tipo'=>2],
                    'label'=>'Cônjuge ou Parceiro(a)',
                    'table'=>[
                        'id'=>['label'=>'Id','type'=>'text'],
                        'nome'=>['label'=>'Nome','type'=>'text'],
                        'cpf'=>['label'=>'CPF','type'=>'text']
                    ],
                    'tab' =>'beneficiarios',
                ],
            ],
            'config[registro]'=>['label'=>'Registro','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>'','cp_busca'=>'config][registro'],
            'config[livro]'=>['label'=>'Livro','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>'','cp_busca'=>'config][livro'],
            'bcp_bolsa_familia'=>['label'=>'BPC ou Bolsa Família','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3'],
            'renda_familiar'=>['label'=>'Renda Fam.','active'=>true,'type'=>'moeda','exibe_busca'=>'d-block','event'=>'disabled','tam'=>'3','class'=>''],
            'doc_imovel'=>['label'=>'Doc Imóvel','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'6'],
            'qtd_membros'=>['label'=>'Membros','active'=>true,'type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>'6'],
            'membros'=>['label'=>'lista de Membros','active'=>false,'type'=>'html','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'familias.lista_membros','script_show'=>'familias.show_membros'],
            'idoso'=>['label'=>'Idoso','active'=>true,'type'=>'chave_checkbox','value'=>'s','exibe_busca'=>'d-none','event'=>'','tam'=>'6','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'crianca_adolescente'=>['label'=>'Criança e Adolescente','active'=>true,'exibe_busca'=>'d-none','event'=>'','type'=>'chave_checkbox','value'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'6','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'obs'=>['label'=>'Observação','active'=>true,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','rows'=>'4','cols'=>'80','tam'=>'12','class'=>'summernote'],
        ];
        // if(isset($dados['tags']) && is_array($dados['tags']) && $local=='show'){
        //     //Cadastros completos tag=10
        //     if(in_array(10,$dados['tags'])){
        //         unset($ret['config[categoria_pendencia]']);
        //     }
        //     //Cadastros pendentes tag=3
        //     if(in_array(3,$dados['tags'])){
        //         unset($ret['config[categoria_processo]']);
        //     }
        // }
        return $ret;
    }
    public function camposJson(User $user)
    {
        return response()->json($this->campos());
    }
    public function create(User $user)
    {
        $this->authorize('create', $this->routa);
        $title = __('Cadastrar Família');
        $titulo = $title;
        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab]);

        //$Users = Users::all();
        //$roles = DB::select("SELECT * FROM roles ORDER BY id ASC");
        $familia = ['ac'=>'cad','token'=>uniqid()];
        $arr_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades ORDER BY nome ", 'nome', 'id');
        $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-familias',
            'route'=>$this->routa,
            'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg,zip',
        ];
        $value = [
            'token'=>uniqid(),
            'matricula'=>false,
        ];
        if(!$value['matricula'])
            $config['display_matricula'] = 'd-none';
        $campos = $this->campos();
        return view($this->routa.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'arr_escolaridade'=>$arr_escolaridade,
            'arr_estadocivil'=>$arr_estadocivil,
            'campos'=>$campos,
            'value'=>$value,
            'routa'=>$this->routa,
        ]);
    }

    public function store(StoreFamilyRequest $request)
    {
        $this->authorize('create', $this->routa);
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        /*
        if (isset($dados['image']) && $dados['image']->isValid()){
            $nameFile = Str::of($dados['name'])->slug('-').'.'.$dados['image']->getClientOriginalExtension();
            $image = $dados['image']->storeAs('users',$nameFile);
            $dados['image'] = $image;
        }*/
        $userLogadon = Auth::id();
        $arr_camposArr = ['membros'];
        foreach ($arr_camposArr as $key => $value) {
            if(isset($dados[$value])){
                $dados[$value] = Qlib::lib_array_json($dados[$value]);
            }
        }
        $dados['idoso'] = isset($dados['idoso'])?$dados['idoso']:'n';
        $dados['crianca_adolescente'] = isset($dados['crianca_adolescente'])?$dados['crianca_adolescente']:'n';
        $dados['renda_familiar'] = isset($dados['renda_familiar'])?$dados['renda_familiar']:'0,00';
        $dados['autor'] = $userLogadon;
        $dados['token'] = uniqid();
        $renda_familiar = str_replace('R$','',$dados['renda_familiar']);
        $dados['renda_familiar'] = Qlib::precoBanco($renda_familiar);
        $salvar = Familia::create($dados);
        $route = $this->routa.'.index';
        $dados['id_familia'] = $salvar->id;
        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'id'=>$salvar->id,'this'=>$this]);

        $ret = [
            'mens'=>'Salvo com sucesso!',
            'color'=>'success',
            'idCad'=>$salvar->id,
            'salvarQuadra'=>$this->salvarQuadra($dados),
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }
    public function salvarQuadra($config = null)
    {
        $ret = false;
        if(isset($config['id_familia']) && isset($config['loteamento'])){
            $arr_lote = $config['loteamento'];
            if(!is_array($arr_lote)){

                if(Qlib::isJson($config['loteamento'])){
                    $arr_lote = Qlib::lib_json_array($config['loteamento']);
                }
            }
            if(isset($arr_lote[0])){
                $quadra = Qlib::buscaValorDb([
                    'tab'=>'lotes',
                    'campo_bus'=>'id',
                    'valor'=>$arr_lote[0],
                    'select'=>'quadra',
                    'compleSql'=>'',
                    'debug'=>false,
                ]);
                if($quadra){
                    $ret=Familia::where('id',$config['id_familia'])->update([
                        'quadra'=>$quadra,
                    ]);
                }
            }
        }
        return $ret;
    }
    public function show($id,User $user)
    {
        $dados = Familia::findOrFail($id);
        $this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = 'Cadastro da família';
            $titulo = $title;
            //dd($dados);
            $dados['ac'] = 'alt';
            if(isset($dados['config'])){
                $dados['config'] = Qlib::lib_json_array($dados['config']);
            }
            $arr_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades ORDER BY nome ", 'nome', 'id');
            $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
            $listFiles = false;
            //$dados['renda_familiar'] = number_format($dados['renda_familiar'],2,',','.');
            $campos = $this->campos($dados,'show');
            if(isset($dados['token'])){
                $listFiles = _upload::where('token_produto','=',$dados['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-familias',
                'route'=>$this->routa,
                'id'=>$id,
                'class_card1'=>'col-md-8',
                'class_card2'=>'col-md-4',
            ];
            // $config['class_card1'] = 'col-md-12';
            // $config['class_card2'] = 'd-none';
            //REGISTRAR EVENTOS
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            if(!$dados['matricula'])
                $config['display_matricula'] = 'd-none';
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
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'arr_escolaridade'=>$arr_escolaridade,
                'arr_estadocivil'=>$arr_estadocivil,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'routa'=>$this->routa,
                // 'eventos'=>(new EventController)->listEventsPost(['post_id'=>$id]),
                'exec'=>true,
            ];
            return view('padrao.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }

    public function edit($id,User $user)
    {
        $dados = Familia::where('id',$id)->get();
        //$roles = DB::select("SELECT * FROM roles ORDER BY id ASC");
        //$permissions = DB::select("SELECT * FROM permissions ORDER BY id ASC");
        $this->authorize('create', $this->routa);
        if(!empty($dados)){
            $title = 'Editar Cadastro de família';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $arr_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades ORDER BY nome ", 'nome', 'id');
            $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
            $listFiles = false;

            //REGISTRAR EVENTOS
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-familias',
                'route'=>$this->routa,
                'id'=>$id,
                'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg',
            ];
            // if($dados[0]['loteamento']>0){
            //     $bairro = Bairro::find($dados[0]['bairro']);
            //     $dados[0]['matricula'] = isset($bairro['matricula'])?$bairro['matricula']:false;
            // }
            if(!$dados[0]['matricula'])
            $config['display_matricula'] = 'd-none';
            if(isset($dados[0]['config']) && is_array($dados[0]['config'])){
                foreach ($dados[0]['config'] as $key => $value) {
                    if(is_array($value)){

                    }else{
                        $dados[0]['config['.$key.']'] = $value;
                    }
                }
            }
            //$dados[0]['tags'] = Qlib::lib_json_array($dados[0]['tags']);
            $_GET['dados'] = $dados[0]; //para ter acesso em todas a views
            $campos = $this->campos($dados[0]);
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'arr_escolaridade'=>$arr_escolaridade,
                'arr_estadocivil'=>$arr_estadocivil,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'routa'=>$this->routa,
                'exec'=>true,
                'redirect'=>'relatorios',
            ];
            return view($this->routa.'.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }
    public function update(StoreFamilyRequest $request, $id)
    {
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
                    //$data[$key] = number_format($value,2,'.','');
                }else{
                    $data[$key] = $value;
                }
            }
        }
        $userLogadon = Auth::id();
        $data['idoso'] = isset($data['idoso'])?$data['idoso']:'n';
        $data['crianca_adolescente'] = isset($data['crianca_adolescente'])?:'n';
        $data['config']['atualizado_por'] = $userLogadon;
        $data['tags'] = isset($data['tags'])?$data['tags']:false;
        if(!empty($data)){
            //Salvar renda familiar calculada
            $atualizar=Familia::where('id',$id)->update($data);
            $route = 'familias.index';
            $data['id_familia'] = $id;
            $ret = [
                'exec'=>true,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
                'salvarQuadra'=>$this->salvarQuadra($data),
                'data'=>$data,
            ];
            if($atualizar){
                $rf = $this->rendaFamiliar($data['id']);
                if(isset($rf['renda_familiar'])){
                    $atr['renda_familiar'] = $rf['renda_familiar'];
                    $ret['atualizar_renda']=Familia::where('id',$id)->update($atr);

                }

                //REGISTRAR EVENTOS
                (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            }
        }else{
            $route = 'familias.edit';
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
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        if (!$post = Familia::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route('familias.index')]);
            }else{
                $ret = redirect()->route('familias.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }
        $user = Auth::user();
        $reg_excluido = ['data'=>date('d-m-Y'),'autor'=>$user->id];
        //Familia::where('id',$id)->delete();
        Familia::where('id',$id)->update(['excluido'=>'s','reg_excluido'=>Qlib::lib_array_json($reg_excluido)]);;
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route('familias.index')]);
        }else{
            $ret = redirect()->route('familias.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        //REGISTRAR EVENTOS
        (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);

        return $ret;
    }
    public function ajaxPost(Request $request){
        $post = $request->all();
        $ret['exec'] = false;
        $ret['mens'] = 'Opção inválida';
        $ret['color'] = 'danger';
        if(!isset($post['opc'])){
            return response()->json($ret);
        }
        $ret['atualiza'] = false;
        if($post['opc']=='salvar_etapa_massa'){
            if(isset($post['ids']) && isset($post['etapa'])){
                $dEtapa = Etapa::find($post['etapa']);
                $ret['etapa'] = $dEtapa['nome'];
                $arr_ids = explode('_',$post['ids']);
                if(is_array($arr_ids)){
                    foreach ($arr_ids as $k => $v) {
                        if($v){
                            $ds = [
                                'etapa'=>$post['etapa'],
                            ];
                            $ret['atualiza'][$v] = Familia::where('id',$v)->update($ds);
                        }
                    }
                }
                $ret['ids']= $arr_ids;
                if($ret['atualiza']){
                    $ret['exec'] = true;
                    $ret['mens'] = 'Cadastro(s) Atualizado(s) com sucesso!';
                    $ret['color'] = 'success';
                }
            }
        }
        if($post['opc']=='salvar_situacao_massa'){
            if(isset($post['ids']) && isset($post['tags'])){
                $dTag = Tag::find($post['tags']);
                $ret['situacao'] = $dTag['nome'];
                $arr_ids = explode('_',$post['ids']);
                if(is_array($arr_ids)){
                    foreach ($arr_ids as $k => $v) {
                        if($v){
                            $arr_tags = [$post['tags']];
                            $df = Familia::find($v);
                            $ds = [
                                'tags'=>Qlib::lib_array_json($arr_tags),
                            ];
                            if(isset($df['config'])){
                                $ds['config']['categoria_pendencia'] = $post['categoria_pendencia'];
                                $ds['config']['categoria_processo'] = $post['categoria_processo'];
                            }
                            $ret['atualiza'][$v] = Familia::where('id',$v)->update($ds);
                        }
                    }
                }
                $ret['ids']= $arr_ids;
                if($ret['atualiza']){
                    $ret['exec'] = true;
                    $ret['mens'] = 'Cadastro(s) Atualizado(s) com sucesso!';
                    $ret['color'] = 'success';
                }
            }
        }
        return response()->json($ret);
    }
    public function rendaFamiliar($id=false) {
        $rf = 0;    //renda Familiar
        $rdp = 0;   //renda do proprietário..
        $rdc = 0;   //renda do conjuge..
        $d = Familia::where('familias.id','=',$id)
        ->Join('beneficiarios','familias.id_beneficiario','=','beneficiarios.id')
        ->select('familias.renda_familiar','familias.membros','familias.id_conjuge','beneficiarios.config')->get();
        if($d->count()){
            $confProp = Qlib::lib_json_array($d[0]['config']);
            if(isset($confProp['renda'])){
                $rd = str_replace('R$','',$confProp['renda']);
                $rdp = Qlib::precoBanco($rd,2,'.',''); //renda do proprietário..
                if(isset($d[0]['id_conjuge']) && !empty($d[0]['id_conjuge'])){
                    $dc = Beneficiario::FindOrFail($d[0]['id_conjuge']);
                    if($dc->count()){
                        $confCon = Qlib::lib_json_array($dc['config']);
                        if(isset($confCon['renda']) && !empty($confCon['renda'])){
                            $rd = str_replace('R$','',$confCon['renda']);
                            $rdc = Qlib::precoBanco($rd,2,'.',''); //renda do conjuge..
                        }
                    }
                }
                $rf = $rdp+$rdc;
            }
            $ret['renda_proprietario'] = $rdp;
            $ret['renda_conjuge'] = $rdc;
            $ret['renda_familiar'] = $rf;

            if(isset($d[0]['membros']) && !empty($d[0]['membros'])){
                $m = Qlib::lib_json_array($d[0]['membros']);
                if(is_array($m)){
                    foreach($m as $k=>$dmemb){
                        $rd = str_replace('R$','',$dmemb['renda']);
                        $rdm = Qlib::precoBanco($rd,2,'.',''); //renda do Membro..
                        $rf += $rdm;
                        $ret['renda_membros'][$k] = $rdm;
                    }
                }
            }
            $ret['renda_familiar'] = $rf;

        }

        return $ret;
    }
}
