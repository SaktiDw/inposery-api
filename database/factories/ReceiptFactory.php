<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $product = Product::factory()->create();
        return [

            "change" => 0,
            "discount" => 0,
            "payment" => $product->qty * 10 * $product->sell_price,
            'store_id' => $product->store_id,
            "total" => $product->qty * 10 * $product->sell_price,
            "products" => json_encode([
                [
                    "id" => $product->id,
                    "name" => $product->name,
                    "sell_price" => $product->sell_price,
                    "qty" => $product->qty,
                    "orderQty" => 10,
                    "customer" => [
                        "name" => "random",
                        "active" => "true"
                    ],
                ]
            ])

        ];
    }
}
