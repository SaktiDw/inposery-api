<?php

use App\Models\Product;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_many_receipt()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/receipts?filter[store_id]=1');
        $response->assertStatus(200);
    }
    // public function test_get_many_receipt_by_name()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/receipts?filter[store_id]=1&filter[product.name]=a');
    //     $response->assertStatus(200);
    // }
    // public function test_get_with_trashed_receipt()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     Receipt::find(2)->delete();
    //     $response = $this->getJson('/api/receipts?filter[store_id]=1&filter[withTrashed]=1');
    //     $response->assertStatus(200);
    // }
    // public function test_get_only_trashed_receipt()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     Receipt::find(2)->delete();
    //     $response = $this->getJson('/api/receipts?filter[store_id]=1&filter[onlyTrashed]=true');
    //     $response->assertStatus(200);
    // }
    // public function test_get_only_trashed_receipt_by_name()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     Receipt::find(2)->delete();
    //     $response = $this->getJson('/api/receipts?filter[store_id]=1&filter[product.name]=a&filter[onlyTrashed]=true');
    //     $response->assertStatus(200);
    // }
    public function test_get_one_receipt()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::find(1);
        Receipt::create([
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
        ]);
        $response = $this->getJson('/api/receipts/1');
        $response->assertStatus(200);
    }
    // public function test_failed_get_one_receipt_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/receipts/3');
    //     $response->assertStatus(403);
    // }
    public function test_create_receipt()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $product = Product::find(1);
        $response = $this->postJson('/api/receipts', [
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
        ]);

        $response->assertStatus(200);
    }

    // public function test_update_receipt()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $product = Product::find(1);

    //     $response = $this->patchJson('/api/receipts/1',);
    //     $response->assertStatus(200);
    // }
    // public function test_failed_update_receipt_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $product = Product::find(2);

    //     $response = $this->patchJson('/api/receipts/2',);
    //     $response->assertStatus(404);
    // }
    public function test_soft_delete_receipt()
    {
        $user = User::find(1);
        // Sanctum::actingAs($user);
        $receipt = Receipt::factory()->create();
        $this->assertTrue($receipt->delete());
        // $response = $this->deleteJson('/api/receipts/' . $receipt->id);
        // $response->assertStatus(200);
    }
    // public function test_failed_soft_delete_receipt_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);

    //     $response = $this->deleteJson('/api/receipts/' . $receipt->id);
    //     $response->assertStatus(404);
    // }
    public function test_restore_receipt()
    {
        $user = User::find(1);
        // Sanctum::actingAs($user);
        $receipt = Receipt::factory()->create();
        $receipt->delete();
        $this->assertTrue($receipt->restore());
        // $response = $this->getJson('/api/receipts/' . $receipt->id . '/restore');
        // $response->assertStatus(200);
    }
    // public function test_failed_restore_receipt_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);

    //     $receipt->delete();
    //     $response = $this->getJson('/api/receipts/' . $receipt->id . '/restore');
    //     $response->assertStatus(404);
    // }
    public function test_permanent_delete_receipt()
    {
        $user = User::find(1);
        // Sanctum::actingAs($user);
        $receipt = Receipt::factory()->create();
        $this->assertTrue($receipt->forceDelete());
        // $response = $this->deleteJson('/api/receipts/' . $receipt->id . '/delete-permanent');
        // $response->assertStatus(200);
    }
    // public function test_failed_permanent_delete_receipt_if_doesnt_belong_to_you()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);

    //     $response = $this->deleteJson('/api/receipts/' . $receipt->id . '/delete-permanent');
    //     $response->assertStatus(404);
    // }
}
