<?php

namespace App\Mail\leilao;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use stdClass;

class lancesNotificSeguidores extends Mailable
{
    use Queueable, SerializesModels;
    private $user;

    public function __construct(stdClass $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $this->subject($this->user->subject);
        $this->to($this->user->email,$this->user->name);
        $mensagem = isset($this->user->mensagem)?$this->user->mensagem:false;
        $type = isset($this->user->type)?$this->user->type:false;
        return $this->markdown('mail.leilao.notification',[
            'user'=>$this->user,
            'mensagem'=>$mensagem,
            'type'=>$type,
        ]);
    }
}
