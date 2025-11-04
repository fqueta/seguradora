<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:list-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todas as conexões de banco de dados configuradas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connections = config('database.connections');
        foreach ($connections as $name => $details) {
            $this->info("Conexão: $name");
        }
    }
}
