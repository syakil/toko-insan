<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(array(
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt ('qwerty'),
                'foto' => 'user.png',
                'level' => 1
            ],
            [
                'name' => 'PO',
                'email' => 'po@gmail.com',
                'password' => bcrypt ('qwerty'),
                'foto' => 'user.png',
                'level' => 3
            ],
            [
                'name' => 'GUDANG',
                'email' => 'gudang@gmail.com',
                'password' => bcrypt ('qwerty'),
                'foto' => 'user.png',
                'level' => 4
            ],
            [
                'name' => 'spvs',
                'email' => 'spvs@gmail.com',
                'password' => bcrypt ('qwerty'),
                'foto' => 'user.png',
                'level' => 5
            ],
            [
                'name' => 'KASIR01',
                'email' => 'kasir@gmail.com',
                'password' => bcrypt ('qwerty'),
                'foto' => 'user.png',
                'level' => 2
            ],
            [
                'name' => 'KP',
                'email' => 'kp@gmail.com',
                'password' => bcrypt ('qwerty'),
                'foto' => 'user.png',
                'level' => 6
            ]
            ));
    }
}
