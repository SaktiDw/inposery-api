<?php

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_many_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/stores');
        $response->assertStatus(200);
    }
    public function test_get_many_store_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/stores?filter[name]=warung');
        $response->assertStatus(200);
    }
    public function test_get_with_trashed_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::find(2)->delete();
        $response = $this->getJson('/api/stores?filter[trashed]=with');
        $response->assertStatus(200);
    }
    public function test_get_with_trashed_store_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::find(2)->delete();
        $response = $this->getJson('/api/stores?filter[name]=warung&filter[trashed]=with');
        $response->assertStatus(200);
    }
    public function test_get_only_trashed_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::find(2)->delete();
        $response = $this->getJson('/api/stores?filter[trashed]=only');
        $response->assertStatus(200);
    }
    public function test_get_only_trashed_store_by_name()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::find(2)->delete();
        $response = $this->getJson('/api/stores?filter[name]=warung&filter[trashed]=only');
        $response->assertStatus(200);
    }
    public function test_get_one_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/stores/1');
        $response->assertStatus(200);
    }
    public function test_failed_get_one_store_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/stores/3');
        $response->assertStatus(403);
    }
    public function test_create_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/stores', [
            'name' => 'Store 1',
            'user_id' => $user->id,
        ]);
        $response->assertStatus(200);
    }

    public function test_failed_create_store_if_already_exist()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::create([
            'name' => 'Store 1',
            'user_id' => $user->id,
        ]);
        $response = $this->postJson('/api/stores', [
            'name' => 'Store 1',
            'user_id' => $user->id,
        ]);
        $response->assertStatus(422);
    }
    public function test_update_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->patchJson('/api/stores/1', [
            'name' => 'Updated Store',
            'user_id' => $user->id,
        ]);
        $response->assertStatus(200);
    }
    public function test_failed_update_store_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->patchJson('/api/stores/3', [
            'name' => 'Updated Store',
            'user_id' => $user->id,
        ]);
        $response->assertStatus(403);
    }
    public function test_soft_delete_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/stores/1');
        $response->assertStatus(200);
    }
    public function test_failed_soft_delete_store_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/stores/3');
        $response->assertStatus(403);
    }
    public function test_restore_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::find(1)->delete();
        $response = $this->getJson('/api/stores/1/restore');
        $response->assertStatus(200);
    }
    public function test_failed_restore_store_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        Store::find(3)->delete();
        $response = $this->getJson('/api/stores/3/restore');
        $response->assertStatus(403);
    }
    public function test_permanent_delete_store()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/stores/1/delete-permanent');
        $response->assertStatus(200);
    }
    public function test_failed_permanent_delete_store_if_doesnt_belong_to_you()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/stores/3/delete-permanent');
        $response->assertStatus(404);
    }
}
