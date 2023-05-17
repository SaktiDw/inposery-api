<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Lisa',
            'email' => "lisa@gmail.com  ",
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'name' => 'Jean',
            'email' => "jean@gmail.com  ",
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);
        Store::create([
            'name' => fake()->name(),
            'user_id' =>  1,
        ]);
        Store::create([
            'name' => 'Warung Barokah',
            'user_id' =>  1,
        ]);
        Store::create([
            'name' => 'Warung Kopi',
            'user_id' =>  2,
        ]);
        // User::factory(10)->create();
        Store::factory(10)->create();

        $product1 = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 1,
        ]);
        $transaction1 = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product1->sell_price,
            "discount" => 0,
            "total" => 10 * $product1->sell_price,
            "description" => fake()->text(),
            'product_id' => $product1->id,
            'store_id' => $product1->store_id,
            'created_at' => Carbon::today()->subDays(rand(0, 180))
        ]);
        $product1->update([
            "qty" => $transaction1->qty
        ]);
        $product2 = Product::create([
            'name' => 'Product 2',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 3,
        ]);
        $transaction2 = Transaction::create([
            "type" => "IN",
            "qty" => 20,
            "price" => $product2->sell_price,
            "discount" => 0,
            "total" => 20 * $product2->sell_price,
            "description" => fake()->text(),
            'product_id' => $product2->id,
            'store_id' => $product2->store_id,
            'created_at' => Carbon::today()->subDays(rand(0, 280))
        ]);
        $product2->update([
            "qty" => $transaction2->qty
        ]);

        Category::factory(10)->create();
        $product = Product::factory(10)->create();
        $product->map(function (Product $item) {
            $category = Category::all()->random()->id;
            $item->category()->attach($category);
            for ($i = 0; $i < 20; $i++) {
                $qty = random_int(1, 100);
                $transaction = Transaction::create([
                    "type" => "IN",
                    "qty" => $qty,
                    "price" => $item->sell_price,
                    "discount" => 0,
                    "total" => $qty * $item->sell_price,
                    "description" => fake()->text(),
                    'product_id' => $item->id,
                    'store_id' => $item->store_id,
                    'created_at' => Carbon::today()->subDays(rand(0, 180))
                ]);
                $item->update([
                    "qty" => $transaction->qty + $item->qty
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
