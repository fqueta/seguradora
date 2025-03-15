<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SendBalcklistNotification;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class BlacklistController extends Controller
{
    public $campo;
    public $campo_motivo;
    // public $uc;
    public function __construct()
    {
        $this->campo = 'backlist';
        $this->campo_motivo = 'motivo_backlist';
        // $this->uc = new UserController;  //user controller
    }
    /**
     * Adiciona usuario ao blacklist
     */
    public function add($user_id,$motivo=false){
        $ret['exec'] = Qlib::update_usermeta($user_id,$this->campo,'s');
        $ret['save_motivo'] = false;
        if($motivo){
            if(is_array($motivo)){
                $ret['save_motivo'] = Qlib::update_usermeta($user_id,$this->campo_motivo,Qlib::lib_array_json($motivo));
            }
        }
        return $ret;
    }
    /**
     * Remove usuario ao blacklist
     */
    public function remove($user_id){
        $ret['exec'] = Qlib::update_usermeta($user_id,$this->campo,'n');
        // $ret['save_motivo'] = false;
        // if($motivo){
        //     if(is_array($motivo)){
        //         $ret['save_motivo'] = Qlib::update_usermeta($user_id,$this->campo_motivo,Qlib::lib_array_json($motivo));
        //     }
        // }
        return $ret;
    }
    /**
     * Metodo para verificar se um usuario está no blacklist
     */
    public function is_blacklist($user_id){
        $rs = Qlib::get_usermeta($user_id,$this->campo,true);
        if($rs=='s'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Metodo para retornar um array do blacklist completo
     * @return array
     */
    public function get_blacklist(){
        $d = User::select('users.*','usermeta.meta_value')->
        join('usermeta','usermeta.user_id','=','users.id')->
        where('usermeta.meta_value','!=','')->
        where('usermeta.meta_value','=','s')->get();
        if($d->count() > 0){
            $d = $d->toArray();
            // foreach ($d as $k => $v) {
            //     $motivo = Qlib::get_usermeta($v['id'],$this->campo_motivo,true);
            //     if($motivo){
            //         $motivo = Qlib::lib_json_array($motivo);
            //     }
            //     $d[$k][$this->campo_motivo] = $motivo;

            // }
        }
        return $d;
    }
    /**
     * Metodo para enviar todos os não pagos para o blacklist
     */
    public function send_to_blacklist($send=true){
        $ret = false;
        $lc = new LeilaoController;
        //pega todos os finalizados
        $prazo = 5;
        $vencimento = Qlib::CalcularDiasAnteriores(date('d/m/Y'),$prazo);
        $ret['vencimento'] = $vencimento;
        $venstr = strtotime(Qlib::dtBanco($vencimento));
        $ret['vencimento_str'] = $venstr;

        // $ret['vencimento_str'] = strtot $vencimento;
        // dd($ret);
        $df = $lc->get_all_finalized();
        if(@$df['data']){
            foreach ($df['data'] as $kf => $vf) {
                //Verifica se está pago
                if(!$lc->is_paid($vf['ID'])){
                    //Verifica quem é o ganhador
                    $dg = $lc->who_won($vf['ID']);
                    if(isset($dg['ultimo_lance']['author']) && ($user_id = $dg['ultimo_lance']['author'])){
                        //verificar se ja venceu o pagamento do leilão
                        // dd($vf);
                        $dataTerminio = isset($vf['config']['termino']) ? $vf['config']['termino'] : date('Y-m-d');
                        $termino_str = strtotime($dataTerminio);
                        $ret['leilao'][$vf['ID']]['user_id'] = $user_id;
                        $ret['leilao'][$vf['ID']]['d'] = $vf;
                        $ret['leilao'][$vf['ID']]['terminio'] = $dataTerminio;
                        // $ret['leilao'][$vf['ID']]['user_id'] = $user_id;
                        $ret['leilao'][$vf['ID']]['terminio_str'] = $termino_str;
                        //se termino for menor que vencimento
                        if ($termino_str <= $venstr){
                            $add['exec'] = false;
                            if($send){
                                $leilao_name = $vf['post_title'];
                                //verifica se ja está na blacklist
                                $is_blacklist = Qlib::get_usermeta($user_id,$this->campo,true);
                                if($is_blacklist!='s'){
                                    $motivo = [
                                        'description' =>'Seu cadastro em nossa plataforma foi suspenso devido ao não pagamento do leilão <b>'.$leilao_name.'</b> com o vencimento para '.$vencimento,
                                        'termino' =>$dataTerminio,
                                        'leilao_id' =>$vf['ID'],
                                    ];
                                    $add = $this->add($user_id,$motivo);
                                    $ret['leilao'][$vf['ID']]['add'] = $add;
                                    if($add['exec']){
                                        $user = User::find($user_id);
                                        if($user->count() > 0){
                                            //notiicar o usuario
                                            $n = explode(' ',$user['name']);
                                            $user->notify(new SendBalcklistNotification([
                                                'mensage' =>$motivo['description'],
                                                'greeting' =>'Olá '.@$n[0],
                                                'subject' =>'Usuário supenso',
                                                'link_leilao' =>$lc->get_link_front($vf['ID']),
                                            ]));
                                        }
                                    }
                                }
                            }else{
                                $add = $this->remove($user_id);
                                $ret['leilao'][$vf['ID']]['remove'] = $add;
                            }
                        }
                    }
                }
            }
        }
        //verifica quem não pagou ainda
        return $ret;
    }
}
