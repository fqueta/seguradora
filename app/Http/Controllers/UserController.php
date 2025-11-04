<?php

namespace App\Http\Controllers;

use App\Models\_upload;
use App\Models\User;
use stdClass;
use App\Qlib\Qlib;
use App\Rules\FullName;
use App\Rules\RightCpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use DataTables;

class UserController extends Controller
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
        $this->routa = $routeName;
        $this->url = 'users';
        $this->label = 'Usuários';
        $this->view = 'padrao';
        $this->tab = 'users';
        $this->title = __('Cadastro de usuário');
        if($routeName == 'fornecedores'){
            $this->title = __('Cadastro de fornecedores');
        }
    }
    /**gera um id de pemissão automatico para o caso de clientes */
    public function id_permission_clientes(){
        return Qlib::qoption('id_permission_clientes');
    }
    /**gera um id de pemissão automatico para o caso de fornecedores */
    public function id_permission_fornecedores(){
        return Qlib::qoption('id_permission_fornecedores');
    }
    /**gera um id de pemissão automatico para o caso de parceiros */
    public function partner_permission_id(){
        return Qlib::qoption('partner_permission_id');
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
        $logado = Auth::user();
        if(isset($get['term'])){
            //Autocomplete
            if(isset($get['id_permission']) && !empty($get['id_permission'])){
                $sql = "SELECT * FROM users WHERE (name LIKE '%".$get['term']."%') AND id_permission=".$get['id_permission']." AND ".Qlib::compleDelete();
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
                // $id_permission_clientes = Qlib::qoption('id_permission_clientes');
                $user =  User::where('id_permission','>=',$logado->id_permission)->where('id_permission','!=',$this->id_permission_clientes())->orderBy('id',$config['order']);
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
                            $user->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            $user->where($key,'LIKE','%'. $value. '%');
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
        $users->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->get()->count();
        $users->ativos = $fm->where('ativo','=','s')->get()->count();
        $users->inativos = $fm->where('ativo','=','n')->get()->count();
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
    public function campos($dados=false,$local='index'){
        $user = Auth::user();
        $permission = new admin\UserPermissions($user);
        if(isset($dados['tipo_pessoa']) && $dados['tipo_pessoa'] && !isset($_GET['tipo'])){
            $_GET['tipo'] = $dados['tipo_pessoa'];
        }
        if($this->routa=='fornecedores'){
            $_GET['tipo'] = isset($_GET['tipo'])?$_GET['tipo']:'pj';
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
        $hidden_editor = '';
        $info_obs = '<div class="alert alert-info alert-dismissable" role="alert"><button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button><i class="fa fa-info-circle"></i>&nbsp;<span class="sw_lato_black">Obs</span>: campos com asterisco (<i class="swfa fas fa-asterisk cad_asterisco" aria-hidden="true"></i>) são obrigatórios.</div>';
        $ret = [
            'id'=>['label'=>'Id','js'=>true,'active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'tipo_pessoa'=>[
                'label'=>'Tipo de Pessoa',
                'active'=>true,
                'js'=>true,
                'type'=>'radio_btn',
                'arr_opc'=>['pf'=>'Pessoa Física','pj'=>'Pessoa Jurídica'],
                'exibe_busca'=>'d-block',
                'event'=>'onclick=selectTipoUser(this.value)',
                'tam'=>'12',
                'value'=>$sec,
                'class'=>'btn btn-outline-secondary',
            ],
            'sep0'=>['label'=>'informações','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','text_html'=>'<h4 class="text-center">'.__('Informe os dados').'</h4><hr>','script_show'=>''],
            'token'=>['label'=>'token','js'=>true,'active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_permission'=>[
                'label'=>'Permissão*',
                'active'=>true,
                'js'=>true,
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$permission->campos(),
                    'route_index'=>route('permissions.index'),
                    'id_form'=>'frm-permission',
                    'action'=>route('permissions.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Permissão',
                   ],
                'arr_opc'=>Qlib::sql_array("SELECT id,name FROM permissions WHERE active='s' AND id >='".$user->id_permission."' AND id != '".$this->id_permission_clientes()."'",'name','id'),'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'3',
                'value'=>@$_GET['id_permission'],
            ],
            'email'=>['label'=>'E-mail *','js'=>true,'active'=>true,'type'=>'email','exibe_busca'=>'d-none','event'=>'required','tam'=>'6','placeholder'=>''],
            'password'=>['label'=>'Senha','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'','tam'=>'3','placeholder'=>'','value'=>''],
            //'password_confirmation'=>['label'=>'Confirmar Senha *','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'required','tam'=>'3','placeholder'=>''],
            // 'nome'=>['label'=>$lab_nome,'active'=>true,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            'name'=>['label'=>$lab_nome,'js'=>true,'active'=>true,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            'cpf'=>['label'=>$lab_cpf,'active'=>false,'js'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cpf','tam'=>'3'],
            'cnpj'=>['label'=>'CNPJ *','active'=>false,'js'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cnpj required','tam'=>'4','class_div'=>'div-pj '.$displayPj],
            'razao'=>['label'=>'Razão social *','js'=>true,'active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[nome_fantasia]'=>['label'=>'Nome fantasia','js'=>true,'active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[celular]'=>['label'=>'Telefone celular','active'=>true,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][celular'],
            'config[telefone_residencial]'=>['label'=>'Telefone residencial','active'=>false,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','class_div'=>'div-pf '.$displayPf,'cp_busca'=>'config][telefone_residencial'],
            'config[telefone_comercial]'=>['label'=>'Telefone comercial','active'=>false,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][telefone_comercial'],
            'config[rg]'=>['label'=>'RG','active'=>false,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][rg','class_div'=>'div-pf '.$displayPf],
            'config[nascimento]'=>['label'=>'Data de nascimento','active'=>false,'type'=>'date','tam'=>'4','exibe_busca'=>'d-block','event'=>'','cp_busca'=>'config][nascimento','class_div'=>'div-pf '.$displayPf],
            'genero'=>[
                'label'=>'Sexo',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::lib_sexo(),
                'event'=>'',
                'tam'=>'4',
                'exibe_busca'=>true,
                'option_select'=>false,
                'class'=>'select2',
                'class_div'=>'div-pf '.$displayPf,
            ],
            'config[escolaridade]'=>[
                'label'=>'Escolaridade',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::lib_escolaridades(),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'select2',
                'cp_busca'=>'config][escolaridade','class_div'=>'div-pf '.$displayPf,
            ],
            'config[profissao]'=>[
                'label'=>'Profissão',
                'active'=>false,
                'type'=>'select',
                'arr_opc'=>Qlib::lib_profissao(),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'select2',
                'cp_busca'=>'config][profissao','class_div'=>'div-pf '.$displayPf,
            ],
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
            'sep1'=>['label'=>'Endereço','active'=>false,'type'=>'html','exibe_busca'=>'d-none','event'=>'','tam'=>'12','text_html'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr>','script_show'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr>'],
            'config[cep]'=>['label'=>'CEP','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'mask-cep onchange=buscaCep1_0(this.value)','tam'=>'3'],
            'config[endereco]'=>['label'=>'Endereço','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'endereco=cep q-inp="endereco"','tam'=>'7','cp_busca'=>'config][endereco'],
            'config[numero]'=>['label'=>'Numero','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'numero=cep','tam'=>'2','cp_busca'=>'config][numero'],
            'config[complemento]'=>['label'=>'Complemento','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'4','cp_busca'=>'config][complemento'],
            'config[bairro]'=>['label'=>'bairro','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'bairro=cep q-inp="bairro"','tam'=>'3','cp_busca'=>'config][bairro'],
            'config[cidade]'=>['label'=>'Cidade','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'cidade=cep q-inp="cidade"','tam'=>'3','cp_busca'=>'config][cidade'],
            'config[uf]'=>['label'=>'UF','active'=>false,'js'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'2','cp_busca'=>'config][uf'],
            //'foto_perfil'=>['label'=>'Foto','active'=>false,'js'=>false,'placeholder'=>'','type'=>'file','exibe_busca'=>'d-none','event'=>'','tam'=>'12'],
            'sep2'=>['label'=>'Preferencias','active'=>false,'type'=>'html','exibe_busca'=>'d-none','event'=>'','tam'=>'12','text_html'=>'<h4 class="text-center">'.__('Preferências').'</h4><hr>','script_show'=>'<h4 class="text-center">'.__('Preferências').'</h4><hr>'],
            'ativo'=>['label'=>'Liberado para uso','js'=>true,'tab'=>$this->tab,'active'=>true,'type'=>'chave_checkbox','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'preferencias[newslatter]'=>['label'=>'Deseja receber e-mails com as novidades','active'=>false,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-none','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não'],'cp_busca'=>'preferencias][newslatter'],

        ];
        if($this->routa == 'fornecedores'){
            $ret['email']['tam'] = 9;
            $ret['id_permission'] = ['label'=>'id_permission','js'=>true,'value'=>$this->id_permission_fornecedores() ,'active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'];

        }
        if($local=='create' || $local=='show' || $local=='edit'){
            //Importante para exibição das preferencias.
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
        //dd($ret);
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
        $this->authorize('is_admin', $user);
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
        $this->authorize('is_admin', $user);
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
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required','string',new FullName],
            'email' => ['required','string','unique:users'],
            'cpf'   =>[new RightCpf,'unique:users']
            ],[
                'name.required'=>__('O nome é obrigatório'),
                'name.string'=>__('É necessário conter letras no nome'),
                'email.unique'=>__('E-mail já cadastrado'),
                'cpf.unique'=>__('CPF já cadastrado'),
            ]);
        $dados = $request->all();
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
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
            if($this->routa=='pefil'){
                $this->routa = 'perfil';
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'url'=>$this->url,
                'id'=>$id,
            ];
            // dd($this->routa);
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
        $this->authorize('is_admin', $user);

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

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            // 'nome' => ['required',new FullName],
            'name' => ['required',new FullName],
            'cpf'   =>[new RightCpf]
        ]);
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
                }elseif($key=='password'){
                    $data[$key] = Hash::make($value);
                }else{
                    $data[$key] = $value;
                }
            }
        }
        $userLogadon = Auth::id();
        $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        $data['preferencias']['newslatter'] = isset($data['preferencias']['newslatter'])?$data['preferencias']['newslatter']:'n';
        $data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(empty($data['passaword'])){
            unset($data['passaword']);
        }
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
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = 'users';
        if (!$post = User::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }

        User::where('id',$id)->delete();
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        return $ret;
    }
}
