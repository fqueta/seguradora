<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendBalcklistNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $config;
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
        $mensage = $this->config['mensage'];
        return (new MailMessage)
            ->line($mensage)
            ->action('Ver leilão', @$this->config['link_leilao'])
            ->greeting(@$this->config['greeting'])
            ->subject(@$this->config['subject'])
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

        $mens = isset($this->config['mensage']) ? $this->config['mensage'] : false;
        if($mens && isset($this->config['link_leilao'])){
            $mens .= '<div class="text-center"><a href="'.$this->config['link_leilao'].'" class="btn btn-secondary text-light d-block mb-1">Ver leilão</a></div>';
            $mens = str_replace('h1', 'h5', $mens);
        }
        return [
            'message' => $mens,
            'config' => @$this->config,
        ];
    }
}
