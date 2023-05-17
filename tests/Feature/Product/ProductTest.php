<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_many_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/products?filter[store_id]=1');
        $response->assertStatus(200);
    }
    public function test_get_many_product_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/products?filter[store_id]=1&filter[name]=warung');
        $response->assertStatus(200);
    }
    public function test_get_with_trashed_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Product::find(2)->delete();
        $response = $this->getJson('/api/products?filter[store_id]=1&filter[trashed]=with');
        $response->assertStatus(200);
    }
    public function test_get_only_trashed_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Product::find(2)->delete();
        $response = $this->getJson('/api/products?filter[store_id]=1&filter[trashed]=only');
        $response->assertStatus(200);
    }
    public function test_get_only_trashed_product_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Product::find(2)->delete();
        $response = $this->getJson('/api/products?filter[store_id]=1&filter[name]=a&filter[trashed]=only');
        $response->assertStatus(200);
    }
    // public function test_get_one_product()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/products/1');
    //     $response->assertStatus(200);
    // }
    // public function test_failed_get_one_product_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/products/3');
    //     $response->assertStatus(403);
    // }
    public function test_create_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/products/', [
            'name' => 'New product',
            'sell_price' => 10000,
            'store_id' => 1,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_create_product_if_already_exist()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Product::create([
            'name' => 'New product',
            'sell_price' => 10000,
            'store_id' => 1,
        ]);
        $response = $this->postJson('/api/products/', [
            'name' => 'New product',
            'sell_price' => 10000,
            'store_id' => 1,
        ]);
        $response->assertStatus(422);
    }
    public function test_update_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->patchJson('/api/products/1', [
            'name' => 'Updated product',
            'sell_price' => 10000,
            'store_id' => 1,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_update_product_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->patchJson('/api/products/1', [
            'name' => 'Updated product',
            'sell_price' => 10000,
            'store_id' => 2,
        ]);
        $response->assertStatus(403);
    }
    public function test_soft_delete_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/products/1');
        $response->assertStatus(200);
    }
    public function test_failed_soft_delete_product_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/products/2');
        $response->assertStatus(404);
    }
    public function test_restore_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => fake()->name(),
            'sell_price' => random_int(1000, 100000),
            'store_id' => 1,
        ]);
        $product->delete();
        $response = $this->getJson('/api/products/' . $product->id . '/restore');
        $response->assertStatus(200);
    }
    public function test_failed_restore_product_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => fake()->name(),
            'sell_price' => random_int(1000, 100000),
            'store_id' => 3,
        ]);
        $product->delete();
        $response = $this->getJson('/api/products/' . $product->id . '/restore');
        $response->assertStatus(404);
    }
    public function test_permanent_delete_product()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => fake()->name(),
            'sell_price' => random_int(1000, 100000),
            'store_id' => 1,
        ]);
        $response = $this->deleteJson('/api/products/' . $product->id . '/delete-permanent');
        $response->assertStatus(200);
    }
    public function test_failed_permanent_delete_product_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::create([
            'name' => fake()->name(),
            'sell_price' => random_int(1000, 100000),
            'store_id' => 3,
        ]);
        $response = $this->deleteJson('/api/products/' . $product->id . '/delete-permanent');
        $response->assertStatus(404);
    }
}
