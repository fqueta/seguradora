<?php

namespace App\Console\Commands;

use App\Http\Controllers\system\TenantController;
use Illuminate\Console\Command;

class AddAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-all-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para adicionar os primeiros tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ret = (new TenantController)->add_all([
            ['id' =>'pratapolis','domain' =>'pratapolis.localhost','name' =>'Prefeitura Municipal de Pratápolis'],
            ['id' =>'pintopolis','domain' =>'pintopolis.localhost','name' =>'Prefeitura Municipal de Pintopolis'],
            ['id' =>'demo','domain' =>'demo.maisaqui.com.br','name' =>'Prefeitura Municipal de Demostração'],
        ]);
        // $ret = (new TenantController)->add_all([
        //     ['id' =>'pratapolis','domain' =>'pratapolis.amsloja.com.br','name' =>'Prefeitura Municipal de Pratápolis'],
        //     ['id' =>'pintopolis','domain' =>'pintopolis.amsloja.com.br','name' =>'Prefeitura Municipal de Pintopolis'],
        //     ['id' =>'demo','domain' =>'demo.maisaqui.com.br','name' =>'Prefeitura Municipal de Demostração'],
        // ]);
        $this->info('Tanants adicionados!');
        $this->call('tenants:seed');
        // $this->info('Tanants adicionados!');
    }
}
