<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "type" => "IN",
            "qty" => random_int(1, 100),
            "price" => Product::all()->random()->sell_price,
            "discount" => 0,
            "description" => fake()->text(),
            'product_id' => Product::all()->random()->id,
            'store_id' => Store::all()->random()->id,
        ];
    }
}
