<?php

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_report_stores()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/dashboard?id=' . $user->stores->pluck('id'));
        $response->assertStatus(200);
    }

    public function test_get_top_product_in()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/getTopProduct?store_id=1&type=IN');
        $response->assertStatus(200);
    }
    public function test_get_top_product_out()
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/getTopProduct?store_id=1&type=OUT');
        $response->assertStatus(200);
    }
}
