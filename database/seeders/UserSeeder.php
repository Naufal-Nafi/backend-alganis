<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'alganis@admin.com',
            'password' => Hash::make('admin12345'),
            'role' => 'owner',
        ]);
        
        User::create([
            'name' => 'pegawai1',
            'email' => 'pegawai1@gmail.com',
            'password' => Hash::make('password1'),
            'role' => 'employee',
        ]);
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'role' => 'employee',
                'email' => fake()->email(),
                'name' => fake()->name(),
                'password' => fake()->password(),
            ]);
        }
    }
}
