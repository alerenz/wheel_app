<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'username' => 'admin1',
            'password' => Hash::make('qwerty1234567890'),
            'surname'=>'Иванова',
            'name'=>'Татьяна',
            'patronymic'=>'Ивановна',
            'role'=>'admin',
            'active'=>true,
            'attempts'=>1
        ]);

        DB::table('users')->insert([
            'username' => 'user25',
            'password' => Hash::make('1234567890qwerty'),
            'surname'=>'Иванова',
            'name'=>'Лариса',
            'patronymic'=>'Ивановна',
            'role'=>'user',
            'active'=>true,
            'attempts'=>1
        ]);
    }
}
