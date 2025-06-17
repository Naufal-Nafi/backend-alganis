<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            StoreSeeder::class,
            UserSeeder::class,
            ExpenseSeeder::class,
            ConsignmentSeeder::class,
        ]);
    }
}
