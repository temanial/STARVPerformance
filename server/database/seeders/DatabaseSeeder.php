<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Vadim Admin',
            'email' => 'admin@starv.ru',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'filial_id' => null,
        ]);
    }
}