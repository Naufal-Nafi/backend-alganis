<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Expense::create([
                'description' => fake()->sentence(3),
                'amount' => fake()->numberBetween(50000, 200000),
                'date' => fake()->date()
            ]);
        }
    }
}
