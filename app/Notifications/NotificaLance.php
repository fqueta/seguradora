<?php

namespace App\Notifications;

use App\Models\lance;
use App\Models\User;
use App\Qlib\Qlib;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificaLance extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $lance;
    public function __construct(User $user,$lance)
    {
        $this->user = $user;
        $this->lance = $lance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return false;
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $msg = Qlib::qoption('notific_lance_seguidor');
        $msg = str_replace('{nome_leilao}',$this->lance->nome_leilao,$msg);
        $msg = str_replace('{link_leilao}',$this->lance->link_leilao,$msg);

        return [
            'message' => $msg,
            'dlance'=>$this->lance
        ];
    }
}
