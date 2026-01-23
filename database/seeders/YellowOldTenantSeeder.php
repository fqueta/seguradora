<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class YellowOldTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = 'yellow-old';
        $domain = 'yellow-old.localhost';

        if ($tenant = Tenant::find($id)) {
            $tenant->delete();
            $this->command->info("Tenant {$id} removido para recriacao.");
        }

        $tenant = Tenant::create(['id' => $id]);
        $tenant->domains()->create(['domain' => $domain]);

        $this->command->info("Tenant {$id} criado com sucesso com o dominio {$domain}.");
    }
}
