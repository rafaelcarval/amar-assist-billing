<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@amar.test',

            'password' => Hash::make(
                'secret123'
            ),
        ]);

        $response = $this->postJson(
            '/login',
            [
                'email' => 'admin@amar.test',
                'password' => 'secret123',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.email',
                'admin@amar.test'
            );

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@amar.test',

            'password' => Hash::make(
                'secret123'
            ),
        ]);

        $this
            ->postJson(
                '/login',
                [
                    'email' => 'admin@amar.test',
                    'password' => 'wrong-password',
                ]
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(
                'email'
            );

        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $this
            ->getJson('/api/customers')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_api(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this
            ->getJson('/api/customers')
            ->assertOk();
    }

    public function test_authenticated_user_can_get_current_user(): void
    {
        $user = User::factory()->create([
            'email' => 'user@amar.test',
        ]);

        $this->actingAs($user);

        $this
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath(
                'data.email',
                'user@amar.test'
            );
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web');

        $this
            ->postJson('/logout')
            ->assertOk();

        $this->assertGuest();
    }
}