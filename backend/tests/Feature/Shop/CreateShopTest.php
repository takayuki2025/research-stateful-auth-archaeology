<?php

namespace Tests\Feature\Shop;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Auth\Domain\Service\TokenIssuerService;
use App\Modules\Auth\Domain\Dto\ProvisionedUser;

class CreateShopTest extends TestCase
{
    use RefreshDatabase;

    private function issueJwtFor(User $user): string
    {
        $issuer = app(TokenIssuerService::class);


        return $issuer->issue(new ProvisionedUser(
            userId: $user->id,
            email: $user->email,
            externalId: 'test_uid',
            roles: ['user'],
            tenantId: null,
            isFirstLogin: false,
            emailVerified: true,
        ));

    }

    /** @test */
    public function user_can_create_shop()
    {
        $user = User::factory()->create();
        $jwt  = $this->issueJwtFor($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$jwt)
            ->postJson('/api/shops', [
                'name' => 'My First Shop',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'shop_code',
                'name',
                'status',
            ])
            ->assertJson([
                'name' => 'My First Shop',
                'status' => 'active',
            ]);

        $this->assertDatabaseHas('shops', [
            'owner_user_id' => $user->id,
            'name' => 'My First Shop',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function user_cannot_create_multiple_shops()
    {
        $user = User::factory()->create();
        $jwt  = $this->issueJwtFor($user);

        $this
            ->withHeader('Authorization', 'Bearer '.$jwt)
            ->postJson('/api/shops', [
                'name' => 'Shop One',
            ])
            ->assertStatus(201);

        $this
            ->withHeader('Authorization', 'Bearer '.$jwt)
            ->postJson('/api/shops', [
                'name' => 'Shop Two',
            ])
            ->assertStatus(500);
    }
}
