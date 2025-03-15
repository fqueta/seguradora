<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnvioDeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        // return $this->view('mail.dataBrasil')
        //             ->with([
        //                 'nome' => $this->data['nome'],
        //                 'to' => $this->data['to'],
        //                 'subject' => $this->data['subject'],
        //                 'message' => $this->data['message'],
        //             ]);
        //             $this->subject('Novo teste');
        //             $this->to($this->user->email,$this->user->name);
        $this->subject($this->data['subject']);
        $this->to($this->data['email'],$this->data['name']);
        return $this->markdown('mail.dataBrasil',[
            'data'=>$this->data,
        ]);
    }
}
