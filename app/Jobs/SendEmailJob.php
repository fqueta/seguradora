<?php
namespace App\Jobs;

use App\Mail\EnviaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function handle()
    {
        if(isset($this->details['cc'])){
            Mail::to($this->details['email'],$this->details['name'])
            ->cc($this->details['cc'])
            ->send(new EnviaMail($this->details));
        }elseif(isset($this->details['bcc']) && $this->details['bcc']){
            Mail::to($this->details['email'],$this->details['name'])
            ->bcc($this->details['bcc'])->cc($this->details['cc'])
            ->send(new EnviaMail($this->details));
        }else{
            Mail::to($this->details['email'],$this->details['name'])
            ->send(new EnviaMail($this->details));
        }
    }
}
