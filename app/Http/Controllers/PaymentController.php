<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\AsaasController;
use App\Models\Post;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $instance = null;
    public $meta_status_pagamento;
    public $meta_resumo_pagamento;
	public function __construct()

	{
        $this->meta_status_pagamento = Qlib::qoption('meta_status_pagamento')?Qlib::qoption('meta_status_pagamento'):'pago';
        $this->meta_resumo_pagamento = Qlib::qoption('meta_resumo_pagamento')?Qlib::qoption('meta_resumo_pagamento'):'resumo_pagamento';

		// $this->get_instance();

	}

	public function get_instance()

	{

		if (null === self::$instance) {

			self::$instance = new self();

		}

		return self::$instance;

	}
    /**
	 * iniciar o pagamento
	 */
	public function init(Request $request){
        $config = $request->all();
		//cadastrar e verificar cadastro de clientes.
		// if(!isset($config['_token'])){return false;}
		// dd($config);
		// if(isset($config['_token']) && $config['_token']!=session_id()){return false;}
		// $r = (new integraAsaas)->schemaCustomerAsaas(91);
		$ret['exec'] = false;
        $logado = Auth::check();
        if(isset($config['compra']['token']) && !empty($config['compra']['token'])){
			//verificar se o cliente está logado
			if(!$logado){return false;}
            //o token da compra é importante para associar o resultado do pagamento ao token do leilao
			//fazer o pagamento e integrar ao gateway
			$ret = (new AsaasController)->integraCompraAsaas($config);
        }else{
			$siga = false;
			//Verifica se ja está logado
			if($logado){
				if(isset($config['Email'])&&isset($config['Cpf']))
					$atualizaClient = self::cad_cliente($config);
				$siga = true;
			}else{
				//Do contrario cadastra e loga
				$ret = self::cad_cliente($config);
				if($logado){
					$siga = true;
				}
				$ret['dbcad'] = $ret;
			}
			if($siga){
				//Segue para o registro da compra
				$ret['registrarCompra'] = self::regCompra($config);
				if(isset($ret['registrarCompra']['exec'])){
					$ret['exec'] = $ret['registrarCompra']['exec'];
					$ret['token'] = @$ret['registrarCompra']['matricula']['tokenCad'];
					if(isset($ret['registrarCompra']['matricula']['salvar']['dados_enc'][0]) && ($de=$ret['registrarCompra']['matricula']['salvar']['dados_enc'][0])){
						if(isset($de['pagamento_asaas']) && empty($de['pagamento_asaas']) && isset($de['token'])){
							$ret['exec'] = true;
							$ret['token'] = $de['token'];
							$ret['registrarCompra']['matricula']['tokenCad'] = $de['token'];
						}
					}
					if(isset($ret['registrarCompra']['matricula']['mensa'])){
						$ret['mens'] = str_replace('O registro do','A Matrícula do Cliente, ',$ret['registrarCompra']['matricula']['mensa']);
					}
				}
			}
			if($logado && $ret['exec'] && isset($ret['token']) && $ret['token'] && $config){
				if(Qlib::isAdmin() || isset($_GET['fq'])){
					$debug = $ret;
				}
				//fazer o pagamento e integrar ao gateway
				if(isset($ret['registrarCompra']['matricula']['tokenCad']) && !empty($ret['registrarCompra']['matricula']) && ($tokenCompra=$ret['registrarCompra']['matricula']['tokenCad'])){
					$config['compra']['token'] = $tokenCompra;
				}
				$ret = (new AsaasController)->integraCompraAsaas($config);
				if(Qlib::isAdmin() || isset($_GET['fq'])){
					$ret['debug_cad_cliente'] = $debug;
				}
			}
		}
		if($ret['exec'] && isset($config['compra']['token'])){
			//Fazer lançamento da fatura referente ao pagamento feito no gateway
			// $ret['lancarFaturaPagamentoAsaas'] = (new ecomerce)->lancarFaturaPagamentoAsaas($config['compra']['token'],$config['compra']['forma_pagamento']);
		}elseif(isset($ret['mens'])){
            $ret['mens'] .= Qlib::formatMensagemInfo('Erro ao efetuar a compra entre em contato com o nosso suporte','danger');
		}else{
			$ret['mens'] = @$ret['mensa'];
			$ret['mens'] .= Qlib::formatMensagemInfo('Erro ao efetuar a compra entre em contato com o nosso suporte','danger');
		}
		return $ret;
	}
    public function form($post_id,$dados=false){
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $ret['exec'] = false;
        $ret['status'] = 404;
        $ret['mens'] = false;
        if($seg1 && $seg2){
            //verificar se o token no seg2 é de um leilao valido
            $tk = explode('-', $seg2);
            if(!isset($tk[1])){
                //a url está infalida
                return view('site.meio404');
            }
            $leilao_id = Qlib::buscaValorDb0('posts','token',$tk[0],'ID');
            //se $tk[1]==01 é para pagar leilao se $tk[1]==02 é para pagar o compreja
            $lc = new LeilaoController; //LeilaoController
            $type = $tk[1];
            if($leilao_id && $tk[1]=='01'){
                //dados do ultimo lance
                $ul = $lc->get_lance_vencedor($leilao_id,false,'ultimo_lance');
                //coletar o valor do leilao arrematado
                // dd($ul);
                if(isset($ul['ultimo_lance']['valor_lance']) && $ul['ultimo_lance']['valor_lance']>0){
                    $ret['type'] = $tk[1];
                    $ret['type_pagamento'] = 'leilao';
                    $ul = $ul['ultimo_lance'];
                    $ret['ul'] = $ul;
                    //verificar se quem está tentando pagar é o ganhador do leilao
                    $user = Auth::user();
                    if($user['id']==$ul['author']){
                        //verifica se verificou o cadastro
                        $iv=(new UserController)->is_verified();
                        if(!$iv){
                            return redirect()->route('verification.notice');
                        }
                        $ret['status'] = 200; //libera o formularo
                        $ret['mens'] = false;
                        $dl = Post::FindOrFail($leilao_id); //Dados do leilao
                        $dc = false; //dados do contrato
                        $dt = false; //dados do termino ou informações do termino
                        if($dl->count()){
                            $dl = $dl->toArray();
                            $dl['thumbnail'] = Qlib::get_thumbnail_link($dl['ID']);
                            $dt = $lc->info_termino($leilao_id,$dl);
                            if(isset($dl['config']['contrato']) && !empty($dl['config']['contrato'])){
                                $dc0 = Post::where('token',$dl['config']['contrato'])->get()->toArray();
                                if(isset($dc0[0])){
                                    $dc = $dc0[0];
                                }
                            }
                        }

                        $ret['dl'] = $dl; //dados do leilao
                        $ret['dc'] = $dc; //dados do contrato
                        $ret['dt'] = $dt; //dados do terminio
                        $ret['form_credit_cart'] = $this->frmCredCardV2([
                            'dt'=>$dt,
                            'ul'=>$ul, //dados do ultimo lance.
                            'dl'=>$dl, //dados do ultimo lance.
                            'type'=>$type, //dados do ultimo lance.
                        ]); //dados do terminio
                    }else{
                        $ret['status'] = 500; //bloqueia o formulario mais exibe uma mensagem de erro
                        $ret['mens'] = Qlib::formatMensagemInfo('Você está tentado pagar uma leilão arrematado pro outro usuário, esta ação não é permitida','danger');
                    }
                    return view('site.leiloes.payment.form',$ret);
                }else{

                }
            }elseif($leilao_id && $tk[1]=='02'){
                $ul = $lc->get_lance_vencedor($leilao_id,false,'ultimo_lance');
                // dd($ul);
                $ret['type'] = $tk[1];
                $ret['type_pagamento'] = 'leilao';
                if($ul==false){
                    $ul = [];
                }
                $ret['ul'] = $ul;
                //verificar se quem está tentando pagar é o ganhador do leilao
                $user = Auth::user();
                if(isset($user['id'])){
                    //verifica se verificou o cadastro
                    $iv=(new UserController)->is_verified();
                    if(!$iv){
                        return redirect()->route('verification.notice');
                    }
                    $ret['status'] = 200; //libera o formularo
                    $ret['mens'] = false;
                    $dl = Post::FindOrFail($leilao_id); //Dados do leilao
                    $dc = false; //dados do contrato
                    $dt = false; //dados do termino ou informações do termino
                    if($dl->count()){
                        $dl = $dl->toArray();
                        $dl['thumbnail'] = Qlib::get_thumbnail_link($dl['ID']);
                        $dt = $lc->info_termino($leilao_id,$dl);
                        if(isset($dl['config']['contrato']) && !empty($dl['config']['contrato'])){
                            $dc0 = Post::where('token',$dl['config']['contrato'])->get()->toArray();
                            if(isset($dc0[0])){
                                $dc = $dc0[0];
                            }
                        }
                        if(isset($dl['config']['valor_venda']) && !empty($dl['config']['valor_venda'])){
                            $valor = Qlib::precoBanco($dl['config']['valor_venda']);
                            $ul['valor_lance'] = $valor;
                            $ret['valor'] = $valor;
                        }
                    }
                    $ret['dl'] = $dl; //dados do leilao
                    $ret['dc'] = $dc; //dados do contrato
                    $ret['dt'] = $dt; //dados do terminio
                    $ret['form_credit_cart'] = $this->frmCredCardV2([
                        'dt'=>$dt,
                        'ul'=>$ul, //dados do ultimo lance.
                        'dl'=>$dl, //dados do ultimo lance.
                        'type'=>$type, //dados do ultimo lance.
                    ]); //dados do terminio
                }//else{
                    //     $ret['status'] = 500; //bloqueia o formulario mais exibe uma mensagem de erro
                    //     $ret['mens'] = Qlib::formatMensagemInfo('Você está tentado pagar uma leilão arrematado pro outro usuário, esta ação não é permitida','danger');
                    // }
                    // dd($ret);
                return view('site.leiloes.payment.form',$ret);
            }else{
                return view('site.meio404');
            }
        }else{
            return view('site.meio404');
        }
    }
    public function frmCredCardV2($config=false){
		// $dFp = dados_tab('lcf_formas_pagamentos','*',"WHERE id='2' AND parcelamento='s'");//Dados Forma de pagamento cartão
		$dt = isset($config['dt']) ? $config['dt'] : false; //dados do termino.
		$ul = isset($config['ul']) ? $config['ul'] : false; //dados do ultimo lance.
        $valor = isset($ul['valor_lance']) ? $ul['valor_lance'] : 0;
        $id_leilao = isset($config['dl']['ID']) ? $config['dl']['ID'] : false; //dados do ultimo lance.
        $d['valor'] = $valor;
        $d['id_leilao'] = $id_leilao;
        $d['dt'] = $dt;
        $d['dl'] = isset($config['dl']) ? $config['dl'] : false; //dados do ultimo lance.
        $d['token'] = isset($config['dl']['token']) ? $config['dl']['token'] : false; //token do leilao.

		$dFp = Qlib::qoption('forma_pagamento');//Dados Forma de pagamento cartão
		$d['total_pacelamento'] = 1;
		$parcelasCurso = isset($config['parcelas'])?$config['parcelas']:1;
        if(isset($dFp[0]['total_pacelamento']) && $dFp[0]['total_pacelamento'] > 0){
			$total_pacelamento = $dFp[0]['total_pacelamento'];
			$d['total_pacelamento'] = $total_pacelamento;
		}else{
			$total_pacelamento = 0;
        }
		$ret = false;
		// if(!$valor){
        //     $ret = Qlib::formatMensagemInfo('Valor '.$valor.' é inválido por favor entre em contato com o nosso suporte.','danger');
        //     // $valor = isset($_SESSION[$tk_conta]['matricula'.$suf_in]['total'])?$_SESSION[$tk_conta]['matricula'.$suf_in]['total']:$_SESSION[SUF_SYS]['cart'][0]['valor'];
        // }
		//if($valor==0){
            //$valor = $_SESSION[SUF_SYS]['cart'][0]['valor'];
            //}
        if($valor<=0){
            $ret .= Qlib::formatMensagemInfo('<b>Erro: </b>não é possível finalizar a compra com este valor <b>'.number_format($valor,'2',',','.').'</b>. <a href="/cursos" class="btn btn-link">Todos cursos</a>','danger',10000);
            return $ret;
        }
        $valor_parcela_curso = isset($config['valor_parcela'])?$config['valor_parcela']:@$valor;
        $d['valor_parcela_curso'] = $valor_parcela_curso;
        $d['acao'] = isset($config['acao'])?$config['acao']:'cad';
        $form = isset($config['form'])?$config['form']:true;
        $d['pg'] = isset($config['pg'])?$config['pg']:false;
        $d['arr_mes'] = [];
        foreach (range(1, 12) as $number) {
            $number = Qlib::zerofill($number,2);
            $d['arr_mes'][$number] = $number;
            //$opt .= '<option value="'.$number.'">'.$number.'</option>';
        }
        $esteAno = date('Y');
        $d['arr_ano'] = [];
        foreach (range($esteAno, ($esteAno +10)) as $number) {
            $number = Qlib::zerofill($number,2);
            $d['arr_ano'][$number] = $number;
        }
        $parcelas = 1;
        $d['parcelas'] = $parcelas;
        $moeda = 'R$ ';
        $taxa = Qlib::qoption('taxa_juros')?:null;
        $aplicaJuros = Qlib::qoption('juros_ao_parcelar')?:'n';
        $d['arr_valores'] = [];
        if($parcelas > 0){
            $esteAno = date('Y');
            $c = $valor;
            $jc = 0; //juro composto
            // $calc = new Calculadoras;
            foreach (range(1, ($parcelas)) as $number) {
                $tempo = $number;
                // if($aplicaJuros=='s' && $taxa && $number>1){
                    //     $valor = $calc->jurosComposto($c,$taxa,$tempo);
                    //     $number = zerofill($number,2);
                    // }else{
                        $number = Qlib::zerofill($number,2);
                        // }
                        $valor_parcela = round(($valor/$number),2);
                        if($tempo == $parcelasCurso && $valor_parcela_curso){
                            $moeda.number_format($valor_parcela_curso,2,',','.').'</option>';
                            $d['arr_valores'][$number.'X'.$valor_parcela_curso] = $number.' X '.$moeda.number_format($valor_parcela_curso,2,',','.');
                        }else{
                            $d['arr_valores'][$number.'X'.$valor_parcela_curso] = $moeda.number_format($valor_parcela_curso,2,',','.');
                        }
                        if($number == $total_pacelamento){
                            break;
                        }
                    }
                }else{
                    $d['arr_valores']['1X'.$valor] = '01 X '.$moeda.number_format($valor,'2',',','.');
                    // $opt .= '<option value="1X'.$valor.'">01 X '.$moeda.number_format($valor,'2',',','.').'</option>';
                }
                // $d['parcelas'] = $this->calcParcelamento($valor);
                // dd($d);
                // $ret .= '<div class="card padding-none mb-3" style="padding-top:10px"  id="card_frm_cred_card">';
                // $ret .= '<div class="card-header">';
                // $ret .= '<i class="fa fa-credit-card"></i> Informações do cartão de crédito';
                // $ret .= '</div>';
                // $ret .= '<div class="card-body">';
                // $class_campos = 'form-control c-cred_card';
                // if($form)
                // $ret .= '<form role="form" id="form_cred_card" method="post" action="'.Qlib::urlAtual().'">';
                // 	$ret .= '<div class="pn-cred_card">';
                // 			$ret .='<div class="col-md-12 d-none"><label><input  type="checkbox" value="outro" name="cartao[dono]" > Usar cartão de outra pessoa</label></div>';
                // 			$config['campos_form'][0] = array('type'=>'tel','col'=>'md','size'=>'12','campos'=>'cartao[numero_cartao]-Numero do cartão de crédito*-','value'=>@$config['numero_cartao'],'css'=>false,'event'=>'required','clrw'=>false,'obs'=>false,'outros'=>false,'class'=>$class_campos,'title'=>false);
                // 			$config['campos_form'][1] = array('type'=>'text','col'=>'md','size'=>'12','campos'=>'cartao[nome_no_cartao]-Nome no cartão*-','value'=>@$config['nome_no_cartao'],'css'=>false,'event'=>'required','clrw'=>false,'obs'=>false,'outros'=>false,'class'=>$class_campos,'title'=>'Este é o nome do titular, que está impresso no cartão');
                // 			$config['campos_form']['id_curso'] = array('type'=>'hidden','col'=>'md','size'=>'12','campos'=>'compra[id_curso]-Nome no cartão*-','value'=>@$config['id'],'css'=>false,'event'=>'required','clrw'=>false,'obs'=>false,'outros'=>false,'class'=>$class_campos,'title'=>'Este é o nome do titular, que está impresso no cartão');

                // 			$ret .= formCampos($config['campos_form']);
                // 			$ret .= '<div class="row pl-3 pr-3"><div class="col-md-12" id="campo_validade"><label>Data de validate:</label></div>';
                // 			$Mes = '
        // 			<div class="col-6">
        // 				<select class="select-mes form-control '.$class_campos.'" name="cartao[validade_mes]" required>
        // 					{opt}
        // 				</select>
        // 			</div>';
        // 			$opt = false;
						// 			$opt .= '<option value="" selected>Mês</option>';

						// 			$ret .= str_replace('{opt}',$opt,$Mes);

						// 			$Ano = '
						// 			<div class="col-6">
						// 				<select class="select-ano form-control '.$class_campos.'" name="cartao[validade_ano]" required>
						// 					{opt}
						// 				</select>
						// 			</div>

						// 			';
						// 			$opt = false;
						// 			$opt .= '<option value="" selected>Ano</option>';
						//
						// 			$ret .= str_replace('{opt}',$opt,$Ano);
						// 			$ret .= '</div>';
						// 			$config['campos_form1'][1] = array('type'=>'tel','col'=>'md','size'=>'12','campos'=>'cartao[codigo_seguranca]-Código de serguraça (CVV)*-','value'=>@$config['codigo_seguranca'],'css'=>false,'event'=>'required','clrw'=>false,'obs'=>false,'outros'=>false,'class'=>$class_campos,'title'=>false);
						// 			$ret .= formCampos($config['campos_form1']);
						// 			$ret .= '<div class="col-md-12" id="valor_compra"><label>Valor: </label><br>';
						// 			$temavalor = '
						// 			<select class="select-valor form-control '.$class_campos.'" name="cartao[valor]" required>
						// 				{opt}
						// 			</select>';
						// 			$opt = false;
						// 			// o total esta declarado no objeto $this->resumoCompra()
						// 			if(Qlib::isAdmin()){

						// 				//lib_print($_SESSION[$tk_conta]['matricula'.$suf_in]);
						// 				//lib_print($_SESSION[SUF_SYS]['cart'][0]);exit;
						// 			}
						// 			// if(isset($_GET['teste'])){
						// 			// 	dd($total_pacelamento);
						// 			// }
						// 			//if($_SESSION[SUF_SYS]['cart'][0]['parcelas'] > 0){

						// 			$ret .= str_replace('{opt}',$opt,$temavalor);
						// 			$ret .= '</div>';
						// 	$ret .= '</div>';
						// 	$ret .= $this->frmDadosResponsavel($config); ///comprar com cartão de terciros
						// 	//$ret .= '<div class="col-sm-12">{link_esqueci_senha}</div>';
						// 	//$ret .= queta_formfield4("hidden",'1',"sec-", 'cad_clientes',"","");
						// 	//$ret .= queta_formfield4("hidden",'1',"tab-", base64_encode($GLOBALS['tab15']),"","");
						// 	// $ret .= '<div class="col-md-12 mens2"></div>';
						// 	$ret .= '<div class="col-md-12" style="padding:10px 10px ">';
						// 	//$ret .= '<button type="submit" class="btn btn-success">Entrar</button><br>';

						// 	$ret .= '</div>';
						// if($form)
						// 	$ret .= '</form>';
			// $ret .= '</div>';
			// $ret .= '</div>';
        $ret = view('site.leiloes.payment.form_credit_card',$d);
		return $ret;
	}
    /**
	 * Cadastrar e verificar cadastro de cliente
	 */
	public function cad_cliente($conf=false){
		$config = isset($conf['cliente']) ? $conf['cliente'] : false;
		$config = isset($conf['cliente']) ? $conf['cliente'] : false;
		global $tk_conta;
		$ret['exec'] = false;
		$ret['mes'] = false;
		$ret['mensa'] = false;
		if(!$config){
			$ret['mensa'] = formatMensagem('dados de clientes não recebidos','danger');
			$ret['mes'] = false;
			return $ret;
		}
		$ac = isset($config['ac']) ? $config['ac'] : 'cad';
		$Nome = isset($config['Nome']) ? $config['Nome'] : false;
		$Email = isset($config['Email']) ? $config['Email'] : false;
		$Cpf = isset($config['Cpf']) ? $config['Cpf'] : false;
		$Nome = filter_var($Nome, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
		$Email = filter_var($Email, FILTER_SANITIZE_EMAIL);
		$no = explode(' ', $Nome);
		if(count($no) <2){
			$mes = 'Informe nome completo';
			$ret['mes'] = $mes;
			$ret['mensa'] = formatMensagem($mes,'danger');
			return $ret;

		}
		if(isset($no[0])){
			$Nome = $no[0];
		}
		if(isset($no[1])){
			$sobrenome = $no[1];
		}
		$Celular = isset($config['Celular']) ? $config['Celular'] : false;
		if(empty($Nome)){
			$mes = 'Nome é obrigatório';
			$ret['mensa'] = formatMensagem($mes,'danger');
			$ret['mes'] = $mes;
			return $ret;
		}
		if(empty($Email)){
			$mes = 'Email é obrigatório';
			$ret['mensa'] = formatMensagem($mes,'danger');
			$ret['mes'] = $mes;
			return $ret;
		}
		if(empty($Celular)){
			$mes = 'Celular é obrigatório';
			$ret['mensa'] = formatMensagem($mes,'danger');
			$ret['mes'] = $mes;
			return $ret;
		}
		if(empty($Cpf)){
			$mes = 'CPF é obrigatório';
			$ret['mensa'] = formatMensagem($mes,'danger');
			$ret['mes'] = $mes;
			return $ret;
		}

		$token = isset($config['token']) ? $config['token'] : uniqid();
		// $config['campo_bus'] = 'Celular';
		$config['campo_bus'] = 'Email';
		$type_alt = 2;
		$senha = str_replace('(','',$Cpf);
		$senha = str_replace(')','',$senha);
		$senha = str_replace('.','',$senha);
		$senha = str_replace('-','',$senha);
		// $nome = sanitize($nome);
		// return $nome;
		$dadosForm = [
			'Nome' => $Nome,
			'sobrenome' => $sobrenome,
			'Celular' => trim($Celular),
			'Email' => $Email,
			'token' => $token,
			'Cpf' => $Cpf,
			'senha' => $senha,
			'conf' => 's',
			'pg' => 'comprar', //para cadastrar no asaas e logar
			'sec' =>'cad_clientes_site',
		];
		if($id_cliente=is_clientLogado(true)){
			// $cond_valid = "WHERE id = '".$id_cliente."' AND ".compleDelete();
			$ac='cad';
			$cond_valid = "WHERE ".$config['campo_bus']." = '".$dadosForm['Email']."' AND ".compleDelete();
			// $dadosForm['campo_id']='id';
			$type_alt = 1;
		}else{
			$cond_valid = "WHERE ".$config['campo_bus']." = '".$dadosForm['Email']."' AND ".compleDelete();
		}
		$tabUser = $GLOBALS['tab15'];
		if($ac=='cad'){
			$dadosForm['enviaEmail'] = 's';
		}
		$config2 = array(
					'tab'=>$tabUser,
					'valida'=>true,
					'condicao_validar'=>$cond_valid,
					'sqlAux'=>false,
					'ac'=>$ac,
					'type_alt'=>$type_alt,
					'dadosForm' => $dadosForm
		);
		$ret = lib_json_array(lib_salvarFormulario($config2));
		if(isset($ret['salvar']['mess']) && $ret['salvar']['mess']=='enc' && ($d_enc=$ret['salvar']['dados_enc'][0])){
			$mes = 'Cliente com o e-mail '.$d_enc['Email'].' já cadastrado, efetue login para prosseguir';
			$ret['mes'] = $mes;
			$ret['mensa'] = formatMensagem($mes,'danger');
			return $ret;
		}elseif($ret['exec']==true && isset($ret['idCad']) && ($id_cliente = $ret['idCad'])){
			$id_cliente = $ret['idCad'];
		}
		// $id_cliente = is_clientLogado(true);
		//Verifica se o cliente ja está logado
		// $compra = isset($conf['compra']) ? $conf['compra'] : false;
		// if($id_cliente && $compra){
		// 	if(isset($compra['id_curso']) && $compra['id_curso']){
		// 		$dias_seguir 			= 1000;
		// 		$dataSeg	  			= CalcularVencimento2(date('d/m/Y'),$dias_seguir);
		// 		$_GET['data_seguir'] 	= dtBanco($dataSeg).' '.date('H:m:i');

		// 		$registrarCompra = self::regCompra($config);
		// 		$ret['registrarCompra'] = $registrarCompra;
		// 		if(isAdmin(1))
		// 			$ret['compra'] = $compra;
		// 		if(isset($registrarCompra['matricula']['tokenCad'])){
		// 			$ret['token'] = $registrarCompra['matricula']['tokenCad'];
		// 		}
		// 	}
		// }
		return $ret;
	}
	/**Metodo para registrar uma compra */
	public function regCompra($config=false){
		global $tk_conta;
		$ret['exec'] = false;
		$id_cliente = isset($config['id_cliente']) ? $config['id_cliente'] : is_clientLogado(true);
		$compra = isset($config['compra']) ? $config['compra'] : false;
		if(!$compra){
			$ret['mensa'] = 'Compra não informada';
			return $ret;
		}
		$configM['id_cliente']	= $id_cliente;
		$configM['id_curso'] 	= isset($compra['id_curso'])?$compra['id_curso']:0;
		$configM['id_turma'] 	= isset($compra['id_turma'])?$compra['id_turma']:0;
		$configM['status'] 		= isset($compra['status'])?$compra['status']:1;
		$configM['id_responsavel']=0;
		$configM['token'] 		= uniqid();
		$configM['Obs']=isset($compra['obs'])?$compra['obs']:false;
		$configM['ac']='cad';
		$configM['tab']=base64_encode($GLOBALS['tab12']);
		$configM['evento']='Compra do site';
		$configM['conf']='s';
		$configM['tag']=json_encode(array('via_site'));
		$configM['memo']=  'Via site';
		if(isset($_SESSION[$tk_conta]['af']['autor']) && !empty($_SESSION[$tk_conta]['af']['autor'])){
			$configM['seguido_por']=@$_SESSION[$tk_conta]['af']['autor'];
			$configM['autor']=@$_SESSION[$tk_conta]['af']['autor'];
			$configM['data_seguir']=$_GET['data_seguir'];
		}
		//Registrar a matricula do clientes
		$retu = (new ecomerce)->registrarCompra($configM);
		if(isset($retu['matricula']['exec'])){
			$ret['exec'] = $retu['matricula']['exec'];
			$ret['matricula'] = $retu['matricula'];

		}
		return $ret;
	}
	/**
	 * envia dados do cliente ao asaas gateway
	 */
	public function cad_cliente_asaas($id_cliente = null)
	{
		$ret = false;
		$asaas = new integraAsaas;
		$config['id_cliente'] = $id_cliente;
		$ret = $asaas->cadastrarCliente($config);

		return $ret;
	}
	/**
	 * Metodo para remover teste
	 */
	public function removeTest($tk_pedido = null,$ac='rc')
	{
		$ret['exec']=false;
		$dp = Escola::dadosMatricula($tk_pedido);
		if($dp){
			if(!empty($dp[0]['id_asaas'])){
				$ret = (new integraAsaas)->deletarCliente($dp[0]['id_cliente']);
				if($ret['exec']){
					if(!empty($dp[0]['Email'])){
						$sqlDelCli = "DELETE FROM ".$GLOBALS['tab15']." WHERE Email='".$dp[0]['Email']."'";
					}else{
						$sqlDelCli = "DELETE FROM ".$GLOBALS['tab15']." WHERE id='".$dp[0]['id']."'";
					}
					$ret['delCli'] = salvarAlterar($sqlDelCli);
					if($ret['delCli']){
						$sqlDelMatr = "DELETE FROM ".$GLOBALS['tab12']." WHERE token='".$tk_pedido."'";
						$ret['delMatr'] = salvarAlterar($sqlDelMatr);
					}
				}
			}else{

			}
		}
		// dd($ret);
		return $ret;
	}
    /**
     * Metodo para unificar a estrurua do array de pagamento do asaas
     * @param string $json da resposta do asaas
     */
    public function scheme_info_pagamento($json=false) {
        $arr_info = [];
        if($json){
			$arr_info = Qlib::lib_json_array($json);
            if(isset($arr_info['payment'])){
				$arr_info = $arr_info['payment'];
            }
            if(isset($arr_info['dueDate'])){
				$arr_info['vencimento'] = Qlib::dataExibe($arr_info['dueDate']);
            }
            if(isset($arr_info['clientPaymentDate'])){
				$arr_info['pagamento'] = Qlib::dataExibe($arr_info['clientPaymentDate']);
            }
            if(isset($arr_info['value'])){
				$arr_info['valor'] = Qlib::valor_moeda($arr_info['value'],'R$ ');
            }
            if(@$arr_info['billingType']=='CREDIT_CARD'){
				$arr_info['forma_pagamento'] = __('Cartão de crédito');
            }
            if(@$arr_info['billingType']=='PIX'){
                $arr_info['forma_pagamento'] = __('PIX');

            }
        }
        return $arr_info;
    }
    /**
     * Metodo para exibir informações de pagamento
     * @param integer $post_id
     */
    public function get_info_pagamento($post_id,$file_blade='modal_info_pagamento') {
        $status = Qlib::get_postmeta($post_id,$this->meta_status_pagamento,true);
        $json_info = Qlib::get_postmeta($post_id,$this->meta_resumo_pagamento,true);
        $arr_info = false;
        if($json_info){
            $arr_info = $this->scheme_info_pagamento($json_info);
        }
        return view('site.leiloes.payment.'.$file_blade,[
            'status'=>$status,
            'info_asaas'=>$arr_info,
            'id'=>$post_id,
        ]);
    }
    /**
     * Metodo para restagar o status de pagamento de um leilao
     * @param int $post_id
     * @return string $situacao_pagamento
     */
    public function get_status_payment($post_id,$type='html'){
        // $meta_status_pagamento = Qlib::qoption('meta_status_pagamento')?Qlib::qoption('meta_status_pagamento'):'pago';
        $sp = Qlib::get_postmeta($post_id,$this->meta_status_pagamento,true);
        if($sp=='s'){
            $sp = 'Pago';
            if($type=='html'){
                $situacao_pagamento = '<span class="text-success">'.$sp.'</span>';
            }else{
                $situacao_pagamento = $sp;
            }
        }elseif($sp == 'a'){
            $sp = 'Pix Gerado';
            if($type=='html'){
                $situacao_pagamento = '<span class="text-warning">'.$sp.'</span>';
            }else{
                $situacao_pagamento = $sp;
            }
        }else{
            $sp = 'Aguardando';
            if($type=='html'){
                $situacao_pagamento = '<span class="text-danger">'.$sp.'</span>';
            }else{
                $situacao_pagamento = $sp;
            }
        }
        return $situacao_pagamento;
    }
    /**
     * Metodo para exibir dados do usuario que pagou o leilao
     * @param int $leilao_id
     * @return array $ret
     */
    public function get_customer_leilao($leilao_id=false){
        $arr_info = [];
        if($leilao_id){
            $pago = Qlib::get_postmeta($leilao_id,$this->meta_status_pagamento,true);
            $json_info = Qlib::get_postmeta($leilao_id,$this->meta_resumo_pagamento,true);
            if($json_info){
                $arr_info = $this->scheme_info_pagamento($json_info);
            }
            if(isset($arr_info['customer']) && !empty($arr_info['customer'])){
                $id_cliente_pago = Qlib::buscaValorDb0('usermeta','meta_value',$arr_info['customer'],'user_id');
                $du = User::Find($id_cliente_pago);
                if($du->count() > 0){
                    $arr_info['user'] = $du->toArray();
                }
                //criar um metodo get_customer_leilao =
                //dd($id_cliente_pago);
            }
        }
        return $arr_info;
    }
    /**
     * Metodo exibir pagina de agradecimento
     * @param int $post_id || $dados = dados do post
     * @return string $situacao_pagamento
     */
    public function agradecimento($mensagem=false){
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $ret = false;
        if($seg1 && $seg2 && $mensagem){
            $status = 501;
            $token = $seg2;
            $leilao_id = Qlib::get_id_by_token($token);
            $dl = Post::FindOrFail($leilao_id);

            if(!$leilao_id){
                //Leião nem foi encontrado
                return view('site.meio404');
            }
            $lc = new LeilaoController; //LeilaoController
            //dados do ultimo lance
            $ul = $lc->get_lance_vencedor($leilao_id,false,'ultimo_lance');
            $ul = isset($ul['ultimo_lance']) ? $ul['ultimo_lance'] : false;
            $dc = false; //dados do contrato
            $dt = false; //dados do termino ou informações do termino
            // $mensagem = $mensagem;
            if($dl->count()){
                $dl = $dl->toArray();
                $dl['thumbnail'] = Qlib::get_thumbnail_link($dl['ID']);
                $dt = $lc->info_termino($leilao_id,$dl);
                if(isset($dl['config']['contrato']) && !empty($dl['config']['contrato'])){
                    $dc0 = Post::where('token',$dl['config']['contrato'])->get()->toArray();
                    if(isset($dc0[0])){
                        $dc = $dc0[0];
                    }
                }
                //Veriricar o nome de quel pagou este leilai na modalidade compre já
                // dd($dl);
            }else{
                //Leião nem foi encontrado
                return view('site.meio404');
            }
            $pago = Qlib::get_postmeta($leilao_id,$this->meta_status_pagamento,true);
            // $json_info = Qlib::get_postmeta($leilao_id,$this->meta_resumo_pagamento,true);
            $arr_info = $this->get_customer_leilao($leilao_id);
            $nome_cliente = false;
            if(isset($ul['nome'])){
                $nome_cliente   = isset($ul['nome'])?$ul['nome']:false;
            }elseif(isset($arr_info['user']['name']) && isset($arr_info['value'])){
                // dd($arr_info);
                $nome_cliente = $arr_info['user']['name'];
                $ul['valor_lance'] = $arr_info['value'];
            }else{
                //Se não tiver um cliente que deu o ultimo lance não aparece
                return view('site.meio404');
            }
            $mensagem = str_replace('{nome}',$nome_cliente,$mensagem);
            // dd($arr_info);
            if($pago=='a'){
                //Solicitação de geração do pix foi enviado para o gateway
                $status = 201;
            }elseif($pago=='s'){
                //pagamento ja foi realizado
                $status = 200;
            }
            if(isset($arr_info['value'])){
                $arr_info['valor'] = Qlib::valor_moeda($arr_info['value']);
            }
            $dview = [
                'status'=>$status,
                'dl'=>$dl, //dados do leilao
                'dc'=>$dc, //dados do contrato
                'ul'=>$ul, //dados do ultimo lance
                'dt'=>$dt, //dados do termino
                'arr_info_pagamento'=>$arr_info, //Informações do gamento vindas do Asaas
                'mensagem'=>$mensagem,
            ];
            $ret = view('site.leiloes.payment.agradecimento',$dview);
        }elseif($seg1 && $mensagem){
            $ret = $mensagem;
            $nome = request()->get('nome') ? request()->get('nome') : '';
            $ret = str_replace('{nome}',$nome,$ret);
        }else{
            $ret = view('site.meio404');
        }
        return $ret;
    }
}
