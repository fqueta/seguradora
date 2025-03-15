<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            [
                'name' => 'Fernando Queta',
                'email' => 'quetafernando1@gmail.com',
                'password' => Hash::make('ferqueta'),
                'status' => 'actived',
                'verificado' => 's',
                'id_permission' => '1',
            ],
            [
                'name' => 'Leandro Lopardi',
                'email' => 'contato@aeroclubejf.com.br',
                'password' => Hash::make('a8240e82'),
                'status' => 'actived',
                'verificado' => 's',
                'id_permission' => '2',
            ],
            [
                'name' => 'primeiro cliente de teste',
                'email' => 'teste@aeroclubejf.com.br',
                'password' => Hash::make('mudar123'),
                'status' => 'actived',
                'verificado' => 's',
                'id_permission' => '5',
            ],
        ];
        User::truncate();
        foreach ($arr as $key => $value) {
            User::create($value);
        }
    }
}
