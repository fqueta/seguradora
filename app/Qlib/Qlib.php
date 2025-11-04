<?php
namespace App\Qlib;

use App\Http\Controllers\admin\PostsController;
use App\Models\_upload;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use App\Models\Qoption;
use Illuminate\Support\Facades\Config;

class Qlib
{
    static public function lib_print($data){
      if(is_array($data) || is_object($data)){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
      }else{
        echo $data;
      }
    }
    static public function qoption($valor = false, $type = false){
        //type é o tipo de respsta
		$ret = false;
		if($valor){
			//if($valor=='dominio_site'){
			//	$ret = dominio();
			//}elseif($valor==''){
			//	$ret = dominio().'/admin';
			//}else{
				//$sql = "SELECT valor FROM qoptions WHERE url = '$valor' AND ativo='s' AND excluido='n' AND deletado='n'";

                //$result = Qlib::dados_tab('qoptions',['sql'=>$sql]);
                $result = Qoption::where('url','=',$valor)->
                where('ativo','=','s')->
                where('excluido','=','n')->
                where('deletado','=','n')->
                select('valor')->
                get();

				if(isset($result[0]->valor)) {
						// output data of each row
						$ret = $result[0]->valor;
						if($valor=='urlroot'){
							$ret = str_replace('/home/ctloja/public_html/lojas/','/home/ctdelive/lojas/',$ret);
						}
                        if($type=='array'){
                            $ret = Qlib::lib_json_array($ret);
                        }
                        if($type=='json'){
                            $ret = Qlib::lib_array_json($ret);
                        }
				}
			//}
		}
		return $ret;
	}
    static function ultimoDiaMes($mes,$ano){
        $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
        return $ultimo_dia;
    }
    static function documento($code,$select='conteudo'){
        $ret = false;
        $d = Documento::where('url','=',$code)->
        where('ativo','=','s')->
        where('excluido','=','n')->
        where('deletado','=','n')->
        select($select)->get();
        if(isset($d[0]->$select)) {
            $ret = $d[0]->$select;
        }
        return $ret;

    }
  static function dtBanco($data) {
			$data = trim($data);
			if (strlen($data) != 10)
			{
				$rs = false;
			}
			else
			{
				$arr_data = explode("/",$data);
				$data_banco = $arr_data[2]."-".$arr_data[1]."-".$arr_data[0];
				$rs = $data_banco;
			}
			return $rs;
	}
    static function dataExibe($data) {
			$val = trim(strlen($data));
			$data = trim($data);$rs = false;
			if($val == 10){
					$arr_data = explode("-",$data);
					$data_banco = $arr_data[2]."/".$arr_data[1]."/".$arr_data[0];
					$rs = $data_banco;
			}
			if($val == 19){
					$arr_inic = explode(" ",$data);
					$arr_data = explode("-",$arr_inic[0]);
					$data_banco = $arr_data[2]."/".$arr_data[1]."/".$arr_data[0];
					$rs = $data_banco."-".$arr_inic[1] ;
			}

			return $rs;
	}
  static function lib_json_array($json=''){
		$ret = false;
		if(is_array($json)){
			$ret = $json;
		}elseif(!empty($json) && Qlib::isJson($json)&&!is_array($json)){
			$ret = json_decode($json,true);
		}
		return $ret;
	}
	public static function lib_array_json($json=''){
		$ret = false;
		if(is_array($json)){
			$ret = json_encode($json,JSON_UNESCAPED_UNICODE);
		}
		return $ret;
	}
    static function precoBanco($preco){
            $sp = substr($preco,-3,-2);
            if($sp=='.'){
                $preco_venda1 = $preco;
            }else{
                $preco_venda1 = str_replace(".", "", $preco);
                $preco_venda1 = str_replace(",", ".", $preco_venda1);
            }
            return $preco_venda1;
    }
    static function isJson($string) {
		$ret=false;
		if (is_object(json_decode($string)) || is_array(json_decode($string)))
		{
			$ret=true;
		}
		return $ret;
	}
  static function Meses($val=false){
  		$mese = array('01'=>'JANEIRO','02'=>'FEVEREIRO','03'=>'MARÇO','04'=>'ABRIL','05'=>'MAIO','06'=>'JUNHO','07'=>'JULHO','08'=>'AGOSTO','09'=>'SETEMBRO','10'=>'OUTUBRO','11'=>'NOVEMBRO','12'=>'DEZEMBRO');
  		if($val){
  			return $mese[$val];
  		}else{
  			return $mese;
  		}
	}
  static function totalReg($tabela, $condicao = false,$debug=false){
			//necessario
			$sql = "SELECT COUNT(*) AS totalreg FROM {$tabela} $condicao";
			if($debug)
				 echo $sql.'<br>';
			//return $sql;
			$td_registros = DB::select($sql);
			if(isset($td_registros[0]->totalreg) && $td_registros[0]->totalreg > 0){
				return $td_registros[0]->totalreg;
			}else
				return 0;
	}
  static function zerofill( $number ,$nroDigo=6, $zeros = null ){
		$string = sprintf( '%%0%ds' , is_null( $zeros ) ?  $nroDigo : $zeros );
		return sprintf( $string , $number );
	}
  static function encodeArray($arr){
			$ret = false;
			if(is_array($arr)){
				$ret = base64_encode(json_encode($arr));
			}
			return $ret;
	}
  static function decodeArray($arr){
			$ret = false;
			if($arr){
				//$ret = base64_encode(json_encode($arr));
				$ret = base64_decode($arr);
				$ret = json_decode($ret,true);

			}
			return $ret;
	}
    static function qForm($config=false){
        if(isset($config['type'])){
            $config['campo'] = isset($config['campo'])?$config['campo']:'teste';
            $config['label'] = isset($config['label'])?$config['label']:false;
            $config['placeholder'] = isset($config['placeholder'])?$config['placeholder']:false;
            $config['selected'] = isset($config['selected']) ? $config['selected']:false;
            $config['tam'] = isset($config['tam']) ? $config['tam']:'12';
            $config['col'] = isset($config['col']) ? $config['col']:'md';
            $config['event'] = isset($config['event']) ? $config['event']:false;
            $config['ac'] = isset($config['ac']) ? $config['ac']:'cad';
            $config['option_select'] = isset($config['option_select']) ? $config['option_select']:true;
            $config['label_option_select'] = isset($config['label_option_select']) ? $config['label_option_select']:'Selecione';
            $config['option_gerente'] = isset($config['option_gerente']) ? $config['option_gerente']:false;
            $config['class'] = isset($config['class']) ? $config['class'] : false;
            $config['style'] = isset($config['style']) ? $config['style'] : false;
            $config['class_div'] = isset($config['class_div']) ? $config['class_div'] : false;
            if(@$config['type']=='chave_checkbox' && @$config['ac']=='cad'){
                if(@$config['checked'] == null && isset($config['valor_padrao']))
                $config['checked'] = $config['valor_padrao'];
            }
            //if($config['type']=='select_multiple'){
                //dd($config);
            //}
            if(@$config['type']=='html_vinculo' && @$config['ac']=='alt'){
                $tab = $config['data_selector']['tab'];
                $config['data_selector']['placeholder'] = isset($config['data_selector']['placeholder'])?$config['data_selector']['placeholder']:'Digite para iniciar a consulta...';
                $dsel = $config['data_selector'];
                $id = $config['value'];
                if(@$dsel['tipo']=='array'){
                    if(is_array($id)){
                        foreach ($id as $ki => $vi) {
                            $config['data_selector']['list'][$ki] = Qlib::dados_tab($tab,['id'=>$vi]);
                            if($config['data_selector']['list'][$ki] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                                foreach ($config['data_selector']['table'] as $key => $v) {
                                    if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$ki][$key]) && isset($v['conf_sql'])){
                                        $config['data_selector']['list'][$ki][$key.'_valor'] = Qlib::buscaValorDb([
                                            'tab'=>$v['conf_sql']['tab'],
                                            'campo_bus'=>$v['conf_sql']['campo_bus'],
                                            'select'=>$v['conf_sql']['select'],
                                            'valor'=>$config['data_selector']['list'][$ki][$key],
                                        ]);
                                    }
                                }
                            }
                        }
                        //dd($config['data_selector']);
                    }
                }else{
                    $config['data_selector']['list'] = Qlib::dados_tab($tab,['id'=>$id]);
                    if($config['data_selector']['list'] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                        foreach ($config['data_selector']['table'] as $key => $v) {
                            if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$key]) && isset($v['conf_sql'])){
                                $config['data_selector']['list'][$key.'_valor'] = Qlib::buscaValorDb([
                                    'tab'=>$v['conf_sql']['tab'],
                                    'campo_bus'=>$v['conf_sql']['campo_bus'],
                                    'select'=>$v['conf_sql']['select'],
                                    'valor'=>$config['data_selector']['list'][$key],
                                ]);
                            }
                        }
                        //dd($config);
                    }
                }
            }
            return view('qlib.campos_form',['config'=>$config]);
        }else{
            return false;
        }
    }
    static function qShow($config=false){
        if(isset($config['type'])){
            $config['campo'] = isset($config['campo'])?$config['campo']:'teste';
            $config['label'] = isset($config['label'])?$config['label']:false;
            $config['placeholder'] = isset($config['placeholder'])?$config['placeholder']:false;
            $config['selected'] = isset($config['selected']) ? $config['selected']:false;
            $config['tam'] = isset($config['tam']) ? $config['tam']:'12';
            $config['col'] = isset($config['col']) ? $config['col']:'md';
            $config['event'] = isset($config['event']) ? $config['event']:false;
            $config['ac'] = isset($config['ac']) ? $config['ac']:'cad';
            $config['option_select'] = isset($config['option_select']) ? $config['option_select']:true;
            $config['label_option_select'] = isset($config['label_option_select']) ? $config['label_option_select']:'Selecione';
            $config['option_gerente'] = isset($config['option_gerente']) ? $config['option_gerente']:false;
            $config['class'] = isset($config['class']) ? $config['class'] : false;
            $config['style'] = isset($config['style']) ? $config['style'] : false;
            $config['class_div'] = isset($config['class_div']) ? $config['class_div'] : false;
            if(@$config['type']=='chave_checkbox' && @$config['ac']=='cad'){
                if(@$config['checked'] == null && isset($config['valor_padrao']))
                $config['checked'] = $config['valor_padrao'];
            }
            if(@$config['type']=='html_vinculo' && @$config['ac']=='alt'){
                $tab = $config['data_selector']['tab'];
                $config['data_selector']['placeholder'] = isset($config['data_selector']['placeholder'])?$config['data_selector']['placeholder']:'Digite para iniciar a consulta...';
                $dsel = $config['data_selector'];
                $id = $config['value'];
                if(@$dsel['tipo']=='array'){
                    if(is_array($id)){
                        foreach ($id as $ki => $vi) {
                            $config['data_selector']['list'][$ki] = Qlib::dados_tab($tab,['id'=>$vi]);
                            if($config['data_selector']['list'][$ki] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                                foreach ($config['data_selector']['table'] as $key => $v) {
                                    if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$ki][$key]) && isset($v['conf_sql'])){
                                        $valse = $config['data_selector']['list'][$ki][$key];
                                        $config['data_selector']['list'][$ki][$key.'_valor'] = Qlib::buscaValorDb([
                                            'tab'=>$v['conf_sql']['tab'],
                                            'campo_bus'=>$v['conf_sql']['campo_bus'],
                                            'select'=>$v['conf_sql']['select'],
                                            'valor'=>$valse,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }else{
                    $config['data_selector']['list'] = Qlib::dados_tab($tab,['id'=>$id]);
                    if($config['data_selector']['list'] && isset($config['data_selector']['table']) && is_array($config['data_selector']['table'])){
                        foreach ($config['data_selector']['table'] as $key => $v) {
                            if(isset($v['type']) && $v['type']=='arr_tab' && isset($config['data_selector']['list'][$key]) && isset($v['conf_sql'])){
                                $config['data_selector']['list'][$key.'_valor'] = Qlib::buscaValorDb([
                                    'tab'=>$v['conf_sql']['tab'],
                                    'campo_bus'=>$v['conf_sql']['campo_bus'],
                                    'select'=>$v['conf_sql']['select'],
                                    'valor'=>$config['data_selector']['list'][$key],
                                ]);
                            }
                        }
                        //dd($config);
                    }
                }
            }
            return view('qlib.campos_show',['config'=>$config]);
        }else{
            return false;
        }
    }
    static function sql_array($sql, $ind, $ind_2, $ind_3 = '', $leg = '',$type=false){
        $table = DB::select($sql);
        $userinfo = array();
        if($table){
            //dd($table);
            for($i = 0;$i < count($table);$i++){
                $table[$i] = (array)$table[$i];
                if($ind_3 == ''){
                    $userinfo[$table[$i][$ind_2]] =  $table[$i][$ind];
                }elseif($ind_3 == 'attr_data'){
                    //neste caso vai retornar um array como valor para ser gravado em um optio de select como atributo dados
                    $userinfo[$table[$i][$ind_2]] = ['option'=>$table[$i][$ind],'attr_data'=>self::encodeArray($table[$i])];
                }elseif(is_array($ind_3) && isset($ind_3['tab'])){
                    /*É sinal que o valor vira de banco de dados*/
                    $sql = "SELECT ".$ind_3['campo_enc']." FROM `".$ind_3['tab']."` WHERE ".$ind_3['campo_bus']." = '".$table[$i][$ind_2]."'";
                    $userinfo[$table[$i][$ind_2]] = $sql;
                }else{
                    if($type){
                        if($type == 'data'){
                            /*Tipo de campo exibe*/
                            $userinfo[$table[$i][$ind_2]] = $table[$i][$ind] . '' . $leg . '' . Qlib::dataExibe($table[$i][$ind_3]);
                        }
                    }else{
                        $userinfo[$table[$i][$ind_2]] = $table[$i][$ind] . '' . $leg . '' . $table[$i][$ind_3];
                    }
                }
            }
        }

        return $userinfo;
    }
    static function formatMensagem($config=false){
        if($config){
            $config['mens'] = isset($config['mens']) ? $config['mens'] : false;
            $config['color'] = isset($config['color']) ? $config['color'] : false;
            $config['time'] = isset($config['time']) ? $config['time'] : 4000;
            return view('qlib.format_mensagem', ['config'=>$config]);
        }else{
            return false;
        }
	}
    static function formatMensagemInfo($mess='',$cssMes='',$event=false){
		$mensagem = "<div class=\"alert alert-$cssMes alert-dismissable\" role=\"alert\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" $event aria-hidden=\"true\">×</button><i class=\"fa fa-info-circle\"></i>&nbsp;".__($mess)."</div>";
		return $mensagem;
	}
    static function gerUploadAquivos($config=false){
        if($config){
            $config['parte'] = isset($config['parte']) ? $config['parte'] : 'painel';
            $config['token_produto'] = isset($config['token_produto']) ? $config['token_produto'] : false;
            $config['listFiles'] = isset($config['listFiles']) ? $config['listFiles'] : false; // array com a lista
            $config['listFilesCode'] = isset($config['listFilesCode']) ? $config['listFilesCode'] : false; // array com a lista
            $config['time'] = isset($config['time']) ? $config['time'] : 4000;
            $config['arquivos'] = isset($config['arquivos']) ? $config['arquivos'] : false;
            if($config['listFiles']){
                $tipo = false;
                foreach ($config['listFiles'] as $key => $value) {
                    if(isset($value['config'])){
                        $arr_conf = Qlib::lib_json_array($value['config']);
                        if(isset($arr_conf['extenssao']) && !empty($arr_conf['extenssao']))
                        {
                            if($arr_conf['extenssao'] == 'jpg' || $arr_conf['extenssao']=='png' || $arr_conf['extenssao'] == 'jpeg'){
                                $tipo = 'image';
                            }elseif($arr_conf['extenssao'] == 'doc' || $arr_conf['extenssao'] == 'docx') {
                                $tipo = 'word';
                            }elseif($arr_conf['extenssao'] == 'xls' || $arr_conf['extenssao'] == 'xlsx') {
                                $tipo = 'excel';
                            }else{
                                $tipo = 'download';
                            }
                        }
                        $config['listFiles'][$key]['tipo_icon'] = $tipo;
                    }
                }
            }
            if(isset($config['parte'])){
                $view = 'qlib.uploads.painel';
                return view($view, ['config'=>$config]);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function gerUploadWp($config=false){
        if($config){
            $config['parte'] = isset($config['parte']) ? $config['parte'] : 'painel';
            $config['token_produto'] = isset($config['token_produto']) ? $config['token_produto'] : false;
            $config['listFiles'] = isset($config['listFiles']) ? $config['listFiles'] : false; // array com a lista
            $config['time'] = isset($config['time']) ? $config['time'] : 4000;
            $config['arquivos'] = isset($config['arquivos']) ? $config['arquivos'] : false;
            if($config['listFiles']){
                $tipo = false;
                foreach ($config['listFiles'] as $key => $value) {
                    if(isset($value['config'])){
                        $arr_conf = Qlib::lib_json_array($value['config']);
                        if(isset($arr_conf['extenssao']) && !empty($arr_conf['extenssao']))
                        {
                            if($arr_conf['extenssao'] == 'jpg' || $arr_conf['extenssao']=='png' || $arr_conf['extenssao'] == 'jpeg'){
                                $tipo = 'image';
                            }elseif($arr_conf['extenssao'] == 'doc' || $arr_conf['extenssao'] == 'docx') {
                                $tipo = 'word';
                            }elseif($arr_conf['extenssao'] == 'xls' || $arr_conf['extenssao'] == 'xlsx') {
                                $tipo = 'excel';
                            }else{
                                $tipo = 'download';
                            }
                        }
                        $config['listFiles'][$key]['tipo_icon'] = $tipo;
                    }
                }
            }
            if(isset($config['parte'])){
                $view = 'qlib.uploads.painel_wp';
                return view($view, ['config'=>$config]);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function formulario($config=false){
        if($config['campos']){
            $view = 'qlib.formulario';
            return view($view, ['conf'=>$config]);
        }else{
            return false;
        }
    }
    static function show($config=false){
        if($config['campos']){
            $view = 'qlib.show';
            return view($view, ['conf'=>$config]);
        }else{
            return false;
        }
    }
    static function listaTabela($config=false){
        if($config['campos_tabela']){
            $view = 'qlib.listaTabela';
            return view($view, ['conf'=>$config]);
        }else{
            return false;
        }
    }
    static function UrlAtual(){
        return URL::full();
    }
    static function ver_PermAdmin($perm=false,$url=false){
        $ret = false;
        if(!Auth::check()){
            return $ret;
        }
        if(!$url){
            $url = URL::current();
            $arr_url = explode('/',$url);
        }
        if($url && $perm){
            $arr_permissions = [];
            $logado = Auth::user();
            $id_permission = $logado->id_permission;
            $dPermission = Permission::findOrFail($id_permission);
            if($dPermission && $dPermission->active=='s'){
                $arr_permissions = Qlib::lib_json_array($dPermission->id_menu);
                if(isset($arr_permissions[$perm][$url])){
                    $ret = true;
                }
            }
        }
        return $ret;
    }
    static public function html_vinculo($config = null)
    {
        /*
        Qlib::html_vinculo([
            'campos'=>'',
            'type'=>'html_vinculo',
            'dados'=>'',
        ]);
         */

        $ret = false;
        $campos = isset($config['campos'])?$config['campos']:false;
        $type = isset($config['type'])?$config['type']:false;
        $dados = isset($config['dados'])?$config['dados']:false;
        if(!$campos)
            return $ret;
        if(is_array($campos) && $dados){
            foreach ($campos as $key => $value) {
                if($value['type']==$type){
                    $id = $dados[$key];
                    $tab = $value['data_selector']['tab'];
                    $d_tab = DB::table($tab)->find($id);
                    if($d_tab){
                        $ret[$key] = (array)$d_tab;
                    }
                }
            }
        }
        return $ret;
    }
    static public function dados_tab($tab = null,$config)
    {
        $ret = false;
        if($tab){
            $id = isset($config['id']) ? $config['id']:false;
            $sql = isset($config['sql']) ? $config['sql']:false;
            if($sql){
                $d = DB::select($sql);
                $arr_list = $d;
                $list = false;
                foreach ($arr_list as $k => $v) {
                    if(is_object($v)){
                        $list[$k] = (array)$v;
                        foreach ($list[$k] as $k1 => $v1) {
                            if(Qlib::isJson($v1)){
                                $list[$k][$k1] = Qlib::lib_json_array($v1);
                            }
                        }
                    }
                }
                $ret = $list;
                return $ret;
            }else{
                $obj_list = DB::table($tab)->find($id);
            }
            if($list=(array)$obj_list){
                //dd($obj_list);
                    if(is_array($list)){
                        foreach ($list as $k => $v) {
                            if(Qlib::isJson($v)){
                                $list[$k] = Qlib::lib_json_array($v);
                            }
                        }
                    }
                    $ret = $list;
            }
        }
        return $ret;
    }
    static public function dados_tab_SERVER($tab = null,$config=false)
    {
        $ret = false;
        if($tab){
            $id = isset($config['id']) ? $config['id']:false;
            $sql = isset($config['sql']) ? $config['sql']:false;
            $dominio = Qlib::dominio();
            $mysql = isset($config['mysql']) ? $config['mysql']:'mysql_ger';
            if($sql){
                $d = DB::connection($mysql)->select($sql);
                $arr_list = $d;
                $list = false;
                foreach ($arr_list as $k => $v) {
                    if(is_object($v)){
                        $list[$k] = (array)$v;
                        foreach ($list[$k] as $k1 => $v1) {
                            if(Qlib::isJson($v1)){
                                $list[$k][$k1] = Qlib::lib_json_array($v1);
                            }
                        }
                    }
                }
                $ret = $list;
                return $ret;
            }else{
                if($tab=='contas_usuarios' && $dominio){
                    $obj_list = DB::connection($mysql)->table($tab)->where('dominio','=',$dominio)->get();
                }else{
                    if($id)
                        $obj_list = DB::connection($mysql)->table($tab)->find($id);
                }
            }
            if($obj_list->count()>0 && $list=$obj_list){
                if(is_array($list)){
                        foreach ($list as $k => $v) {
                            if(Qlib::isJson($v)){
                                $list[$k] = Qlib::lib_json_array($v);
                            }
                        }
                    }
                    $ret = $list;
            }
        }
        return $ret;
    }
    static public function buscaValorDb($config = false)
    {
        /*Qlib::buscaValorDd([
            'tab'=>'',
            'campo_bus'=>'',
            'valor'=>'',
            'select'=>'',
            'compleSql'=>'',
        ]);
        */
        $ret=false;
        $tab = isset($config['tab'])?$config['tab']:false;
        $campo_bus = isset($config['campo_bus'])?$config['campo_bus']:'id';//campo select
        $valor = isset($config['valor'])?$config['valor']:false;
        $select = isset($config['select'])?$config['select']:false; //
        $compleSql = isset($config['compleSql'])?$config['compleSql']:false; //
        if($tab && $campo_bus && $valor && $select){
            $sql = "SELECT $select FROM $tab WHERE $campo_bus='$valor' $compleSql";
            if(isset($config['debug'])&&$config['debug']){
                echo $sql;
            }
            $d = DB::select($sql);
            if($d)
                $ret = $d[0]->$select;
        }
        return $ret;
    }
    static public function valorTabDb($tab = false,$campo_bus,$valor,$select,$compleSql=false)
    {

        $ret=false;
        /*
        $tab = isset($config['tab'])?$config['tab']:false;
        $campo_bus = isset($config['campo_bus'])?$config['campo_bus']:'id';//campo select
        $valor = isset($config['valor'])?$config['valor']:false;
        $select = isset($config['select'])?$config['select']:false; //
        $compleSql = isset($config['compleSql'])?$config['compleSql']:false; //
        */
        if($tab && $campo_bus && $valor && $select){
            $sql = "SELECT $select FROM $tab WHERE $campo_bus='$valor' $compleSql";
            if(isset($config['debug'])&&$config['debug']){
                echo $sql;
            }
            $d = DB::select($sql);
            if($d)
                $ret = $d[0]->$select;
        }
        return $ret;
    }
    static function lib_valorPorExtenso($valor=0) {
		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");

		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");

		$z=0;

		$valor = @number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);
		for($i=0;$i<count($inteiro);$i++)
			for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
				$inteiro[$i] = "0".$inteiro[$i];

		// $fim identifica onde que deve se dar junção de centenas por "e" ou por "," 😉
		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		$rt=false;
		for ($i=0;$i<count($inteiro);$i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
		}
		return($rt ? $rt : "zero");
	}
	static function convert_number_to_words($number) {

		$hyphen      = '-';
		$conjunction = ' e ';
		$separator   = ', ';
		$negative    = 'menos ';
		$decimal     = ' ponto ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'um',
			2                   => 'dois',
			3                   => 'três',
			4                   => 'quatro',
			5                   => 'cinco',
			6                   => 'seis',
			7                   => 'sete',
			8                   => 'oito',
			9                   => 'nove',
			10                  => 'dez',
			11                  => 'onze',
			12                  => 'doze',
			13                  => 'treze',
			14                  => 'quatorze',
			15                  => 'quinze',
			16                  => 'dezesseis',
			17                  => 'dezessete',
			18                  => 'dezoito',
			19                  => 'dezenove',
			20                  => 'vinte',
			30                  => 'trinta',
			40                  => 'quarenta',
			50                  => 'cinquenta',
			60                  => 'sessenta',
			70                  => 'setenta',
			80                  => 'oitenta',
			90                  => 'noventa',
			100                 => 'cento',
			200                 => 'duzentos',
			300                 => 'trezentos',
			400                 => 'quatrocentos',
			500                 => 'quinhentos',
			600                 => 'seiscentos',
			700                 => 'setecentos',
			800                 => 'oitocentos',
			900                 => 'novecentos',
			1000                => 'mil',
			1000000             => array('milhão', 'milhões'),
			1000000000          => array('bilhão', 'bilhões'),
			1000000000000       => array('trilhão', 'trilhões'),
			1000000000000000    => array('quatrilhão', 'quatrilhões'),
			1000000000000000000 => array('quinquilhão', 'quinquilhões')
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words só aceita números entre ' . PHP_INT_MAX . ' à ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . Qlib::convert_number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}
        $number = (int)$number;
		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $conjunction . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = floor($number / 100)*100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds];
				if ($remainder) {
					$string .= $conjunction . Qlib::convert_number_to_words($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				if ($baseUnit == 1000) {
					$string = Qlib::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[1000];
				} elseif ($numBaseUnits == 1) {
					$string = Qlib::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit][0];
				} else {
					$string = Qlib::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit][1];
				}
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= Qlib::convert_number_to_words($remainder);
				}
				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}

		return $string;
	}
    static function limpar_texto($str){
        return preg_replace("/[^0-9]/", "", $str);
    }
    static function compleDelete($var = null)
    {
        if($var){
            return "$var.excluido='n' AND $var.deletado='n'";
        }else{
            return "excluido='n' AND deletado='n'";
        }
    }
    static public function show_files(Array $config = null)
    {
        $ret = Qlib::formatMensagemInfo('Nenhum Arquivo','info');
        if($config['token']){
            $files = DB::table('_uploads')->where('token_produto',$config['token'])->get();
            if($files){
                if(isset($files[0]))
                    return view('qlib.show_file',['files'=>$files,'config'=>$config]);
            }
        }
        return $ret;
    }
    /***
     * Busca um tipo de routa padrão do sistema
     * Ex.: routa que será aberta ao logar
     *
     */
    static function redirectLogin($ambiente='back')
    {
        $ret = '/';
        if(!Auth::check()){
            return $ret;
        }
        $id_permission = auth()->user()->id_permission;
        $dPermission = Permission::FindOrFail($id_permission);
        $ret = isset($dPermission['redirect_login']) ? $dPermission['redirect_login']:'/';
        return $ret;
    }
    /***
     * Informa o ambiente da página aberta
     * Ex.: routa que será aberta ao logar
     *
     */
    static function ambiente()
    {
        $seg1 = request()->segment(1);
        $prefixo_admin = config('app.prefixo_admin');
        $prefixo_site = config('app.prefixo_site');
        if($seg1==$prefixo_admin){
            return 'back';
        }else{
            return 'front';
        }
    }
    static function verificaArquivo($file,$tipo_permitido='jpg,png'){
        $ret['exec']=false;
        $ret['mens']=false;
        $extension = $file->getClientOriginalExtension();
        if($tipo_permitido){
            $arr_extension = explode(',',$tipo_permitido);
        }
        if(in_array($extension,$arr_extension)){
            $ret['exec'] = true;
        }else{
            $ret['mens'] = 'O Arquivo tipo '.$extension.' não é permitido!';
        }
        return $ret;
    }
    /**
     * MONTA UM ARRAY COM OPÇÕES DE SEXO
     * @retun array ou string se $var não for nulo
     */
    static function lib_sexo($var = null)
    {
        $arr_tipo_genero = [
            'm'=>__('Masculino'),'f'=>__('Feminino'),'ni'=>__('Não informar')
        ];
        if(!$var){
            return $arr_tipo_genero;
        }else{
            return $arr_tipo_genero[$var];
        }
    }
    /**
     * MONTA UM ARRAY COM OPÇÕES DE ESCOLARIDADE ORIGEM TABELA ESCOLARIDADES
     * @retun array ou string se $var não for nulo
     */
    static function lib_escolaridades($var = null)
    {
        $arr_tipo_escolaridade = Qlib::sql_array("SELECT id,nome FROM escolaridades WHERE ativo='s' ORDER BY nome ASC",'nome','id');
        if(!$var){
            return $arr_tipo_escolaridade;
        }else{
            return $arr_tipo_escolaridade[$var];
        }
    }
    /**
     * MONTA UM ARRAY COM OPÇÕES DE PROFISSÃO ORIGEM TABELA profissaos
     * @retun array ou string se $var não for nulo
     */
    static function lib_profissao($var = null)
    {
        $arr_tipo_profissao = Qlib::sql_array("SELECT id,nome FROM profissaos WHERE ativo='s' ORDER BY nome ASC",'nome','id');
        if(!$var){
            return $arr_tipo_profissao;
        }else{
            return $arr_tipo_profissao[$var];
        }
    }
    static function dominio(){
        $url_atual = "http" . (isset($_SERVER['HTTPS']) ? (($_SERVER['HTTPS']=="on") ? "s" : "") : "") . "://" . "$_SERVER[HTTP_HOST]";
        return $url_atual;
    }
    /**
     * Metodo para retornar o nome do subdominio o vazio caso não seja um subdominio
     */
    static function is_subdominio(){
        $ret = explode('.', request()->getHost())[0];
        return $ret;
    }
    static function selectDefaultConnection($connection='mysql',$database){
        if($connection=='tenant'){
            $clone = config('database.connections.mysql');
            $clone['database'] = $database;
            Config::set('database.connections.tenant.database', $clone['database']);
            Config::set('database.connections.tenant.username', $clone['username']);
            Config::set('database.connections.tenant.password', $clone['password']);

            // if(isset($conn['name']) && isset($conn['user']) && isset($conn['pass'])){
            //     $db = isset($conn['name'])?$conn['name']:false;
            //     $user = isset($conn['user'])?$conn['user']:false;
            //     $pass = isset($conn['pass'])?$conn['pass']:false;
            //     if($user && $db){
            //     }
            // }else{
            //     $arr_tenancy = session()->get('tenancy');
            //     if(isset($arr_tenancy['config']) && Qlib::isJson($arr_tenancy['config'])){
            //         $arr_config=Qlib::lib_json_array($arr_tenancy['config']);
            //         $db = isset($arr_config['name'])?$arr_config['name']:false;
            //         $user = isset($arr_config['user'])?$arr_config['user']:false;
            //         $pass = isset($arr_config['pass'])?$arr_config['pass']:false;
            //         if($user && $db){
            //             Config::set('database.connections.tenant.database', trim($db));
            //             Config::set('database.connections.tenant.username', trim($user));
            //             Config::set('database.connections.tenant.password', trim($pass));
            //         }
            //     }
            // }
            // $clone = config('database.connections.mysql');
            // $clone['database'] = $db;
            // Config::set('database.connections.'.$connection, $clone);

        }
        DB::purge($connection);
        DB::reconnect($connection);
        DB::setDefaultConnection($connection);
        // Config::set('database.default', $connection);
        return DB::getDefaultConnection();

    }
    /**
     * Metodo que verifica se a conexão atual é de um tenant ou não
     */
    static function is_tenant(){
        $conn = DB::getDefaultConnection();
        if($conn == 'tenant'){
            return true;
        }else{
            return false;
        }
    }
    static function is_backend(){
        $ret = false;
        // $urlAt = Qlib::UrlAtual();
        $seg1 = request()->segment(1);
        if($seg1 == 'admin'){
            $ret = true;
        }
        return $ret;
    }
    static function is_frontend(){
        $ret = false;
        // $urlAt = Qlib::UrlAtual();
        $seg1 = request()->segment(1);
        if($seg1 != 'admin'){
            $ret = true;
        }
        return $ret;
    }

    static function get_slug_post_by_id($post_id){
        return self::buscaValorDb0('posts','ID', $post_id,'post_name');
    }
    // public static function createSlug($str, $delimiter = '-'){

    //     $slug = \Str::slug($str);
    //     return $slug;
    // }
    static public function buscaValorDb0($tab,$campo_bus,$valor,$select,$compleSql=false,$debug=false)
    {
        $ret = false;
        if($tab && $campo_bus && $valor && $select){
            $sql = "SELECT $select FROM $tab WHERE $campo_bus='$valor' $compleSql";
            if(isset($debug)&&$debug){
                echo $sql;
            }
            $d = DB::select($sql);
            if($d)
                $ret = $d[0]->$select;
        }
        return $ret;
    }
    /**
     * Verifica se o usuario logado tem permissao de admin ou alguma expessífica
     */
    static function dataLocal(){
        $dataLocal = date('d/m/Y H:i:s', time());
        return $dataLocal;
    }
    static function dataLocalDb(){
        $dtBanco = date('Y-m-d H:i:s', time());
        return $dtBanco;
    }
    static function dataBanco(){
        global $dtBanco;
        $dtBanco = date('Y-m-d H:i:s', time());
        return $dtBanco;
    }
    static function isAdmin($perm_admin = 2)
    {
        $user = Auth::user();

        if(isset($user->id_permission) && $user->id_permission<=$perm_admin){
            return true;
        }else{
            return false;
        }
    }

    static function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    /**
     * Metodo buscar o post_id com o token
     * @param string $token
     * @return string $ret;
     */
    static function get_id_by_token($token)
    {
        if($token){
            return Qlib::buscaValorDb0('posts','token',$token,'ID');
        }
    }
    /**
     * Metodo para salvar ou atualizar os meta posts
     */
    static function update_postmeta($post_id,$meta_key=null,$meta_value=null)
    {
        $ret = false;
        $tab = 'postmeta';
        if($post_id&&$meta_key&&$meta_value){
            $verf = Qlib::totalReg($tab,"WHERE post_id='$post_id' AND meta_key='$meta_key'");
            if($verf){
                $ret=DB::table($tab)->where('post_id',$post_id)->where('meta_key',$meta_key)->update([
                    'meta_value'=>$meta_value,
                    'updated_at'=>self::dataBanco(),
                ]);
            }else{
                $ret=DB::table($tab)->insert([
                    'post_id'=>$post_id,
                    'meta_value'=>$meta_value,
                    'meta_key'=>$meta_key,
                    'created_at'=>self::dataBanco(),
                ]);
            }
            //$ret = DB::table($tab)->storeOrUpdate();
        }
        return $ret;
    }

    /**
     * Metodo para pegar os meta posts
     */
    static function get_postmeta($post_id,$meta_key=null,$string=null)
    {
        $ret = false;
        $tab = 'postmeta';
        if($post_id){
            if($meta_key){
                $d = DB::table($tab)->where('post_id',$post_id)->where('meta_key',$meta_key)->get();
                if($d->count()){
                    if($string){
                        $ret = $d[0]->meta_value;
                    }else{
                        $ret = [$d[0]->meta_value];
                    }
                }else{
                    $post_id = self::get_id_by_token($post_id);
                    if($post_id){
                        $ret = self::get_postmeta($post_id,$meta_key,$string);
                    }
                }
            }
        }
        return $ret;
    }
     /**
     * Metodo para salvar ou atualizar os meta users
     */
    static function update_usermeta($user_id,$meta_key=null,$meta_value=null)
    {
        $ret = false;
        $tab = 'usermeta';
        if($user_id&&$meta_key&&$meta_value){
            $verf = Qlib::totalReg($tab,"WHERE user_id='$user_id' AND meta_key='$meta_key'");
            if($verf){
                $ret=DB::table($tab)->where('user_id',$user_id)->where('meta_key',$meta_key)->update([
                    'meta_value'=>$meta_value,
                    'updated_at'=>self::dataBanco(),
                ]);
            }else{
                $ret=DB::table($tab)->insert([
                    'user_id'=>$user_id,
                    'meta_value'=>$meta_value,
                    'meta_key'=>$meta_key,
                    'created_at'=>self::dataBanco(),
                ]);
            }
            //$ret = DB::table($tab)->storeOrUpdate();
        }
        return $ret;
    }
    /**
     * Metodo para pegar os meta users
     */
    static function get_usermeta($user_id,$meta_key=null,$string=null)
    {
        $ret = false;
        $tab = 'usermeta';
        if($user_id){
            if($meta_key){
                $d = DB::table($tab)->where('user_id',$user_id)->where('meta_key',$meta_key)->get();
                if($d->count()){
                    if($string){
                        $ret = $d[0]->meta_value;
                    }else{
                        $ret = [$d[0]->meta_value];
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * remove meta datao
     */
    static function delete_usermeta($user_id,$meta_key=null)
    {
        return  DB::table('usermeta')->where('user_id',$user_id)->where('meta_key',$meta_key)->delete();
    }
    static function explodeReturnPrimeiro($separador,$string){
        $dados = explode($separador,$string);
        if(is_array($dados)){
            return $dados[0];
        }
    }
    static function select_text_em_html($string,$start,$end){
        $ret = false;
        $dados = explode($start,$string);
        if(is_array($dados)){
            foreach($dados As $key=>$valor){
                if($key>0)
                    $ret[$key] = self::explodeReturnPrimeiro($end,$valor);
                //$str2 = substr(substr($string, stripos($string, $start)), strlen($start));
                //$b = stripos($str2, $end);

            }
        }
        return $ret;
        //return trim(substr($str2, 0, $b));
    }
    static function shortCode_html($string){
        $arr_texto = self::select_text_em_html($string,'*|','|*');
        if(is_array($arr_texto)){
            foreach($arr_texto As $key=>$val){
                $string = str_replace('*|'.$val.'|*',(new PostsController)->short_code($val),$string);
            }
        }
        return $string;
    }
    /**
     * Retorna um link da logo1 do site
     */
    static function get_link_logo($val=0){
        $tenant = tenant();
        $ret = false;
        if($tenant){
            $logo = _upload::select('id', 'nome as name','pasta as link' , 'ordem as ordenar', 'config')->where('token_produto','=',$tenant['id'])->orderBy('ordem','asc')->get();
            if(isset($logo[$val]['link']) && !empty($logo[$val]['link'])){
                $ret = tenant_asset($logo[$val]['link']);
            }
        }
        return $ret;
    }
    /**
     * retorna um array de todas as midias gravadas no cadastro da empresa
     */
    static function get_midias_site($val=null){
        $tenant = tenant();
        $ret = false;
        if($tenant){
            $midias = _upload::select('id', 'nome as name','pasta as link' , 'ordem as ordenar', 'config')->where('token_produto','=',$tenant['id'])->orderBy('ordem','asc')->get();
            if($val!=null){
                if(isset($midias[$val]['link']) && !empty($midias[$val]['link'])){
                    $ret = tenant_asset($midias[$val]['link']);
                }
            }else{
                if($midias->count()>0){
                    $midias = $midias->toArray();
                    foreach ($midias as $k => $value) {
                        $ret[$k] = tenant_asset($value['link']);
                    }
                }
            }
        }
        return $ret;
    }
    /**
     * retornar listas de anos de determinada consulta por padrao traz anos das licitações
     * @param string $tab='biddings',$campo='YEAR(`data_exec`)',$order='ORDER BY data_exec ASC'
     */
    static function sql_distinct($tab='biddings',$campo='year',$valor='vl',$order='ORDER BY year ASC',$debug=false){
        $sql = "SELECT DISTINCT $campo As $valor  FROM $tab $order";
        if($debug){
            echo $sql;
        }
        $ret = DB::select($sql);
        return $ret;
    }
    /**
     * Metodo padrão para gravar e atualizar qualquer tabela
     * @param string $tab nome da tabela para ser cadastrado os dados
     * @param array $dados array contendo os nomes de campos com seus respectivos valores..
     * @param bool $edit opções false| true controla a Edição ou não edição de um registro encontrado, caso     encontre um registro similar a opão false somente informa que o registro foi encontrado true pode alterar
     */
    static function update_tab($tab='',$dados=[],$where='',$edit=true){
        // $dados = [
        //     'Nome' => 'Maria',
        //     'Email' => 'maria@example.com',
        //     'token' => uniqid(),
        //     'senha' => bcrypt('senha_secreta')
        // ];
        //veriricar se ja existe
        $ret['exec'] = false;
        $ret['mens'] = 'Erro ao salvar';
        $ret['color'] = 'danger';
        try {
            if(is_array($dados)){
                if(!empty($where)){
                    $d = DB::select("SELECT id FROM $tab $where");
                    $id = isset($d[0]->id) ? $d[0]->id : null;
                    if($id){
                        if($edit){
                            $salva = DB::table($tab)->where('id', $id)->update($dados);
                            if($salva){
                                $ret['exec'] = true;
                                $ret['idCad'] = $id;
                                $ret['dados'] = $dados;
                                $ret['color'] = 'success';
                                $ret['mens'] = 'Registro atualizado com sucesso!';
                            }else{
                                $ret['exec'] = true;
                                $ret['idCad'] = $id;
                                $ret['dados'] = $dados;
                                $ret['color'] = 'success';
                                $ret['mens'] = 'Registro sem necessidade de atualização!';
                            }
                        }else{
                            $ret['exec'] = false;
                            $ret['idCad'] = $id;
                            $ret['dados'] = $dados;
                            $ret['color'] = 'warning';
                            $ret['mens'] = 'Registro encotrado!';
                        }
                    }else{
                        $id = DB::table($tab)->insertGetId($dados);
                        if($id){
                            $ret['exec'] = true;
                            $ret['idCad'] = $id;
                            $ret['dados'] = $dados;
                            $ret['color'] = 'success';
                            $ret['mens'] = 'Registro criado com sucesso!';
                        }
                    }
                }else{
                    $id = DB::table($tab)->insertGetId($dados);
                    if($id){
                        $ret['exec'] = true;
                        $ret['idCad'] = $id;
                        $ret['dados'] = $dados;
                        $ret['color'] = 'success';
                        $ret['mens'] = 'Registro criado com sucesso!';
                    }
                }
            }else{
                $ret['exec'] = false;
                // $ret['idCad'] = $id;
                $ret['dados'] = $dados;
                $ret['color'] = 'danger';
                $ret['mens'] = 'A variavel de dados não é array válido!';
            }
        } catch (\Throwable $th) {
            $ret['exec'] = false;
            // $ret['idCad'] = $id;
            $ret['error'] = $th->getMessage();
            $ret['mens'] = 'Erro ao cadastrar registro!';
            $ret['color'] = 'danger';
            //throw $th;
        }
        return $ret;
    }
    /**
     * Metodo para atualzar ou adicionar qualquer campo json se uma tabela
     * @param $tab tabela
     * @param $campo_bus campo de busca da tabela
     * @param $valor_bus valor de busca na tabela
     * @param $f_tab campo da tabela
     * @param $f_json campo da string json
     * @param $value campo do valor
     */
    static function update_json_fields($tab,$campo_bus,$valor_bus,$f_tab,$f_json,$value=false){
        $campo_json = self::buscaValorDb0($tab,$campo_bus,$valor_bus,$f_tab);
        $ret = false;
        // dump($campo_json, $tab,$campos_bus,$valor_bus,$f_tab,$f_json,$value);
        if($campo_json){
            if(Qlib::isJson($campo_json)){
                $arr = Qlib::lib_json_array($campo_json);
                //atualizar o valor no array
                $arr[$f_json] = $value;
                $dsalv = [
                    $f_tab=>Qlib::lib_array_json($arr),
                ];
                $where = "WHERE $campo_bus='$valor_bus'";
                $ret = self::update_tab($tab,$dsalv,$where,true);

            }
        }else{
            $arr[$f_json] = $value;
            $dsalv = [
                $f_tab=>Qlib::lib_array_json($arr),
            ];
            $where = "WHERE $campo_bus='$valor_bus'";
            $ret = self::update_tab($tab,$dsalv,$where,true);
        }
        return $ret;
    }
    /**
     * Retorna a permissão do usuario logado
     */
    static function get_permission($user_logado=null){
        $ret = null;
        if(!$user_logado){
            $user_logado = Auth::user();
        }
        if(isset($user_logado['id_permission'])){
            $ret = $user_logado['id_permission'];
        }
        return $ret;
    }
    /**
     * Verifica se o usuario logado é um parceiro ou não
     */
    static function is_partner(){
        $id_permission = self::get_permission();
        $partner_permission_id = Qlib::qoption('partner_permission_id');
        if($id_permission==$partner_permission_id){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Verifica se um parceiro está ativo
     */
    static function is_partner_active(){
        $partner_permission_id = Qlib::qoption('partner_permission_id');
        $active = self::buscaValorDb0('permissions','id',$partner_permission_id,'name'," AND active='s'");
        if($active){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Atualiza um campo json em uma determinada tabela
     * @param string $tab = Nome da tabela
     * @param string $campo_bus = Campos de referencia para ser buscado do Ex: Id
     * @param string $valor_bus = Valor de referencia para ser buscado do Ex: Id = 41
     * @param string $campo_enc = Campo como valores json, para ser encontrado os dados do Ex: config
     * @param array $data = dados a serem editados ou adicionados ao valor do campo config
     * @param bool $insert_new = condição para inserir valor ao array salvo ou não true para sim e false para não
     */
    static function json_update_tab($tab,$campo_bus,$valor_bus,$campo_enc,$data=[],$insert_new=true){
        $json = self::buscaValorDb0($tab,$campo_bus,$valor_bus,$campo_enc);
        $ret['exec'] = false;
        if(is_string($json)){
            $arr_json = json_decode($json,true);
            if(is_array($arr_json)){
                foreach ($data as $k1 => $v1) {
                    if(is_array($v1)){
                        foreach ($v1 as $k2 => $v2) {
                            # code...
                        }
                    }else{
                        if(isset($arr_json[$k1])){
                            $arr_json[$k1] = $v1;
                        }else{
                            if($insert_new){
                                //adiciona novos valores
                                $arr_json[$k1] = $v1;
                            }
                        }
                    }
                }
                $ret['data'] = $arr_json;
                $up = self::update_tab($tab,[$campo_enc=>Qlib::lib_array_json($arr_json)],"WHERE $campo_bus=$valor_bus");
                if(self::isAdmin(1))
                $ret['update'] = $up;
                if($up['exec']){
                    $ret['exec'] = $up['exec'];
                    $ret['mens'] = isset($up['mens']) ? $up['mens'] : false;
                }
            }
        }
        return $ret;
    }
    /**
     * Gera Array modificado baseando em campo json em uma determinada tabela
     * @param string $tab = Nome da tabela
     * @param string $campo_bus = Campos de referencia para ser buscado do Ex: Id
     * @param string $valor_bus = Valor de referencia para ser buscado do Ex: Id = 41
     * @param string $campo_enc = Campo como valores json, para ser encontrado os dados do Ex: config
     * @param array $data = dados a serem editados ou adicionados ao valor do campo config
     * @param bool $insert_new = condição para inserir valor ao array salvo ou não true para sim e false para não
     */
    static function json_generate_tab($tab,$campo_bus,$valor_bus,$campo_enc,$data=[],$insert_new=true){
        $json = self::buscaValorDb0($tab,$campo_bus,$valor_bus,$campo_enc);
        $ret['exec'] = false;
        if(is_string($json)){
            $arr_json = json_decode($json,true);
            if(is_array($arr_json)){
                foreach ($data as $k1 => $v1) {
                    if(is_array($v1)){
                        foreach ($v1 as $k2 => $v2) {
                            # code...
                        }
                    }else{
                        if(isset($arr_json[$k1])){
                            $arr_json[$k1] = $v1;
                        }else{
                            if($insert_new){
                                //adiciona novos valores
                                $arr_json[$k1] = $v1;
                            }
                        }
                    }
                }
                // $ret['data'] = $arr_json;
                $ret = $arr_json;
                // $up = self::update_tab($tab,[$campo_enc=>Qlib::lib_array_json($arr_json)],"WHERE $campo_bus=$valor_bus");
                // if(self::isAdmin(1))
                //     $ret['update'] = $up;
                // if($up['exec']){
                //     $ret['exec'] = $up['exec'];
                //     $ret['mens'] = isset($up['mens']) ? $up['mens'] : false;
                // }
            }
        }
        return $ret;
    }
}
