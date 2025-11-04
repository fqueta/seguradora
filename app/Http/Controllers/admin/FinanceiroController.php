<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\_upload;
use App\Models\admin\financeiro;
use App\Qlib\Qlib;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class FinanceiroController extends Controller
{
    public $user;
    public $sec;
    public $sec2;
    public $title;
    public $routa;
    public $view;
    public $tab;
    public function __construct()
    {
        $this->middleware('auth');
        $seg1 = request()->segment(2);
        $seg2 = request()->segment(3);
        $this->sec = $seg1;
        $this->sec2 = $seg2;
        $this->tab = 'financeiro';
        $this->routa = $this->sec2;
        $this->title = ucwords($seg2);
        $this->user = Auth::user();
        $this->view = 'admin.financeiro';
    }
    public function queryContas($get=false,$config=false)
    {

        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        $ultimoDiaMes = Qlib::ultimoDiaMes($mes,$ano);
        $dataI = isset($get['dataI']) ? $get['dataI'] : date('Y-m').'-01';
        $dataF = isset($get['dataF']) ? $get['dataF'] : $ano.'-'.$mes.'-'.$ultimoDiaMes;
        //$todasFamilias = Familia::where('excluido','=','n')->where('deletado','=','n');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        if($this->sec2=='receitas'){
            $startDate = Carbon::createFromFormat('Y-m-d', $dataI)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dataF)->endOfDay();
            // $contas = financeiro::whereBetween('vencimento',[$startDate,$endDate])->
            $contas = financeiro::whereNull('id_fatura_fixa')->
            where('tipo',$this->sec2)->
            orderBy('vencimento',$config['order']);
        }elseif($this->sec2=='despesas'){
            $startDate = Carbon::createFromFormat('Y-m-d', $dataI)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dataF)->endOfDay();
            $contas = financeiro::whereBetween('vencimento',[$startDate,$endDate])->
            where('tipo',$this->sec2)->
            orderBy('vencimento',$config['order']);

        }
        //$contas =  DB::table('posts')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $contas_totais = new stdClass;
        $campos = $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $contas->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $contas->where($key,'LIKE','%'. $value. '%');
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
                $cts = $contas;
                if($config['limit']=='todos'){
                    $contas = $contas->get();
                }else{
                    $contas = $contas->paginate($config['limit']);
                }
        }else{
            $resum = clone $contas;
            $cts = $contas;
            if($config['limit']=='todos'){
                $contas = $contas->get();
            }else{
                $contas = $contas->paginate($config['limit']);
            }
        }
        $contas_totais->todos = $cts->count();
        // $contas_totais->esteMes = $cts->whereYear('post_date', '=', $ano)->whereMonth('post_date','=',$mes)->count();
        $contas_totais->pagos = $resum->where('pago','=','s')->count();
        $contas_totais->apagar = $resum->where('pago','=','n')->count();
        $ret['contas'] = $contas;
        $ret['contas_totais'] = $contas_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        // $ret['post_type'] = $this->post_type;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_pagos'=>['label'=>'Todos pagos','value'=>$contas_totais->pagos,'icon'=>'fas fa-thmbs-up'],
            'todos_apagar'=>['label'=>'Cadastros apagar','value'=>$contas_totais->apagar,'icon'=>'fas fa-fa-thmbs-down'],
            // 'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$contas_totais->ativos,'icon'=>'fas fa-check'],
            // 'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$contas_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function receitas(Request $request){
        $this->authorize('is_admin', $this->user);
        //buscar os dados da página
        $titulo = $this->title;
        $queryContas = $this->queryContas($_GET);
        $queryContas['config']['exibe'] = 'html';
        $routa = $this->routa;
        //if(isset($queryContas['contas']));
        return view($this->view.'.index',[
            'dados'=>$queryContas['contas'],
            'title'=>$this->title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryContas['campos'],
            'post_totais'=>$queryContas['contas_totais'],
            'titulo_tabela'=>$queryContas['tituloTabela'],
            'arr_titulo'=>$queryContas['arr_titulo'],
            'config'=>$queryContas['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ]);
    }
    public function despesas(Request $request){
        $this->authorize('is_admin', $this->user);
        //buscar os dados da página
        $titulo = $this->title;
        $queryContas = $this->queryContas($_GET);
        $queryContas['config']['exibe'] = 'html';
        $routa = $this->routa;
        //if(isset($queryContas['contas']));
        return view($this->view.'.index',[
            'dados'=>$queryContas['contas'],
            'title'=>$this->title,
            'titulo'=>$titulo,
            'campos_tabela'=>$queryContas['campos'],
            'post_totais'=>$queryContas['contas_totais'],
            'titulo_tabela'=>$queryContas['tituloTabela'],
            'arr_titulo'=>$queryContas['arr_titulo'],
            'config'=>$queryContas['config'],
            'routa'=>$routa,
            'view'=>$this->view,
            'i'=>0,
        ]);
    }
    public function extrato(){

    }
    /**
     * Metodo para retornar um array para montar o formularo de laçamentos das receitas
     */
    public function campos($id=false){
        $dados = false;
        $conta = request()->get('conta'); //se for null não é fatura principal do contra é uma subfatura
        if($id){
            $dados = financeiro::Find($id);
            if($dados->count()>0){
                $dados = $dados->toArray();
            }
        }
        if($this->routa=='receitas'){
            $label0 = __('DADOS DO CLIENTE');
            $label1 = __('Cliente');
        }elseif($this->routa=='despesas'){
            $label0 = __('DADOS DO FORNECEDOR');
            $label1 = __('Fornecedor');
        }else{
            $label0 = __('DADOS');
            $label1 = '';

        }
        $larg_campos = 6;
        $rf = 'fornecedores';
        $userC = new UserController(['route'=>$rf]);
        $id_permision = $userC->id_permission_fornecedores();
        $arr_opc = Qlib::sql_array("SELECT id,name,email FROM users WHERE ativo='s' AND id_permission='$id_permision'",'name','id');
        if($conta){
            $ret = [
                'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'html0'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h6>'.$label0.'</h6><hr class="mt-0 pb-0">'],
                'id_cliente'=>[
                    'label'=>$label1,
                    'active'=>true,
                    'type'=>'selector',
                    'data_selector'=>[
                        'campos'=>$userC->campos(),
                        'route_index'=>route($rf.'.index'),
                        'id_form'=>'frm-'.$rf,
                        'action'=>route($rf.'.store'),
                        'campo_id'=>'id',
                        'campo_bus'=>'name',
                        'label'=>$label1,
                    ],'arr_opc'=>$arr_opc,'exibe_busca'=>'d-block',
                    'event'=>'required',
                    //'event'=>'onchange=carregaMatricula($(this).val())',
                    'tam'=>'12',
                    'class'=>'select2',
                    'value'=>@$_GET['id_cliente'],
                ],
                'tipo'=>['label'=>'tipo','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','value'=>$this->routa,'event'=>'','tam'=>'2'],
                'html1'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h5>Dados da Conta</h5><hr>'],
                'numero'=>['label'=>'Numero','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>$larg_campos],
                'valor'=>['label'=>'Valor*','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos,'validate'=>['required','string']],
                'emissao'=>['label'=>'Data de Emissão*','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
                'vencimento'=>['label'=>'Data de vencimento*','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
                'descricao'=>['label'=>'Descrição','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                // 'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
                // 'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
                'pago'=>['label'=>'Pagar','active'=>true,'tab'=>$this->tab ,'campo'=>'pago' ,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'6','arr_opc'=>['s'=>'Pago','n'=>'A pagar'],'tab'=>'financeiro'],
                // 'vencimento'=>['label'=>'Data de vencimento*','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
            ];
            if(is_array($dados)){
                $ret['id_cliente']=[
                        'label'=>$label1,
                        'active'=>false,
                        'type'=>'html_vinculo',
                        'exibe_busca'=>'d-none',
                        'event'=>'',
                        'tam'=>'12',
                        'script'=>'',
                        'data_selector'=>[
                            'campos'=>$userC->campos(),
                            'route_index'=>route('fornecedores.index'),
                            'id_form'=>'frm-fornecedores',
                            // 'tipo'=>'array', // int para somente um ou array para vários
                            'tipo'=>'text', // int para somente um ou array para vários
                            'action'=>route('fornecedores.store'),
                            'campo_id'=>'id',
                            'campo_bus'=>'name',
                            'campo'=>'id_cliente',
                            'value'=>[],
                            'label'=>'Informações do lote',
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
                                'url'=>route('fornecedores.create').'',
                                // 'param'=>['name','cnpj','email'],
                                'param'=>[],
                                'form-param'=>'',
                            ],
                            'salvar_primeiro' =>false,//exigir cadastro do vinculo antes de cadastrar este
                        ],
                        'script' => false,//'familias.loteamento', //script admicionar
                ];
            }
        }else{

            $rf = 'tipo_receitas';
            $rc = 'cat_receitas';
            $campos_tipo = (new PostsController(['post_type'=>$rf]))->campos();
            $campos_tipo_cat = (new PostsController(['post_type'=>$rc]))->campos();
            $arr_opc = Qlib::sql_array("SELECT ID,post_title,post_name FROM posts WHERE post_status='publish' AND post_type='$rf'",'post_title','ID','attr_data');
            $arr_opc_cat = Qlib::sql_array("SELECT ID,post_title,post_name FROM posts WHERE post_status='publish' AND post_type='$rc'",'post_title','ID');
            $ret = [
                'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'tipo'=>['label'=>'tipo','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','value'=>$this->routa,'event'=>'','tam'=>'2'],
                // 'html0'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h6>'.$label0.'</h6><hr class="mt-0 pb-0">'],
                'categoria'=>[
                    'label'=>'Categoria',
                    'active'=>true,
                    'type'=>'selector',
                    'data_selector'=>[
                        'campos'=>$campos_tipo_cat,
                        'route_index'=>route($rc.'.index'),
                        'id_form'=>'frm-'.$rc,
                        'action'=>route($rc.'.store'),
                        'campo_id'=>'ID',
                        'campo_bus'=>'post_title',
                        'label'=>'Categoria',
                    ],'arr_opc'=>$arr_opc_cat,'exibe_busca'=>'d-block',
                    'event'=>'required',
                    //'event'=>'onchange=carregaMatricula($(this).val())',
                    'tam'=>'12',
                    'class'=>'',
                ],
                'conta'=>[
                    'label'=>'Descrição da receita',
                    'active'=>true,
                    'type'=>'selector',
                    'data_selector'=>[
                        'campos'=>$campos_tipo,
                        'route_index'=>route($rf.'.index'),
                        'id_form'=>'frm-'.$rf,
                        'action'=>route($rf.'.store'),
                        'campo_id'=>'ID',
                        'campo_bus'=>'post_title',
                        'label'=>'Descrição',
                    ],'arr_opc'=>$arr_opc,'exibe_busca'=>'d-block',
                    'event'=>'required',
                    'event'=>'onchange=carregaDados(this,alvoDescricaoReceita) ',
                    'tam'=>'12',
                    'class'=>'select2',
                ],
                'ano'=>['label'=>'Ano','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
                'numero'=>['label'=>'Numero','active'=>true,'placeholder'=>'','type'=>'tel','exibe_busca'=>'d-block','event'=>'','tam'=>$larg_campos],
                'valor'=>['label'=>'Valor de previsão*','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos,'validate'=>['required','string']],
                'valor_pago'=>['label'=>'Valor pago','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'','tam'=>$larg_campos],
                'descricao'=>['label'=>'Observação(Opcional)','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12','class_div'=>'','class'=>'','placeholder'=>__('Escreva seu conteúdo aqui..')],
            ];
        }
        return $ret;
    }

    /**
     * Metodo para retornar um array para montar o formularo de laçamentos das receitas mensais
     * @param $id id da receita pai ou seja da receita de previsão principal do qual esse lançamento vai herdar as partes
     */
    public function campos_mensais($id=null){
        $df = financeiro::find($id);
        if(!$df){
            return false;
        }
        // dd($df);
        $rc = 'cat_receitas';
        $rf = 'tipo_receitas';
        $arr_tipo_receita = Qlib::sql_array("SELECT ID,post_title,post_name FROM posts WHERE ID='".$df['conta']."'",'post_title','ID');
        $arr_opc_cat = Qlib::sql_array("SELECT ID,post_title,post_name FROM posts WHERE ID='".$df['categoria']."'",'post_title','ID');
        $larg_campos = 4;
        $ret = [
            'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'token'=>['label'=>'token','active'=>false,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'tipo'=>['label'=>'tipo','active'=>false,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','value'=>$this->routa,'event'=>'','tam'=>'2'],
            'id_fatura_fixa'=>['label'=>'pai','js'=>true,'active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','value'=>$id,'event'=>'','tam'=>'2'],
            // 'html0'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h6>'.$label0.'</h6><hr class="mt-0 pb-0">'],
            // 'conta'=>[
            //     'label'=>'Descrição da receita',
            //     'active'=>true,
            //     'js'=>true,
            //     'type'=>'select',
            //     'arr_opc'=>$arr_tipo_receita,'exibe_busca'=>'d-block',
            //     'event'=>'required',
            //     'event'=>'disabled ',
            //     'tam'=>'12',
            //     'class'=>'select2',
            //     'value'=>@$arr_tipo_receita[$df['conta']],
            // ],
            'categoria'=>['label'=>'Categoria','js'=>true,'active'=>true,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','js'=>true,'event'=>'','tam'=>'6','value'=>$df['categoria'],'value_text'=>@$arr_opc_cat[$df['categoria']]],
            'ano'=>['label'=>'Ano','js'=>true,'active'=>true,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','js'=>true,'event'=>'','tam'=>3,'value'=>$df['ano']],
            'numero'=>['label'=>'Numero','js'=>true,'active'=>true,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','tam'=>3,'value'=>$df['numero']],
            'conta'=>['label'=>'Descrição da receita','js'=>true,'active'=>true,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','js'=>true,'event'=>'','tam'=>'12','value'=>$df['conta'],'value_text'=>@$arr_tipo_receita[$df['conta']]],
            // 'categoria'=>[
            //     'label'=>'Categoria',
            //     'active'=>true,
            //     'type'=>'select','arr_opc'=>$arr_opc_cat,'exibe_busca'=>'d-block',
            //     'event'=>'disabled',
            //     'js'=>true,
            //     //'event'=>'onchange=carregaMatricula($(this).val())',
            //     'tam'=>'6',
            //     'class'=>'',
            //     'value'=>@$arr_opc_cat[$df['conta']],
            // ],
            'valor'=>['label'=>'Valor de previsão*','active'=>true,'js'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos,'js'=>true,'validate'=>['required','string']],
            'valor_pago'=>['label'=>'Valor pago','js'=>true,'active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'','tam'=>$larg_campos],
            'data_pagamento'=>['label'=>'Data de pagamento*','js'=>true,'active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos,'validate'=>['required','string']],
            'descricao'=>['label'=>'Observação(Opcional)','js'=>true,'active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12','class_div'=>'','class'=>'','placeholder'=>__('Escreva seu conteúdo aqui..')],
        ];
        return $ret;
    }
    // public function campos($id=false){
    //     $dados = false;
    //     if($id){
    //         $dados = financeiro::Find($id);
    //         if($dados->count()>0){
    //             $dados = $dados->toArray();
    //         }
    //     }

    //     $hidden_editor = '';
    //     if($this->routa=='receitas'){
    //         $label0 = __('DADOS DO CLIENTE');
    //         $label1 = __('Cliente');
    //     }elseif($this->routa=='despesas'){
    //         $label0 = __('DADOS DO FORNECEDOR');
    //         $label1 = __('Fornecedor');
    //     }else{
    //         $label0 = __('DADOS');
    //         $label1 = '';

    //     }
    //     $larg_campos = 6;
    //     $rf = 'fornecedores';
    //     $userC = new UserController(['route'=>$rf]);
    //     $id_permision = $userC->id_permission_fornecedores();
    //     $arr_opc = Qlib::sql_array("SELECT id,name,email FROM users WHERE ativo='s' AND id_permission='$id_permision'",'name','id');
    //     $ret = [
    //         'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
    //         // 'post_type'=>['label'=>'tipo de post','active'=>false,'type'=>'hidden','exibe_busca'=>'d-none','event'=>'','tam'=>'2','value'=>$this->post_type],
    //         'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
    //         'html0'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h6>'.$label0.'</h6><hr class="mt-0 pb-0">'],
    //         'id_cliente'=>[
    //             'label'=>$label1,
    //             'active'=>true,
    //             'type'=>'selector',
    //             'data_selector'=>[
    //                 'campos'=>$userC->campos(),
    //                 'route_index'=>route($rf.'.index'),
    //                 'id_form'=>'frm-'.$rf,
    //                 'action'=>route($rf.'.store'),
    //                 'campo_id'=>'id',
    //                 'campo_bus'=>'name',
    //                 'label'=>$label1,
    //             ],'arr_opc'=>$arr_opc,'exibe_busca'=>'d-block',
    //             'event'=>'required',
    //             //'event'=>'onchange=carregaMatricula($(this).val())',
    //             'tam'=>'12',
    //             'class'=>'select2',
    //             'value'=>@$_GET['id_cliente'],
    //         ],
    //         'tipo'=>['label'=>'tipo','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','value'=>$this->routa,'event'=>'','tam'=>'2'],
    //         'html1'=>['label'=>'titulo','active'=>false,'type'=>'html_script','script'=>'<h5>Dados da Conta</h5><hr>'],
    //         'numero'=>['label'=>'Numero','active'=>true,'placeholder'=>'','type'=>'number','exibe_busca'=>'d-block','event'=>'','tam'=>$larg_campos],
    //         'valor'=>['label'=>'Valor*','active'=>true,'placeholder'=>'','type'=>'moeda','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos,'validate'=>['required','string']],
    //         'emissao'=>['label'=>'Data de Emissão*','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
    //         'vencimento'=>['label'=>'Data de vencimento*','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
    //         'descricao'=>['label'=>'Descrição','active'=>true,'placeholder'=>'Uma síntese do um post','type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
    //         // 'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
    //         // 'post_content'=>['label'=>'Conteudo','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>$hidden_editor,'tam'=>'12','class_div'=>'','class'=>'summernote','placeholder'=>__('Escreva seu conteúdo aqui..')],
    //         'pago'=>['label'=>'Pagar','active'=>true,'tab'=>$this->tab ,'campo'=>'pago' ,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'6','arr_opc'=>['s'=>'Pago','n'=>'A pagar'],'tab'=>'financeiro'],
    //         // 'vencimento'=>['label'=>'Data de vencimento*','active'=>true,'placeholder'=>'','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_campos],
    //     ];
    //     if(is_array($dados)){
    //         $ret['id_cliente']=[
    //                 'label'=>$label1,
    //                 'active'=>false,
    //                 'type'=>'html_vinculo',
    //                 'exibe_busca'=>'d-none',
    //                 'event'=>'',
    //                 'tam'=>'12',
    //                 'script'=>'',
    //                 'data_selector'=>[
    //                     'campos'=>$userC->campos(),
    //                     'route_index'=>route('fornecedores.index'),
    //                     'id_form'=>'frm-fornecedores',
    //                     // 'tipo'=>'array', // int para somente um ou array para vários
    //                     'tipo'=>'text', // int para somente um ou array para vários
    //                     'action'=>route('fornecedores.store'),
    //                     'campo_id'=>'id',
    //                     'campo_bus'=>'name',
    //                     'campo'=>'id_cliente',
    //                     'value'=>[],
    //                     'label'=>'Informações do lote',
    //                     'table'=>[
    //                         //'id'=>['label'=>'Id','type'=>'text'],
    //                         'name'=>['label'=>'Nome','type'=>'text', //campos que serão motands na tabela
    //                         'conf_sql'=>[
    //                             'tab'=>'users',
    //                             'campo_bus'=>'id',
    //                             'select'=>'name',
    //                             'param'=>['name','email'],
    //                             ]
    //                         ],
    //                         'email'=>['label'=>'Email','type'=>'text'], //campos que serão motands na tabela
    //                     ],
    //                     'tab' =>'users',
    //                     'placeholder' =>'Digite somente o nome do '.$label1.'...',
    //                     'janela'=>[
    //                         'url'=>route('fornecedores.create').'',
    //                         // 'param'=>['name','cnpj','email'],
    //                         'param'=>[],
    //                         'form-param'=>'',
    //                     ],
    //                     'salvar_primeiro' =>false,//exigir cadastro do vinculo antes de cadastrar este
    //                 ],
    //                 'script' => false,//'familias.loteamento', //script admicionar
    //         ];
    //     }
    //     return $ret;
    // }
    public function create()
    {
        $user = $this->user;
        $this->authorize('is_admin', $user);
        $title = 'Cadastrar '.$this->title;
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
        $defaultDate = '1900-01-01';
        $dados['pago'] = isset($dados['pago'])?$dados['pago']:'n';
        if($dados['pago'] == 's'){
            //caso o campo pago vem sim e não tem uma data de pagameto o sistema assume que o pagamento foi na data do vencimento importante para lançamentos futuros ou debito automatico
            $dados['data_pagamento'] = isset($dados['vencimento']) ? $dados['vencimento'] : $defaultDate;
        }else{
            $dados['data_pagamento'] = isset($dados['data_pagamento'])?$dados['data_pagamento']:$defaultDate;
        }
        $dados['emissao'] = isset($dados['emissao'])?$dados['emissao']:$defaultDate;
        $dados['autor'] = isset($dados['autor'])?$dados['autor']:Auth::id();
        //remover variaveis que não tenham um campo no banco de dados
        $dados = (new DefaultController())->sanitizeDados($dados);
        // dd($dados);
        $reg_id = financeiro::create($dados);
        if($reg_id){
            $color = 'success';
            $idCad = $reg_id;
            $exec = true;
            $route = $this->routa.'.index';
            //se é uma fatura mensal lista todas que tem o mesmo $id_fatura_fixa
            if(isset($dados['id_fatura_fixa']) && !empty($dados['id_fatura_fixa'])){

            }
        }else{
            $color = 'danger';
            $idCad = 0;
            $exec = false;

        }
        $ret = [
            'mens'=>$this->title.' cadastrada com sucesso!',
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
    public function edit($id)
    {
        $routa = $this->routa;
        $this->authorize('ler', $routa);
        $dados = financeiro::where('id',$id)->get()->toArray();
        if(isset($dados[0]) && !empty($dados[0])){
            $dados[0] = (array)$dados[0];
            // dd($dados[0]);
            $title = __('Editar cadastro de '.$this->routa);
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            $campos = $this->campos($id);
            if(isset($dados[0]['token'])){
                $listFiles = (new PostsController)->list_files($dados[0]['token']);
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-financeiro',
                'route'=>$this->routa,
                'id'=>$id,
                'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg',
                'tam_col1'=>'col-md-6',
                'tam_col2'=>'col-md-6',
            ];

            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'listFilesCode'=>Qlib::encodeArray($listFiles),
                'campos'=>$campos,
                'campos_mensais'=>$this->campos_mensais($id),
                'campos_mensais_code'=>Qlib::encodeArray($this->campos_mensais($id)),
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
        $userLogadon = Auth::id();
        $data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $data = (new DefaultController())->sanitizeDados($dados);
        $data['pago'] = isset($data['pago'])?$data['pago']:'n';
        $atualizar=false;
        $d_ordem = isset($data['ordem'])?$data['ordem']:false;
        unset($data['file'],$data['ordem']);

        if(!empty($data)){
            $atualizar = financeiro::where('id',$id)->update($data);
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
            ];
            if(is_array($d_ordem)){
                //atualizar ordem dos arquivos
                $ret['order_update'] = (new AttachmentsController)->order_update($d_ordem,'uploads');
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
}
