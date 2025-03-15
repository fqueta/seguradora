<?php

namespace App\Jobs;

use App\Http\Controllers\admin\RdstationController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RdstationJob implements ShouldQueue
{
    use Queueable;

     /**
     * Create a new job instance.
     */
    protected $token;
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobLogger = Log::channel('jobs');
        $jobLogger->info('Iniciando fila rdstation: '.$this->token.'.');

        try {
            $token = $this->token;
            // Lógica do Job
            $ret = (new RdstationController)->add_rd_negociacao($token);
            $jobLogger->info('RdstationController token matricula: '.$token.' foi processado...',$ret);
        } catch (\Exception $e) {
            $jobLogger->error('Erro no RdstationController: ' . $e->getMessage());
        }
    }
}
