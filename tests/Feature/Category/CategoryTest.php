<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_many_category()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }
    public function test_get_many_category_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/categories?filter[name]=warung');
        $response->assertStatus(200);
    }

    // public function test_get_one_category()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->getJson('/api/categories/1');
    //     $response->assertStatus(200);
    // }

    public function test_create_category()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/categories', [
            'name' => 'Category 1'
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_create_category_if_already_exist()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Category::create([
            'name' => 'Category 1',
            'slug' => 'category-1',
        ]);
        $response = $this->postJson('/api/categories', [
            'name' => 'Category 1',
            'slug' => 'category-1',
        ]);
        $response->assertStatus(422);
    }
    // public function test_update_category()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->patchJson('/api/categories/1', [
    //         'name' => 'Updated Category'
    //     ]);
    //     $response->assertStatus(200);
    // }

    // public function test_delete_category()
    // {
    //     $user = User::find(1);
    //     Sanctum::actingAs($user);
    //     $response = $this->deleteJson('/api/categories/1');
    //     $response->assertStatus(200);
    // }

    // public function test_attach_category_to_a_product()
    // {
    //     $category = Category::factory()->create();
    //     $product = Product::factory()->create();
    //     $product->category()->syncWithoutDetaching([$category->id]);
    //     $this->assertArrayHasKey('attached', $product->category()->syncWithoutDetaching([$category->id]));
    // }
}
