<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\admin\sicController as AdminSicController;
use App\Http\Controllers\Controller;

use stdClass;
use Illuminate\Http\Request;
use App\Qlib\Qlib;
use App\Models\User;
use App\Models\_upload;
use App\Models\Sic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class sicController extends Controller
{
    protected $user;
    //public $sic_admin;
    public $routa;
    public $label;
    public $ambiente;
    public function __construct()
    {
        $user = Auth::user();
        //$this->middleware('auth');
        //$this->sic_admin = new AdminSicController();
        $this->user = $user;
        $this->routa = 'sic';
        $this->label = 'Sic';
        $this->ambiente = Qlib::ambiente(); //Verifica se estamos no backend ou no frontend
        $this->view = 'padrao';
    }
    public function querySic($get=false,$campos=false)
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
        $sic =  Sic::where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);
        //$sic =  DB::table('sics')->where('excluido','=','n')->where('deletado','=','n')->orderBy('id',$config['order']);

        $sic_totais = new stdClass;
        $campos = $campos?$campos: (new AdminSicController())->campos_resposta();
        $tituloTabela = 'Lista de todos cadastros';
        $arr_titulo = false;
        if(isset($get['filter'])){
                $titulo_tab = false;
                $i = 0;
                foreach ($get['filter'] as $key => $value) {
                    if(!empty($value)){
                        if($key=='id'){
                            $sic->where($key,'LIKE', $value);
                            $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            $arr_titulo[$campos[$key]['label']] = $value;
                        }else{
                            if(is_array($value)){
                                foreach ($value as $k1 => $v1) {
                                    if(!empty($v1)){

                                        $sic->where($key,'LIKE','%'. $v1. '%');
                                        if(@$campos[$key]['type']=='select'){
                                            $v1 = $campos[$key]['arr_opc'][$v1];
                                        }
                                        @$arr_titulo[$campos[$key]['label']] = $v1;
                                        $titulo_tab .= 'Todos com *'. @$campos[$key]['label'] .'% = '.$v1.'& ';
                                    }
                                }
                            }else{
                                $value = trim($value);
                                //dd($campos);
                                $sic->where($key,'LIKE','%'. $value. '%');
                                if($campos[$key]['type']=='select'){
                                    $value = $campos[$key]['arr_opc'][$value];
                                }
                                $arr_titulo[$campos[$key]['label']] = $value;
                                $titulo_tab .= 'Todos com *'. $campos[$key]['label'] .'% = '.$value.'& ';
                            }
                        }
                        $i++;
                    }
                }
                if($titulo_tab){
                    $tituloTabela = 'Lista de: &'.$titulo_tab;
                                //$arr_titulo = explode('&',$tituloTabela);
                }
                $fm = $sic;
                if($config['limit']=='todos'){
                    $sic = $sic->get();
                }else{
                    $sic = $sic->paginate($config['limit']);
                }
        }else{
            $fm = $sic;
            if($config['limit']=='todos'){
                $sic = $sic->get();
            }else{
                $sic = $sic->paginate($config['limit']);
            }
        }
        $sic_totais->todos = $fm->count();
        $sic_totais->esteMes = $fm->whereYear('created_at', '=', $ano)->whereMonth('created_at','=',$mes)->count();
        $sic_totais->ativos = $fm->where('ativo','=','s')->count();
        $sic_totais->inativos = $fm->where('ativo','=','n')->count();
        $ret['sic'] = $sic;
        $ret['sic_totais'] = $sic_totais;
        $ret['arr_titulo'] = $arr_titulo;
        $ret['campos'] = $campos;
        $ret['config'] = $config;
        $ret['tituloTabela'] = $tituloTabela;
        $ret['config']['resumo'] = [
            'todos_registro'=>['label'=>'Todos cadastros','value'=>$sic_totais->todos,'icon'=>'fas fa-calendar'],
            'todos_mes'=>['label'=>'Cadastros recentes','value'=>$sic_totais->esteMes,'icon'=>'fas fa-calendar-times'],
            'todos_ativos'=>['label'=>'Cadastros ativos','value'=>$sic_totais->ativos,'icon'=>'fas fa-check'],
            'todos_inativos'=>['label'=>'Cadastros inativos','value'=>$sic_totais->inativos,'icon'=>'fas fa-archive'],
        ];
        return $ret;
    }
    public function campos(){
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'type'=>['label'=>'type','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2','value'=>'solicitacao'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'info'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('Preencha os campos abaixo para enviar sua solicitação de informação. Serviço disponibilizado conforme Art. 10, da Lei 12.527/11.','info'),'tam'=>'12'],
            'config[secretaria]'=>[
                'label'=>'Secretaria*',
                'active'=>true,
                'id'=>'secretaria',
                'type'=>'select',
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='1'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'required',
                'tam'=>'12',
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
                'event'=>'required',
                'tam'=>'12',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
                'cp_busca'=>'config][categoria',
            ],
            'config[assunto]'=>['label'=>'Assunto*','active'=>true,'placeholder'=>'','type'=>'text','exibe_busca'=>'d-block','cp_busca'=>'config][assunto','event'=>'required','tam'=>'12'],
            'config[origem]'=>['label'=>'origem','active'=>false,'placeholder'=>'','type'=>'hidden','exibe_busca'=>'d-block','cp_busca'=>'config][origem','event'=>'','value'=>'sistema','tam'=>'12'],
            //'nome'=>['label'=>'Nome','active'=>true,'placeholder'=>'Ex.: Ensino médio completo','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            //'ativo'=>['label'=>'Ativado','active'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'3','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            'mensagem'=>['label'=>'Mensagem*','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'required','tam'=>'12'],
            'anexo'=>['label'=>'Anexos','active'=>false,'placeholder'=>'Anexar arquivos','type'=>'file','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
            'info1'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>'<p>* Formatos de arquivo aceitos: PDF, JPG, JPEG, GIF, PNG, MP4, RAR e ZIP. Tamanho máximo permitido: 10 MB.</p>','tam'=>'12'],
            'info2'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('<label for="preservarIdentidade"><input name="config[preservarIdentidade]" value="s" type="checkbox" id="preservarIdentidade"> Gostaria de ter a minha identidade preservada neste pedido, em atendimento ao princípio constitucional da impessoalidade e, ainda, conforme o disposto no art. 10, § 7º da Lei nº 13.460/2017.</label>','warning'),'tam'=>'12'],
        ];
    }
    /**ARRAY CONTENDO INFORMAÇÕES PARA MOTAR A TELA DE REPOSTA DO SIC PARO O INTERNAUTA
     * @retun array
     */
    public function campos_resposta($config=false){
        $sicControllerAdmin = new AdminSicController;
        $pai_status = $sicControllerAdmin->pai_status;
        $pai_motivo = $sicControllerAdmin->pai_motivo;
        $dados = isset($config['dados'])?$config['dados']:false;
        return [
            'id'=>['label'=>'Id','active'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'info'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('Preencha os campos abaixo para enviar uma resposta a solicitação acima de informação. Serviço disponibilizado conforme Art. 10, da Lei 12.527/11.','info'),'tam'=>'12'],
            'token'=>['label'=>'token','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            'id_requerente'=>['label'=>'id_requerente','active'=>false,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
            //'protocolo'=>['label'=>'Protocolo','active'=>true,'placeholder'=>'','type'=>'hidden_text','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
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
                'label'=>'Categoria',
                'active'=>false,
                'id'=>'categoria',
                'type'=>'select',
                'arr_opc'=>$sicControllerAdmin->arr_tags(2),
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
                    'campos'=>$sicControllerAdmin->campos_status($pai_status),
                    'route_index'=>route('tags.index'),
                    'id_form'=>'frm-tags',
                    'action'=>route('tags.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Tag',
                ],
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$pai_status' AND ".Qlib::compleDelete(),'nome','id'),
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
                    'campos'=>$sicControllerAdmin->campos_status($pai_motivo),
                    'route_index'=>route('tags.index'),
                    'id_form'=>'frm-tags',
                    'action'=>route('tags.store'),
                    'campo_id'=>'id',
                    'campo_bus'=>'nome',
                    'label'=>'Tag',
                ],
                'arr_opc'=>Qlib::sql_array("SELECT id,nome FROM tags WHERE ativo='s' AND pai='$pai_motivo'",'nome','id'),
                'exibe_busca'=>'d-block',
                'event'=>'',
                'tam'=>'12',
                'class'=>'',
                'title'=>'',
                'exibe_busca'=>true,
                'option_select'=>true,
            ],
            'mensagem'=>['label'=>'Mensagem Inicial','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'required','tam'=>'12'],
            'resposta'=>['label'=>'Resposta','active'=>false,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'required','tam'=>'12','class'=>'summernote'],
            'anexo'=>['label'=>'Anexos','active'=>false,'placeholder'=>'Anexar arquivos','type'=>'file','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],            'show_file_front'=>['label'=>'Info1','active'=>false,'type'=>'show_file_front','script'=>'portal.sic_front.show_files','tam'=>'12','value'=>$dados],
            //'info2'=>['label'=>'Info1','active'=>false,'type'=>'html_script','script'=>Qlib::formatMensagemInfo('<label for="preservarIdentidade"><input name="config[preservarIdentidade]" type="checkbox" id="preservarIdentidade"> Gostaria de ter a minha identidade preservada neste pedido, em atendimento ao princípio constitucional da impessoalidade e, ainda, conforme o disposto no art. 10, § 7º da Lei nº 13.460/2017.</label>','warning'),'tam'=>'12'],
        ];
    }
    public function index()
    {
        //$this->authorize('ler', $this->routa);
        $title = 'Cadastro de sic';
        $titulo = $title;
        $_GET['filter']['id_requerente'] = Auth::id();
        $querySic = $this->querySic($_GET);
        $querySic['config']['exibe'] = 'html';
        $routa = $this->routa;
        $local = $this->ambiente;
        if($local=='front'){
            $view = 'portal.sic_front';
        }
        $sicControllerAdmin = new AdminSicController;
        $ret = [
            'dados'=>$querySic['sic'],
            'title'=>$title,
            'titulo'=>$titulo,
            'campos_tabela'=>$querySic['campos'],
            'sic_totais'=>$querySic['sic_totais'],
            'titulo_tabela'=>$querySic['tituloTabela'],
            'arr_titulo'=>$querySic['arr_titulo'],
            'config'=>$querySic['config'],
            'routa'=>$routa,
            'arr_categorias'=>$sicControllerAdmin->arr_tags(2),
            'arr_motivos'=>$sicControllerAdmin->arr_tags($sicControllerAdmin->pai_motivo),
            'arr_status'=>$sicControllerAdmin->arr_tags($sicControllerAdmin->pai_status),
            'view'=>$this->view,
            'i'=>0,
        ];
        return view($view.'.index',$ret);
    }
    public function create(User $user)
    {
        // $this->middleware('auth');
        // if(!Auth::check()){
        //     //return redirect()->route('login');
        //     return false;
        // }
        if(auth()->user()->id_permission==Qlib::qoption('id_permission_front')){
            $this->authorize('is_user_front');
        }else{
            $this->authorize('create', $this->routa);
        }
        $local = $this->ambiente;
        $title = 'Cadastrar sic';
        $titulo = $title;
        $config = [
            'ac'=>'cad',
            'frm_id'=>'frm-sics',
            'route'=>$this->routa,
            'url'=>$this->routa,
            'ambiente'=>$local,
            'event'=>'enctype=multipart/form-data',
        ];
        $value = [
            'token'=>uniqid(),
        ];
        $campos = $this->campos();

        $view = $this->view;
        if($local=='front'){
            $view = 'portal.sic_front';
        }
        return view($view.'.createedit',[
            'config'=>$config,
            'title'=>$title,
            'titulo'=>$titulo,
            'campos'=>$campos,
            'value'=>$value,
        ]);
    }
    public function store(Request $request)
    {
        if(auth()->user()->id_permission==Qlib::qoption('id_permission_front')){
            $this->authorize('is_user_front');
        }else{
            $this->authorize('create', $this->routa);
        }
        $local = $this->ambiente;
        $dados = $request->all();
        if (isset($dados['anexo']) && $dados['anexo']!='undefined'){
            $validatedData = $request->validate([
                'mensagem' => ['required','string'],
                'anexo' => ['mimes:pdf,jpg,jpeg,gif,mp4,png,rar,zip','max:10000'],
                [
                    'mensagem.required'=>'É necessário uma mensagem',
                    'mensagem.string'=>'Mensagem inválida',
                ]
            ]);
            //if($dados['anexo']->isValid()){
                // $verTipoArq = Qlib::verificaArquivo($dados['anexo'],'pdf,jpg,jpeg,gif,png,mp4,rar,zip');
                // if($verTipoArq['mens']){
                //     $ret = [
                //         'mens'=>$verTipoArq['mens'],
                //         'color'=>'danger',
                //         'idCad'=>false,
                //         'exec'=>false,
                //         'dados'=>$dados
                //     ];
                //     //dd($ret);
                //     return $ret;
                // }
            //}
        }else{
            $validatedData = $request->validate([
                'mensagem' => ['required','string'],
                [
                    'mensagem.required'=>'É necessário uma mensagem',
                    'mensagem.string'=>'Mensagem inválida',
                ]
            ]);
        }
        $ajax = isset($dados['ajax'])?$dados['ajax']:'n';
        $dados['ativo'] = isset($dados['ativo'])?$dados['ativo']:'s';
        $userLogadon = Auth::id();
        $dados['autor'] = $userLogadon;
        $dados['id_requerente'] = isset($dados['id_requerente'])?$dados['id_requerente']:$userLogadon;
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
            $mensagem .= __('Seu protocolo é: ').'<b>'.$data['protocolo'].'</b><br>'.$dados['mensagem'];
            //$mensagem .= '<p>Observação: A confirmação do seu e-mail é obrigatória.</p>';
            $mensagem = str_replace('Foi enviado um e-mail para sua caixa postal contendo os dados da solicitação.','',$mensagem);

            $email = $this->enviarEmail([
                'mensagem'=>$mensagem,
                'arquivos'=>$data['arquivo'],
                'nome_supervisor'=>'Responsável por E-sic',
                'email_supervisor'=>'ger.maisaqui1@gmail.com',
            ]);
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
            // 'mensagem'=>@$mensagem,
            'dados'=>$dados
        ];

        if($ajax=='s'){
            $ret['return'] = route($route).'?idCad='.$salvar->id;
            // $ret['redirect'] = route($this->routa.'.edit',['id'=>$salvar->id]);
            $ret['redirect'] = route('sic.internautas.relatorios');
            return response()->json($ret);
        }else{
            return redirect()->route($route,$ret);
        }
    }

    public function enviarEmail($config=false)
    {
        $ret = false;
        if($config){
            $para_email = isset($config['para_email'])?$config['para_email']:'';
            $para_nome = isset($config['para_nome'])?$config['para_nome']:'';
            $assunto = isset($config['assunto'])?$config['assunto']:'';
            $mensagem = isset($config['mensagem'])?$config['mensagem']:'';
            $assunto_supervisor = isset($config['assunto_supervisor'])?$config['assunto_supervisor']:'';
            $mensagem_supervisor = isset($config['mensagem_supervisor'])?$config['mensagem_supervisor']:'';
            $arquivos = isset($config['arquivos'])?$config['arquivos']:'';
            $nome_supervisor = isset($config['nome_supervisor'])?$config['nome_supervisor']:Qlib::qoption('nome_responsavel_sic');
            $email_supervisor = isset($config['email_supervisor'])?$config['email_supervisor']:Qlib::qoption('email_responsavel_sic');

            $info = new \App\Mail\sic\infoSolicitacao([
                'para_email'=>$para_email,
                'para_nome'=>$para_nome,
                'assunto'=>$assunto,
                'mensagem'=>$mensagem,
                'arquivos'=>$arquivos,
                'nome_supervisor'=>$nome_supervisor,
                'email_supervisor'=>$email_supervisor,
                'assunto_supervisor'=>$assunto_supervisor,
                'mensagem_supervisor'=>$mensagem_supervisor,
            ]);
            try {
                Mail::send($info);
                $ret = true;
            } catch (\Throwable $th) {
                $ret = false;
                //throw $th;
            }

            // if(count(Mail::failures())==0){
            // }
        }
        return $ret;
    }
    public function show($id){
        $dados = Sic::findOrFail($id);
        //$this->authorize('ler', $this->routa);
        if(!empty($dados)){
            $title = __('Solicitação').' '.$dados['protocolo'];
            $titulo = $title;
            $dados['ac'] = 'alt';
            if(isset($dados['config'])){
                $dados['config'] = Qlib::lib_json_array($dados['config']);
            }
            $arr_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades ORDER BY nome ", 'nome', 'id');
            $arr_estadocivil = Qlib::sql_array("SELECT id,nome FROM estadocivils ORDER BY nome ", 'nome', 'id');
            $listFiles = false;
            //$dados['renda_familiar'] = number_format($dados['renda_familiar'],2,',','.');
            $campos = $this->campos_resposta();
            if(isset($dados['token'])){
                $listFiles = _upload::where('token_produto','=',$dados['token'])->get();
            }
            $view = $this->view;
            $local = $this->ambiente;
            if($local=='front'){
                $view = 'portal.sic_front';
            }

            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-sic',
                'route'=>$this->routa,
                'url'=>$this->routa,
                'ambiente'=>$local,
                'id'=>$id,
            ];
            if(isset($dados['config']) && is_array($dados['config'])){
                foreach ($dados['config'] as $key => $value) {
                    if(is_array($value)){

                    }else{
                        $dados['config['.$key.']'] = $value;
                    }
                }
            }
            $campos = $this->campos_resposta(['dados'=>$dados]);

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
                'url'=>$this->routa,
                'exec'=>true,
            ];
            return view($view.'.show',$ret);
        }else{
            $ret = [
                'exec'=>false,
            ];
            return redirect()->route($this->routa.'.index',$ret);
        }
    }

    public function edit($sic,User $user)
    {
        $id = $sic;
        $dados = Sic::where('id',$id)->get();
        $routa = 'sics';
        $this->authorize('ler', $this->routa);

        if(!empty($dados)){
            $title = 'Editar cadastro de sic';
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
                'frm_id'=>'frm-sics',
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
        $validatedData = $request->validate([
            'nome' => ['required'],
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
                }elseif($key == 'renda_familiar') {
                    $value = str_replace('R$','',$value);
                    $data[$key] = Qlib::precoBanco($value);
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
        if(!empty($data)){
            $atualizar=Sic::where('id',$id)->update($data);
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
    public function relatorios(Request $request){
        $ret = (new AdminSicController)->dadosRelatorio($request);
        $view = $this->view;
        $local = $this->ambiente;
        if($local=='front'){
            $view = 'portal.sic_front';
        }

        return view($view.'.relatorios',$ret);
    }
}
