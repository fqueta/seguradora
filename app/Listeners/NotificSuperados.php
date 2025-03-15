<?php

namespace App\Listeners;

use App\Events\LanceLeilaoEvent;
use App\Http\Controllers\LanceController;
use App\Http\Controllers\LeilaoController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotificSuperados implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(LanceLeilaoEvent $lance)
    {
        $ret = false;
        if(isset($lance->lance['type']) && $lance->lance['type'] == 'lance' && isset($lance->lance['leilao_id']) && ($leilao_id=$lance->lance['leilao_id'])){
            // $lco = new LeilaoController;
            $lc = new LanceController;
            // $lance_id = isset($lance->lance['id']) ? $lance->lance['id'] : false;
            //Marca lance anterior como superado e enviar um email o dono nolance anterior
            //Criar uma notificação no painel do usuario.
            $ret = $lc->marca_lance_superado($leilao_id);
        }
        return $ret;
    }
}
