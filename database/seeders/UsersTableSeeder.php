<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\User::create([
            "name" => 'bpi' ,
            "email" => 'bpi@gmail.com' ,
            "password" => bcrypt('123123') ,
            "role" => "admin"
        ]);
        App\User::create([
            "name" => 'data entry' ,
            "email" => 'bpi2@gmail.com' ,
            "password" => bcrypt('123123') ,
            "role" => "assessment"
        ]);
    }
}
