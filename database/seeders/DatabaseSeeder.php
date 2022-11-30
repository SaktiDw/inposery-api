<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create();
        Store::factory(10)->create();
        $product = Product::factory(1000)->create();
        $product->map(function (Product $item) {
            for ($i = 0; $i < 20; $i++) {
                Transaction::create([
                    "type" => "IN",
                    "qty" => random_int(1, 100),
                    "price" => $item->sell_price,
                    "discount" => 0,
                    "description" => fake()->text(),
                    'product_id' => $item->id,
                    'store_id' => $item->store_id,
                    'created_at' => Carbon::today()->subDays(rand(0, 180))
                ]);
            }
        });
        // $transaction = Transaction::factory(1000)->create();
        // $transaction->map(function (Transaction $item) {
        //     $product = Product::findOrFail($item->product_id);
        //     if ($item->type == "IN") {
        //         $product->qty = $product->qty + $item->qty;
        //     } else {
        //         $product->qty = $product->qty - $item->qty;
        //     }
        //     $product->save();
        // });



        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
