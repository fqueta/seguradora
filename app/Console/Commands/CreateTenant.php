<?php

namespace App\Console\Commands;

use App\Models\Prefeituras;
use App\Qlib\Qlib;
use App\Tenant\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {--ids= : Ids of tenants to create structure}';
    protected $description = 'Create new tenants';

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
        $ids = explode(",", $this->option('ids'));//1,2,3
        $companies = Prefeituras::whereIn('id', $ids)->get();
        Tenant::loadConnections();
        foreach ($companies as $company) {
            $db = $company->database;
            $connection = $company->prefix;
            DB::statement("CREATE DATABASE IF NOT EXISTS {$db};");
            $this->call('migrate', [
                '--database' => $connection, //conexao company1
                '--path' => 'database/migrations/tenant',
                '--seed'
            ]);
            // if(isset($company->config)){
            //     $arr = Qlib::lib_json_array($company->config);
            //     $connection = isset($arr['connection'])?$arr['connection']:'tenant';
            //     dd($arr);
            //     if(isset($arr['name']) && ($db = $arr['name'])){
            //         Qlib::selectDefaultConnection($connection,$arr);

            //     }
            // }
        }
        if(!$companies->count()){
            $this->error('Ids of tenant not found in table.');
        }
    }
}
