<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\admin\ContratoController;
use App\Http\Requests\StoreClientesRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateClientesRequest;
use App\Models\_upload;
use App\Models\Contrato;
use App\Models\User;
use stdClass;
use App\Qlib\Qlib;
use App\Rules\FullName;
use App\Rules\RightCpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use DataTables;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $url;
    public $tab;
    public $id_permission;
    public $title;
    public function __construct($config=[])
    {
        $this->middleware('auth');
        $user = Auth::user();
        $routeName = isset($config['route']) ? $config['route'] : false;
        $sec2 = request()->segment(2);
        $routeName = $routeName ? $routeName : $sec2;
        $this->user = $user;
        $this->id_permission = 5;
        $this->routa = $routeName;
        $this->url = 'users';
        $this->label = 'Usuários';
        $this->view = 'clientes';
        $this->tab = 'users';
        $this->title = __('Cadastro de usuário');
        if($routeName == 'fornecedores'){
            $this->title = __('Cadastro de fornecedores');
        }
    }
    /**gera um id de pemissão automatico para o caso de fornecedores */
    public function id_permission_fornecedores(){
        return Qlib::qoption('id_permission_fornecedores');
    }
    public function queryUsers($get=false,$config=false)
    {
        $ret = false;
        $get = isset($_GET) ? $_GET:[];
        $ano = date('Y');
        $mes = date('m');
        $config = [
            'limit'=>isset($get['limit']) ? $get['limit']: 50,
            'order'=>isset($get['order']) ? $get['order']: 'desc',
        ];
        // $logado = Auth::user();


        if(isset($get['term'])){
            //Autocomplete
            if(isset($get['a-campo'])&&!empty(['a-campo'])){
                $get['filter'][$get['a-campo']] = $get['term'];
            }else{
                $get['filter']['nome'] = $get['term'];
            }
            if(isset($get['id_permission']) && !empty($get['id_permission'])){
                $sql = "SELECT * FROM users WHERE (name LIKE '%".$get['term']."%') AND id_permission=".$this->id_permission." AND ".Qlib::compleDelete();
            }else{
                // $sql = "SELECT l.*,q.name quadra_valor FROM users as l
                // JOIN quadras as q ON q.id=l.quadra
                // WHERE (l.name LIKE '%".$get['term']."%' OR q.name LIKE '%".$get['term']."%' ) AND ".Qlib::compleDelete('l');
                $compleSql = false;

                if($this->routa == 'fornecedores'){
                    $compleSql = "AND id_permission = '".$this->id_permission_fornecedores() ."'";
                }
                $sql = "SELECT * FROM users WHERE name LIKE '%".$get['term']."%' $compleSql AND ".Qlib::compleDelete();

            }
            $user = DB::select($sql);
            $ret['user'] = $user;
            return $ret;
        }else{
            if($this->routa == 'fornecedores'){
                $user =  User::where('id_permission','=',Qlib::qoption('id_permission_fornecedores'))->orderBy('id',$config['order']);
            }else{
                if(Qlib::is_partner()){
                    //quando for um parceiro ele visualiza apenas os seus clientes
                    $compleSql = false;
                    $user =  User::join('contratos','contratos.id_cliente','=','users.id')
                    ->select('users.*','contratos.inicio','contratos.fim')
                    ->where('users.id_permission','>=',$this->id_permission)
                    ->where('contratos.autor','=',Auth::id())
                    ->orderBy('users.id',$config['order']);
                }else{

                    $user =  User::join('contratos','contratos.id_cliente','=','users.id')
                    ->select('users.*','contratos.inicio','contratos.fim')
                    ->where('users.id_permission','>=',$this->id_permission)->orderBy('users.id',$config['order']);
                }
            }
            //$user =  DB::table('users')->where('ativo','s')->orderBy('id',$config['order']);
        }
        $users = new stdClass;
        $campos = isset($_SESSION['campos_users_exibe']) ? $_SESSION['campos_users_exibe'] : $this->campos();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $user->where('users.'.$key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $user->where('users.'.$key,'LIKE','%'. $value. '%');
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
                }
                $fm = $user;
                if($config['limit']=='todos'){
                    $user = $user->get();
                }else{
                    $user = $user->paginate($config['limit']);
                }
        }else{
            $fm = $user;
            if($config['limit']=='todos'){
                $user = $user->get();
            }else{
                $user = $user->paginate($config['limit']);
            }
        }
        $users->todos = $fm->count();
        $users->esteMes = $fm->whereYear('users.created_at', '=', $ano)->whereMonth('users.created_at','=',$mes)->get()->count();
        $users->ativos = $fm->where('users.'.'ativo','=','s')->get()->count();
        $users->inativos = $fm->where('users.'.'ativo','=','n')->get()->count();
        $ret['user'] = $user;
        $ret['user_totais'] = $users;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$users->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$users->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$users->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$users->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    /**
     * Retorna o status de um contrato
     */
    public function get_status_contrato($id){
        return Qlib::get_usermeta($id,(new ContratoController)->campo_meta3,true);
    }
    /**
     * Retorna o numero de operação
     */
    public function get_numero_operacao($id){
        return Qlib::get_usermeta($id,(new ContratoController)->campo_meta2,true);
    }
    /**
     * Retorna o numero de operação
     */
    public function get_contrato_sulamerica($id){
        return Qlib::get_usermeta($id,(new ContratoController)->campo_meta1,true);
    }
    // public function get_certificado($id_cliente=null,$json_contrato=null){
    //     if($id_cliente && !$json_contrato){
    //         $json_contrato =
    //     }
    // }
    public function campos($dados=false,$local='index'){
        $autor_id = Auth::id();
        // $permission = new admin\UserPermissions($user);
        $status = false;
        if(isset($dados['tipo_pessoa']) && $dados['tipo_pessoa'] && !isset($_GET['tipo'])){
            $_GET['tipo'] = $dados['tipo_pessoa'];
        }
        if($this->routa=='fornecedores'){
            $_GET['tipo'] = isset($_GET['tipo'])?$_GET['tipo']:'pj';
        }
        if(is_object($dados) || is_array($dados)){
            $ac='alt';
        }else{
            $ac='cad';
        }
        $sec = isset($_GET['tipo'])?$_GET['tipo']:'pf';
        if($sec=='pf'){
            $lab_nome = 'Nome completo *';
            $lab_cpf = 'CPF *';
            $displayPf = '';
            $displayPj = 'd-none';
        }elseif($sec=='pj'){
            $lab_nome = 'Nome do responsável *';
            $lab_cpf = 'CPF do responsável*';
            $displayPf = 'd-none';
            $displayPj = '';
        }else{
            $lab_nome = 'Nome completo *';
            $lab_cpf = 'CPF *';
            $displayPf = '';
            $displayPj = 'd-none';
        }
        // $hidden_editor = '';
        $info_obs = '<div class="alert alert-info alert-dismissable" role="alert"><button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button><i class="fa fa-info-circle"></i>&nbsp;<span class="sw_lato_black">Obs</span>: campos com asterisco (<i class="swfa fas fa-asterisk cad_asterisco" aria-hidden="true"></i>) são obrigatórios.</div>';
        if(isset($dados['id']) && ($id_cliente=$dados['id'])){
            $status = $this->get_status_contrato($id_cliente);
            if($status == 'Reativando'){
                $alerta_processo_reativacao = Qlib::qoption('alerta_processo_reativacao') ? Qlib::qoption('alerta_processo_reativacao') : '';
                if($alerta_processo_reativacao){
                    $alerta_processo_reativacao = __($alerta_processo_reativacao);
                }
                $info_obs .= '<div class="alert alert-warning alert-dismissable" role="alert"><button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>'.$alerta_processo_reativacao.'</div>';

            }
        }
        $id_produto_padrao = Qlib::qoption('produtoParceiro') ? Qlib::qoption('produtoParceiro') : '10232'; //unico produto padrão que pode ser contratado pela sulameriaca
        $ret = [
            'sep2'=>['label'=>'info','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>$info_obs,'script_show'=>''],
            'id'=>['label'=>'Id','js'=>true,'active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'config[id_produto]'=>['label'=>'Id do produto','js'=>false,'cp_busca'=>'config][rg','active'=>false,'type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','value'=>$id_produto_padrao],
            'tipo_pessoa'=>[
                'label'=>'Tipo de Pessoa',
                'active'=>false,
                'js'=>true,
                'type'=>'radio_btn',
                'arr_opc'=>['pf'=>'Pessoa Física','pj'=>'Pessoa Jurídica'],
                'exibe_busca'=>'d-block',
                'event'=>'onclick=selectTipoUser(this.value)',
                'tam'=>'12',
                'value'=>$sec,
                'class'=>'btn btn-outline-secondary',
            ],
            'sep0'=>['label'=>'informações','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Informe os dados').'</h4><hr>','script_show'=>''],
            'token'=>['label'=>'token','js'=>true,'active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_permission'=>['label'=>'token','js'=>true,'active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','value'=>5,'tam'=>'2'],
            'autor'=>['label'=>'autor','js'=>true,'active'=>false,'type'=>'hidden','value'=>$autor_id,'exibe_busca'=>'d-block','value'=>5,'tam'=>'2'],

            'name'=>['label'=>$lab_nome,'js'=>true,'active'=>true,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            'cpf'=>['label'=>$lab_cpf,'active'=>true,'js'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cpf required','tam'=>'3'],
            'cnpj'=>['label'=>'CNPJ *','active'=>false,'js'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cnpj required','tam'=>'4','class_div'=>'div-pj '.$displayPj],
            'razao'=>['label'=>'Razão social *','js'=>true,'active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[nome_fantasia]'=>['label'=>'Nome fantasia','js'=>true,'active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[celular]'=>['label'=>'Telefone celular','active'=>true,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][celular'],
            'config[telefone_residencial]'=>['label'=>'Telefone residencial','active'=>false,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','class_div'=>'div-pf '.$displayPf,'cp_busca'=>'config][telefone_residencial'],
            'config[telefone_comercial]'=>['label'=>'Telefone comercial','active'=>false,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][telefone_comercial'],
            'config[rg]'=>['label'=>'RG','active'=>false,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'','cp_busca'=>'config][rg','class_div'=>'div-pf '.$displayPf],
            'config[nascimento]'=>['label'=>'Data de nascimento*','active'=>false,'type'=>'date','tam'=>'4','exibe_busca'=>'d-block','event'=>'required','cp_busca'=>'config][nascimento','class_div'=>'div-pf '.$displayPf],
            'genero'=>[
                'label'=>'Sexo',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::lib_sexo(),
                'event'=>'',
                'tam'=>'4',
                'exibe_busca'=>true,
                'option_select'=>false,
                // 'class'=>'select2',
                'class_div'=>'div-pf '.$displayPf,
            ],
            // 'config[escolaridade]'=>[
            //     'label'=>'Escolaridade',
            //     'active'=>false,
            //     'type'=>'select',
            //     'arr_opc'=>Qlib::lib_escolaridades(),'exibe_busca'=>'d-block',
            //     'event'=>'',
            //     'tam'=>'6',
            //     'class'=>'select2',
            //     'cp_busca'=>'config][escolaridade','class_div'=>'div-pf '.$displayPf,
            // ],
            // 'config[profissao]'=>[
            //     'label'=>'Profissão',
            //     'active'=>false,
            //     'type'=>'select',
            //     'arr_opc'=>Qlib::lib_profissao(),'exibe_busca'=>'d-block',
            //     'event'=>'',
            //     'tam'=>'6',
            //     'class'=>'select2',
            //     'cp_busca'=>'config][profissao','class_div'=>'div-pf '.$displayPf,
            // ],
            'config[tipo_pj]'=>[
                'label'=>'Tipo de Pessoa Jurídica',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='tipo_pj'",'nome','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'4',
                'class'=>'select2',
                'cp_busca'=>'config][tipo_pj','class_div'=>'div-pj '.$displayPj,
            ],
            'sep1'=>['label'=>'Endereço','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr>','script_show'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr>'],
            'config[cep]'=>['label'=>'CEP','active'=>false,'placeholder'=>'','type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cep onchange=buscaCep1_0(this.value)','tam'=>'3','title'=>'Se informar o CEP o endereço será preenchido automaticamente','cp_busca'=>'config][cep'],
            'config[endereco]'=>['label'=>'Endereço','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'endereco=cep q-inp="endereco"','tam'=>'7','cp_busca'=>'config][endereco'],
            'config[numero]'=>['label'=>'Numero','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'numero=cep','tam'=>'2','cp_busca'=>'config][numero'],
            'config[complemento]'=>['label'=>'Complemento','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'4','cp_busca'=>'config][complemento'],
            'config[bairro]'=>['label'=>'bairro','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'bairro=cep q-inp="bairro"','tam'=>'3','cp_busca'=>'config][bairro'],
            'config[cidade]'=>['label'=>'Cidade','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'cidade=cep q-inp="cidade"','tam'=>'3','cp_busca'=>'config][cidade'],
            'config[uf]'=>['label'=>'UF*','active'=>false,'js'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'2','cp_busca'=>'config][uf'],
            //'foto_perfil'=>['label'=>'Foto','active'=>false,'js'=>false,'placeholder'=>'','type'=>'file','exibe_busca'=>'d-none','event'=>'','tam'=>'12'],
            'info_contrato'=>['label'=>'contrato','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center bg-secondary">'.__('Contrato Sulamerica').'</h4><hr>','script_show'=>'<h4 class="text-center">'.__('Contrato').'</h4><hr>'],
            // 'config[token]'=>['label'=>'token','js'=>true,'active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'config[inicioVigencia]'=>['label'=>'Início Vigência*','active'=>true,'type'=>'date','tam'=>'3','exibe_busca'=>'d-block','event'=>'required onchange=calculaFim(this.value)','cp_busca'=>'config][inicioVigencia','class_div'=>''],
            'config[fimVigencia]'=>['label'=>'Fim Vigência*','active'=>true,'type'=>'date','tam'=>'3','exibe_busca'=>'d-block','event'=>'required','cp_busca'=>'config][fimVigencia','class_div'=>''],
            // 'config[premioSeguro]'=>['label'=>'Valor','title'=>'Valor do premio' ,'active'=>false,'type'=>'hidden','tam'=>'2','exibe_busca'=>'d-block','event'=>'required','cp_busca'=>'config][premioSeguro','class_div'=>''],
            'config[numCertificado]'=>['label'=>'C.','active'=>false,'type'=>'hidden_text','tam'=>'2','exibe_busca'=>'d-block','event'=>'','cp_busca'=>'config][numCertificado','class_div'=>''],
            'config[numOperacao]'=>['label'=>'N.°','active'=>false,'type'=>'hidden_text','tam'=>'2','exibe_busca'=>'d-block','event'=>'','cp_busca'=>'config][numOperacao','class_div'=>''],
            'config[status_contrato]'=>['label'=>'Status','active'=>true,'type'=>'hidden_text','tam'=>'2','exibe_busca'=>'d-block','event'=>'required','cp_busca'=>'config][status_contrato','class_div'=>''],

            'painelsulamerica'=>['label'=>'Peinel','active'=>false,'type'=>'html','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'clientes.painel_sulamerica','dados'=>[
                'dados'=>$dados,
                'ac'=>$ac,
            ],'script_show'=>'clientes.painel_sulamerica'],
            'preferencias_label' =>['label'=>'Preferencias','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'','script_show'=>''],
            'ativo'=>['label'=>'Ativar contrato','js'=>false,'tab'=>$this->tab,'active'=>false,'type'=>'hidden','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            // 'preferencias[newslatter]'=>['label'=>'Deseja receber e-mails com as novidades','active'=>false,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-none','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não'],'cp_busca'=>'preferencias][newslatter'],
        ];
        if(Qlib::isAdmin(2)){
            // $ret['preferencias_label'] = ['label'=>'Preferencias','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Preferências').'</h4><hr>','script_show'=>'<h4 class="text-center">'.__('Preferências').'</h4><hr>'];
            // $ret['ativo'] = ['label'=>'Visualizar cadastro','js'=>true,'tab'=>$this->tab,'active'=>true,'type'=>'chave_checkbox','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']];
            if($local=='edit'){
                if($status!='Aprovado' && $status!='Cancelado'){
                    // $status_contrato = $this->get_status_contrato($id_cliente);
                    $ret['config[status_contrato]']['type'] = 'select';
                    $ret['config[status_contrato]']['arr_opc'] = Qlib::sql_array("SELECT value,nome FROM tags WHERE ativo='s' AND pai ='status_contratos'",'nome','value');
                    $ret['config[status_contrato]']['option_select'] = true;
                    $ret['config[status_contrato]']['event'] = '';
                }
            }
            //Libera essa opção se o parceiro estiver ativo
            if(Qlib::is_partner_active()){
                $ret['autor'] = [
                    'label'=>'Proprietário',
                    'active'=>true,
                    'type'=>'select',
                    // 'arr_opc'=>Qlib::sql_array("SELECT id,name FROM users WHERE ativo='s' AND id_permission='".Qlib::qoption('partner_permission_id')."'",'name','id'),'exibe_busca'=>'d-block',
                    'arr_opc'=>Qlib::sql_array("SELECT id,name FROM users WHERE ativo='s' AND id_permission!='1' AND id_permission!='".Qlib::qoption('id_permission_clientes')."'",'name','id'),'exibe_busca'=>'d-block',
                    'event'=>'',
                    'tam'=>'12',
                    'class'=>'select2','class_div'=>' ',
                ];
            }
        }else{
            if(Qlib::is_partner_active()){
                $ret['autor']['value'] = Auth::id();
            }
        }
        // dd($ret);
        //Reformular campos para parceiros
        if(Qlib::is_partner()){
            $ret['config[id_produto]']['type'] = 'hidden';
        }
        if($this->routa == 'fornecedores'){
            $ret['email']['tam'] = 9;
            $ret['id_permission'] = ['label'=>'id_permission','js'=>true,'value'=>$this->id_permission_fornecedores() ,'active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'];

        }
        if($local=='create' || $local=='show' || $local=='edit'){
            //Importante para exibição das preferencias.
            if(($local=='edit' || $local == 'show') && isset($dados['id'])){
                $cc = new ContratoController; //CotratoController
                $campo_meta1 = $cc->campo_meta1;
                $campo_meta2 = $cc->campo_meta2;
                $campo_meta3 = $cc->campo_meta3;
                $campo_meta5 = $cc->campo_meta5;
                $contrato = Qlib::get_usermeta($dados['id'],$campo_meta1,true);
                $numOperacao = Qlib::get_usermeta($dados['id'],$campo_meta2,true);
                $status_aprovado = Qlib::get_usermeta($dados['id'],$campo_meta3,true);
                $dados['autor'] = $dados['autor'] ? $dados['autor'] : Auth::id();
                $ret['autor']['value'] = $dados['autor'];
                if(Qlib::is_partner()){
                    $ret['autor']['type'] = 'hidden';
                }

                if($numOperacao){
                    if($contrato){
                        $id_contrato = $cc->get_certificado($dados['id']);
                        $ret['config['.$campo_meta5.']']['value'] = $id_contrato;
                    }
                    $ret['config['.$campo_meta2.']']['value'] = $numOperacao;
                    $ret['config['.$campo_meta2.']']['value'] = $numOperacao;
                    $ret['config['.$campo_meta3.']']['value'] = $status_aprovado;
                    $ret['config[inicioVigencia]']['type'] = 'hidden_text';
                    $ret['config[fimVigencia]']['type'] = 'hidden_text';
                    if(isset($dados['config']['inicioVigencia']) && ($inicioVigencia = $dados['config']['inicioVigencia'])){
                        $ret['config[inicioVigencia]']['value_text'] = Qlib::dataExibe($inicioVigencia);
                    }
                    if(isset($dados['config']['fimVigencia']) && ($fimVigencia = $dados['config']['fimVigencia'])){
                        $ret['config[fimVigencia]']['value_text'] = Qlib::dataExibe($fimVigencia);
                    }
                    $ret['config[premioSeguro]']['type'] = 'hidden_text';
                    //carregar os dados para motar o painel que servirá visutalização de status e para cancelamento
                    $ret['painelsulamerica']['dados'][$campo_meta2] = $numOperacao;
                    $ret['painelsulamerica']['dados'][$campo_meta3] = $status_aprovado;
                    // dump($status_aprovado,$ret);

                }
            }
            if(isset($dados['preferencias'])){
                foreach ($dados['preferencias'] as $k => $v) {
                   // $ret['preferencias['.$k.']']['value'] = $v;
                    if($v=='s'){
                        $ret['preferencias['.$k.']']['checked'] = $v;
                    }
                }
            }
            if($local=='show'){
                unset($ret['password']);
            }elseif($local=='create' || $local=='edit'){
                $ret['tipo_pessoa']['label'] = '';
            }
        }
        return $ret;
    }
    public function campos_show($dados=false){
        $user = Auth::user();
        $permission = new admin\UserPermissions($user);
        if(isset($dados['tipo_pessoa']) && $dados['tipo_pessoa']){
            $_GET['tipo'] = $dados['tipo_pessoa'];
        }
        $sec = isset($sec)?$sec:request()->segment(3);
        $sec = isset($_GET['tipo'])?$_GET['tipo']:'pf';
        if($sec=='pf'){
            $lab_nome = 'Nome completo *';
            $lab_cpf = 'CPF *';
            $displayPf = '';
            $displayPj = 'd-none';
        }elseif($sec=='pj'){
            $lab_nome = 'Nome do responsável *';
            $lab_cpf = 'CPF do responsável*';
            $displayPf = 'd-none';
            $displayPj = '';
        }else{
            $lab_nome = 'Nome completo *';
            $lab_cpf = 'CPF *';
            $displayPf = '';
            $displayPj = 'd-none';
        }
        $hidden_editor = '';
        $info_obs = '<div class="alert alert-info alert-dismissable" role="alert"><button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button><i class="fa fa-info-circle"></i>&nbsp;<span class="sw_lato_black">Obs</span>: campos com asterisco (<i class="swfa fas fa-asterisk cad_asterisco" aria-hidden="true"></i>) são obrigatórios.</div>';
        $ret = [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_permission'=>[
                'label'=>'Permissão*',
                'active'=>true,
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$permission->campos(),
                    'route_index'=>route('permissions.index'),
                    'id_form'=>'frm-permission',
                    'action'=>route('permissions.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Permissão',
                ],'arr_opc'=>Qlib::sql_array("SELECT id,name FROM permissions WHERE active='s' AND id >='".$user->id_permission."'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'value'=>@$_GET['id_permission'],
            ],
            'nome'=>['label'=>'Nome completo','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'6'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'email'=>['label'=>'Email','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'4'],
            'password'=>['label'=>'Senha','active'=>false,'type'=>'password','value'=>'','exibe_busca'=>'d-none','event'=>'','tam'=>'6'],
            'ativo'=>['label'=>'Liberar','active'=>true,'type'=>'chave_checkbox','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'2','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            //'email'=>['label'=>'Observação','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
        ];
        return $ret;
    }
    public function index(User $user)
    {
        // $this->authorize('is_admin', $user);
        $this->authorize('is_user_back', $user);
        $ajax = isset($_GET['ajax'])?$_GET['ajax']:'n';
        $title = 'Usuários Cadastrados';
        $titulo = $title;
        $queryUsers = $this->queryUsers($_GET);
        $queryUsers['config']['exibe'] = 'html';
        $routa = $this->routa;
        $view = $this->view;
        if(isset($_GET['term'])){
            $ret = false;
            $ajax = 's';
            // $campos = $this->campos();
            if($queryUsers['user']){
               //$ret = $queryUsers['user'];
                if(isset($_GET['id_permission']) && empty($_GET['id_permission'])){
                    $ret[0]['value'] = 'Por favor selecione a Permissão! ';
                    $ret[0]['id'] = '';
                }else{
                    foreach ($queryUsers['user'] as $key => $v) {
                        $bairro = false;
                        if(isset($v->config)){
                            $v->config = Qlib::lib_json_array($v->config);
                            if(isset($v->config['celular'])){
                                $v->celular = $v->config['celular'];
                            }
                        }
                        if($id_permission = $v->id_permission){
                            $permission = Qlib::buscaValorDb([
                                'tab'=>'permissions',
                                'campo_bus'=>'id',
                                'valor'=>$id_permission,
                                'select'=>'name',
                            ]);
                            $ret[$key]['dados'] = $v;
                        }
                        $nome_quadra = false;
                        $ret[$key]['value'] = ' Usuario: '.$v->name.' | E-mail: '.$v->email;
                        if($this->routa=='fornecedores'){
                            $ret[$key]['value'] .= ' CNPJ: '.$v->cnpj;
                        }
                    }
                }
            }else{
                $ret[0]['value'] = 'Usuario não encontrado. Cadastrar agora?';
                $ret[0]['id'] = 'cad';
            }
        }else{
            $ret = [
                'dados'=>$queryUsers['user'],
                'title'=>$title,
                'titulo'=>$titulo,
                'campos_tabela'=>$queryUsers['campos'],
                'user_totais'=>$queryUsers['user_totais'],
                'titulo_tabela'=>$queryUsers['tituloTabela'],
                'arr_titulo'=>$queryUsers['arr_titulo'],
                'config'=>$queryUsers['config'],
                'routa'=>$routa,
                'view'=>$view,
                'url'=>$this->url,
                'i'=>0,
            ];
        }
        if($ajax=='s'){
            return response()->json($ret);
        }else{
            return view($this->view.'.index',$ret);
        }
    }
    public function create(User $user)
    {
        // $this->authorize('is_admin', $user);
         $this->authorize('is_user_back', $user);
        $title = $this->title;
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-users',
            'route'=>$this->routa,
            'url'=>$this->url,
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos(false,'create');
        return view($this->view.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);
    }
    public function store(StoreClientesRequest $request)
    {
        // $validatedData = $request->validate([
        //     'name' => ['required','string',new FullName],
        //     // 'email' => ['required','string','unique:users'],
        //     'cpf'   =>[new RightCpf,'required','unique:users']
        //     ],[
        //         'name.required'=>__('O nome é obrigatório'),
        //         'name.string'=>__('É necessário conter letras no nome'),
        //         // 'email.unique'=>__('E-mail já cadastrado'),
        //         'cpf.unique'=>__('CPF já cadastrado'),
        //     ]);
        $dados = $request->all();
        return $this->salvar_clientes($dados);
    }
    /**
     * Para salver os clientes no banco de dados
     */
    public function salvar_clientes($dados=[],$api=false){
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        $dados['autor'] = isset($dados['autor'])?$dados['autor']:Auth::id();
        //$dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        //inicio gerenciar a pemissão automatica
        if($this->routa == 'fornecedores' && is_null($dados['id_permission'])){
            $dados['id_permission'] =  $this->id_permission_fornecedores();
        }
        //fim gerenciar a pemissão automatica
        if(isset($dados['password']) && !empty($dados['password'])){
            $dados['password'] = Hash::make($dados['password']);
        }else{
            if(empty($dados['password'])){
                unset($dados['password']);
            }
        }

        $salvar = User::create($dados);
        $dados['id'] = $salvar->id;
        $ret = [
            'mens'=>$this->label.' cadastrada com sucesso!',
            'color'=>'success',
            'idCad'=>$salvar->id,
            'exec'=>true,
            'dados'=>$dados
        ];
        //Adicionar um contrato caso o cliente foi salvo com sucesso
        if($ret['idCad'] && ($id_cliente = $ret['idCad']) && isset($dados['config']['id_produto'])){
            $premioSeguro = Qlib::qoption('premioSeguro') ? Qlib::qoption('premioSeguro') : 3.96;
            $produtoParceiro = Qlib::qoption('produtoParceiro') ? Qlib::qoption('produtoParceiro') : 10232;
            $dados['config']['token'] = isset($dados['token']) ? $dados['token'] : false;
            $dados['config']['id_produto'] = isset($dados['id_produto']) ? $dados['id_produto'] : $produtoParceiro;
            $dados['config']['premioSeguro'] = $premioSeguro ? $premioSeguro : false;
            $dados['config']['nome_fantasia'] = isset($dados['nome_fantasia']) ? $dados['nome_fantasia'] : null;
            $dados['config']['telefone_residencial'] = isset($dados['telefone_residencial']) ? $dados['telefone_residencial'] : null;
            $dados['config']['telefone_comercial'] = isset($dados['telefone_comercial']) ? $dados['telefone_comercial'] : null;
            $dados['config']['celular'] = isset($dados['celular']) ? $dados['celular'] : null;
            $ret = $this->store_contratos($id_cliente,$dados['config']);
        }
        if($api){
            return response()->json($ret);
        }
        if($ajax=='s'){
            $route = $this->routa.'.index';
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);
            return response()->json($ret);
        }else{
            $route = $this->routa.'.index';
            return redirect()->route($route,$ret);
        }
    }
    /**
     * MEtodo para salver os contratos atraves dos dados de formularo de cadastro do cliente
     * @param string $id_cliente
     * @param array $dados dados do formulario
     *
     */
    public function store_contratos($id_cliente=false,$dados=[],$ac='cad'){
        $id_produto = isset($dados['id_produto']) ? $dados['id_produto'] : false;
        $inicio = isset($dados['inicioVigencia']) ? $dados['inicioVigencia'] : false;
        // $ac = isset($ac) ? $ac : 'cad';
        $fim = isset($dados['fimVigencia']) ? $dados['fimVigencia'] : false;
        if(!$id_cliente){
            return ['exec'=>false,'mens'=>'Cliente não informado inválido','color'=>'danger'];
        }
        if(!$id_produto){
            return ['exec'=>false,'mens'=>'Produto inválido','color'=>'danger'];
        }
        $autor = Qlib::buscaValorDb0('users','id',$id_cliente,'autor');
        $autor = $autor ? $autor : Auth::id();
        $planoPadrao = Qlib::qoption('planoPadrao') ? Qlib::qoption('planoPadrao') : 2;
        $premioSeguro = Qlib::qoption('premioSeguro') ? Qlib::qoption('premioSeguro') : 3.96;
        $config = [
            'premioSeguro'=>$premioSeguro,
        ];
        $dsalv = [
            'id_cliente'=>$id_cliente,
            'id_produto'=>$id_produto,
            'id_plano'=>$planoPadrao,
            'inicio'=>$inicio,
            'config'=>Qlib::lib_array_json($config),
            'autor'=>$autor,
            'fim'=>$fim,
        ];
        if($ac=='cad'){
            $dsalv['token'] = isset($dados['token']) ? $dados['token'] : uniqid();
            $dsalv['created_at'] = Qlib::dataBanco();
        }else{
            $dsalv['updated_at'] = Qlib::dataBanco();
        }
        $ret = Qlib::update_tab('contratos',$dsalv,"WHERE id_cliente='$id_cliente' AND id_produto='$id_produto'",true);
        if(isset($ret['exec'])){
            // $token_contrato = isset($ret['dados']['token']) ? $ret['dados']['token'] : false;
            $token_contrato = $this->get_token_by_id(@$ret['idCad']);
            //aprveita para integração com a sulaverica
            if($token_contrato){
                // $ret['sulamerica'] = (new ContratoController)->sulamerica_contratar($token_contrato);
                $sulamerica_contratar = (new ContratoController)->sulamerica_contratar($token_contrato);
                if(isset($sulamerica_contratar['exec'])){
                    //é por que teve interação com a API de integração nesse caso deve retornar o status da api
                    $ret = $sulamerica_contratar;
                    $ret['status_contrato'] = $this->get_status_contrato($id_cliente);
                    $ret['numero_operacao'] = $this->get_numero_operacao($id_cliente);
                // }else{
                //     if(isset($sulamerica_contratar['retorno']) && $sulamerica_contratar['retorno'] == 65){
                //         $status_contrato = $this->get_status_contrato($id_cliente);
                //         if(!$status_contrato){
                //             $status = 'Duplicidade';
                //             $ret['status_contrato'] = (new ContratoController)->status_update($token_contrato,$status,$ret);
                //         }
                //     }
                }
            }
        }
        return $ret;
    }
    public function get_token_by_id($id){
        return Qlib::buscaValorDb0('contratos','id',$id,'token');
    }
    public function get_id_by_token($token){
        return Qlib::buscaValorDb0('contratos','token',$token,'id');
    }
    public function show($id)
    {
        //$id = $this->user;
        $dados = User::where('id',$id)->get();
        $routa = 'users';
        $this->authorize('ler', $this->url);

        if(!empty($dados)){
            $title = 'Editar Cadastro de users';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'url'=>$this->url,
                'id'=>$id,
            ];
            $dcampo = $dados[0];
            $campos = $this->campos($dcampo,'show');
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];

            return view($routa.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }
    public function perfilShow()
    {
        $d = Auth::user();
        $id = $d['id'];
        $dados[0] = $d;
        $routa = 'users';
        $this->url = 'sistema';
        $this->authorize('ler', $this->url);

        if(!empty($dados)){
            $title = 'Perfil';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'url'=>$this->url,
                'id'=>$id,
            ];
            $dcampo = $dados[0];
            $campos = $this->campos($dcampo,'show');
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];
            return view($routa.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }

    public function edit($user)
    {
        $id = $user;
        $dados = User::where('id',$id)->get();
        $routa = $this->routa;//'users';
        $view = $this->view;//'users';
        // $this->authorize('is_admin', $user);
        $this->authorize('is_user_back', $user);

        if(!empty($dados)){
            $title = 'Editar Cadastro de users';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                // 'status_contrato'=>$this->get_status_contrato($id),
                'url'=>$this->url,
                'id'=>$id,
            ];
            $dcampo = $dados[0];
            $campos = $this->campos($dcampo,'edit');
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];

            return view($view.'.createedit',$ret);
            // return view('admin.padrao.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }
    public function perfilEdit(Request $request)
    {
        $d = Auth::user();
        $id = $d['id'];
        $dados = User::where('id',$id)->get();
        $routa = $this->routa;//'users';
        $view = $this->view;//'users';
        // $this->authorize('is_admin', $routa);
         $this->authorize('is_user_back', $routa);

        if(!empty($dados)){
            $title = 'Editar Cadastro de users';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'route_update'=>'users',
                'url'=>$this->url,
                'id'=>$id,
            ];
            $dcampo = $dados[0];
            $campos = $this->campos($dcampo,'edit');
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];
            return view($view.'.createedit',$ret);
            // return view('admin.padrao.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }
    /**
     * Integrar ou conectar os dados que vem de um formulario com a função de salvamento no bando de daos
     *
     */
    public function update(UpdateClientesRequest $request, $id){
        // $validatedData = $request->validate([
        //     'name' => ['required',new FullName],
        //     'cpf'   =>[new RightCpf,'unique:users,cpf,'.$id],
        // ]);
        $dados = $request->all();
        return $this->atualizar_clientes($dados,$id);
    }
    /**
     * Gerenciar a taulização dos cadastro do bando de dados
     */
    public function atualizar_clientes($dados,$id=null,$api=false)
    {

        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        foreach ($dados as $key => $value) {
            if($key!='_method'&&$key!='_token'&&$key!='ac'&&$key!='ajax'){
                if($key=='data_batismo' || $key=='data_nasci'){
                    if($value=='0000-00-00' || $value=='00/00/0000'){
                    }else{
                        $data[$key] = Qlib::dtBanco($value);
                    }
                }elseif($key=='password'){
                    $data[$key] = Hash::make($value);
                }else{
                    $data[$key] = $value;
                }
            }
        }
        $userLogadon = Auth::id();
        $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        if(Qlib::is_partner_active()){
            $data['autor'] = isset($data['autor'])?$data['autor']:0;
        }else{
            $data['autor'] = isset($data['autor'])?$data['autor']:$userLogadon;
        }
        $data['preferencias']['newslatter'] = isset($data['preferencias']['newslatter'])?$data['preferencias']['newslatter']:'n';
        // $data['autor'] = $userLogadon;
        $dados_config = [];
        if(isset($dados['config'])){
            $dados_config = $dados['config'];
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(empty($data['passaword'])){
            unset($data['passaword']);
        }
        // dd($data,$id);
        // return $id;
        if(!empty($data)){
            $atualizar=User::where('id',$id)->update($data);
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                'return'=>$route,
            ];
            //Adicionar um contrato caso o cliente foi salvo com sucesso
            if($ret['idCad'] && ($id_cliente = $ret['idCad'])){
                $ret = $this->store_contratos($id_cliente,$dados_config,'alt');
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
        if($api){
            return response()->json($ret);
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

        $ret = $this->delete_all($id);
        return $ret;
    }
    /**
     * Metodo para deletetar permantente o registro de cliente e o registro de contrato
     */
    public function delete_all($id){
        try {
            //Cancelar ele caso o status seja aprovado
            $status = Qlib::get_usermeta($id,(new ContratoController)->campo_meta3,true);
            if($status=='Aprovado' || $status=='aprovado'){
                $ret = [
                    'exec'=>false,
                    // 'error'=>$th->getMessage(),
                    'mens'=>'Contrato <b>'.$status.'</b> é necessário <b>Cancelar com a sulamerica</b> antes',
                    'color'=>'danger',
                ];
                return $ret;
            }
            //deletar o registro de contrato
            $del_cadastro = Contrato::where('id_cliente',$id)->delete();
            //deletar o registro de cliente
            $del_cliente = User::where('id',$id)->delete();
            $ret = [
                'exec'=>true,
                'mens'=>'Registros excluidos com sucesso',
                'error'=>'',
                'color'=>'success',
                'return'=>route('clientes.index'),
            ];

        } catch (\Throwable $th) {
            $ret = [
                'exec'=>false,
                'error'=>$th->getMessage(),
                'mens'=>'Registros excluidos com sucesso',
                'color'=>'danger',
            ];
            //throw $th;
        }
        return $ret;
    }
    /**
     * Verificar se o Usuario informado pelo user_id é proprioetário do client_id
     * @param string $user_id quando for null ele buscará o user_id logado no sistema
     * @param string $client_id
     */
    public function is_owner($user_id=null,$client_id=null){
        $user_id = $user_id ? $user_id : Auth::id();
        // $client_id =
        $ret = false;
        if($user_id && $client_id){
            $ret = Qlib::totalReg('users',"WHERE id='$client_id' AND autor='$user_id'");
        }
        return $ret;
    }
}
