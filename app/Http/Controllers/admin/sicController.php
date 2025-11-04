<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\portal\sicController as PortalSicController;
use App\Http\Controllers\portalController;
use App\Http\Controllers\UserController;
use App\Models\_upload;
use App\Models\Sic;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class sicController extends Controller
{
    private $sic;
    public $user;
    public $routa;
    public $label;
    public $url;
    public $ambiente;
    public $pai_motivo;
    public $pai_status;
    public $view;

    public function __construct()
    {
        $user = Auth::user();
        $this->sic = new PortalSicController();
        $this->user = $user;
        $this->ambiente = Qlib::ambiente();
        $this->routa = 'admin.sic';
        $this->label = 'Sic';
        $this->url = 'sic';
        $this->view = 'admin.sic';
        $this->pai_status = 'status_sic';
        $this->pai_motivo = 'motivos_sic';

    }
    public function index(){
        $this->authorize('ler', $this->routa);
        $title = 'Cadastro de sic';
        $titulo = $title;
        $querySic = $this->sic->querySic($_GET);
        $querySic['config']['exibe'] = 'html';
        $routa = $this->routa;
        return view($this->view.'.index',[
            'dados'=>$querySic['sic'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$this->campos_resposta(),
            'sic_totais'=>$querySic['sic_totais'],
            'titulo_tabela'=>$querySic['tituloTabela'],
            'arr_titulo'=>$querySic['arr_titulo'],
            'config'=>$querySic['config'],
            'routa'=>$routa,
            'url'=>$this->url,
            'view'=>$this->view,
            'i'=>0,
        ]);
    }
    public function campos(){
        $user = Auth::user();
        $internauta = new UserController($user);
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'info'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('Preencha os campos abaixo para enviar sua solicitação de informação. Serviço disponibilizado conforme Art. 10, da Lei 12.527/11.','info'),'tam'=>'12'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_permission'=>['label'=>'id_permission','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','value'=>Qlib::qoption('id_permission_front')],
            //'protocolo'=>['label'=>'Protocolo','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            'id_requerente'=>[
                'label'=>'Dados do solicitante',
                'active'=>false,
                'type'=>'html_vinculo',
                'exibe_busca'=>'d-none',
                'event'=>'',
                'tam'=>'12',
                'script'=>'',
                'data_selector'=>[
                    'campos'=>$internauta->campos(),
                    'route_index'=>route('users.index'),
                    'id_form'=>'frm-id_requerente',
                    'tipo'=>'int', // int para somente um ou array para vários
                    'action'=>route('users.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'campo'=>'id_requerente',
                    'value'=>[],
                    'label'=>'Informações do lote',
                    'table'=>[
                        //'id'=>['label'=>'Id','type'=>'text'],
                        // 'nome'=>['label'=>'Nome','type'=>'arr_tab',
                        //     'conf_sql'=>[
                        //         'tab'=>'users',
                        //         'campo_bus'=>'id',
                        //         'select'=>'nome',
                        //         'param'=>['id_permission'],
                        //     ]
                        // ],
                        'nome'=>['label'=>'Nome','type'=>'text'],
                        'email'=>['label'=>'E-mail','type'=>'text'],
                        //'celular'=>['label'=>'Celular','type'=>'text'],
                    ],
                    'tab' =>'users',
                    'placeholder' =>__('Digite somente o nome do usuário').'...',
                    'janela'=>[
                        'url'=>route('users.create').'',
                        'param'=>['id_permission'],
                        'form-param'=>'',
                    ],
                    'salvar_primeiro' =>false,//exigir cadastro do vinculo antes de cadastrar este
                ],
                //'script' =>'familias.loteamento',
            ],
            'config[origem]'=>[
                'label'=>'origem*',
                'active'=>true,
                'id'=>'origem',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='origem_sic'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'6',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][origem',
            ],
            'config[secretaria]'=>[
                'label'=>'Secretaria',
                'active'=>true,
                'id'=>'secretaria',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][secretaria',
            ],
            'config[categoria]'=>[
                'label'=>'Categoria*',
                'active'=>true,
                'id'=>'categoria',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='2'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'12',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][categoria',
            ],
            'config[assunto]'=>['label'=>'Assunto*','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','cp_busca'=>'config][assunto','event'=>'','tam'=>'12'],
            //'nome'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Ensino médio completo','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            //'ativo'=>['label'=>'Ativado','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'mensagem'=>['label'=>'Mensagem*','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'required','tam'=>'12'],
            'arquivo'=>['label'=>'Anexos','active'=>false,'placeholder'=>'Anexar arquivos','type'=>'file','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            'info1'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>'<p>* Formatos de arquivo aceitos: PDF, JPG, JPEG, GIF, PNG, MP4, RAR e ZIP. Tamanho máximo permitido: 10 MB.</p>','tam'=>'12'],
            //'info2'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('<label for="preservarIdentidade"><input name="config[preservarIdentidade]" type="checkbox" id="preservarIdentidade"> Gostaria de ter a minha identidade preservada neste pedido, em atendimento ao princípio constitucional da impessoalidade e, ainda, conforme o disposto no art. 10, § 7º da Lei nº 13.460/2017.</label>','warning'),'tam'=>'12'],
        ];
    }
    public function campos_status($pai=false){
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>''],
            'pai'=>['label'=>'pai','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'3','value'=>$pai],
            'name'=>['label'=>'Nome','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','placeholder'=>'','class_div'=>''],
            'obs'=>['label'=>'Descrição','active'=>true,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12','placeholder'=>''],
            'ativo'=>['label'=>'ativo','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'12','value'=>'s'],
        ];
    }
    public function campos_resposta(){
        $pai_status = $this->pai_status;
        $pai_motivo = $this->pai_motivo;
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'info'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('Preencha os campos abaixo para enviar uma resposta a solicitação acima de informação. Serviço disponibilizado conforme Art. 10, da Lei 12.527/11.','info'),'tam'=>'12'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_requerente'=>['label'=>'id_requerente','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'protocolo'=>['label'=>'Protocolo','active'=>true,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            'config[assunto]'=>['label'=>'Assunto','active'=>false,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','cp_busca'=>'config][assunto','event'=>'required','tam'=>'12'],
            'config[secretaria]'=>[
                'label'=>'Secretaria',
                'active'=>true,
                'id'=>'secretaria',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][secretaria',
            ],
            'config[categoria]'=>[
                'label'=>'Categoria*',
                'active'=>false,
                'id'=>'categoria',
                'type'=>'select',
                'arr_opc'=>$this->arr_tags(2),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'3',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][categoria',
            ],
            'config[origem]'=>[
                'label'=>'Origem*',
                'active'=>true,
                'id'=>'origem',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='origem_sic'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'3',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][origem',
            ],
            'status'=>[
                'label'=>'Status',
                'active'=>true,
                'id'=>'status',
                'type'=>'select',
                'data_selector'=>[
                    'campos'=>$this->campos_status($pai_status),
                    'route_index'=>route('tags.index'),
                    'id_form'=>'frm-tags',
                    'action'=>route('tags.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Tag',
                ],
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$this->pai_status' AND ".Qlib::compleDelete(),'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
            ],
            'motivo'=>[
                'label'=>'Motivos de Negativa de Respostas',
                'active'=>true,
                'id'=>'motivo',
                'type'=>'selector',
                'data_selector'=>[
                    'campos'=>$this->campos_status($pai_motivo),
                    'route_index'=>route('tags.index'),
                    'id_form'=>'frm-tags',
                    'action'=>route('tags.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Tag',
                ],
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$this->pai_motivo'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'6',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
            ],
            //'config[assunto]'=>['label'=>'Assunto*','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-none','cp_busca'=>'config][assunto','event'=>'','tam'=>'12'],
            //'nome'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Ensino médio completo','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            'resposta'=>['label'=>'Resposta*','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'required','tam'=>'12','class'=>'summernote'],
            'meta[enviar_email]'=>['label'=>'Enviar resposta por e-mail','active'=>false,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não'],'cp_busca'=>'meta][enviar_email'],

            //'info1'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>'<p>* Formatos de arquivo aceitos: PDF, JPG, JPEG, GIF, PNG, MP4, RAR e ZIP. Tamanho máximo permitido: 10 MB.</p>','tam'=>'12'],
            //'info2'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('<label for="preservarIdentidade"><input name="config[preservarIdentidade]" type="checkbox" id="preservarIdentidade"> Gostaria de ter a minha identidade preservada neste pedido, em atendimento ao princípio constitucional da impessoalidade e, ainda, conforme o disposto no art. 10, § 7º da Lei nº 13.460/2017.</label>','warning'),'tam'=>'12'],
        ];
    }
    /** MONTA UM ARRAY BASEADO NA TABELA TAG
     * @var pai ordenacao
     * @return array()
     */
    public function arr_tags($pai=2){
        $arr_tag = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$pai'",'nome','id');
        return $arr_tag;
    }
    public function create(User $user)
    {
        $this->authorize('create', $this->url);
        $title = __('Cadastrar Solicitação');
        $titulo = $title;
        //$Users = Users::all();
        //$roles = DB::select("SELECT * FROM roles ORDER BY id ASC");
        $sic = ['ac'=>'cad','token'=>uniqid()];
        $arr_escolaridade = Qlib::lib_escolaridades();
        $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-sics',
            'route'=>$this->routa,
            'url'=>$this->url,
            'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg,zip,rar',
        ];
        $value = [
            'token'=>uniqid(),
            'matricula'=>false,
        ];
        if(!$value['matricula'])
            $config['display_matricula'] = 'd-none';
        $campos = $this->campos();
        return view($this->routa.'.create',[
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
    public function store(Request $request){
        if(auth()->user()->id_permission==Qlib::qoption('id_permission_front')){
            $this->authorize('is_user_front');
        }else{
            $this->authorize('create', $this->url);
        }
        $local = $this->ambiente;
        $dados = $request->all();
        //dd($dados);
        if (isset($dados['anexo']) && $dados['anexo']!='undefined'){
            $validatedData = $request->validate([
                'id_requerente' => ['required'],
                'mensagem' => ['required','string'],
                'anexo' => ['mimes:pdf,jpg,jpeg,gif,mp4,png,rar,zip','max:10000'],
                ],[
                    'id_requerente.required'=>'É necessário informar o solicitante',
                    'mensagem.required'=>'É necessário uma mensagem',
                    'mensagem.string'=>'Mensagem inválida',
                ]);
            }else{
                $validatedData = $request->validate([
                    'id_requerente' => ['required'],
                    'mensagem' => ['required','string'],
                    ],[
                        'id_requerente.required'=>'É necessário informar o solicitante',
                        'mensagem.required'=>'É necessário uma mensagem',
                        'mensagem.string'=>'Mensagem inválida',
                ]);
        }
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'s';
        $userLogadon = Auth::id();
        $dados['autor'] = $userLogadon;
        $dados['id_requerente'] = isset($dados['id_requerente'])?$dados['id_requerente']:$userLogadon;
        // dd($dados);
        $salvar = Sic::create($dados);
        $email = false;
        if(isset($salvar->id)){
            $id = $salvar->id;
            $data['protocolo'] = isset($data['protocolo'])?$data['protocolo']:date('YmdH').'-'.Qlib::zerofill($salvar->id,'4');
            $mens = 'Sua solicitação foi cadastrada com sucesso e gerou o número de protocolo <b>'.$data['protocolo'].'</b>. guarde este número pois será com ele que você consultará o andamento da sua solicitação. Foi enviado um e-mail para sua caixa postal contendo os dados da solicitação.';
            $salvAnexo = false;
            if (isset($dados['anexo']) && $dados['anexo']!='undefined'){
                if($dados['anexo']->isValid()){
                    $nameFile = Str::of($data['protocolo'])->slug('-').'.'.$dados['anexo']->getClientOriginalExtension();
                    $anexo = $dados['anexo']->storeAs('sic/anexo',$nameFile);
                    $salvAnexo = $anexo;
                    //dd($dados['anexo']->getSize());
                }
            }
            $arr_tags = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND (pai='1' OR pai='2')",'nome','id');
            if($salvAnexo){
                $data['arquivo'] = $salvAnexo;
            }else{
                $data['arquivo'] = false;
            }
            $ret['upd_cad'] = Sic::where('id',$id)->update($data);
            $mensagem = $mens.'<br><br>';
            $mensagem .= '<h2>Resumo da Solicitação</h2>';
            $mensagem .= '<ul>';
            if(isset($dados['config']['secretaria']))
                $mensagem .= '<li>Secretaria: <b>'.$arr_tags[$dados['config']['secretaria']].'</b></li>';
            if(isset($dados['config']['categoria']))
                $mensagem .= '<li>Categoria: <b>'.$arr_tags[$dados['config']['categoria']].'</b></li>';
            if(isset($dados['config']['assunto']))
                $mensagem .= '<li>Assunto: <b>'.$dados['config']['assunto'].'</b></li>';
            $mensagem .= '</ul>';
            $mensagem .= '<h4>Mensagem:</h4>';
            $mensagem .= $dados['mensagem'];
            //$mensagem .= '<p>Observação: A confirmação do seu e-mail é obrigatória.</p>';
            $mensagem = str_replace('Foi enviado um e-mail para sua caixa postal contendo os dados da solicitação.','',$mensagem);

            // $email = (new portalController)->enviarEmail([
            //     'mensagem'=>$mensagem,
            //     'arquivos'=>$data['arquivo'],
            //     'nome_supervisor'=>'Responsável por E-sic',
            //     'email_supervisor'=>'ger.maisaqui1@gmail.com',
            // ]);
        }
        //Qlib::lib_print($salvar);
        //dd($ret);
        $route = $this->routa.'.index';
        $ret = [
            'mens'=>$mens,
            'color'=>'success',
            'idCad'=>$salvar->id,
            'email'=>$email,
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
    public function show($id){
        $dados = Sic::findOrFail($id);
        $this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = __('Cadastro da família');
            $titulo = $title;
            $dados['ac'] = 'alt';
            if(isset($dados['config'])){
                $dados['config'] = Qlib::lib_json_array($dados['config']);
            }
            $arr_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades ORDER BY nome ", 'nome', 'id');
            $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
            $listFiles = false;
            //$dados['renda_familiar'] = number_format($dados['renda_familiar'],2,',','.');
            $campos = $this->campos();
            if(isset($dados['token'])){
                $listFiles = _upload::where('token_produto','=',$dados['token'])->get();
            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-familias',
                'route'=>$this->routa,
                'url'=>$this->url,
                'id'=>$id,
                'url'=>$this->url,
                'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg,zip,rar',
            ];
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
                'exec'=>true,
            ];
            // dd($ret);
            return view($this->routa.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }
    public function edit($sic)
    {
        $id = $sic;
        $dados = Sic::where('id',$id)->get();
        $routa = 'sics';
        $this->authorize('update', $this->url);

        if(!empty($dados)){
            $title = 'Responder cadastro de sic';
            $titulo = $title;
            $dados[0]['ac'] = 'alt';
            if(isset($dados[0]['config'])){
                $dados[0]['config'] = Qlib::lib_json_array($dados[0]['config']);
            }
            $listFiles = false;
            $campos_solicitacao = $this->campos();
            $campos = $this->campos_resposta();
            if(isset($dados[0]['token'])){
                $listFiles = _upload::where('token_produto','=',$dados[0]['token'])->get();
            }
            //INICIO DADOS DO SOLICITANTE

            $info_solicitante = [];
            $ds = false;
            if(isset($dados[0]['id_requerente']) && ($id_r=$dados[0]['id_requerente'])){
                if(isset($dados[0]['config']['preservarIdentidade'])&&$dados[0]['config']['preservarIdentidade']=='s'){
                    $info_solicitante['nome'] = 'Anonimo';
                    $info_solicitante['email'] = '';
                    $info_solicitante['telefone'] = '';
                }else{
                    $ds = User::find($id_r);
                }
            }else{
                $ds = User::find($id_r);
            }
            if($ds){
                $info_solicitante = [
                    'id'=>$ds['id'],
                    'nome'=>$ds['nome'],
                    'email'=>$ds['email'],
                    'celular'=>isset($ds['config']['celular'])?$ds['config']['celular']:false,
                ];
            }
            //FIM DADOS DO SOLICITANTE
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-sics',
                'route'=>$this->routa,
                'url'=>$this->url,
                'id'=>$id,
                'info_solicitante'=>$info_solicitante,
                'arquivos'=>'docx,PDF,pdf,jpg,xlsx,png,jpeg,zip,rar',
                'local'=>'sic_admin',
            ];
            $ret = [
                'value'=>$dados[0],
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'listFiles'=>$listFiles,
                'campos'=>$campos,
                'campos_solicitacao'=>$campos_solicitacao,
                'exec'=>true,
            ];
            return view($this->view.'.edit',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($routa.'.index',$ret);
        }
    }
    public function update(Request $request, $id)
    {
        $this->authorize('update', $this->url);
        $validatedData = $request->validate([
            'protocolo' => ['required'],
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
                }else{
                    $data[$key] = $value;
                }
            }
        }
        $userLogadon = Auth::id();
        $data['autor'] = $userLogadon;
        $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        $email = false;
        $mens = false;
        if(!empty($data)){
            $atualizar=Sic::where('id',$id)->update($data);
            if($atualizar){
                $mens = 'Salvo com sucesso!';
            }
            if($atualizar && isset($data['meta']['enviar_email']) && $data['meta']['enviar_email']=='s'){
                $d_sic = Sic::Find($id);
                $d_requerente = false;
                if($d_sic){
                    $d_requerente = User::Find($d_sic['id_requerente']);
                }
                if($d_requerente){
                    $arr_tags = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND (pai='".$this->pai_motivo."' OR pai='".$this->pai_status."')",'nome','id');

                    //if(isset($dados['secretaria']))
                    $assunto = 'Resposta SIC '.$d_sic['protocolo'];
                    $mensagem = '<h2>Resumo da Resposta</h2>';
                    $mensagem .= '<ul>';
                    if(!empty($d_sic['motivo']))
                        $mensagem .= '<li>Motivo: <b>'.$arr_tags[$d_sic['motivo']].'</b></li>';
                    if(!empty($d_sic['status']))
                        $mensagem .= '<li>Status: <b>'.$arr_tags[$d_sic['status']].'</b></li>';
                    // if(isset($dados['config']['categoria']))
                    //     $mensagem .= '<li>Categoria: <b>'.$arr_tags[$dados['config']['categoria']].'</b></li>';
                    // if(isset($dados['config']['assunto']))
                    //     $mensagem .= '<li>Assunto: <b>'.$dados['config']['assunto'].'</b></li>';
                    $mensagem .= '</ul>';
                    $mensagem .= '<h2>Mensagem da resposta:</h2>';
                    $mensagem .= $d_sic['resposta'];
                    //$mensagem = str_replace('Foi enviado um e-mail para sua caixa postal contendo os dados da solicitação.','',$mensagem);
                    $arquivos = false;
                    if(isset($d_sic['token'])){
                        $d_arq = _upload::where('token_produto','=',$d_sic['token'])->get();
                        if($d_arq){
                            foreach ($d_arq as $ka => $va) {
                                $arquivos[] = $va['pasta'];
                            }
                        }
                    }
                    // dd($mensagem);
                    $email = (new PortalSicController)->enviarEmail([
                        'para_email'=>$d_requerente['email'],
                        'para_nome'=>$d_requerente['nome'],
                        'assunto'=>$assunto,
                        'mensagem'=>$mensagem,
                        'arquivos'=>$arquivos,
                        'nome_supervisor'=>'Responsável por E-sic',
                        'email_supervisor'=>'ger.maisaqui1@gmail.com',
                    ]);
                    if($email){
                        $mens .= ' O E-mail com a resposta foi enviado com sucesso para '.$d_requerente['email'];
                    }
                }
            }
            $route = $this->routa.'.index';
            $ret = [
                'exec'=>$atualizar,
                'id'=>$id,
                'mens'=>$mens,
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
    public function mensagem(){

    }
    public function destroy($id,Request $request)
    {
        $this->authorize('delete', $this->url);
        $config = $request->all();
        $ajax =  isset($config['ajax'])?$config['ajax']:'n';
        $routa = $this->routa;
        if (!$post = Sic::find($id)){
            if($ajax=='s'){
                $ret = response()->json(['mens'=>'Registro não encontrado!','color'=>'danger','return'=>route($this->routa.'.index')]);
            }else{
                $ret = redirect()->route($routa.'.index',['mens'=>'Registro não encontrado!','color'=>'danger']);
            }
            return $ret;
        }

        Sic::where('id',$id)->delete();
        if($ajax=='s'){
            $ret = response()->json(['mens'=>__('Registro '.$id.' deletado com sucesso!'),'color'=>'success','return'=>route($this->routa.'.index')]);
        }else{
            $ret = redirect()->route($routa.'.index',['mens'=>'Registro deletado com sucesso!','color'=>'success']);
        }
        return $ret;
    }
    public function dadosRelatorio($request = null)
    {
        //DECLARAÇÕES DE VARIAVEIS
        $title = __('RELATÓRIOS DE INFORMAÇÕES');
        $titulo = $title;
        $titulo2 = __('Relatório Estatístico dos Solicitantes');
        $dataI = false;
        $dataF = false;
        $origem = false;
        $arr_status = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$this->pai_status' AND ".Qlib::compleDelete(),'nome','id','','','data');
        $arr_assuntos = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='2' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'nome','id','','','data');
        $arr_motivos = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$this->pai_motivo' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'nome','id','','','data');
        $arr_escolaridade = Qlib::lib_escolaridades();
        $arr_profissao = Qlib::lib_profissao();
        $arr_tipo_pj = Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='tipo_pj'",'nome','id');
        //FIM DECLARAÇÃO DE VAIRAVEIS

        if($request->isMethod('get') && $request->has(['dataI','dataF','origem'])){
            $dataI = $request->input('dataI');
            $dataF = $request->input('dataF');
            $origem = $request->input('origem');
        }
        $total_users = User::where('id_permission','=',Qlib::qoption('id_permission_front'))->count();
        $campos_form_consulta = [
            'dataI'=>['label'=>'Data inicial','active'=>false,'value'=>@$_GET['dataI'],'placeholder'=>'Data inicial','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>'3'],
            'dataF'=>['label'=>'Data final','active'=>false,'value'=>@$_GET['dataF'],'placeholder'=>'Data final','type'=>'date','exibe_busca'=>'d-block','event'=>'required','tam'=>'3'],
            'origem'=>[
                'label'=>'Origem',
                'active'=>true,
                'id'=>'origem',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='origem_sic'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'3',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'value'=>@$_GET['origem'],
                //'cp_busca'=>'config][secretaria',
            ],
            'btn'=>['label'=>'btn_buscar','active'=>false,'type'=>'html_script','exibe_busca'=>'d-none','event'=>'','tam'=>'3','class'=>'','script'=>'<button type="submit" class="btn btn-secondary btn-block  mt-4">Buscar</button>'],

        ];
        $solicitantes = Sic::where('sics.excluido','=','n')
        ->where('sics.deletado','=','n')
        ->distinct('id_requerente')
        ->join('users', 'users.id', '=', 'sics.id_requerente');

        $totalPedidos = Sic::where('sics.excluido','=','n')
        ->where('sics.deletado','=','n');

        $totalPedidosAbertos = Sic::where('sics.excluido','=','n')
                                    ->where('sics.deletado','=','n')
                                    ->whereNull('status');
        $totalPedidosRespondidos = Sic::where('sics.excluido','=','n')
                                        ->where('sics.deletado','=','n')
                                        ->whereNotNull('status');

        if($dataI && $dataF){
            $solicitantes->whereBetween('sics.created_at',[$dataI,$dataF]);
            $totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
            $totalPedidosAbertos->whereBetween('sics.created_at',[$dataI,$dataF]);
            $totalPedidosRespondidos->whereBetween('sics.created_at',[$dataI,$dataF]);
        }
        if($origem){
            $solicitantes->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
            $totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
            $totalPedidosAbertos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
            $totalPedidosRespondidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
        }

        $totalSolicitantes = $solicitantes->count();
        $totalPedidos = $totalPedidos->count();
        $totalPedidosAbertos = $totalPedidosAbertos->count();
        $totalPedidosRespondidos = $totalPedidosRespondidos->count();

        $res = false;
        $d_rel = [
            'total_users'=>$total_users,
            //'grafico'=>$res,
        ];
        //MONTAGEM DO ARRAY DE STATUS
        if($totalPedidos){
            $arr_color=['rgba(250, 174, 0, 1)','#0571ec','#ad2503','#03973c'];
            $res = [
                ['name'=>'Abertos','y'=>$totalPedidosAbertos,'color'=>$arr_color[0]],
                ['name'=>'Respondidos','y'=>$totalPedidosRespondidos,'color'=>$arr_color[1]],
            ];
            if(is_array($arr_status)){
                $i=2;
                foreach ($arr_status as $k => $v) {
                    $status_totalPedidos = Sic::where('sics.excluido','=','n')
                                                ->where('sics.deletado','=','n')
                                                ->where('status','=',$k);
                    if($dataI && $dataF){
                        $status_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                    }
                    if($origem){
                        $status_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                    }
                    $status_totalPedidos = $status_totalPedidos->count();
                                                //->count();
                    $res0 = ['name'=>$v.'s','y'=>$status_totalPedidos,'color'=>$arr_color[$i]];
                    array_push($res,$res0);
                    $i++;
                }
            }
            $d_rel['grafico'] = $res;
        }

        //MONTAGEM DO ARRAY DE ASSUNTOS

        $res_assunto=[];
        if(is_array($arr_assuntos)){
            $i=2;
            foreach ($arr_assuntos as $k => $v) {
                $assunto_totalPedidos = Sic::where('sics.excluido','=','n')
                                            ->where('sics.deletado','=','n')
                                            ->where('config','LIKE','%"'.$k.'"%');
                if($dataI && $dataF){
                    $assunto_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $assunto_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $assunto_totalPedidos = $assunto_totalPedidos->count();

                $res_assunto0 = ['name'=>$v,'y'=>$assunto_totalPedidos];
                array_push($res_assunto,$res_assunto0);
                $i++;
            }
        }
        $d_rel['grafico_assunto'] = $res_assunto;

        //MONTAGEM DO ARRAY DE MOTIVOS

        $res_motivo=[];
        if(is_array($arr_motivos)){
            $i=2;
            foreach ($arr_motivos as $k => $v) {
                $motivo_totalPedidos = Sic::where('sics.excluido','=','n')
                                            ->where('sics.deletado','=','n')
                                            ->where('motivo','=',$k);
                                            //->count();
                if($dataI && $dataF){
                    $motivo_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $motivo_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $motivo_totalPedidos = $motivo_totalPedidos->count();

                $res_motivo0 = ['name'=>$v,'y'=>$motivo_totalPedidos];
                array_push($res_motivo,$res_motivo0);
                $i++;
            }
        }
        $d_rel['grafico_motivo'] = $res_motivo;

        //MONTAGEM DO ARRAY DE SOLICITANTE
        $arr_tipo_solicitante = [
            'pf'=>__('Pessoa Física'),'pj'=>__('Pessoa Jurídica')
        ];

        $res_solicitante=[];
        if(is_array($arr_tipo_solicitante)){
            foreach ($arr_tipo_solicitante as $k => $v) {
                $solicitante_totalPedidos = Sic::where('sics.excluido','=','n')
                                                ->where('sics.deletado','=','n')
                                                ->distinct('id_requerente')
                                                ->join('users', 'users.id', '=', 'sics.id_requerente')
                                                ->where('users.tipo_pessoa','=',$k);
                if($dataI && $dataF){
                    $solicitante_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $solicitante_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $solicitante_totalPedidos = $solicitante_totalPedidos->count();
                $res_solicitante0 = ['name'=>$v,'y'=>$solicitante_totalPedidos];
                array_push($res_solicitante,$res_solicitante0);
            }
        }
        $d_rel['grafico_solicitante'] = $res_solicitante;

        //MONTAGEM DO ARRAY DE GÊNERO

        $arr_tipo_genero = Qlib::lib_sexo();

        $res_genero=[];
        if(is_array($arr_tipo_genero)){
            foreach ($arr_tipo_genero as $k => $v) {
                $genero_totalPedidos = Sic::where('sics.excluido','=','n')
                                            ->where('sics.deletado','=','n')
                                            ->distinct('id_requerente')
                                            ->join('users', 'users.id', '=', 'sics.id_requerente')
                                            ->where('users.genero','=',$k);
                                            //->count();
                if($dataI && $dataF){
                    $genero_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $genero_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $genero_totalPedidos = $genero_totalPedidos->count();
                $res_genero0 = ['name'=>$v,'y'=>$genero_totalPedidos];
                array_push($res_genero,$res_genero0);
            }
        }
        $d_rel['grafico_genero'] = $res_genero;

        //MONTAGEM DO ARRAY DE ESCOLARIDADE

        $res_escolaridade=[];
        if(is_array($arr_escolaridade)){
            foreach ($arr_escolaridade as $k => $v) {
                $escolaridade_totalPedidos = Sic::where('sics.excluido','=','n')
                                                ->where('sics.deletado','=','n')
                                                ->distinct('id_requerente')
                                                ->join('users', 'users.id', '=', 'sics.id_requerente')
                                                ->where('users.config','LIKE','%"escolaridade":"'.$k.'"%');
                                                //->count();
                if($dataI && $dataF){
                    $escolaridade_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $escolaridade_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $escolaridade_totalPedidos = $escolaridade_totalPedidos->count();
                $res_escolaridade0 = ['name'=>$v,'y'=>$escolaridade_totalPedidos];
                array_push($res_escolaridade,$res_escolaridade0);
            }
        }
        $d_rel['grafico_escolaridade'] = $res_escolaridade;


        //MONTAGEM DO ARRAY DE PROFISSÃO

        $res_profissao=[];
        if(is_array($arr_profissao)){
            foreach ($arr_profissao as $k => $v) {
                $profissao_totalPedidos = Sic::where('sics.excluido','=','n')
                                                ->where('sics.deletado','=','n')
                                                ->distinct('id_requerente')
                                                ->join('users', 'users.id', '=', 'sics.id_requerente')
                                                ->where('users.config','LIKE','%"profissao":"'.$k.'"%');
                if($dataI && $dataF){
                    $profissao_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $profissao_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $profissao_totalPedidos = $profissao_totalPedidos->count();
                $res_profissao0 = ['name'=>$v,'y'=>$profissao_totalPedidos];
                array_push($res_profissao,$res_profissao0);
            }
        }
        $d_rel['grafico_profissao'] = $res_profissao;



        //MONTAGEM DO ARRAY DE TIPO PJ

        $res_tipo_pj=[];
        if(is_array($arr_tipo_pj)){
            foreach ($arr_tipo_pj as $k => $v) {
                $tipo_pj_totalPedidos = Sic::where('sics.excluido','=','n')
                                                ->where('sics.deletado','=','n')
                                                ->distinct('id_requerente')
                                                ->join('users', 'users.id', '=', 'sics.id_requerente')
                                                ->where('users.config','LIKE','%"tipo_pj":"'.$k.'"%');
                if($dataI && $dataF){
                    $tipo_pj_totalPedidos->whereBetween('sics.created_at',[$dataI,$dataF]);
                }
                if($origem){
                    $tipo_pj_totalPedidos->where('sics.config','LIKE','%"origem":"'.$origem.'"%');
                }
                $tipo_pj_totalPedidos = $tipo_pj_totalPedidos->count();
                $res_tipo_pj0 = ['name'=>$v,'y'=>$tipo_pj_totalPedidos];
                array_push($res_tipo_pj,$res_tipo_pj0);
            }
        }
        $d_rel['grafico_tipo_pj'] = $res_tipo_pj;


        //dd($d_rel);
        if(is_array($res)){

            $totais_gerais = [
                ['label'=>__('Total de Solicitantes'),'value'=>$totalSolicitantes],
                ['label'=>__('Total de Pedidos'),'value'=>$totalPedidos],
                ['label'=>__('Pedidos em Aberto'),'value'=>$res[0]['y']],
                ['label'=>__('Pedidos Respondidos'),'value'=>$res[1]['y']],
                ['label'=>__('Pedidos Ideferidos'),'value'=>$res[2]['y']],
                ['label'=>__('Pedidos Resolvidos'),'value'=>$res[3]['y']],
            ];
        }
        else{
            $totais_gerais = [];
        }
        $ret = [
            'title'=>$title,
            'titulo'=>$titulo,
            'titulo2'=>$titulo2,
            'd_rel' =>$d_rel,
            'campos_form_consulta' =>$campos_form_consulta,
            'totais_gerais' =>$totais_gerais,
        ];
        return $ret;
    }
    public function relatorios(Request $request){
        $ret = $this->dadosRelatorio($request);
        return view($this->routa.'.relatorios',$ret);
    }
}
