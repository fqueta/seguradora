<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\api\SulAmericaController;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Qoption;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use App\Services\ContractEventLogger;
use Illuminate\Support\Facades\Auth;
use stdClass;

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
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $tab;
    public function __construct()
    {
        $this->middleware('auth');
        $this->user = Auth::user();
        $this->routa = 'contratos';
        $this->label = 'Contratos';
        $this->view = 'admin.padrao';
        $this->tab = 'contratos';
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
            // dd($dcc);
            $integracao = [
                'planoProduto'=>Qlib::qoption('planoPadrao')? Qlib::qoption('planoPadrao') : @$dcc['id_plano'],
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
            // dd($ret);
            //registrar na tabela contrata
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
                    // Log: contratação aprovada
                    ContractEventLogger::logByToken(
                        $token,
                        'integracao_sulamerica',
                        'Contratação aprovada pela SulAmérica',
                        [
                            'numOperacao' => $numOperacao,
                            'ret' => $ret,
                        ],
                        auth()->id()
                    );
                    if( Qlib::isAdmin(1)){
                        $ret['config'] = $config;
                        $ret['salvar'] = $salvar;
                        $ret['salvar2'] = $salvar2;
                        $ret['salv_json_fiels'] = $salv_json_fiels;
                        $ret['update_status'] = $update_status;
                        // $ret['dc'] = $dc;
                    }
                }else{
                    // Log: contratação rejeitada
                    ContractEventLogger::logByToken(
                        $token,
                        'integracao_sulamerica',
                        'Contratação rejeitada pela SulAmérica',
                        [
                            'numOperacao' => $numOperacao,
                            'ret' => $ret,
                        ],
                        auth()->id()
                    );
                }
                // $salvar_contrado = Qlib::update_tab('contratos',[
                //     'config'=>Qlib::lib_array_json($ret['data']),
                // ],"WHERE token='$token'");
            }else{
                // Log: contratação rejeitada
                ContractEventLogger::logByToken(
                    $token,
                    'integracao_sulamerica',
                    'Contratação realizada pela SulAmérica',
                    [
                        'numOperacao' => $numOperacao,
                        'ret' => $ret,
                    ],
                    auth()->id()
                );
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
                // Captura status anterior antes da atualização
                $oldStatus = Qlib::get_usermeta($id_cliente,$this->campo_meta3,true);
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
                // Log: atualização de status
                ContractEventLogger::logStatusChangeByToken(
                    $token_contrato,
                    $oldStatus,
                    $status,
                    'Status do contrato atualizado',
                    [
                        'user_id' => auth()->id(),
                        'result' => $ret,
                    ],
                    auth()->id()
                );
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
                // Log: reativação iniciada com troca de token
                ContractEventLogger::logByToken(
                    $new_token,
                    'reativacao',
                    'Reativação iniciada com novo token',
                    [
                        'old_token' => $token,
                        'new_token' => $new_token,
                    ],
                    auth()->id()
                );
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
    public function cancelar($numOperacao=false,$token_contrato=false)
    {
        $ret = ['exec'=>false,'mens'=>'Erro ao cancelar'];
        if($numOperacao){
            $ret = (new sulAmericaController)->cancelamento(['numeroOperacao'=>$numOperacao]);
        }
        //adicionar evento de cancelamento
        if($ret['exec'] && $token_contrato){
            ContractEventLogger::logByToken(
                $token_contrato,
                'cancelamento',
                'Cancelamento do contrato',
                [
                    'numeroOperacao' => $numOperacao,
                ],
                auth()->id()
            );
        }
        return $ret;
    }

    /**
     * Lista o histórico de eventos de um contrato por token.
     *
     * Português: Recupera o contrato pelo token e lista apenas os eventos
     * dos tipos 'integracao_sulamerica', 'reativacao' e 'cancelamento', ordenados por data
     * de criação (decrescente), para exibição em timeline.
     *
     * English: Fetch contract by token and list only events of types
     * 'integracao_sulamerica' and 'reativacao', ordered by creation date
     * (desc) for timeline rendering on the details page.
     *
     * @param string $token Token único do contrato
     * @return \Illuminate\View\View
     */
    public function history(string $token)
    {
        // Busca contrato pelo token
        $contrato = Contrato::where('token', $token)->first();

        if (!$contrato) {
            abort(404, 'Contrato não encontrado.');
        }
        // Carrega eventos relacionados ao contrato: integração, reativação e cancelamento
        $events = \App\Models\ContractEvent::with('user')
            ->where('contrato_id', $contrato->id)
            ->whereIn('event_type', ['integracao_sulamerica', 'reativacao', 'cancelamento'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Retorna view com dados para renderização da timeline
        return view('admin.contratos.history', [
            'contrato' => $contrato,
            'events' => $events,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * Campos do formulário e da listagem de contratos.
     * PT-BR: Define os campos do CRUD de contratos.
     * EN: Defines the Contracts CRUD fields.
     */
    public function campos($id = null)
    {
        $labelCliente = 'Cliente';
        $labelProduto = 'Produto';
        return [
            'id' => ['label' => 'Id', 'active' => true, 'js' => true, 'type' => 'hidden', 'exibe_busca' => 'd-block', 'event' => '', 'tam' => '2'],
            'autor' => ['label' => 'autor', 'active' => true, 'js' => true, 'type' => 'hidden', 'exibe_busca' => 'd-block', 'event' => '', 'tam' => '2'],
            'token' => ['label' => 'token', 'active' => false, 'type' => 'hidden', 'exibe_busca' => 'd-block', 'event' => '', 'tam' => '2'],
            'id_cliente' => [
                'label' => $labelCliente,
                'active' => true,
                'type' => 'select',
                'arr_opc' => Qlib::sql_array("SELECT id,name FROM users WHERE ativo='s'", 'name', 'id'),
                'exibe_busca' => 'd-block',
                'event' => 'required',
                'tam' => '6',
                'label_option_select' => 'Selecione',
            ],
            'id_produto' => [
                'label' => $labelProduto,
                'active' => true,
                'type' => 'select',
                'arr_opc' => Qlib::sql_array("SELECT ID,post_title FROM posts WHERE post_type='products'", 'post_title', 'ID'),
                'exibe_busca' => 'd-block',
                'event' => 'required',
                'tam' => '6',
                'label_option_select' => 'Selecione',
            ],
            'id_plano' => [
                'label' => 'Plano',
                'active' => true,
                'type' => 'select',
                'arr_opc' => [
                    '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
                    '6' => '6', '7' => '7', '8' => '8', '9' => '9'
                ],
                'exibe_busca' => 'd-block',
                'event' => 'required',
                'tam' => '4',
                'label_option_select' => 'Selecione',
            ],
            'inicio' => ['label' => 'Início', 'active' => true, 'type' => 'date', 'exibe_busca' => 'd-block', 'event' => 'required', 'tam' => '4'],
            'fim' => ['label' => 'Fim', 'active' => true, 'type' => 'date', 'exibe_busca' => 'd-block', 'event' => 'required', 'tam' => '4'],
            'config[premioSeguro]' => ['label' => 'Prêmio do Seguro', 'active' => true, 'type' => 'moeda', 'exibe_busca' => 'd-block', 'event' => '', 'tam' => '4', 'placeholder' => 'Ex.: 199,90'],
            'ativo' => ['label' => 'Ativado', 'tab' => $this->tab, 'active' => true, 'type' => 'chave_checkbox', 'value' => 's', 'valor_padrao' => 's', 'exibe_busca' => 'd-block', 'event' => '', 'tam' => '3', 'arr_opc' => ['s' => 'Sim', 'n' => 'Não']],
        ];
    }

    /**
     * Consulta e paginação da listagem de contratos.
     */
    public function queryContratos($get = false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET : [];
        $ano = date('Y');
        $mes = date('m');
        $config = [
            'limit' => isset($get['limit']) ? $get['limit'] : 50,
            'order' => isset($get['order']) ? $get['order'] : 'desc',
        ];

        $contratos = Contrato::where('excluido', '=', 'n')->where('deletado', '=', 'n')->orderBy('id', $config['order']);

        $campos = $this->campos();
        $tituloTabela = 'Lista de contratos';
        $arr_titulo = false;
        if (isset($get['filter'])) {
            $titulo_tab = false;
            foreach ($get['filter'] as $key => $value) {
                if (!empty($value)) {
                    if ($key == 'id') {
                        $contratos->where($key, 'LIKE', $value);
                        $titulo_tab .= 'Todos com *' . $campos[$key]['label'] . '% = ' . $value . '& ';
                        $arr_titulo[$campos[$key]['label']] = $value;
                    } else {
                        $contratos->where($key, 'LIKE', '%' . $value . '%');
                        if (isset($campos[$key]['type']) && $campos[$key]['type'] == 'select' && isset($campos[$key]['arr_opc'][$value])) {
                            $value = $campos[$key]['arr_opc'][$value];
                        }
                        $arr_titulo[$campos[$key]['label']] = $value;
                        $titulo_tab .= 'Todos com *' . $campos[$key]['label'] . '% = ' . $value . '& ';
                    }
                }
            }
            if ($titulo_tab) {
                $tituloTabela = 'Lista de: &' . $titulo_tab;
            }
        }
        $registros = clone $contratos;
        $ativos = clone $contratos;
        $inativos = clone $contratos;
        $novos = clone $contratos;
        if ($config['limit'] == 'todos') {
            $contratos = $contratos->get();
        } else {
            $contratos = $contratos->paginate($config['limit']);
        }
        $totais = new stdClass;
        $totais->todos = $registros->count();
        $totais->esteMes = $novos->whereYear('created_at', '=', $ano)->whereMonth('created_at', '=', $mes)->count();
        $totais->ativos = $ativos->where('ativo', '=', 's')->count();
        $totais->inativos = $inativos->where('ativo', '=', 'n')->count();

        $ret['contratos'] = $contratos;
        $ret['totais'] = $totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro' => ['label' => 'Todos cadastros', 'value' => $totais->todos, 'icon' => 'fas fa-calendar'],
            'todos_mes' => ['label' => 'Cadastros recentes', 'value' => $totais->esteMes, 'icon' => 'fas fa-calendar-times'],
            'todos_ativos' => ['label' => 'Cadastros ativos', 'value' => $totais->ativos, 'icon' => 'fas fa-check'],
            'todos_inativos' => ['label' => 'Cadastros inativos', 'value' => $totais->inativos, 'icon' => 'fas fa-archive'],
        ];
        return $ret;
    }

    /**
     * Listagem de contratos.
     */
    public function index(Request $request)
    {
        $this->authorize('ler', $this->routa);
        $title = __('Cadastro de contratos');
        $titulo = $title;
        $query = $this->queryContratos($_GET);
        $query['config']['exibe'] = 'html';
        $routa = $this->routa;
        return view($this->view . '.index', [
            'dados' => $query['contratos'],
            'title' => $title,
            'titulo' => $titulo,
            'campos_tabela' => $query['campos'],
            'escolaridade_totais' => $query['totais'],
            'titulo_tabela' => $query['tituloTabela'],
            'arr_titulo' => $query['arr_titulo'],
            'config' => $query['config'],
            'routa' => $routa,
            'view' => $this->view,
            'i' => 0,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Tela de criação de contrato.
     */
    public function create()
    {
        $this->authorize('create', $this->routa);
        $title = __('Cadastrar contrato');
        $titulo = $title;
        $config = [
            'ac' => 'cad',
            'frm_id' => 'frm-contratos',
            'route' => $this->routa,
        ];
        $value = [
            'token' => uniqid(),
            'autor' => Auth::id(),
            'ativo' => 's',
        ];
        $campos = $this->campos();
        return view($this->view . '.createedit', [
            'config' => $config,
            'title' => $title,
            'titulo' => $titulo,
            'campos' => $campos,
            'value' => $value,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Persistência de um novo contrato.
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->routa);
        $validatedData = $request->validate([
            'id_cliente' => ['required'],
            'id_produto' => ['required'],
            'id_plano' => ['required'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date'],
            'ativo' => ['nullable', 'in:s,n'],
        ]);
        $dados = $request->all();
        $ajax = isset($dados['ajax']) ? $dados['ajax'] : 'n';
        $dados['ativo'] = isset($dados['ativo']) ? $dados['ativo'] : 'n';
        $dados['token'] = isset($dados['token']) ? $dados['token'] : uniqid();
        $dados['autor'] = isset($dados['autor']) ? $dados['autor'] : Auth::id();
        $dados['excluido'] = 'n';
        $dados['deletado'] = 'n';

        $salvar = Contrato::create($dados);
        $route = $this->routa . '.index';
        $ret = [
            'mens' => $this->label . ' cadastrado com sucesso!',
            'color' => 'success',
            'idCad' => $salvar->id,
            'exec' => true,
            'dados' => $dados,
        ];

        if ($ajax == 's') {
            $ret['return'] = route($route) . '?idCad=' . $salvar->id;
            $ret['redirect'] = route($this->routa . '.edit', ['contrato' => $salvar->id]);
            return response()->json($ret);
        } else {
            return redirect()->route($route, $ret);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return redirect()->route($this->routa . '.edit', ['id' => $id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Tela de edição do contrato.
     */
    public function edit($id)
    {
        $this->authorize('ler', $this->routa);
        $title = __('Editar contrato');
        $titulo = $title;
        $config = [
            'ac' => 'alt',
            'frm_id' => 'frm-contratos',
            'route' => $this->routa,
        ];
        $contrato = Contrato::findOrFail($id);
        $value = $contrato->toArray();
        $campos = $this->campos($contrato->id);
        return view($this->view . '.createedit', [
            'config' => $config,
            'title' => $title,
            'titulo' => $titulo,
            'campos' => $campos,
            'value' => $value,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Atualização do contrato.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', $this->routa);
        $validatedData = $request->validate([
            'id_cliente' => ['required'],
            'id_produto' => ['required'],
            'id_plano' => ['required'],
            'inicio' => ['required', 'date'],
            'fim' => ['required', 'date'],
            'ativo' => ['nullable', 'in:s,n'],
        ]);
        $dados = $request->all();
        $dados['ativo'] = isset($dados['ativo']) ? $dados['ativo'] : 'n';
        $contrato = Contrato::findOrFail($id);
        $contrato->update($dados);
        $route = $this->routa . '.edit';
        $ret = [
            'mens' => $this->label . ' atualizado com sucesso!',
            'color' => 'success',
            'idCad' => $contrato->id,
            'exec' => true,
            'dados' => $dados,
        ];
        return redirect()->route($route, ['id' => $contrato->id] + $ret);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Remoção lógica de contrato.
     */
    public function destroy($id)
    {
        $this->authorize('delete', $this->routa);
        $contrato = Contrato::findOrFail($id);
        $contrato->update([
            'excluido' => 's',
            'reg_excluido' => 'Removido em ' . date('Y-m-d H:i:s') . ' por ' . Auth::id(),
        ]);
        $route = $this->routa . '.index';
        $ret = [
            'mens' => $this->label . ' removido com sucesso!',
            'color' => 'success',
            'exec' => true,
        ];
        return redirect()->route($route, $ret);
    }
}
