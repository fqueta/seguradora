<?php

namespace App\Jobs;

use App\Http\Controllers\admin\OrcamentoController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class EnvioZapsingJob implements ShouldQueue
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
        $jobLogger->info('Iniciando o ExampleJob.');

        try {
            $ret = (new OrcamentoController)->send_to_zapSing($this->token);
            // Lógica do Job
            $jobLogger->info('EnvioZapsingJob está processando...',$ret);
        } catch (\Exception $e) {
            $jobLogger->error('Erro no EnvioZapsingJob: ' . $e->getMessage());
        }
    }
}
