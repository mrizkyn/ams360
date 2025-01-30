<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => 'bpi' ,
            "email" => 'bpi@gmail.com' ,
            "password" => bcrypt('123123') ,
            "role" => "admin"
        ]);
        User::create([
            "name" => 'data entry' ,
            "email" => 'bpi2@gmail.com' ,
            "password" => bcrypt('123123') ,
            "role" => "assessment"
        ]);
    }
}
