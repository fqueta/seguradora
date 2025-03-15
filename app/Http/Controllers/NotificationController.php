<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Metodo para receber requisições Ajax das notificações
     */
    public function receive_ajax(Request $request){
        $d = $request->all();
        $ret = false;
        $ac = isset($d['ac']) ? $d['ac'] : false;
        if($ac=='markAsRead'){
            $ret = $this->markAsRead($request);
        }
        return $ret;
    }
    /**
     * Metodo para macar notificação como lida
     */
    public function markAsRead(Request $request){
        $d = $request->all();
        $ret['exec'] = false;
        $ret['total'] = false;
        $id=isset($d['id'])?$d['id']:false;
        if($id){
            $Notification = Auth::user()->Notifications->find($id);
            if($Notification){
                $Notification->markAsRead();
                $ret['exec'] = true;
                $ret['total'] = $this->total_unread();
            }
        }
        return $ret;
    }
    /**
     * Retorna o total de notificações não lidas;
     */
    public function total_unread(){
        return Auth::user()->unreadNotifications()->count();
    }
}
