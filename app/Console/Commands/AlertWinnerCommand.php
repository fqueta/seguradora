<?php

namespace App\Console\Commands;

use App\Http\Controllers\LeilaoController;
use App\Jobs\NotificWinnerJob;
use Illuminate\Console\Command;

class AlertWinnerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alertar:ganhador';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando alerta os ganhadores dos leilões';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // return (new LeilaoController())->list_alert_winners();
        NotificWinnerJob::dispatch();
        return 'true';
    }
}
