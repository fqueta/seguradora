<?php

namespace App\Listeners;

use App\Events\LanceLeilaoEvent;
use App\Http\Controllers\LeilaoController;
use App\Models\User;
use App\Notifications\NotificaLance;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnviarEmailSeguidores implements ShouldQueue
{
    public function __construct()
    {
        //
    }
    public function handle(LanceLeilaoEvent $lance)
    {
        $ret = false;
        if(isset($lance->lance['type']) && $lance->lance['type'] == 'lance' && isset($lance->lance['leilao_id']) && ($leilao_id=$lance->lance['leilao_id'])){
            $lco = new LeilaoController;
            $lance_id = isset($lance->lance['id']) ? $lance->lance['id'] : false;
            $seguidores = $lco->get_seguidores($leilao_id);
            $dl = $lco->get_leilao($leilao_id);
            if(is_array($seguidores) && $dl){
                foreach ($seguidores as $ks => $v) {
                    //enviar um email de notificação
                    if($ks && $ks != $lance->lance['author']){
                        $ret[$ks] = $lco->enviar_email([
                            'type' => 'notific_lance_seguidor',
                            'leilao_id' => $leilao_id,
                            'lance_id' => $lance_id,
                            'dados_lance' => $lance->lance,
                            'dados_leilao' => $dl,
                            'subject' => 'Lance em leilao',
                            'd_user' => $v,
                            // 'mensagem' => $mensagem,
                        ]);
                        $user = User::find($ks);
                        if($user!=null){
                            $lance->lance['link'] = $lco->get_link_front($leilao_id);
                            $lance->lance['nome_leilao'] = $lco->nome_leilao($leilao_id);
                            $user->notify(new NotificaLance($user,$lance->lance));
                        }
                    }
                }
            }
            //return $lc->marca_lance_superado($leilao_id);
        }
        // dd($ret);
        return $ret;
    }
}
