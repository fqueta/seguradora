<?php

namespace App\Mail\leilao;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use stdClass;

class lancesNotific extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $user;

    public function __construct(stdClass $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
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
