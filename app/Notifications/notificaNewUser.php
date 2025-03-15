<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
class notificaNewUser extends Notification implements ShouldQueue
{
    use Queueable;
    private $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $arn = explode(' ', $this->user->name);
        if(!isset($arn[0])){
            return false;
        }
        return (new MailMessage)
            ->subject('Bem vindo')
            ->greeting('Olá '. $arn[0])
            ->line('Seja bem vindo ao leilão do '.config('app.name').'')
            ->action('Acesso ao sistema', url('/leiloes-publicos'))
            ->line('Obrigado por usar o nosso sistema!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message'=>'Obrigado pelo seu cadastro!!',
            $this->user,
        ];
    }
}
