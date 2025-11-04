<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\wp\ApiWpController;
use App\Http\Controllers\UserController;
use App\Http\Requests\StoreBeneficiarioRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use stdClass;
use App\Models\Post;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PhpParser\Node\Stmt\TryCatch;

class portalController extends Controller
{
    protected $user;
    public $routa;
    public $label;
    public $view;
    public $post_type;
    public $prefixo_site;
    public $prefixo_admin;
    public $pg;
    public $i_wp;//integração com wp
    public $wp_api;
    public $m_email;//mensagem de emivo de mail
    public function __construct()
    {
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $type = false;
        if($seg1){
            $type = substr($seg1,0,-1);
        }
        $this->post_type = $type;
        $this->pg = $seg1;
        $this->m_email = Qlib::qoption('mens_sucesso_cad_esic') ? Qlib::qoption('mens_sucesso_cad_esic') : '<br>Foi enviado um email de confirmação de cadastro para você. <p><b>Atenção:</b> para que consiga enviar uma solicitação é necessário confirmar o seu cadastro, acessando o email que foi enviado para sua caixa de entrada.</p><p><b>Alerta:</b> Se não encontar em sua caixa de entrada verifique a caixa de spam ou de lixo eletrônico, de seu email</p>';
        //$this->user = $user;
        $this->routa = $this->pg;
        $this->prefixo_admin = config('app.prefixo_admin');
        $this->prefixo_site = config('app.prefixo_site');
        $this->label = 'Portal';
        $this->view = 'portal';
        $this->i_wp = Qlib::qoption('i_wp');//indegração com Wp s para sim
        $this->wp_api = new ApiWpController();
        //$this->routeIndex = route('internautas.index');
    }
    public function index(Request $request)
    {
        //if($this->pg==NULL){
            // $user = Auth::user();
            if($request->get('exec')==1){
                return redirect()->route('home');
            }else{
                return view('portal.index',['prefixo_site'=>$this->prefixo_site,'prefixo_admin'=>$this->prefixo_admin]);
            }
        //}
    }
    public function cadInternautas($tipo = null)
    {
        if(Auth::check()){
            return redirect()->route('sic.index');
        }
        $tp = $tipo?$tipo:'pf';
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-cad-internautas',
            'route'=>'internautas',
            'tipo'=>$tp,
            'arquivos'=>'jpeg,jpg,png',
            'event'=>'enctype="multipart/form-data"',
            'ambiente'=>'front',
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $title = __('Cadastrar internautas');
        $titulo = $title;
        $campos = $this->camposCadInternautas();
        return view('portal.internautas.cadastrar',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);

    }
    public function camposCadInternautas($sec=false)
    {
        $sec = $sec?$sec:request()->segment(3);
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
            'sep0'=>['label'=>'informações','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Informe seus dados').'</h4><hr>','script_show'=>''],
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'tipo_pessoa'=>[
                'label'=>'',
                'active'=>true,
                'type'=>'radio_btn',
                'arr_opc'=>['pf'=>'Pessoa Física','pj'=>'Pessoa Jurídica'],
                'exibe_busca'=>'d-block',
                'event'=>'onclick=selectTipoUser(this.value)',
                'tam'=>'12',
                'value'=>$sec,
                'class'=>'btn btn-outline-primary',
            ],
            'info_obs'=>['label'=>'Informações obs','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>$info_obs,'script_show'=>''],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'email'=>['label'=>'E-mail *','active'=>false,'type'=>'email','exibe_busca'=>'d-none','event'=>'required','tam'=>'6','placeholder'=>''],
            'password'=>['label'=>'Senha *','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'required','tam'=>'3','placeholder'=>''],
            'password_confirmation'=>['label'=>'Confirmar Senha *','active'=>false,'type'=>'password','exibe_busca'=>'d-none','event'=>'required','tam'=>'3','placeholder'=>''],
            'name'=>['label'=>$lab_nome,'active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'9','placeholder'=>''],
            'cpf'=>['label'=>$lab_cpf,'active'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cpf','tam'=>'3'],
            'cnpj'=>['label'=>'CNPJ *','active'=>true,'type'=>'tel','exibe_busca'=>'d-block','event'=>'mask-cnpj required','tam'=>'4','class_div'=>'div-pj '.$displayPj],
            'razao'=>['label'=>'Razão social *','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'required','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[nome_fantasia]'=>['label'=>'Nome fantasia','active'=>false,'type'=>'text','exibe_busca'=>'d-none','event'=>'','tam'=>'4','placeholder'=>'','class_div'=>'div-pj '.$displayPj],
            'config[celular]'=>['label'=>'Telefone celular','active'=>true,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][celular'],
            'config[telefone_residencial]'=>['label'=>'Telefone residencial','active'=>true,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','class_div'=>'div-pf '.$displayPf,'cp_busca'=>'config][telefone_residencial'],
            'config[telefone_comercial]'=>['label'=>'Telefone comercial','active'=>true,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','cp_busca'=>'config][telefone_comercial'],
            'config[rg]'=>['label'=>'RG','active'=>true,'type'=>'tel','tam'=>'4','exibe_busca'=>'d-block','event'=>'','cp_busca'=>'config][rg','class_div'=>'div-pf '.$displayPf],
            'config[nascimento]'=>['label'=>'Data de nascimento','active'=>true,'type'=>'date','tam'=>'4','exibe_busca'=>'d-block','event'=>'','cp_busca'=>'config][nascimento','class_div'=>'div-pf '.$displayPf],
            'genero'=>[
                'label'=>'Sexo',
                'active'=>true,
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
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::lib_escolaridades(),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'select2',
                'cp_busca'=>'config][escolaridade','class_div'=>'div-pf '.$displayPf,
            ],
            'config[profissao]'=>[
                'label'=>'Profissão',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::lib_profissao(),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'select2',
                'cp_busca'=>'config][profissao','class_div'=>'div-pf '.$displayPf,
            ],
            'config[tipo_pj]'=>[
                'label'=>'Tipo de Pessoa Jurídica',
                'active'=>true,
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='tipo_pj'",'nome','id'),'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'4',
                'class'=>'select2',
                'cp_busca'=>'config][tipo_pj','class_div'=>'div-pj '.$displayPj,
            ],
            'sep1'=>['label'=>'Endereço','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Endereço').'</h4><hr>','script_show'=>''],
            'config[cep]'=>['label'=>'CEP','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'mask-cep onchange=buscaCep1_0(this.value)','tam'=>'3'],
            'config[endereco]'=>['label'=>'Endereço','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'q-inp=endereco','tam'=>'7','cp_busca'=>'config][endereco'],
            'config[numero]'=>['label'=>'Numero','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'q-inp=numero','tam'=>'2','cp_busca'=>'config][numero'],
            'config[complemento]'=>['label'=>'Complemento','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'4','cp_busca'=>'config][complemento'],
            'config[cidade]'=>['label'=>'Cidade','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'q-inp=cidade','tam'=>'6','cp_busca'=>'config][cidade'],
            'config[uf]'=>['label'=>'UF','active'=>false,'js'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','event'=>'q-inp=uf','tam'=>'2','cp_busca'=>'config][uf'],
            //'foto_perfil'=>['label'=>'Foto','active'=>false,'js'=>true,'placeholder'=>'','type'=>'file','exibe_busca'=>'d-none','event'=>'','tam'=>'12'],
            'sep2'=>['label'=>'Preferencias','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'12','script'=>'<h4 class="text-center">'.__('Preferências').'</h4><hr>','script_show'=>''],
            'preferencias[newslatter]'=>['label'=>'Desejo receber e-mails com as novidades','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-none','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']],


        ];
        return $ret;
    }
    public function storeInternautas(StoreUserRequest $request)
    {
        /*
        $validatedData = $request->validate([
            'nome' => ['required','string','unique:users'],
            'email' => ['required','string','unique:users'],
        ]);*/
        $dados = $request->all();
        //dd($dados);
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $pass = '';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'n';
        if(isset($dados['password']) && !empty($dados['password'])){
            $pass = $dados['password'];
            $dados['password'] = Hash::make($dados['password']);
        }else{
            if(empty($dados['password'])){
                unset($dados['password']);
            }
        }
        $dados['id_permission'] = Qlib::qoption('id_permission_front')? Qlib::qoption('id_permission_front'): 5;
        $dados['ativo'] = 's';
        $salvar = User::create($dados);
        $route = $this->routa.'.index';
        $enviarEmail = false;
        $exec = false;
        $redirect = false;
        if($id=$salvar->id){
            $mens = 'Cadastro realizado com sucesso. ';
            $salvos = User::FindOrFail($id);
            if($salvos){
                $credentials = ['email' => $dados['email'], 'password' => $pass, 'ativo' => 's', 'excluido' => 'n'];
                $logado = Auth::guard('web')->attempt($credentials, $request->filled('remember'));
                // $logado = Auth::attempt([
                //     'email'=>$dados['email'],
                //     'password'=>$dados['password'],
                // ]);
                try {
                    $enviarEmail = Mail::send(new \App\Mail\veriUser($salvos));
                    $mens .= $this->m_email;
                    $exec = true;
                    if(!$logado){
                        $redirect = url('/internautas/login?email='.$dados['email'].'&password='.$pass);
                    }else{
                        $redirect = url('/internautas/sics');
                    }
                } catch (\Throwable $e) {
                    $mens .= $e->getMessage();
                }
                // if(count(Mail::failures()) > 0){
                //     $mens .= 'Falha ao enviar e-mail entre em contato com o nosso suporte!';
                // }else{
                //     $mens .= 'Um e-mail foi enviado para sua caixa de e-mails, contendo um link para ativação do seu cadastro. Caso não encontre na caixa de entrada, por favor consulte o spam. <p>Lembre-se é <b>obrigatório a confirmação do E-mail</b> para ativação do seu cadastro e poder utilizar os <b>serviços do portal.</b></p>';
                // }
            }else{
                $mens .= ' Mais não foi encontrado!';

            }
        }
        $ret = [
            'mens'=>$mens,
            'color'=>'success',
            'idCad'=>$salvar->id,
            'exec'=>$exec,
            'redirect'=>$redirect,
            'login'=>true,
            'enviarEmail'=>$enviarEmail,
            'dados'=>$dados
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }
    /**
     * Metodo para reenviar um email de verificação
     */
    public function send_verific_user(){
        $ret['exec'] = false;
        $ret['mens'] = false;
        $ret['color'] = 'danger';
        try {
            $user = Auth::user();
            $enviarEmail = Mail::send(new \App\Mail\veriUser($user));
            $mens = $this->m_email;
            $ret['exec'] = true;
            $ret['color'] = 'success';
        } catch (\Throwable $e) {
            $mens = $e->getMessage();
        }
        $ret['mens'] = $mens;
        return response()->json($ret);
    }
    public function loginInternautas(Request $request)
    {
        return $this->manualLogin($request);
    }
    public function logoutInternautas($var = null)
    {
        Auth::logout();
        return redirect()->route('internautas.index');

    }
    /**
     * Metodo usado para um login manual depos de verifcar uma conta
     */
    public function manualLogin($id){
        $user = User::find($id);
        Auth::login($user);
        if(Auth::check()){
            return 'usuario '.$user->nome.'Logado com sucesso';
        }
        return redirect('/');
    }
    public function quick_login(Request $request){
        return (new LoginController)->login($request);
    }
    public function acaoInternautas( $tipo,$id)
    {
        $ret['exec'] = false;
        if($tipo=='veriuser'){
            //Verifica usuarios
            $email = base64_decode($id);
            if($email){
                $atualiza = User::where('email','=',$email)->update([
                   'verificado'=>'s',
                ]);
                if($atualiza){
                    $user = User::where('email','=',$email)->get();
                    if($id=$user[0]->id){
                        $ret['mens'] = $this->manualLogin($id);
                        if($user[0]->verificado=='s'){
                            $ret['exec'] = true;
                        }
                    }
                }
            }
            return redirect()->route('internautas.index',$ret);
        }
    }
}
