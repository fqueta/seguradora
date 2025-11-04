<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Prefeituras;
use App\Qlib\Qlib;
use App\Tenant\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tenant for test';

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
     * @return mixed
     */
    public function handle()
    {
        if (Schema::hasTable((new Prefeituras())->getTable())) {
            $companies = Prefeituras::all();
            // $connection = isset($arr['connection'])?$arr['connection']:'tenant';
            foreach ($companies as $company) {
                if(isset($company->config)){
                    // $arr = Qlib::lib_json_array($company->config);
                    // if(isset($arr['name']) && ($db = $arr['name'])){
                    DB::statement("DROP DATABASE IF EXISTS {$company->database};");
                        // dd($arr);
                    // }
                }
            }
            $this->info('Tenant database dropped');
        }
        $connection_system = 'system';
        $this->info('Seeding system');
        $this->call('migrate:fresh', [
            '--database' => $connection_system,
            '--path' => 'database/migrations'
        ]);
        $this->call('db:seed', [
            '--class' => 'DatabaseSeeder',
            '--database' =>$connection_system
        ]);
        //seeding tenant now
        // Qlib::selectDefaultConnection($connection,$arr);
        $this->info('Seeding system finished');
        DB::setDefaultConnection($connection_system);
        $companies = Prefeituras::all();
        Tenant::loadConnections();
        dump(DB::getDefaultConnection());
        // dd($companies);

        // $this->call('db:list-connections');
        // $this->call('tenant:create', [
        //     '--ids' => implode(",", $companies->pluck('id')->toArray()),
        // ]);
        foreach ($companies as $company){
            // DB::setDefaultConnection($connection_system);
            // dd($company);
            $this->call('db:seed', [
                '--database' => $company->prefix,
                '--class' => 'TenantDatabaseSeeder'
            ]);
        }

    }
}
