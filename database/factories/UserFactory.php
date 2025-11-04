<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = User::class;
     public function definition()
     {
         $email = $this->faker->unique()->safeEmail();
         $gender = $this->getGender();
        //  dd($email);
         return [
            'nome' => $this->faker->name($gender),
            'email' => $email,
            'password' => Hash::make('mudar123'),
            'status' => 'actived',
            'verificado' => 'n',
            'id_permission' => '2',
         ];
        //  'nome' => $this->faker->name($gender),
        //     'email' => $email,
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('ferqueta'),
        //     'remember_token' => Str::random(10),
        //     // 'status' => $this->getStatus(),
        //     'status' => 'actived',
        //     // 'gender' => $gender,
        //     // 'profile' => $this->getProfile(),
        //     'verificado' => 'n',
        //     'id_permission' => '2',

     }

     /**
      * Indicate that the model's email address should be unverified.
      *
      * @return \Illuminate\Database\Eloquent\Factories\Factory
      */
     public function unverified()
     {
         return $this->state(function (array $attributes) {
             return [
                 'email_verified_at' => null,
             ];
         });
     }

     private function getStatus() : string {
         $statuses = ['actived','inactived','pre_registred'];
         shuffle($statuses);
         return $statuses[0];
     }

     private function getGender() : string {
         $genders = ['male','female'];
         shuffle($genders);
         return $genders[0];
     }

     private function getProfile() : string {
         $profiles = ['administrator','user'];
         shuffle($profiles);
         return $profiles[0];
     }
}
