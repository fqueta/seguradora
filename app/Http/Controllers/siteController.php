<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\menu_site;
use App\Models\User;

class siteController extends Controller
{
    public $sec1;
    public $sec2;
    function __construct()
    {
        $seg1 = request()->segment(1);
        $seg2 = request()->segment(2);
        $this->sec1 = $seg1;
        $this->sec2 = $seg2;
    }
    public function index(Request $request)
    {
        $slug1 = $this->sec1 ? $this->sec1 : 'home';
        $slug2 = $this->sec2 ? $this->sec2 : false;
        $ret['exec']=false;
        $ret['dados']=false;
        global $post,$menus,$notification,$ganhador,$user;
        $user = false;
        if(Auth::check()){
            $user_id = Auth::id();
            $user = User::find($user_id);
            $ganhador = (new LeilaoController())->lista_leilao_terminado(Auth::id(),'n');
            // dd($ganhador);
            $notification['all'] = $user->notifications;
            $notification['unread'] = $user->unreadNotifications;
            $notification['total'] = $notification['unread']->count();
            //Verifica se está no blacklist
            if((new BlacklistController)->is_blacklist($user_id)){
                //Suspender acesso
                return view('site.suspense_blackList',$ret);
            }
        }
        if($slug1){
            if($slug1 == 'home' || $slug1 == null){
                $url = url('/').'/'.Qlib::get_slug_post_by_id(4);
                // $url = 'https://aeroclubejf.com.br';
                return redirect()->to($url);
            }
            $ds = Post::where('post_name', $slug1)->where('post_status', 'publish')->get();
            if(Auth::check()){
                $menus = menu_site::where('actived','1')->get()->toArray();
            }else{
                $menus = menu_site::where('permission','=','public')->where('actived','1')->get()->toArray();
            }

            if(count($menus) > 0){
                //Pegando a url da página
                foreach ($menus as $km => $vm) {
                    $url = Qlib::get_slug_post_by_id($vm['page_id']);
                    if($url){
                        $menus[$km]['url'] = $url;
                    }
                }
                if(Qlib::is_repasses()){
                    if(isset($menus[0]['url'])){
                        $menus[0]['url'] = 'https://aeroclubejf.com.br';
                    }
                }
            }
            if($ds->count()){

                $dados=$ds[0];
                $post = $dados;

                if(isset($dados['config']['permission']) && $arr_perm = $dados['config']['permission']){
                    if(is_array($dados['config']['permission'])){
                        if(Auth::check()){
                            $user = Auth::user();
                            if(!in_array($user->id_permission, $dados['config']['permission'])){
                                return view('erro404',$ret);
                            }
                        }else{
                            return view('erro404',$ret);
                        }
                    }
                }
                $ret['exec']=true;
                if($slug2){
                    $dados['slug2']=$slug2;
                }
                $ret['dados']=$dados;
                return view('site.index',$ret);
            }else{
                return view('erro404',$ret);
            }
        }else{
            return view('erro404',$ret);
        }

    }
    public function get_main_post($post_id=false){
        $ret = false;
        if($post_id){
            $dados = Post::Find($post_id);
            if(isset($dados['post_content'])){
                $ret = $dados['post_content'];
                $lc = new LeilaoController;
                $lac = new LanceController;
                $uc = new UserController;
                $pa = new PaymentController;
                $mensagem_agradecimento = '<p>Obrigado pela sua compra <b>{nome}</b></p>';
                if($this->sec1==Qlib::get_slug_post_by_id(5)){
                    //obrigado-pela-compra
                    $arr_shortC['agradecimento'] = $pa->agradecimento($mensagem_agradecimento);
                }elseif($this->sec1=='seguindo'){
                    //leiloes-publicos
                    $arr_shortC['leilao_list_seguindo'] = $lc->leilao_list_seguindo($post_id,$dados);
                }elseif($this->sec1==Qlib::get_slug_post_by_id(37)){
                    //leiloes-publicos
                    $arr_shortC['leiloes_publicos'] = $lc->leiloes_publicos($post_id,$dados);
                }elseif($this->sec1=='payment'){
                    //payment
                    $arr_shortC['payment'] = $pa->form($post_id,$dados);
                }elseif($this->sec1==Qlib::get_slug_post_by_id(12)){
                    //leiloes-list
                    $arr_shortC['list_leilao'] = $lc->list_leilao($post_id,$dados);
                }elseif($this->sec1==Qlib::get_slug_post_by_id(4)){
                    //orçamento
                    $arr_shortC['form_orcamento'] = $this->form_orcamento($post_id,$dados);
                }elseif($this->sec1==Qlib::get_slug_post_by_id(18)){
                    //contato
                    $arr_shortC['form_contato'] = $this->form_contato($post_id,$dados);
                }else{
                    //user
                    $arr_shortC = [
                        'form_leilao' => $lc->form_leilao($post_id,$dados),
                        'list_lances' => $lac->list_lances($post_id,$dados),
                        'list_lance_user' => $lac->list_lance_user(), //Lista os lances do usuario no frontend
                        // 'leiloes_publicos' => $lc->leiloes_publicos($post_id,$dados),
                        'form_meu_cadastro' => $uc->form_meu_cadastro($post_id,$dados),
                        'ger_user' => $uc->ger_user($post_id,$dados), //Metodo para gerenciar usuarios no site
                        'teste' => 'teste de conteudo do formulario para gadastr',
                    ];
                }

                $arr_short = [];
                $arr_k = explode('[sc ac="',$ret);
                if(isset($arr_k[1]) && !empty($arr_k[1])){
                    $arr_k1 = explode('"]',$arr_k[1]);
                    if(isset($arr_k1[0]) && !empty($arr_k1[0])){
                        $k = $arr_k1[0];
                        $arr_short[$k] = $arr_shortC[$k];
                    }
                }
                $ret = Qlib::short_code_global($ret,'sc',$arr_short);
            }
        }
        return $ret;
    }
    /**
     * Montar o formulario de contato
     */
    public function form_contato($post_id,$dados=false){
        // $campos =
        return view('site.form_contato',['id'=>$post_id, 'dados'=>$dados]);
    }
    /**
     * Montar o formulario de orçamento
     */
    public function form_orcamento($post_id,$dados=false){
        // $campos =
        $arr_ddi = (new UserController)->get_ddi();
        $dados['arr_ddi'] = $arr_ddi;
        return view('site.form_orcamento',['id'=>$post_id, 'dados'=>$dados]);
    }
}
