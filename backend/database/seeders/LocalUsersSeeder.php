<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class LocalUsersSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Local',
            'email' => 'local@coachtech.com',
            'password' => bcrypt('testtest2'),
            'email_verified_at' => now(),
        ]);
    }
}