<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_many_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/transactions?filter[store_id]=1');
        $response->assertStatus(200);
    }
    public function test_get_many_transaction_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/transactions?filter[store_id]=1&filter[product.name]=a');
        $response->assertStatus(200);
    }
    public function test_get_with_trashed_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Transaction::find(2)->delete();
        $response = $this->getJson('/api/transactions?filter[store_id]=1&filter[withTrashed]=1');
        $response->assertStatus(200);
    }
    public function test_get_only_trashed_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Transaction::find(2)->delete();
        $response = $this->getJson('/api/transactions?filter[store_id]=1&filter[onlyTrashed]=true');
        $response->assertStatus(200);
    }
    public function test_get_only_trashed_transaction_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Transaction::find(2)->delete();
        $response = $this->getJson('/api/transactions?filter[store_id]=1&filter[product.name]=a&filter[onlyTrashed]=true');
        $response->assertStatus(200);
    }
    // public function test_get_one_transaction()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/transactions/1');
    //     $response->assertStatus(200);
    // }
    // public function test_failed_get_one_transaction_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/transactions/3');
    //     $response->assertStatus(403);
    // }
    public function test_create_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::find(1);
        $response = $this->postJson('/api/transactions', [
            "type" => "IN",
            'store_id' => $product->store_id,
            "transaction" => [
                [
                    'product_id' => $product->id,
                    "qty" => 10,
                    "price" => $product->sell_price,
                    "discount" => 0,
                    "total" => 10 * $product->sell_price,
                    "description" => fake()->text(),
                ]
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_update_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::find(1);

        $response = $this->patchJson('/api/transactions/1', [
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_update_transaction_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::find(2);

        $response = $this->patchJson('/api/transactions/2', [
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $response->assertStatus(404);
    }
    public function test_soft_delete_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 1,
        ]);
        $transaction = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $product->update([
            "qty" => $transaction->qty
        ]);
        $response = $this->deleteJson('/api/transactions/' . $transaction->id);
        $response->assertStatus(200);
    }
    public function test_failed_soft_delete_transaction_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 3,
        ]);
        $transaction = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $product->update([
            "qty" => $transaction->qty
        ]);
        $response = $this->deleteJson('/api/transactions/' . $transaction->id);
        $response->assertStatus(404);
    }
    public function test_restore_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 1,
        ]);
        $transaction = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $product->update([
            "qty" => $transaction->qty
        ]);
        $transaction->delete();
        $response = $this->getJson('/api/transactions/' . $transaction->id . '/restore');
        $response->assertStatus(200);
    }
    public function test_failed_restore_transaction_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 3,
        ]);
        $transaction = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $product->update([
            "qty" => $transaction->qty
        ]);
        $transaction->delete();
        $response = $this->getJson('/api/transactions/' . $transaction->id . '/restore');
        $response->assertStatus(404);
    }
    public function test_permanent_delete_transaction()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 1,
        ]);
        $transaction = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $product->update([
            "qty" => $transaction->qty
        ]);
        $response = $this->deleteJson('/api/transactions/' . $transaction->id . '/delete-permanent');
        $response->assertStatus(200);
    }
    public function test_failed_permanent_delete_transaction_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => 'Product 1',
            'sell_price' => random_int(1000, 100000),
            'store_id' => 3,
        ]);
        $transaction = Transaction::create([
            "type" => "IN",
            "qty" => 10,
            "price" => $product->sell_price,
            "discount" => 0,
            "total" => 10 * $product->sell_price,
            "description" => fake()->text(),
            'product_id' => $product->id,
            'store_id' => $product->store_id,
        ]);
        $product->update([
            "qty" => $transaction->qty
        ]);
        $response = $this->deleteJson('/api/transactions/' . $transaction->id . '/delete-permanent');
        $response->assertStatus(404);
    }
}
