<?php

namespace Database\Seeders;

use App\Models\Consignment;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productIds = Product::pluck('product_id')->toArray();
        $storeIds = Store::pluck('store_id')->toArray();
        $users = User::all(); // Ambil semua user lengkap (bukan hanya ID)

        $timeFrames = [
            ['start' => '-7 days', 'count' => 10],
            ['start' => '-14 days', 'count' => 15],
            ['start' => '-30 days', 'count' => 20],
            ['start' => '-1 year', 'count' => 55],
        ];

        foreach ($timeFrames as $frame) {
            for ($i = 0; $i < $frame['count']; $i++) {
                $exitDate = fake()->dateTimeBetween($frame['start'], 'now');
                $entryDate = clone $exitDate;
                $entryDate->modify('+' . fake()->numberBetween(10, 40) . ' days');

                $stock = fake()->numberBetween(30, 100);
                $sold = fake()->numberBetween(10, min($stock, 30));

                $user = $users->random(); // ambil user acak

                Consignment::create([
                    'product_id' => fake()->randomElement($productIds),
                    'store_id' => fake()->randomElement($storeIds),
                    'user_id' => $user->user_id,
                    'creator_name' => $user->name, // ambil nama user
                    'entry_date' => $entryDate,
                    'exit_date' => $exitDate,
                    'stock' => $stock,
                    'sold' => $sold,
                ]);
            }
        }
    }

}
