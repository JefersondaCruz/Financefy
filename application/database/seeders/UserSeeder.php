<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Jeferson da cruz',
            'email' => 'jeferson@teste.com',
            'password' => Hash::make('123456'),
            'phone' => '554298315862',
        ]);

        foreach (range(1, 5) as $i) {
            User::create([
                'name' => "Usuário {$i}",
                'email' => "usuario{$i}@teste.com",
                'password' => Hash::make('123456'),
                'phone' => "55999999999{$i}",
            ]);
        }
    }
}
