<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarEmail;
use App\Jobs\EnvioZapsingJob;
use App\Jobs\RdstationJob;
use App\Jobs\SendEmailJob;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\TryCatch;

class OrcamentoController extends Controller
{

    public $campo_assinatura;
    public $campos_gerado;
    public $campos_enviado;
    public $link_termo_assinado;
    public function __construct()
    {
        $this->campo_assinatura = 'assinatura_termo';
        $this->campos_gerado = 'termo_gerado';
        $this->campos_enviado = 'enviado_zapsing';
        $this->link_termo_assinado = 'link_termo_assinado';
    }
    /**
     * Metodo para retornar as informações de propriedade de uma aeronave apartidar da consulta na RAB
     * @param string $matricula a matricula da aeronave
     */
    public function get_info_by_matricula($matricula){
        $url = 'https://api.aeroclubejf.com.br/api/v1/rab?matricula='.$matricula;
        try {
            $json = file_get_contents($url);
            if($json){
                $ret = Qlib::lib_json_array($json);
                if($ret['exec']){
                    $ret['color'] = 'success';
                    $ret['mens'] = 'Aeronave Localizada';
                    $ret['consulta'] = Qlib::codificarBase64($json);
                }
            }
        } catch (\Throwable $e) {
            $ret['exec'] = false;
            $ret['error'] = $e->getMessage();
            $ret['color'] = 'danger';
            $ret['mens'] = 'Aeronave não encontrada na Anac tente mais tarde';
        }
        return $ret;
    }
    public function get_rab(Request $request){
        $matricula = $request->get('matricula') ? $request->get('matricula') : null;
        $config = $request->get('config') ? $request->get('config') : null;
        $api = $request->get('api') ? $request->get('api') : 'true';
        $matricula = strtoupper($matricula);
        $ret['exec'] = false;
        $ret['color'] = 'danger';
        $ret['mens'] = 'Aeronave não encontrada na Anac';
        // $dg = $request->all();
        $passa_consulta = isset($config['consulta']) ? $config['consulta'] : '';
        if($matricula && $api == 'true' && (!$passa_consulta || empty($passa_consulta)) ){
            // $url = 'https://api.aeroclubejf.com.br/api/v1/rab?matricula='.$matricula;
            // $json = file_get_contents($url);
            // if($json){
            //     $ret = Qlib::lib_json_array($json);
            //     if($ret['exec']){
            //         $ret['color'] = 'success';
            //         $ret['mens'] = 'Aeronave Localizada';
            //         $ret['consulta'] = Qlib::codificarBase64($json);
            //     }else{
            //         $ret['color'] = 'danger';
            //         $ret['mens'] = 'Aeronave não encontrada na Anac tente mais tarde';
            //     }
            // }
            $ret = $this->get_info_by_matricula($matricula);
            $ret['salv'] = $this->salverContato($request);
        }else{

            $json = base64_decode($passa_consulta);
            // $json = str_replace('b"','',$json);
            // $json1 = '{"Matrcula":"PRHNA","Proprietrio":"SBXL LOCADORA DE AERONAVES LTDA","CPF/CNPJ":"21616420000177","Cota Parte %":"100","Data da Compra/Transferncia":"19/10/22","Operador":"AEROCLUBE DE JUIZ DE FORA","Fabricante":"CESSNA AIRCRAFT","Ano de Fabricao":"1977","Modelo":"152","Nmero de Srie":"15281007","Tipo ICAO":"C152","Categoria de Homologao":"UTILIDADE","Tipo de Habilitao para Pilotos":"MNTE","Classe da Aeronave":"POUSO CONVECIONAL 1 MOTOR CONVENCIONAL","Peso Mximo de Decolagem":"757 - Kg","Nmero de Passageiros":"001","Tipo de voo autorizado":"VFR Noturno","Tripulao Mnima prevista na Certificao":"1","Nmero de Assentos":"2","Categoria de Registro":"PRIVADA INSTRUCAO","Nmero da Matrcula":"20879","Status da Operao":"OPERAO NEGADA PARA TXI AREO","Gravame":"ARRENDAMENTO OPERACIONAL","Data de Validade do CVA":"23/01/25","Situao de Aeronavegabilidade":"SITUAO NORMAL","Motivo(s)":"","Data da consulta":"20/11/2024 19:00:32"}';
            $ret = Qlib::lib_json_array($json);
            $ret['mens'] = '';
            $ret['color'] = 'success';
            // $request->merge(['ac'=>'cad']);
            $ret = $this->salverContato($request);
        }
        return response()->json($ret);

    }
    public function salverContato(Request $request){
        $ret['exec'] = false;
        $email = $request->get('email');
        $d = $request->all();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $ret['mens'] = "O endereço de Email '$email' é inválido.\n";
            return $ret;
        }
        $config = $request->get('config');
        // $config['_token'] = $config['
        $name = strip_tags($request->get('name'));
        $id_cliente = false;
        $id_cliente_front = Qlib::qoption('id_permission_front'); //id da permissão do cliente
        try {
            $arr_sav = [
                'email'=>$email,
                'config'=>$config,
                'name'=>$name,
                'id_permission'=>$id_cliente_front,
            ];
            $salv = User::create($arr_sav);
            $id_cliente = isset($salv['id']) ? $salv['id'] : false;
            $ret['salv'] = $salv;
        } catch (\Throwable $th) {
            //verrifica se ja tem o email cadastrado
            // $ret['catch'] = $th;
            $dc = User::where('email', '=', $email)->get();
            if($dc) {
                $id_cliente = isset($dc[0]['id']) ? $dc[0]['id'] : false;
                if($id_cliente) {
                    $ret['up'] = User::where('id', $id_cliente)->update([
                        'name' => $name,
                        'config' => $config,
                        // 'id_permission'=>$id_cliente_front,
                    ]);
                }
            }
        }
        $ret['id_cliente'] = $id_cliente;
        if($id_cliente && isset($config['consulta'])) {
            $ret = $this->salvarOrcamento($id_cliente,$d,$config);
        }
        if(Qlib::isAdmin(1)){
            $ret['d'] = $d;
        }
        return $ret;
    }
    /***
     * Metodo para revelar um orcamento whatsapp pela api
     */
    public function orcamento_zap(request $request){
        $ret['link'] = '';
        if($token=$request->get('token')){
            if($request->has('base64')){
                $ret['link'] = base64_encode($this->orcamento_html($token,'whatsapp'));
            }else{
                $ret['link'] = $this->orcamento_html($token,'whatsapp');
            }
        }
        return response()->json($ret);
    }
    /**
     * Metodo para salver uma solicitação de oraçamento do formulario do fornt End
     * @param string $config base64
     * @param string $id_cliente
     * @param array $d para informar o token, obs
     * @param array $config contendo o campo da consulta RAB codificado em json e base64 feito pelo metodo Qlib::encodeArray();
     * @return array $ret
     */
    public function salvarOrcamento($id_cliente,$d,$config){
        $ret['exec'] = false;
        $ret['mens'] = __('Erro ao enviar orçamento!');
        $ret['color'] = 'danger';
        $arr_config    = $config;
        $enviar_assinatura = isset($config['enviar_assinatura']) ? $config['enviar_assinatura'] : 's';
        $consulta = isset($config['consulta']) ? $config['consulta'] : '';
        $arr_config['consulta'] = Qlib::decodeArray($config['consulta']);
        $ret['arr_config'] = $arr_config;
        $post_type = 'orcamentos';
        $dc = User::find($id_cliente);
        $post_title = 'Solicitação de orçamento '.@$dc['name'];
        $token = isset($d['token']) ? $d['token'] : uniqid(); //Status de orçamentos enviado,aguardando
        $post_status = isset($d['post_status']) ? $d['post_status'] : 'aguardando'; //Status de orçamentos enviado,aguardando
        if(empty($consulta)){
            //Validar a consulta rab
            $ret['mens'] = __('Dados da consulta R.A.B inválidos.');
            return $ret;
        }
        $arr_salv = [
            'guid'=>$id_cliente,  //id do cliente
            'config'=>Qlib::lib_array_json($arr_config),
            'post_type'=>$post_type,
            'post_title'=>$post_title,
            'post_author'=> (Auth::id() ? Auth::id() : $id_cliente),
            'post_status'=> $post_status,
            'token'=> $token,
            'post_content'=> @$d['obs'],
        ];
        //primeiro tenta atualizar um token que ja existe
        $salv = Post::where('token','=',$token)->update($arr_salv);
        if(!$salv){
            $salv = Post::create($arr_salv);
        }
        if($salv){
            $link_zap = $this->orcamento_html($token,'whatsapp');
            $link_redirect = '/'.Qlib::get_slug_post_by_id(5);
            if($link_zap){
                $link_redirect .= '?zlink='.base64_encode($link_zap);
            }
            try {
                $email_admin = explode(',',Qlib::qoption('email_gerente'));
                $ret['exec'] = true;
                //enviar 2 emails para admin
                $subject = 'SOLICITAÇÃO DE AGENDAMENTO DE MANUTENÇÃO';
                // $ret['env'] = EnviarEmail::dispatch($data);
                $mensagem =  'Antenção foi solicitado um orçamento por <b>'.$dc['name'].'</b> em '.Qlib::dataLocal();
                $mensagem .= $this->orcamento_html($token);
                //Salvar o contrato
                $post_id = Qlib::get_id_by_token($token);
                $ret['termo'] = $this->salvar_aceito_termo(['id_cliente'=>$id_cliente,'post_id'=>$post_id,'meta'=>@$d['meta']]);
                //salvar a consulta
                $ret['idCad'] = $post_id;
                $ret['salvar_consulta'] = Qlib::update_postmeta($post_id,'consulta_rab',$consulta);
                if(is_array($email_admin)){
                    $from = 'suporte@aeroclubejf.com.br';
                    // dd($email_admin);
                    // foreach ($email_admin as $email) {
                        if(isset($email_admin[0])){
                            $details = [
                                'email' => $email_admin[0],
                                'from' => $from,
                                'name' => '',
                                'subject' => $subject,
                                'message' => $mensagem,
                                'cc' => $email_admin[1],
                                'bcc' => @$email_admin[2],
                            ];
                            SendEmailJob::dispatch($details);
                        }
                    // }
                }
                $mensagem = '<p>Olá <b>'.$dc['name'].'</b> obrigado pelo seu contato!</p><p>Sua solicitação foi encaminhada para a nossa oficina em breve entraremos em contato.</p>';
                $mensagem .= $this->orcamento_html($token);
                $details_cliente = [
                    'email' => $dc['email'],
                    'name' => $dc['name'],
                    'subject' => $subject,
                    'message' => $mensagem
                ];
                //enviar 1 email copia para o cliente
                SendEmailJob::dispatch($details_cliente);
                //criar um link de redirecioanmento
                $ret['mens'] = __('Orçamento enviado com sucesso!');
                $ret['color'] = 'success';
                $ret['link_zap'] = $link_zap;
                $ret['redirect'] = $link_redirect;
                //Enviar para o zapsing
                if($enviar_assinatura=='s'){
                    $ret['EnvioZapsingJob'] = EnvioZapsingJob::dispatch($token);
                    // $send_to_zapSing = $this->send_to_zapSing($token);
                    $ret['RdstationJob'] = RdstationJob::dispatch($token);
                    // if(Qlib::is_backend()){
                    //     $ret['send_to_zapSing'] = $send_to_zapSing;
                    // }
                }
            } catch (\Throwable $th) {
                $ret['link_zap'] = $link_zap;
                $ret['redirect'] = $link_redirect;
                $ret['erro'] = $th;
                $ret['mens'] = __('Tivemos um erro inesperdado entre em contato com o suporte!');
                $ret['color'] = 'danger';
            }
        }
        // $ret['salv'] = $salv;
        return $ret;
    }
    /**
     * Metodo para salvar a aceitação do termo
     * @param array $config
     * @return arr $config
     */
    public function salvar_aceito_termo($config=[]){
        $id_cliente = isset($config['id_cliente']) ? $config['id_cliente'] : false;
        $post_id = isset($config['post_id']) ? $config['post_id'] : false;
        $meta = isset($config['meta']) ? $config['meta'] : false;
        $ret['exec'] = false;
        if(isset($meta['termo']) && $meta['termo']=='s' && $id_cliente && $post_id){
            //nesse caso o contrato foi aceito por um cliente válido
            $assinatura = Qlib::update_postmeta($post_id,$this->campo_assinatura,Qlib::lib_array_json([
                'aceito_termo' => 's',
                'data' => Qlib::dataLocal(),
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]));
            if($assinatura){
                $texto_termo_assinado = Qlib::update_postmeta($post_id,'texto_termo_assinado',Qlib::lib_array_json([
                    'texto' => $this->get_termo_texto(),
                ]));
                //salvar assinatura e
                if($texto_termo_assinado){
                    $ret['exec'] = true;
                }
            }
        }
        return $ret;
    }
    /**
     * Metodo para exibir o texto de um termo cadastrado
     * @return string $ret o texto html no content do post
     */
    public function get_termo_texto(){
        $id = 10;
        $d = Post::find($id);
        $ret = isset($d['post_content']) ? $d['post_content'] : '';
        return $ret;
    }
    public function enviar_orcamento(Request $request){
        $ret['exec'] = false;
        $d = $request->all();
        $ret['d'] = $d;
        return $ret;
    }
    public function orcamento_html($token,$type=false){
        $d = Post::where('token','=', $token)->get();
        $ret = '';
        $tm1 = '';
        $type = $type ? $type : 'markdown';
        if($d->isNotEmpty()){
            $tbody = '';
            if($type == 'whatsapp'){
                $link_zap = 'https://api.whatsapp.com/send?phone={celular_zap}&text={text}';
                $tbody = '<br>';
            }
            $dc = User::find($d[0]['guid']);
            $email = isset($dc['email']) ? $dc['email'] : '';
            $name = '';
            if(isset($dc['name']) && $type == 'whatsapp'){
                $tm1 .= 'Meu nome é *'.@$dc['name'].'* gostaria de um orçamento de serviço para a Aeronave abaixo: %0A--------%0A';
            }else{
                $name = isset($dc['name']) ? $dc['name'] : '';
                $tm1 .= '<p class=\'mb-0\'><b>Nome</b>: {name}</p>';
            }
            $tm1 .= '<p><b>ID orçamento</b>: {id}</p>';
            if($type == 'whatsapp'){
                $tm1 .= '<p><b>Telefone</b>: {telefone}</p>';
                $tm1 .= '<p><b>Email</b>: {email}</p>';
            }else{
                $tm1 .= '<p class=\'mb-0\'><b>Telefone</b>: {telefone}</p>';
                $tm1 .= '<p class=\'mb-0\'><b>Email</b>: {email}</p>';
            }

            if($type=='markdown' || $type == 'whatsapp'){
                $tm1 .= '
                   <p>{tbody}</p>
                   <br><p><b>'.__('Serviço').'</b>: {servicos}</p>
                   <br><p><b>'.__('Descrição').'</b>:<br>{obs}</p>
                ';
                $tm2 = ' - <b>{label}:</b> {value}<br>';
            }else{
                if($type == 'table_only'){
                    $tm1 = '
                    <table class="table">
                        {tbody}
                    </table>';
                }else{
                    $tm1 .= '
                    <table class="table">
                        {tbody}
                    </table>';

                    $tm1 .= '
                    <p><b>'.__('Serviço').'</b>: {servicos}</p>
                    <p><b>'.__('Descrição').'</b>:<br>{obs}</p>
                    ';
                }
                $tm2 = '
                <tr>
                    <th>{label}</th>
                    <td>{value}</td>
                </tr>
                ';
            }
            $darr = $d->toArray();
            $dar = $darr[0];
            if(is_string($dar['config'])){
                $dar['config'] = Qlib::lib_json_array($dar['config']);
            }
            $config = $dar['config'];
            // dump($config);
            $telefone = isset($config['ddi']) ? $config['ddi'] : '';
            // if($telefone && $type=='whatsapp'){

            // }
            if(isset($config['whatsapp'])){
                $telefone .= ' ';
                $telefone .= $config['whatsapp'] ? $config['whatsapp'] : '';
            }
            $servicos = isset($dar['config']['servicos']) ? $dar['config']['servicos'] : false;
            $arr = isset($dar['config']['consulta']['data']) ? $dar['config']['consulta']['data'] : [];

            if(isset($arr) && is_array($arr)){
                foreach ($arr as $k => $v) {
                    $tbody .= str_replace('{label}',$k,$tm2);
                    $tbody = str_replace('{value}',$v,$tbody);
                }
            }
            $ret = str_replace('{tbody}',$tbody,$tm1);
            $ret = str_replace('{obs}',@$dar['post_content'],$ret);
            $ret = str_replace('{id}',@$dar['ID'],$ret);
            $ret = str_replace('{servicos}',@$servicos,$ret);
            $ret = str_replace('{telefone}',@$telefone,$ret);
            $ret = str_replace('{email}',@$email,$ret);
            $ret = str_replace('{name}',@$name,$ret);
            if($type == 'whatsapp'){
                $text = $ret;
                $celular_zap = Qlib::qoption('celular_zap');
                $celular_zap = str_replace(' ','',trim($celular_zap));
                $celular_zap = str_replace('(','',$celular_zap);
                $celular_zap = str_replace(')','',$celular_zap);
                $celular_zap = str_replace('-','',$celular_zap);
                $link_zap = 'https://api.whatsapp.com/send?phone={celular_zap}&text={text}';
                $link_zap = str_replace('{celular_zap}',$celular_zap,$link_zap);
                $link_zap = str_replace('{text}',trim($text),$link_zap);
                $celular_zap = str_replace('-','',$celular_zap);
                $ret = str_replace('<b>','*',$link_zap);
                $ret = str_replace('</b>','*',$ret);
                $ret = str_replace('<br>','%0A',$ret);
                $ret = str_replace('\n','%0A',$ret);
                $ret = str_replace('</p>','%0A',$ret);
                $ret = str_replace('<p>','',$ret);
                $ret = str_replace(' ','%20',$ret);
                // dd($link_zap);
                // $ret = $link_zap;
            }
        }
        return $ret;
    }
    public function termo_aceito($id){
        $termo = Qlib::get_postmeta($id,$this->campos_gerado,true);
        $titulo = __('Termo assinado!');
        $arr_texto = Qlib::lib_json_array($termo);
        // dd($arr_texto);
        return view('admin.orcamentos.termo_assinado',['termo'=>$arr_texto['texto'],'titulo'=>$titulo]);
    }
    /**
     * Metodo para contar o numero total de orçamentos
     */
    public function total_orcamentos($status=''){
        if($status=='andamento'){
            $total = Post::where('post_type','=','orcamentos')
            ->where('post_status','!=','trash')
            ->where('post_status','!=','aguardando')
            ->count();
        }else{
            $total = Post::where('post_type','=','orcamentos')
            ->where('post_status','=',$status)
            ->count();
        }
        return $total;
    }
    /**
     * Metodo para retornar um orçamento apartir do token
     */
    public function get_orcamento($token){
        // $id = Qlib::get_id_by_token($token);
        $d = Post::select('posts.*','users.email','users.name','users.cpf','users.config as config_user')->join('users','posts.guid','=','users.id')->where('posts.token','=',$token)->get();
        if($d->isNotEmpty()){
            $d = $d[0]->toArray();
            if(is_string($d['config'])){
                $d['config'] = Qlib::lib_json_array($d['config']);
            }
            if(is_string($d['config_user'])){
                $d['config_user'] = Qlib::lib_json_array($d['config_user']);
            }
        }else{
            return false;
        }
        return $d;
    }
    /**
     * Metodo para enviar o termo para zapsing
     */
    public function send_to_zapSing($token_orcamento,$orcamento=''){
        $d = $this->get_orcamento($token_orcamento);
        $id = isset($d['ID']) ? $d['ID'] : '';
        $nome = isset($d['name']) ? $d['name'] : '';
        $token = isset($d['token']) ? $d['token'] : '';
        $email = isset($d['email']) ? $d['email'] : '';
        $ret = ['exec' => false, 'mens'=>'Orçamento não encontrado','color'=>'danger', 'status'=>'403'];
        if(!$d){
            return $ret;
        }
        $cpf = $d['cpf'] ? $d['cpf'] : '';
        $conteudo = Qlib::get_post_content(10);// 'Meu teste 06';
        if(!$id){
            return $ret;
        }
        if(!$conteudo){
            $ret = ['exec' => false, 'mens'=>'Conteudo de termo inválido','color'=>'danger', 'status'=>'403'];
            return $ret;
        }
        // $titulo = 'Termo de solicitação de orçamento '.$id;
        $titulo = Qlib::qoption('titulo_termo') ? Qlib::qoption('titulo_termo') : 'Termo para assinatura da aeronave';
        $matricula = isset($d['config']['matricula']) ? $d['config']['matricula'] : '';
        $titulo = str_replace('{id}',$id,$titulo);
        $titulo = str_replace('{matricula}',$matricula,$titulo);
        // $matricula  = isset($d['config']['matricula']) ? $d['config']['matricula'] : '';
        // $servicos  = isset($d['config']['servicos']) ? $d['config']['servicos'] : '';
        $id_assinante_oficina = Qlib::qoption('id_assinante_oficina');
        $da = User::find($id_assinante_oficina);
        $nome_oficina = isset($da['name']) ? $da['name'] : '';
        $email_oficina = isset($da['email']) ? $da['email'] : '';
        $cpf_oficina = isset($da['cpf']) ? $da['cpf'] : '';
        $body = [
            "name" => $titulo,
            "url_pdf" => "https://oficina.aeroclubejf.com.br/storage/pdfs/termo_pdf",
            "external_id" => $token,
            "signers" => [
                [
                    "name" => $nome,
                    "email" => $email,
                    "cpf" => $cpf,
                    "send_automatic_email" => true,
                    "send_automatic_whatsapp" => false,
                    "auth_mode" => "CPF", //tokenEmail,assinaturaTela-tokenEmail,tokenSms,assinaturaTela-tokenSms,tokenWhatsapp,assinaturaTela-tokenWhatsapp,CPF,assinaturaTela-cpf,assinaturaTela
                    "order_group" => 1,
                ],
                [
                    "name" => $nome_oficina, //assinatura da oficina
                    "email" => $email_oficina,
                    "cpf" => $cpf_oficina,
                    "send_automatic_email" => true,
                    "send_automatic_whatsapp" => false,
                    "auth_mode" => "CPF", //tokenEmail,assinaturaTela-tokenEmail,tokenSms,assinaturaTela-tokenSms,tokenWhatsapp,assinaturaTela-tokenWhatsapp,CPF,assinaturaTela-cpf,assinaturaTela
                    "order_group" => 2,
                ],
            ],
        ];
        // $oracamento = $this->orcamento_html($token,'table');
        // $conteudo = str_replace('{nome}',$nome,$conteudo);
        // $conteudo = str_replace('{email}',$email,$conteudo);
        // $conteudo = str_replace('{cpf}',$cpf,$conteudo);
        // $conteudo = str_replace('{matricula}',$matricula,$conteudo);
        // $conteudo = str_replace('{servicos}',$servicos,$conteudo);
        // $conteudo = str_replace('{orcamento}',$oracamento,$conteudo);
        $gerar_pdf = $this->gerar_termo_orcamento($token,$d,$conteudo,$titulo);
        $body['url_pdf'] = isset($gerar_pdf['caminho']) ? $gerar_pdf['caminho'] : '';
        $ret = (new ZapsingController)->post([
            "body" => $body
        ]);
        //gravar historico do envio do orçamento
        if(isset($ret['exec'])){
            $post_id = Qlib::get_id_by_token($token);
            $ret['salv_hist'] = Qlib::update_postmeta($post_id,$this->campos_enviado,Qlib::lib_array_json($ret));
        }
        Log::info('send_to_zapSing:', $ret);
        return $ret;
    }
    /**
     * Metodo para gerar um termo em pdf de um determinado orçamento mediante um token
     */
    public function gerar_termo_orcamento($token,$d=false,$conteudo=false,$titulo=false){
        if(!$d && $token){
            $d = $this->get_orcamento($token);
        }
        if(!$conteudo){
            $conteudo = Qlib::get_post_content(10);// 'Meu teste 06';
        }
        $id = isset($d['ID']) ? $d['ID'] : '';
        $nome = isset($d['name']) ? $d['name'] : '';
        $token = isset($d['token']) ? $d['token'] : '';
        $email = isset($d['email']) ? $d['email'] : '';
        $cpf = $d['cpf'] ? $d['cpf'] : '';
        if(!$titulo){
            $titulo = 'Termo de solicitação de orçamento '.$id;
        }
        $oracamento = $this->orcamento_html($token,'table');
        $conteudo = str_replace('{nome}',$nome,$conteudo);
        $conteudo = str_replace('{email}',$email,$conteudo);
        $conteudo = str_replace('{cpf}',$cpf,$conteudo);
        $conteudo = str_replace('{orcamento}',$oracamento,$conteudo);
        // $conteudo = str_replace('{matricula}',$matricula,$conteudo);
        // $conteudo = str_replace('{servicos}',$servicos,$conteudo);
        $arquivo = 'termos/'.$token.'/nao_assinado.pdf';
        $ret = (new PdfController)->salvarPdf(['titulo'=>$titulo,'conteudo'=>$conteudo],['arquivo'=>$arquivo]);
        if($ret['exec']){
            $ret['data'] = Qlib::dataLocal();
            $post_id = Qlib::get_id_by_token($token);
            $ret['salvar'] = Qlib::update_postmeta($post_id,'termo_gerado',Qlib::lib_array_json($ret));
        }
        return $ret;
    }
    /**
     * Metodo para expor o metodo (new OrcamentoController)->send_to_zapsing() em uma routa de ajax
     */
    public function sendToZapsing(Request $request){
        $token = $request->get('token');
        $ret = ['exec'=>false,'mens'=>'Token inválido','color'=>'danger'];
        if($token){
            try {
                // EnvioZapsingJob::dispatch($token);
                // $ret = ['exec'=>true,'mens'=>'Enviado com sucesso','color'=>'success'];
                $ret = $this->send_to_zapSing($token);
            } catch (\Throwable $e) {
                $ret = ['exec'=>false,'mens'=>'Erro ao enviar','color'=>'danger','error'=>$e->getMessage()];
            }
        }
        Log::info('setToZapsing:', $ret);
        return $ret;
    }
    /**
     * Metodo para baixar o arquivo assinado de um oraçmento baixar em um diretorio padrão de oraçamento
     * @param string $token
     */
    public function baixar_arquivo($token,$url){
        // $url = "https://zapsign.s3.amazonaws.com/sandbox/dev/2024/12/pdf/72d30d89-da1f-4e10-9025-3689b03ef3d4/7a773057-05d3-4843-be1d-0fe6bffdb730.pdf?AWSAccessKeyId=AKIASUFZJ7JCTI2ZRGWX&Signature=oRLj2PALoDs1JEkx%2FHm4TV1ZM%2BQ%3D&Expires=1734026017";
        $num=null;
        $caminhoSalvar = 'pdfs/termos/'.$token.'/assinado.pdf';
        if(Storage::exists($caminhoSalvar)){
            $num=time();
        }
        $caminhoSalvar = 'pdfs/termos/'.$token.'/assinado'.$num.'.pdf';
        $ret = Qlib::download_file($url,$caminhoSalvar);
        $ret['url'] = $url;
        $ret['token'] = $token;
        $post_id = Qlib::get_id_by_token($token);
        if($ret['exec']){
            $ret['salv'] = Qlib::update_postmeta($post_id,$this->link_termo_assinado,Qlib::lib_array_json(['link'=>$caminhoSalvar,'data'=>Qlib::dataLocal()]));
        }
        return $ret;
    }
    /**Metodo para buscar e atualizar no sistema um status atualizado dos documentos no zapsing
     * @param string $id do orçamento
     */

    public function get_status_zapsing($id) {
        //conseguir o id do envio para o zapsintg
        $campo_meta = 'enviado_zapsing';
        $zapsing = Qlib::get_postmeta($id,$campo_meta,true);
        //pegar o status atual das assinaturas
        $ret['exec'] = false;
        if($zapsing){
            $arr = Qlib::lib_json_array($zapsing);
            $ret = $arr;
            $ret['exec'] = false;
            $tokenz = isset($arr['response']['token']) ? $arr['response']['token'] : false;
            $status_atual = isset($arr['response']['status']) ? $arr['response']['status'] : false;
            if($tokenz && $status_atual!='signed'){
                $stat = (new ZapsingController)->status_doc_remoto($tokenz);
                $status = isset($stat['response']['status']) ? $stat['response']['status'] : '';
                $ret['status'] = $stat;
                $ret = $stat;
                if($status=='signed'){
                    $salv_historico = Qlib::update_postmeta($id,$campo_meta,Qlib::lib_array_json($stat));
                    $ret['exec'] = true;
                }
            }
        }
        return $ret;
    }
}
