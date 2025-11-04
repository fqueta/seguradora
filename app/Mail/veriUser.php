<?php

namespace App\Mail;

use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class veriUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public function __construct($user)
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
        $this->subject('Confirmar seu cadastro');
        $this->to($this->user['email'],$this->user['name']);
        return $this->markdown('mail.veriUser',[
            'user'=>$this->user,
            'empresa'=>Qlib::qoption('empresa'),
            'link_confirma_email'=>route('internautas.acao.get',[
                'tipo'=>'veriuser',
                'id'=>base64_encode($this->user['email'])
            ]),
        ]);
    }
}
