<?php

namespace App\Console\Commands;

use App\Jobs\SendBlacklistJob;
use Illuminate\Console\Command;

class SendBlacklistCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:blacklist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando envia inadimplentes para o blacklist';

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
        SendBlacklistJob::dispatch();
        return 'SendBlacklistJob::dispatch()';
    }
}
