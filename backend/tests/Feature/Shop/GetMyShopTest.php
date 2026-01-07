<?php

namespace Tests\Feature\Shop;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsWithJwt;

class GetMyShopTest extends TestCase
{
    use RefreshDatabase;
    use ActsWithJwt;

    public function user_can_get_own_shop()
    {
        $user = User::factory()->create();

        Shop::factory()->create([
            'owner_user_id' => $user->id,
            'name' => 'Owned Shop',
        ]);

        $response = $this
            ->actingAsJwt($user)
            ->getJson('/api/shops/me');

        $response
            ->assertStatus(200)
            ->assertJson([
                'shop' => [
                    'name' => 'Owned Shop',
                    'status' => 'active',
                ],
            ]);
    }



    /** @test */
    public function user_with_no_shop_gets_null()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAsJwt($user)   // ★ ここが重要
            ->getJson('/api/shops/me');

        $response
            ->assertStatus(200)
            ->assertJson([
                'shop' => null,
            ]);
    }
}
