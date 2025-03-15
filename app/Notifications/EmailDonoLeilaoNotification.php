<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailDonoLeilaoNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $config;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $message=$this->config['mensagem'];
            return (new MailMessage)
            ->line($message)
            ->action('Ver leilão', @$this->config['link_leilao'])
            ->greeting(@$this->config['greeting'])
            ->subject('Leilão finalizado')
            // ->action('Ver leilão', url('/'))
            ->line(__('Obrigado por usar nossa plataforma').'!');

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
            //
        ];
    }
}
