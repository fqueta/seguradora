<?php

namespace App\Console;

use App\Http\Controllers\LeilaoController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\AlertWinnerCommand::class,
        Commands\SendBlacklistCommand::class,
    ];
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('alertar:ganhador')->daily();
        $schedule->command('send:blacklist')->daily();
        // $schedule->call((new LeilaoController)->list_alert_winners())->daily()->timezone('America/sao_Paulo');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
