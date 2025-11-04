<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\_upload;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ConfigController extends Controller
{
    // public $sec;
    public $routa;
    public $view;
    public $label;
    public $tab;
    public function __construct($config=[])
    {
        $this->middleware('auth');
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $routeName = isset($config['route']) ? $config['route'] : false;
        $routeName = $routeName ? $routeName : $seg2;
        // $routeName = $routeName ? $routeName : '';
        $this->routa = $routeName;
        if($this->routa == 'enterprise'){
            $this->label = 'Dados da prefeitura';
            $this->tab = 'tenants';
        }else{
            $this->label = 'Sem titulo';
        }
        $this->view = 'admin.config';
    }

    /**
     * Metodo para mudar os status de todas as postagens
     */

    public function chage_status(Request $request){
        $d = $request->all();
        $id = isset($d['id']) ? $d['id'] : false;
        $status = isset($d['status']) ? $d['status'] : false;
        $tab = isset($d['tab']) ? $d['tab'] : false;
        $campo = isset($d['campo']) ? $d['campo'] : 'ativo';
        $ret['exec'] = false;
        // $ret['d'] = $d;
        $ret['mens'] = 'Erro ao atualizar!';
        $ret['color']='danger';
        if($id && $tab && $status){
            $dsalv = false;
            if($tab=='posts'){
                $arr_status = ['true'=>'publish','false'=>'pending'];//(new PostsController)->campos()['post_status']['arr_opc'];
                $ret['status'] = $arr_status[$status];
                $dsalv = ['post_status' => $arr_status[$status]];
            }elseif($tab=='biddings' || $tab=='permissions'){
                $arr_status = ['true'=>'s','false'=>'n'];//(new PostsController)->campos()['post_status']['arr_opc'];
                $ret['status'] = $arr_status[$status];
                $dsalv = ['active' => $arr_status[$status]];
            }else{
                $arr_status = ['true'=>'s','false'=>'n'];//(new PostsController)->campos()['post_status']['arr_opc'];
                $ret['status'] = $arr_status[$status];
                $dsalv = [$campo => $arr_status[$status]];
            }
            if(isset($arr_status[$status]) && $dsalv){
                $ret['salv'] = DB::table($tab)->where('id',$id)->update($dsalv);
                if($ret['salv']){
                    $ret['exec'] = $ret['salv'];
                    $ret['mens'] = 'Atualizado com sucesso!';
                    $ret['color'] ='success';
                }
                // dump($ret);
                // dd($dsalv);
            }
        }
        return $ret;
    }
    /**
     * Metudo para gerencia paniel de configuração
     */
    public function index(){
        $this->authorize('ler', $this->routa);
        $ret = [];
        // $user =  Auth::user();
        $tenant = tenant();
        // dump($tenant);
        $campos = $this->campos($tenant);
        $title = __('Cadastro de ').$this->label;
        $titulo = $title;
        if($this->routa=='enterprise'){
            $listFiles = false;
            $campos = $this->campos();
            $tenant['token'] = $tenant['id'];
            if(isset($tenant['token'])){
                $listFiles = _upload::where('token_produto','=',$tenant['token'])->get();
                if(count($listFiles) > 0){
                    $listFiles = $listFiles->toArray();
                }

            }
            $config = [
                'ac'=>'alt',
                'frm_id'=>'frm-enterprise',
                'route'=>$this->routa,
                'id'=>@$tenant['id'],
                'arquivos'=>'png,jpeg,jpg,JPG,JPEG',
                'tam_col1'=>'col-md-8',
                'tam_col2'=>'col-md-4',
            ];
            $ret = [
                'value'=>$tenant,
                'config'=>$config,
                'title'=>$title,
                'titulo'=>$titulo,
                'campos'=>$campos,
                'exec'=>true,
                'listFiles'=>$listFiles,
                'listFilesCode'=>Qlib::encodeArray($listFiles),
            ];
        }else{
            $ret = [
                'dados'=>$tenant,
                'campos'=>$campos,
                'title'=>$title,
                'titulo'=>$titulo,
            ];
        }
        return view($this->view.'.index',$ret);
    }
    public function update(Request $request, $id)
    {
        $this->authorize('update', $this->routa);
        $tenant = tenant();
        $campos = $this->campos($tenant);
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
        $data['ativo'] = isset($data['ativo'])?$data['ativo']:'n';
        $data['autor'] = $userLogadon;
        if(isset($dados['config'])){
            $dados['config'] = Qlib::lib_array_json($dados['config']);
        }
        $atualizar=false;
        unset($data['type'],$data['ativo'],$data['autor']);
        if(!empty($data)){
            $atualizar = $tenant->update($data);
            $route = $this->routa.'.index';
            $d_ordem = isset($data['ordem'])?$data['ordem']:false;
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
    public function campos($config=[]){
        $ret = array();
        if($this->routa=='enterprise'){
            // dd($tenant);
            $ret = [
                'id'=>['label'=>'Id','active'=>true,'js'=>true,'type'=>'hidden','value'=>@$config['id'],'exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'type'=>['label'=>'type','active'=>false,'value'=>$this->routa,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'pai'=>['label'=>'pai','active'=>true,'js'=>true,'value'=>$id_pai,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                'name'=>['label'=>'Nome','active'=>true,'js'=>true,'placeholder'=>'Ex.: Nome da prefeitura','type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'12','validate'=>['required','string']],
                'config[cep]'=>['cp_busca'=>'config][cep','label'=>'CEP','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'mask-cep onchange=buscaCep1_0(this.value)','tam'=>'3','placeholder'=>''],
                'config[endereco]'=>['cp_busca'=>'config][endereco','label'=>'Endereço','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'endereco=cep q-inp="endereco"','tam'=>'7','placeholder'=>''],
                'config[numero]'=>['cp_busca'=>'config][numero','label'=>'Número','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'numero=cep','tam'=>'2','placeholder'=>''],
                'config[complemento]'=>['cp_busca'=>'config][complemento','label'=>'Complemento','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'','tam'=>'3','placeholder'=>''],
                'config[bairro]'=>['cp_busca'=>'config][bairro','label'=>'Bairro','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'bairro=cep q-inp="bairro"','tam'=>'3','placeholder'=>''],
                'config[cidade]'=>['cp_busca'=>'config][cidade','label'=>'Cidade','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'cidade=cep q-inp="cidade"','tam'=>'4','placeholder'=>''],
                'config[uf]'=>['cp_busca'=>'config][uf','label'=>'UF','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'uf=cep q-inp="uf"','tam'=>'2','placeholder'=>''],
                'config[telefone]'=>['cp_busca'=>'config][telefone','label'=>'Telefone','active'=>true,'type'=>'text','exibe_busca'=>'d-block','event'=>'onblur=mask(this,clientes_mascaraTelefone); onkeypress=mask(this,clientes_mascaraTelefone);','tam'=>'3','placeholder'=>''],
                'config[description]'=>['cp_busca'=>'config][description','label'=>'Descrição','active'=>true,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12','placeholder'=>'','class'=>'summernote'],
                // 'token'=>['label'=>'token','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'autor'=>['label'=>'autor','active'=>true,'js'=>true,'type'=>'hidden','exibe_busca'=>'d-block','event'=>'','tam'=>'2'],
                // 'obs'=>['label'=>'Descrição','active'=>false,'js'=>true,'type'=>'textarea','exibe_busca'=>'d-block','event'=>'','tam'=>'12'],
                'ativo'=>['label'=>'Ativado','tab'=>$this->tab,'active'=>true,'js'=>true,'type'=>'chave_checkbox','value'=>'s','valor_padrao'=>'s','exibe_busca'=>'d-block','event'=>'','tam'=>'12','arr_opc'=>['s'=>'Sim','n'=>'Não']],
            ];
        }
        return $ret;
    }
}
