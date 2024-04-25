<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* User::create([
            'name' => 'Gerald José',
            'email' => 'geraldjoseavalosseveriche@gmail.com',
            'password' => bcrypt('123456789')
        ]); */

        User::create([
            'name' => 'User1',
            'email' => 'email1@gmail.com',
            'password' => bcrypt('123456789')
        ]);
    }
}
