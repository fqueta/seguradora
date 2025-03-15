<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\EventController;
use App\Http\Controllers\admin\QuickCadController;
use Illuminate\Support\Facades\Auth;
use App\Models\_upload;
use App\Models\Post;
use App\Models\User;
use stdClass;
use App\Qlib\Qlib;
use App\Rules\FullName;
use App\Rules\RightCpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Notifications\notificaNewUser;
use App\Rules\RightCnpj;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $tab;

    public $access_token;
	public $url_plataforma;
	public $url;
	public $tk_conta;
	public $seg1;

    public function __construct()
    {
        $user = Auth::user();
        // $this->middleware('auth');
        $this->user = $user;
        if(Qlib::is_backend()){
            $this->routa = 'users';
        }else{
            $this->routa = 'users_site';
        }
        $this->label = 'Usuários';
        $this->view = 'padrao';
        $this->tab = 'users';
        $this->credenciais();
        $this->seg1 = request()->segment(1);
        //$seg2 = request()->segment(2);
    }
    public function credenciais(){
		$this->access_token = 'NWM5OGMyZGRiOTAzMS41ZmQwZGQyNTUzZGI0LjQx';
		$this->url 		 	= 'https://api.ctloja.com.br/v1';
		$this->tk_conta	 	= '624384509209d';
		//$this->tk_conta	 	= '60b77bc73e7c0';
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
        $campos = $this->campos();
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
            $ret['campos'] = $campos;
            $ret['user_totais'] = @count($user);
            $ret['titulo_tabela'] = 'Clientes';
            return $ret;
        }else{
            if($this->routa == 'fornecedores'){
                $user =  User::where('id_permission','=',Qlib::qoption('id_permission_fornecedores'))->orderBy('id',$config['order']);
            }else{
                $user =  User::where('id_permission','>=',$logado->id_permission)->orderBy('id',$config['order']);
            }
            //$user =  DB::table('users')->where('ativo','s')->orderBy('id',$config['order']);
        }
        $users = new stdClass;

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
        $logado = Auth::check();
        if($logado){
            $user = Auth::user();
            $permission = new admin\UserPermissions($user);
            $campos_permissions = $permission->campos();
            $arr_opc = Qlib::sql_array("SELECT id,name FROM permissions WHERE active='s' AND id >='".$user->id_permission."'",'name','id');
        }else{
            $campos_permissions = false;
            $user = false;
            $arr_opc = [];
        }
        if(Qlib::is_backend()){
            $origem = 'admin';
        }else{
            $origem = 'site';
        }
        if(isset($dados['tipo_pessoa']) && $dados['tipo_pessoa']){
            $_GET['tipo'] = $dados['tipo_pessoa'];
        }
        if(Qlib::is_backend()){
            $sec = isset($_GET['tipo'])?$_GET['tipo']:'pf';
        }else{
            $sec=request()->segment(3);
        }
        if($sec=='pf'){
            $lab_nome = 'Nome completo *';
            $lab_cpf = 'CPF *';
            $displayPf = '';
            $displayPj = 'd-none';
            $larg_email = 6;
        }elseif($sec=='pj'){
            $lab_nome = 'Nome do responsável *';
            $lab_cpf = 'CPF do responsável*';
            $displayPf = 'd-none';
            $displayPj = '';
            $larg_email = 9;
        }else{
            $lab_nome = 'Nome completo *';
            $lab_cpf = 'CPF *';
            $larg_email = 6;
            $displayPf = '';
            $displayPj = 'd-none';
        }
        $telddi = $this->ger_select_ddi([
            'label' => 'Celular',
            'dados' => $dados,
        ]);
        $ddi = isset($dados['config']['ddi']) ? $dados['config']['ddi'] : '';
        $telefonezap = isset($dados['config']['telefonezap']) ? $dados['config']['telefonezap'] : '';

        $telddi_show = '<div class="col-12"><label>Celular:</label> '.$ddi.''.$telefonezap.'</div>';

        $ret = [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'sep0'=>['label'=>'informações','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h6 class="text-left pt-2">'.__('Informe os dados').'</h6><hr class="mt-0">','script_show'=>''],
            'tipo_pessoa'=>[
                'label'=>'Pessoa*',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>['pf'=>'Pessoa Física','pj'=>'Pessoa Jurídica'],'exibe_busca'=>'d-block',
                'event'=>'onchange=selectTipoUser(this.value)',
                'tam'=>'12',
            ],
            'name'=>['label'=>$lab_nome,'active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'required','tam'=>'12'],
            // 'nome'=>['label'=>$lab_nome,'active'=>true,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            // 'cpf'=>['label'=>$lab_cpf,'active'=>false,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cpf','tam'=>'3'],
            'cpf'=>['label'=>$lab_cpf,'active'=>false,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cpf required','tam'=>'3','value'=>@$_GET['cpf']],
            'cnpj'=>['label'=>'CNPJ *','active'=>false,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cnpj required','tam'=>'3','class_div'=>'div-pj '.$displayPj],
            'razao'=>['label'=>'Razão social *','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'3','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[nome_fantasia]'=>['label'=>'Nome fantasia','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'3','placeholder'=>'','class_div'=>'div-pj '.$displayPj,'cp_busca'=>'config][nome_fantasia'],
            'config[CodigoCiac]'=>['label'=>'CIAC','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'2','placeholder'=>'','class_div'=>'div-pj '.$displayPj,'cp_busca'=>'config][CodigoCiac'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'telddi'=>['label'=>'Telefone com ddi','active'=>false,'tam'=>'9','script'=>$telddi,'script_show'=>$telddi_show,'type'=>'html_script','class_div'=>''],
            'config[Telefone]'=>['label'=>'Telefone','active'=>true,'type'=>'tel','tam'=>'3','exibe_busca'=>'d-block','event'=>' onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][Telefone'],
            'email'=>['label'=>'Email','active'=>true,'type'=>'email','exibe_busca'=>'d-block','event'=>'required','tam'=>$larg_email],
            'password'=>['label'=>'Senha','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'','tam'=>'3'],
            'sep1'=>['label'=>'Documento','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h6 class="text-left pt-2">'.__('Documentos').'</h6><hr class="mt-0">','script_show'=>''],
            'sep1'=>['label'=>'Endereço','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h6 class="text-left pt-2">'.__('Configurações').'</h6><hr class="mt-0">','script_show'=>''],
            'config[cep]'=>['label'=>'CEP','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'mask-cep onchange=buscaCep1_0(this.value)','tam'=>'3','cp_busca'=>'config][cep'],
            'config[endereco]'=>['label'=>'Endereço','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'endereco=cep','tam'=>'7','cp_busca'=>'config][endereco'],
            'config[numero]'=>['label'=>'Numero','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'numero=cep','tam'=>'2','cp_busca'=>'config][numero'],
            'config[complemento]'=>['label'=>'Complemento','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3','cp_busca'=>'config][complemento'],
            'config[bairro]'=>['label'=>'Bairro','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'bairro=cep','tam'=>'3','cp_busca'=>'config][bairro'],
            'config[cidade]'=>['label'=>'Cidade','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'cidade=cep','tam'=>'4','cp_busca'=>'config][cidade'],
            'config[uf]'=>['label'=>'UF','active'=>false,'js'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'2','cp_busca'=>'config][uf'],
            'sep2c'=>['label'=>'Documento','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h6 class="text-left pt-2">'.__('Configurações').'</h6><hr class="mt-0">','script_show'=>''],
            'id_permission'=>[
                'label'=>'Permissão*',
                'active'=>true,
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$campos_permissions,
                    'route_index'=>route('permissions.index'),
                    'id_form'=>'frm-permission',
                    'action'=>route('permissions.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Permissão',
                ],'arr_opc'=>$arr_opc,'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'12',
            ],'ativo'=>['label'=>'Liberar acesso','active'=>true,'type'=>'chave_checkbox','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'config[origem]'=>['label'=>'origem','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','cp_busca'=>'config][origem','value'=>$origem],
        ];
        if(!$logado){
            unset($ret['id_permission']);
        }
        if($origem=='admin'){
            //Adicionar input hidden para o quick cadastro
            if(isset($_GET['quick_cad'])){
                $ret['quick_cad'] = ['label'=>'quick cad','active'=>false,'type'=>'hidden','value'=>$_GET['quick_cad'],'exibe_busca'=>'d-block','event'=>'','tam'=>'2'];
            }
            $ret['sep3']=[
                'label'=>'Termos',
                'active'=>false,
                'type'=>'html_script',
                'exibe_busca'=>'d-none','event'=>'','tam'=>'12','script_show'=>'<h6 class="text-left pt-2">'.__('Termos').'</h6><hr class="mt-0">',
                'script_'=>''
            ];
            if($termo=$this->aceito_termo(@$dados['id'],'html')){
                $ret['ttermo']=[
                    'label'=>'Termos',
                    'active'=>false,
                    'type'=>'html_script',
                    'exibe_busca'=>'d-none','event'=>'','tam'=>'12','script_'=>'<p class="pt-2 mb-3">'.$termo.'</p>','script_show'=>'<p class="mb-3">'.$termo.'</p>'
                ];
                unset($ret['meta[termo]']);
            }else{
                $ret['ttermo']=[
                    'label'=>'Termos',
                    'active'=>false,
                    'type'=>'html_script',
                    'exibe_busca'=>'d-none','event'=>'','tam'=>'12','script_'=>'<p class="pt-2 mb-3 text-danger">Usuário ainda não concordou com os temos.</p>',
                    'script_show'=>'<p class="pt-2 mb-3 text-danger">Usuário ainda não concordou com os temos.</p>'
                ];
            }
            if(isset($dados['id'])){
                //Veririca se está no blacklist
                $bl = new BlacklistController;
                if($bl->is_blacklist($dados['id'])){
                    $duser = @$dados->toArray();
                    $duser['motivo'] = Qlib::get_usermeta($dados['id'],$bl->campo_motivo,true);
                    if($duser['motivo']){
                        $duser['motivo'] = Qlib::lib_json_array($duser['motivo']);
                        if(isset($duser['motivo']['leilao_id']) && ($leilao_id = $duser['motivo']['leilao_id'])){
                            $duser['motivo']['link_front'] = (new LeilaoController)->get_link_front($leilao_id);
                            $duser['motivo']['link_admin'] = (new LeilaoController)->get_link_admin($leilao_id);
                        }
                    }
                    $ret['balcklist']=[
                        'label'=>__('Link no site'),
                        'type'=>'html',
                        'active'=>false,
                        'script'=>'admin.blacklist.card_detalhes',
                        'script_show'=>'admin.blacklist.card_detalhes',
                        'dados'=>$duser,
                    ];
                }
            }
        }
        if(Qlib::is_frontend()){
            unset($ret['sep2c'],$ret['ativo'],$ret['id_permission']);
            //determinar o tipo de pessoa pela url
            $ret['tipo_pessoa']['type'] = 'hidden';
            if($sec){
                $ret['tipo_pessoa']['value'] = $sec;
            }
            $ret['sep3']=[
                'label'=>'Termos',
                'active'=>false,
                'type'=>'html_script',
                'exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h6 class="text-left pt-2">'.__('Termos').'</h6><hr class="mt-0">',
                'script_show'=>''
            ];
            $ret['meta[termo]']=[
                'label'=>'Concordo com os <a href="'.url('/termos-do-site').'" target="_blank">termos do site</a>',
                'active'=>false,
                'type'=>'checkbox',
                'exibe_busca'=>'d-block',
                'name'=>'meta[termos_site]',
                'event'=>'required',
                'value'=>'s',
                'tam'=>'12'
            ];
            if($logado){
                if($termo=$this->aceito_termo($user->id,'html')){
                    $ret['ttermo']=[
                        'label'=>'Termos',
                        'active'=>false,
                        'type'=>'html_script',
                        'exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<p class="pt-2 mb-3">'.$termo.'</p>',
                        'script_show'=>''
                    ];
                    unset($ret['meta[termo]']);
                }
            }else{

            }

            if($logado){
                //Desablitar a edição de email no frontend
                // $ret['email']['event'] = 'disabled';
            }
            //Impedir editção de email,cpf,
            $cpf = isset($dados['cpf']) ? $dados['cpf'] : false;
            $email = isset($dados['email']) ? $dados['email'] : false;
            if($cpf){
                $ret['cpf']['class_div'] = ' mt-4 pt-2 bg-secondary text-light';
                $ret['cpf']['type'] = 'hidden_text';
            }
            if($email){
                $ret['email']['class_div'] = ' mt-4 pt-2 bg-secondary text-light';
                $ret['email']['type'] = 'hidden_text';
            }
            // dump($dados);
        }
        // dd($ret);
        return $ret;
    }
    public function campos_bk2($dados=false,$local='index'){
        $logado = Auth::check();
        if($logado){
            $user = Auth::user();
            $permission = new admin\UserPermissions($user);
            $campos_permissions = $permission->campos();
            $arr_opc = Qlib::sql_array("SELECT id,name FROM permissions WHERE active='s' AND id >='".$user->id_permission."'",'name','id');
        }else{
            $campos_permissions = false;
            $user = false;
            $arr_opc = [];
        }
        if(Qlib::is_backend()){
            $origem = 'admin';
        }else{
            $origem = 'site';
        }
        if(isset($dados['tipo_pessoa']) && $dados['tipo_pessoa']){
            $_GET['tipo'] = $dados['tipo_pessoa'];
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
        $ret = [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            // 'tipo_pessoa'=>[
            //     'label'=>'Tipo de Pessoa',
            //     'active'=>true,
            //     'type'=>'radio_btn',
            //     'arr_opc'=>['pf'=>'Pessoa Física','pj'=>'Pessoa Jurídica'],
            //     'exibe_busca'=>'d-block',
            //     'event'=>'onclick=selectTipoUser(this.value)',
            //     'tam'=>'12',
            //     'value'=>$sec,
            //     'class'=>'btn btn-outline-secondary',
            // ],
            'sep0'=>['label'=>'informações','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-left">'.__('Informe os dados').'</h4><hr class="mt-0">','script_show'=>''],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_permission'=>[
                'label'=>'Permissão*',
                'active'=>true,
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$campos_permissions,
                    'route_index'=>route('permissions.index'),
                    'id_form'=>'frm-permission',
                    'action'=>route('permissions.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Permissão',
                   ],
                'arr_opc'=>$arr_opc,'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
                'value'=>@$_GET['id_permission'],
            ],
            'email'=>['label'=>'E-mail *','active'=>true,'type'=>'email','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            'password'=>['label'=>'Senha','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'','tam'=>'3','placeholder'=>'','value'=>''],
            //'password_confirmation'=>['label'=>'Confirmar Senha *','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'required','tam'=>'3','placeholder'=>''],
            'name'=>['label'=>$lab_nome,'active'=>true,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            'cpf'=>['label'=>$lab_cpf,'active'=>false,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cpf','tam'=>'3'],
            'cnpj'=>['label'=>'CNPJ *','active'=>false,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cnpj required','tam'=>'4','class_div'=>'div-pj '.$displayPj],
            'razao'=>['label'=>'Razão social *','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[nome_fantasia]'=>['label'=>'Nome fantasia','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
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
            'sep1'=>['label'=>'Endereço','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr class="mt-0">','script_show'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr class="mt-0">'],
            'config[cep]'=>['label'=>'CEP','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'mask-cep onchange=buscaCep1_0(this.value)','tam'=>'3'],
            'config[endereco]'=>['label'=>'Endereço','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'endereco=cep','tam'=>'7','cp_busca'=>'config][endereco'],
            'config[numero]'=>['label'=>'Numero','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'numero=cep','tam'=>'2','cp_busca'=>'config][numero'],
            'config[complemento]'=>['label'=>'Complemento','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'4','cp_busca'=>'config][complemento'],
            'config[cidade]'=>['label'=>'Cidade','active'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'cidade=cep','tam'=>'6','cp_busca'=>'config][cidade'],
            'config[uf]'=>['label'=>'UF','active'=>false,'js'=>false,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'2','cp_busca'=>'config][uf'],
            //'foto_perfil'=>['label'=>'Foto','active'=>false,'js'=>false,'placeholder'=>'','type'=>'file','exibe_busca'=>'d-none','event'=>'','tam'=>'12'],
            'sep2'=>['label'=>'Preferencias','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-left">'.__('Preferências').'</h4><hr class="mt-0">','script_show'=>'<h4 class="text-left">'.__('Preferências').'</h4><hr class="mt-0">'],
            'ativo'=>['label'=>'Liberado para uso','active'=>true,'type'=>'chave_checkbox','value'=>'s','checked'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'preferencias[newslatter]'=>['label'=>'Deseja receber e-mails com as novidades','active'=>false,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-none','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não'],'cp_busca'=>'preferencias][newslatter'],

        ];
        if(!$logado){
            unset($ret['id_permission']);
        }
        if(Qlib::is_frontend()){
            unset($ret['sep2'],$ret['ativo'],$ret['id_permission']);
            //Desablitar a edição de email no frontend
            if($logado){
                $ret['email']['event'] = 'disabled';
            }
        }
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
        $title = __('Cadastrar usuário');
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-users',
            'route'=>$this->routa,
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos();
        //REGISTRAR EVENTO CADASTRO
        $regev = Qlib::regEvent(['action'=>'create','tab'=>$this->tab,'config'=>[
            'obs'=>'Abriu tela de cadastro',
            'link'=>route($this->routa.'.create'),
            ]
        ]);

        return view($this->routa.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);
    }
    public function store(Request $request)
    {
        $dados = $request->all();
        $origem = isset($dados['config']['origem']) ? $dados['config']['origem'] : false;
        $tag_origem = $origem;

        if($origem=='admin'){
            $validatedData = $request->validate([
                'name' => ['required','string',new FullName],
                'email' => ['required','string','unique:users'],
                'cpf'   =>[new RightCpf,'required','unique:users'],
            ],[
                'nome.required'=>__('O nome é obrigatório'),
                'nome.string'=>__('É necessário conter letras no nome'),
                'email.unique'=>__('E-mail já cadastrado'),
                'cpf.unique'=>__('CPF já cadastrado'),
            ]);
        }else{
            if($origem=='precadastro'){
                $validatedData = $request->validate([
                    'cnpj'   =>[new RightCnpj,'required','unique:users']
                ],[
                        'cnpj.unique'=>__('CNPJ já cadastrado'),
                ]);
            }else{
                $validatedData = $request->validate([
                    'name' => ['required','string',new FullName],
                    'email' => ['required','string','unique:users'],
                    'cpf'   =>[new RightCpf,'required','unique:users']
                ],[
                        'nome.required'=>__('O nome é obrigatório'),
                        'nome.string'=>__('É necessário conter letras no nome'),
                        'email.unique'=>__('E-mail já cadastrado'),
                        'cpf.unique'=>__('CPF já cadastrado'),
                ]);
            }
        }
        // $vl = ob_get_clean();
        // dd($vl);
        // if($origem!='admin'){
        //     $ret = (new RegisterController)->init($dados);
        //     $ret['login'] = new llController($request);
        //     return $ret;
        // }
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['tipo_pessoa'] = isset($dados['tipo_pessoa'])?$dados['tipo_pessoa']:'pf';
        if(empty($dados['tipo_pessoa'])){
            $dados['tipo_pessoa'] = 'pf';
        }
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'s';
        $dados['token'] = isset($dados['token'])?$dados['token']:uniqid();
        $dados['id_permission'] = isset($dados['id_permission'])?$dados['id_permission']:5;
        if(isset($dados['password']) && !empty($dados['password'])){
            $dados['password'] = Hash::make($dados['password']);
        }else{
            if(empty($dados['password'])){
                unset($dados['password']);
            }
        }
        $d = $dados;
        unset($dados['meta']);
        $salvar = User::create($dados);
        $dados['id'] = $salvar->id;
        //Atualização de meta dados
        $s_me=false;
        if(isset($d['meta']) && is_array($d['meta'])){
            $d['meta']['tag_origem'] = $tag_origem;
            $s_me = $this->save_meta($salvar->id,$d['meta']);
        }
        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$this->label.' cadastrada com sucesso!',
            'color'=>'success',
            's_me'=>$s_me,
            'idCad'=>$salvar->id,
            'exec'=>true,
            'dados'=>$dados
        ];
        if($origem=='precadastro'){
            if($salvar->id){
                $ret['exec'] = true;
            }
            return $ret;
        }
        if($ajax=='s'){
            //Envia notificação de cadastro
            // $notific_new_user_add = Qlib::qoption('notific_new_user_add')?Qlib::qoption('notific_new_user_add'):'s';
            if(isset($salvar->id) && $this->notific_new_user() && $origem != 'precadastro'){
                $user_cad = User::Find($salvar->id);
                Notification::send($user_cad,new notificaNewUser($user_cad));
            }
            //REGISTRAR EVENTOS
           if($origem=='admin'){
                //requisição realizada no painel de administrador
                (new EventController)->listarEvent(['tab'=>$this->tab,'id'=>$salvar->id,'this'=>$this]);
                $ret['return'] = route($route).'?idCad='.$salvar->id;
                if($request->has('quick_cad')){
                    if($request->get('quick_cad')=='leilao'){
                        //go to stet 2
                        $ret['redirect'] = (new QuickCadController())->link_step2(@$salvar->id);;
                    }
                }else{
                    $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);
                }
            }else{
                //requisição realizada pelo usuario do site
                if($salvar->id){
                    $authenticate = $this->authenticate($request);
                    if($authenticate){
                        $ret['redirect'] = url('/').'/'.Qlib::get_slug_post_by_id(37);
                        $ret['return'] = $ret['redirect'];
                    }else{
                        $ret['mens'] = @$authenticate['mens'];
                    }
                    // $ret['return'] = route($route).'?idCad='.$salvar->id;
                    // $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);

                }else{

                }
            }
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function perfilShow(){
        $id = Auth::id();
        return $this->show($id,'perfil');
    }
    public function show($id)
    {
        $local = request()->route()->getName();
        $dados = User::findOrFail($id);
        if($local=='sistema.perfil'){
            $rt = 'sistema';
        }else{
            $rt = $this->routa;
        }
        $this->authorize('ler', $rt);
        if(!empty($dados)){
            $title = 'Cadastro de usuários';
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
                'frm_id'=>'frm-users',
                'route'=>$this->routa,
                'id'=>$id,
                'local'=>$local,
                'class_card1'=>'col-md-8',
                'class_card2'=>'col-md-4',
            ];
            if($local=='sistema.perfil'){
                $config['class_card1'] = 'col-md-12';
                $config['class_card2'] = 'd-none';
            }else{
                //REGISTRAR EVENTOS
                (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            }

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
            $campos['ativo']['type']='hidden';
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
                'eventos'=>(new EventController)->listEventsPost(['post_id'=>$id]),
                // 'eventos'=>(new EventController)->listEventsUser(['id_user'=>$id]),
                'exec'=>true,
            ];
            // return view($this->routa.'.show',$ret);
            return view($this->view.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }
    public function perfilEdit($user,$local=false)
    {
        $id = Auth::id();
        return $this->edit($id);
    }
    public function edit($user)
    {
        $id = $user;
        $dados = User::where('id',$id)->get();
        $local = request()->route()->getName();

        $routa = 'users';
        if($local=='sistema.perfil.edit'){
            $this->authorize('is_admin_logado', $user);
        }else{
            $this->authorize('is_admin', $user);
        }

        if(!empty($dados)){
            $title = 'Editar Cadastro de usuários';
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
                'id'=>$id,
                'local'=>$local,
            ];
            $campos = $this->campos($dados[0]);
            if($local=='sistema.perfil.edit'){
                $campos['ativo']['type']='hidden';
            }
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'exec'=>true,
            ];
            //REGISTRAR EVENTOS
            (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this,'id'=>$id]);
            return view($routa.'.createedit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }
    /**
     * Metodo para gerencia o login dos clientes
     * @param ['email' => $email, 'password' => $password, 'ativo' => 's', 'excluido' => 'n']
     * @return boolean true|false
     */
    public function login($dados){
        $email = isset($dados['email']) ? $dados['email'] : false;
        $password = isset($dados['password']) ? $dados['password'] : false;
        if (Auth::attempt(['email' => $email, 'password' => $password, 'ativo' => 's', 'excluido' => 'n'],@$dados['remember'])) {
            return true;
        }else{
            return false;
        }
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => ['required',new FullName],
            'email'   =>['required','string','unique:users,email,'.$id],
            'cpf'   =>[new RightCpf,'unique:users,cpf,'.$id],
        ]);

        $data = [];
        $dados = $request->all();

        $meta = isset($dados['meta']) ? $dados['meta']:false;
        unset($dados['meta']);
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        if(!$dados['password'] || empty($dados['password'])){
            unset($dados['password']);
        }
        if(!$dados['token'] || empty($dados['token'])){
            $dados['token'] = uniqid();
        }
        //verifica se requisição esta vido do site e se a orgem do cadastro tbm
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
        if(Qlib::is_backend()){
            $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        }
        $data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $arr_config = $dados['config'];
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        if(empty($data['passaword'])){
            unset($data['passaword']);
        }
        $s_me = [];
        if(!empty($data)){
           $atualizar=User::where('id',$id)->update($data);
            $route = $this->routa.'.index';
            //Atualização de meta dados
            if(is_array($meta)){
                $s_me = $this->save_meta($id,$meta);
            }
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>'Salvo com sucesso!',
                'color'=>'success',
                'idCad'=>$id,
                's_me'=>$s_me,
                'return'=>$route,
            ];
            if($request->has('redirect')){
                $ret['redirect'] = $request->get('redirect');
            }else{
                if(isset($data['config']['origem']) && $data['config']['origem']=='site'){
                    //$ret['redirect'] = route('users.show',['id'=>$id]); sem ação no memento
                }else{
                    $ret['redirect'] = route('users.show',['id'=>$id]);
                }
            }
            if($atualizar){
                //REGISTRAR EVENTOS
                (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
            }
            //verifica qual é a orgem
            //se for do site e o cliente não estiver logado efetur login no sistema
            // dump(Qlib::is_frontend(),$arr_config['origem']);
            // dd($arr_config);
            if(Qlib::is_frontend() && isset($arr_config['origem']) && $arr_config['origem']=='site'){
                //verifica usuario atual está logado
                if (!Auth::check()) {
                    $user_cad = User::Find($id);
                    // Notification::send($user_cad,new notificaNewUser($user_cad));
                    //Efetuar login
                    //adicionar redirec
                    // dd($dados);
                    if($this->login($dados)){
                        $ret['redirect'] = url('/');
                        $ret['return'] = $ret['redirect'];
                    }
                    // $ret['login'] = $this->login($request);
                    // dump($ret);
                }
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
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = 'users';
        if (!$post = User::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            //REGISTRAR EVENTO
            $regev = Qlib::regEvent(['action'=>'destroy','tab'=>$this->tab,'config'=>[
                'obs'=>'Exclusão de cadastro Id '.$id,
                'link'=>'#',
                ]
            ]);

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
    /**
     * Metodo para salvar campos meta do usuario
     * @param integer user_id, Array metadata=dados meta do usuario
     *
     */
    public function save_meta($user_id,$metadata)
    {
        $ret = [];
        if($user_id && is_array($metadata)){
            // dd($metadata,$user_id);
            foreach ($metadata as $k => $v) {
                if(is_array($v)){
                    $v = Qlib::lib_array_json($v);
                }
                if($k == 'termo' && $v=='s'){
                    //aceitação dos termos
                    $v = Qlib::lib_array_json([
                        'aceito_termo' => 's',
                        'data' => Qlib::dataLocal(),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                    ]);
                }
                $ret[$k] = Qlib::update_usermeta($user_id,$k,$v);
            }
        }
        return $ret;
    }
    /**
     * Metodo para verifica se usuario aceito os termos
     * @param int $user_id,$type=tipo de retorno se for html resultado será um html
     */
    public function aceito_termo($user_id,$type='')
    {
        $termo = Qlib::get_usermeta($user_id,'termo',true);
        $ret = false;
        if($termo){
            $arr_t = Qlib::lib_json_array($termo);
            if(@$arr_t['aceito_termo']=='s'){
                if($type=='html'){
                    $ret = '<span class="text-success">Termos aceitos pelo usuário em {data} :: {ip}</span>';
                    $ret = str_replace('{data}',$arr_t['data'],$ret);
                    $ret = str_replace('{ip}',$arr_t['ip'],$ret);
                }else{
                    $ret = true;
                }
            }
        }
        return $ret;
    }
    public function exec($token_conta = null)
    {
        $cont = false;
        //if($token_conta){
            $verifica_fatura = $this->verifica_faturas(array('token_conta'=>$token_conta));
            if(isset($_GET['teste'])){
                Qlib::lib_print($verifica_fatura);
            }
            $verifica_fatura['acao'] = isset($verifica_fatura['acao'])?$verifica_fatura['acao']:false;
            if(isset($verifica_fatura['acao'])&&$verifica_fatura['acao']=='alertar'){
                if(Qlib::isAdmin()){
                    $cont = @$verifica_fatura['mens'];
                    //echo $cont;
                }
            }elseif($verifica_fatura['acao']=='suspender' || $verifica_fatura['acao']=='desativar'){
                //Não terá acesso ao admin somente ao boleto e as faturas e o site estará desativado tbem
                if(Qlib::isAdmin(3)){
                    $cont = @$verifica_fatura['mens'];
                }else{
                    $cont = Qlib::formatMensagemInfo('Sistema temporariamente suspenso entre em contato com o administrador','danger');
                }
                $pagSusped = 'suspenso';
                if($this->seg1!=$pagSusped){
                    Qlib::redirect('/'.$pagSusped,0);
                    die();
                }
                //echo $cont;
            }
        //}
        return $cont;
    }
    public function verifica_faturas($config=false,$cache=true){
		$ret['exec'] = false;
		$ret['cache'] = false;
		//exemplo de uso
		/*
		$this = new apictloja;
		$ret = $this->verifica_faturas(array('token_conta'=>''));
		Qlib::lib_print($ret);
		*/
		$token = isset($config['token_conta'])?$config['token_conta']:$this->tk_conta;

		if($token){
            $ver_sess = session('verifica_faturas');
            //Qlib::lib_print($ver_sess);

			if(isset($ver_sess['exec'])&&$ver_sess['exec'] && $cache){
				$arr_response = $ver_sess;
				$ret['cache'] = true;
			}else{

				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $this->url.'/verifica_faturas/'.$token,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'GET',
				  CURLOPT_HTTPHEADER => array(
					'Access-Token: '.$this->access_token
				  ),
				));
				$response = curl_exec($curl);
				curl_close($curl);
				$arr_response = json_decode($response,true);
				//$ret['arr_response'] = $arr_response;
			}
			if(isset($arr_response['exec'])){
				$ret['exec'] = $arr_response['exec'];
				$ret['acao'] = $arr_response['acao'];
                session(['verifica_faturas'=>$arr_response]);
				//$ver_sess=$arr_response;
			}else{
				$ret['acao'] = 'liberar';
            }
			if(isset($arr_response['mens'])){
				$ret['mens'] = $arr_response['mens'];
			}
			$ret['token'] = $token;
		}else{
			$ret['mens'] = Qlib::formatMensagemInfo('Configuração ou token inválido','danger');
		}
        //dd($ret);
        return $ret;
	}
    public function pararAlertaFaturaVencida(Request $request){
        $request->session()->put('verifica_faturas.acao','liberar');
        $ret['exec']=true;
		return $ret;
	}
    public function suspenso()
    {
        return view('admin.suspenso');
    }
    /**Metodo para gerar o formulario no front pode ser iniciado com o short_code [sc ac="form_meu_cadastro"] */
    public function form_meu_cadastro($post_id=false,$dados=false,$meu_cadastro_id=false){
        $route = $this->routa;
        if(Gate::allows('is_customer_logado')||Gate::allows('is_admin2')){
            if(Auth::check()){
                $ac = 'alt';
                $dadosmeu_cadastro = Auth::user();
                $meu_cadastro_id = $dadosmeu_cadastro->id;
            }else{
                $dadosmeu_cadastro = false;
                $ac = 'cad';
            }
        }else{
            $ac = 'cad';
            $dadosmeu_cadastro = false;
        }
        $seg3 = request()->segment(3);
        $seg4 = request()->segment(4);
        if($seg4 && $ac=='cad'){
            //é porque o clientes não está com o cadastro completo
            $dadosmeu_cadastro = User::where('token','=',$seg4)->get();
            if($dadosmeu_cadastro->count() > 0){
                //verifica se ele é um precadastro incompleto
                $dcli = $dadosmeu_cadastro->toArray();
                $ac = 'alt';
                $dadosmeu_cadastro = $dcli[0];
            }else{
                return redirect('/user/create');
                // dd($dadosmeu_cadastro->count());
            }
        }
        // $meu_cadastro_id = $meu_cadastro_id ? $meu_cadastro_id : $seg2;

        if(!$dados && $post_id){

            $dados = Post::Find($post_id);
        }
        $title = __('Meu Cadastro');
        $redirect = url('/');
        if($seg3=='pj'){
            $title .= ' '.__('de Escola');
        }elseif($seg3=='pf'){
            $title .= ' '.__('de Aluno');
            // $redirect = url('/');
        }
        $titulo = $title;
        $config = [
            'ac'=>$ac,
            'frm_id'=>'frm-posts',
            'route'=>$route,
            'view'=>'site.index',
            'file_submit'=>'site.js_submit',
            'arquivos'=>'jpeg,jpg,png',
            'redirect'=>$redirect,
            'title'=>$title,
            'titulo'=>$titulo,
        ];
        if(isset($_GET['mbase'])){
            $config['mes'] = base64_decode($_GET['mbase']);
        }
        if(isset($dadosmeu_cadastro['id'])){
            $config['id'] = $dadosmeu_cadastro['id'];
        }
        $config['media'] = [
            'files'=>'jpeg,jpg,png,pdf,PDF',
            'select_files'=>'unique',
            'field_media'=>'post_parent',
            'post_parent'=>$post_id,
        ];
        $listFiles = false;
        $campos = $this->campos($dadosmeu_cadastro);

        $ret = [
            'value'=>$dadosmeu_cadastro,
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'listFiles'=>$listFiles,
            'campos'=>$campos,
            'exec'=>true,
        ];
        return view('site.user.edit',$ret);
    }
    /**
     * Metodo para chamar a viw de seleção de tipo de usuario que se cadaastra no site
     */
    public function painel_select_user_site($post_id=false,$dados=false){
        if(!$dados && $post_id){

            $dados = Post::Find($post_id);
        }
        $title = __('Selecione seu perfil de usuário');
        $titulo = $title;
        $config['media'] = [
            'files'=>'jpeg,jpg,png,pdf,PDF',
            'select_files'=>'unique',
            'field_media'=>'post_parent',
            'post_parent'=>$post_id,
        ];
        $ret = [
            // 'value'=>$dadosmeu_cadastro,
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            // 'listFiles'=>$listFiles,
            // 'campos'=>$campos,
            'exec'=>true,
        ];
        return view('site.user.painel_select_user_site',$ret);
    }
    /**
     * Metodo para checar se o usuario verificou seu email
     */
    public function is_verified(){
        //uso (new UserController)->is_verified();
        $user = Auth::user();
        return $user->email_verified_at;
    }
    /**
     * Metodo para plubicar dados dos usuario
     * @param int $user_id
     * @return array $ret
     */
    public function get_user_data($user_id){
        return User::Find($user_id);
    }
    /**
     * Metodo para Gerenciar o cadastro de usuarios peelo site
     * @param int $id_pagina,$dp= dados de postagem da página
     * @return string $ret
     */
    public function ger_user($id_pagina=false,$dp=false){
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $seg3 = request()->segment(3);

        if(!$seg2){
            if(Qlib::isAdmin(3)){
                return $this->get_users_site();
            }else{
                return redirect()->to('/meu-cadastro');
            }
        }elseif($seg2=='create' && $seg3!=null){
            //Criar usuario
            return $this->form_meu_cadastro($id_pagina,$dp);
        }elseif($seg2=='create'){
            //Exibir uma tela seleção de timpo de usuario
            if(Qlib::is_repasses()){
                return $this->form_meu_cadastro($id_pagina,$dp);
            }else{
                return $this->painel_select_user_site($id_pagina,$dp);
            }
        }elseif($seg2=='edit'){
            //editar usuario
        }elseif($seg2=='show'){
            //visualizar usuario

        }elseif($seg2=='remove'){
            //excluir usuario
        }
        $ret = $seg2;

        return $ret;
    }
    /**
     * Metodo para logar cliente apos cadastro de usuario no site tbme envia um email de verificação
     */
    public function after_cad_user_site(Request $request){
        $ret = false;
        if($request){
            $ret = $this->authenticate($request);
        }
        return $ret;
    }
    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request)    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $ret['exec'] = false;
        $ret['mens'] = false;
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $ret['exec'] = true;
            // return redirect()->intended('dashboard');
        }else{
            $ret['mens'] = 'Invalid';
            // return back()->withErrors([
            //     'email' => 'The provided credentials do not match our records.',
            // ])->onlyInput('email');
        }
        return $ret;

    }
    /**
     * Metodo para verifica usuaior unico basedo em cpf ou email
     * @param string $campo = o campo a ser verificado,strin $value=valor a ser verificado, integer $id opcional se for informado ele ignora o usuario com esse $id
     * @return boolean
     */
    public function is_user_exist($campo,$value,$id=false){
        $ret = false;
        if($campo && $value){
            if($id){
                $ver = User::where($campo,$value)->where('id','!=',$id)->where('excluido','n')->where('deletdo','n')->get();
                if($ver->count()){
                    $ret = $ver->count();
                }
            }else{
                $ver = User::where($campo,$value)->where('excluido','n')->where('deletdo','n')->get();
                if($ver->count()){
                    $ret = $ver->count();
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo que retorna id do primeiro administrados encontrado
     */
    public function get_first_admin(){
        $id_permission_admin = Qlib::qoption('id_permission_admin')?Qlib::qoption('id_permission_admin'):1;
        $d = User::where('id_permission','=',$id_permission_admin)->first();
        // dd($d);
        if($d->count()>0){
            return $d['id'];
        }
    }
    public function get_users_site(){

    }
    /**
     * Metodo para retorna o total de usuarios cadastrados
     */
    public function total(){
        $d = User::all()->count();
        return $d;

    }
    //Metodo para montar um array contendo os ddi que estão na api do aeroclubejf
    public function get_ddi(){
        $ret = false;
        $link_ddi = 'https://api.aeroclubejf.com.br/api/ddi';
		$js_ddi = file_get_contents($link_ddi);
		$ret = Qlib::lib_json_array($js_ddi);
        return $ret;
    }
    /**
     * Metodo para gerar um select de ddi
     * @return string $input_ddi em html
     */
    public function ger_select_ddi($config=false){
        $arr_ddi = $this->get_ddi();
        $label = isset($config['label']) ? $config['label'] : '';
        $dados = isset($config['dados']) ? $config['dados'] : [];
        $ddi = isset($dados['config']['ddi']) ? $dados['config']['ddi'] : 55;
        $telefonezap = isset($dados['config']['telefonezap']) ? $dados['config']['telefonezap'] : '';
        $tmsel1 = '<select name="{name}" class="{class}" {event} id="{id}">{option}</select>';
		$tmsel2 = '<option value="{value}" {selected}>{option_label}</option>';
        $class_form = 'form-control';
		if($arr_ddi && is_array($arr_ddi)){
            // self::lib_print($arr_ddi);
            if($tmsel1 && $tmsel2){
                $opt = false;
                foreach ($arr_ddi as $ki => $vi) {
                    $opt .= str_replace('{value}',$vi['ddi'],$tmsel2);
                    $option_label = $vi['pais'].' +'.$vi['ddi'];
                    $selected = false;
                    if($vi['ddi']==$ddi){
                        $selected = 'selected';
                    }
                    $opt = str_replace('{option_label}',$option_label,$opt);
                    $opt = str_replace('{selected}',$selected,$opt);
                }
            }
            $input_zap = str_replace('{option}',$opt,$tmsel1);
            $input_zap = str_replace('{name}','config[ddi]',$input_zap);
            $input_zap = str_replace('{id}','ddi',$input_zap);
            $cont_tel = '<input type="tel" value="'.$telefonezap.'" name="config[telefonezap]" required onblur="mask(this,clientes_mascaraTelefone);" onkeypress="mask(this,clientes_mascaraTelefone);" class="form-control" placeholder="Seu whatsapp" />';
            $tms = '<div class="row"><div class="col-12"><label>'.$label.'</label></div><div class="col-3 pr-0">{cont_ddi}</div><div class="col-9 pl-0">{cont_tel}</div></div>';
            $input_zap = str_replace('{cont_ddi}',$input_zap,$tms);
            $input_zap = str_replace('{cont_tel}',$cont_tel,$input_zap);
            $input_zap = str_replace('{class}',$class_form,$input_zap);
            $input_zap = str_replace('{event}','',$input_zap);
        }else{
            $input_zap = false;
        }
        return $input_zap;
    }
    /**
     * verifica se o sistema está cunfigurado para o usuario receber notificação de cadastro de nova conta
     * @return boolean true|false
     */
    public function notific_new_user(){
        $notific_new_user_add = Qlib::qoption('notific_new_user_add')?Qlib::qoption('notific_new_user_add'):'s';
        if($notific_new_user_add=='s'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Metodo para validar a escola na anac atraves do cnpj
     * @param string $cnpj
     * @return array
     */
    public function valida_escola(Request $request){
        if($request->has('cnpj')){
            $cnpj = $request->get('cnpj');
        }else{
            return ['recordsTotal'=>0];
        }
        $curl = curl_init();
        // $cnpj = str_replace('.','',$cnpj);
        // $cnpj = str_replace('/','',$cnpj);
        // $cnpj = str_replace('-','',$cnpj);
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        $data = ['filtros.cnpj'=>$cnpj];
        // dd($cnpj);
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sistemas.anac.gov.br/RBAC141/(X(1)S(bzf31g5fwxb2u1ujulqstrcg))/ciac/GetCiacsList',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>Qlib::lib_array_json($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: TS01ef03de=016a5a6bebed3b8f740efb0b9d341ff728c6a72567f6b0a5da2c74e471d386524ba4d1c05f0b9b7c2559805ab1f2a9c0ba4f9ed6d2'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return Qlib::lib_json_array($response) ;
    }
    /**
     * Metodo para gravar opre
     */
    public function pre_cadastro_escola(Request $request){
        $ret['exec'] = false;
        //Somente usuarios não logados pode acessar essa ares
        if (Auth::check()) {
            $ret['mens'] = Qlib::formatMensagem0('Ação não permitida para usuários logados','danger');
            return $ret;
        }

        if($request->has('cnpj')){
            $cnpj = $request->get('cnpj');
            //verificar se o cadastro de um cnpj está incompleto.
            $du = User::where('cnpj',$cnpj)->get();
            if($du->count() > 0){
                $dus = $du->toArray();$du=$dus[0];
                //verificar se ja é um cadastro do sistema completo
                if(isset($du['config']['origem']) && $du['config']['origem']=='precadastro'){
                    //enviar para a proxima etapa de cadastro
                    $ret['redirect'] = url('/user/create/pj/'.$du['token']);
                    $ret['exec'] = true;
                    return $ret;
                }
                //se for notificar para realizar login
                // dd($du);
            }
            //Validar o cnpj
            $validatedData = $request->validate([
                'email' => ['required','string','unique:users'],
                'cnpj'   =>[new RightCnpj,'required','unique:users']
            ],[
                'cnpj.unique'=>__('CNPJ já cadastrado'),
                'email.unique'=>__('E-mail já cadastrado'),
            ]);
            // dd($request->all());
            $resultado = Qlib::lib_json_array($this->valida_escola($request));
            if(isset($resultado['recordsTotal']) && $resultado['recordsTotal']>0 && isset($resultado['data'])){
                //salvar o precadastro com cnpj e o meta_user dados da validação
                $request->merge([
                    'config' => [
                        'origem'=>'precadastro',
                        'nome_fantasia'=>@$resultado['data'][0]['NomeFantasia'],
                        'cidade'=>@$resultado['data'][0]['Cidade'],
                        'uf'=>@$resultado['data'][0]['Estado'],
                        'CodigoCiac'=>@$resultado['data'][0]['CodigoCiac'],
                    ],
                    // 'cnpj'=>$cnpj,
                    'tipo_pessoa'=>'pj',
                    'razao'=>@$resultado['data'][0]['RazaoSocial'],
                    'meta'=>['validacao_anac'=>$resultado['data']]
                ]);
                // dd($request->all());
                $salvar = $this->store($request);
                if($salvar['exec'] && $salvar['idCad']){
                    $token_user = Qlib::buscaValorDb0('users','id',$salvar['idCad'],'token');
                    $ret['redirect'] = url('/user/create/pj/'.$token_user);
                    $ret['exec'] = $salvar['exec'];
                }
                $ret['salvar'] = $salvar;
            }else{
                //informar que este CNPJ não está cadastrado na ANAC
                // dump($resultado);
                $ret['mens'] = Qlib::formatMensagem0('Este CNPJ não está cadastrado na ANAC','danger');
                $ret['exec'] = false;
            }
            $ret['resultado'] = $resultado;
        }
        return $ret;
    }
}
