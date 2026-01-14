<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_on_profile_page(): void
    {
        $this->get(route('profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk();
    }

    public function test_profile_page_renders_inertia_component(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->component('profile/Show');
        };

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertInertia($callback);
    }

    public function test_profile_page_shares_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $callback = function (AssertableInertia $page) use ($user): void {
            $page
                ->where('user.id', $user->id)
                ->where('user.name', 'John Doe')
                ->where('user.email', 'john@example.com');
        };

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertInertia($callback);
    }

    public function test_profile_page_shares_empty_tokens_collection(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->has('tokens')
                ->where('tokens', []);
        };

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertInertia($callback);
    }

    public function test_profile_page_shares_user_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('Token 1');
        $user->createToken('Token 2');

        $callback = function (AssertableInertia $page): void {
            $page
                ->has('tokens')
                ->count('tokens', 2);
        };

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertInertia($callback);
    }

    public function test_guests_cannot_create_token(): void
    {
        $this->post(route('profile.tokens.store'), [
            'name' => 'Test Token',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_create_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 'Test Token',
            ]);

        $response->assertOk();
    }

    public function test_token_creation_returns_token_data(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->has('token')
                ->has('token.id')
                ->has('token.name')
                ->has('token.token')
                ->has('token.created_at')
                ->has('token.updated_at');
        };

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 'Test Token',
            ])
            ->assertInertia($callback);
    }

    public function test_created_token_has_correct_name(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->where('token.name', 'My Custom Token');
        };

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 'My Custom Token',
            ])
            ->assertInertia($callback);
    }

    public function test_created_token_has_plain_text_token(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->whereType('token.token', 'string');
        };

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 'Test Token',
            ])
            ->assertInertia($callback);

        // Verify token was created in database
        $tokens = $user->tokens()->get();
        $this->assertCount(1, $tokens);
        $this->assertEquals('Test Token', $tokens->first()?->name);
    }

    public function test_created_token_renders_profile_page(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->component('profile/Show');
        };

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 'Test Token',
            ])
            ->assertInertia($callback);
    }

    public function test_token_creation_updates_tokens_list(): void
    {
        $user = User::factory()->create();
        $user->createToken('Existing Token');

        $callback = function (AssertableInertia $page): void {
            $page->count('tokens', 2);
        };

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 'New Token',
            ])
            ->assertInertia($callback);
    }

    public function test_guests_cannot_delete_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');

        $this->delete(route('profile.tokens.destroy', $token->accessToken->id))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_delete_their_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');

        $this->actingAs($user)
            ->delete(route('profile.tokens.destroy', $token->accessToken->id))
            ->assertRedirect();
    }

    public function test_token_deletion_removes_token_from_database(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');
        $tokenId = $token->accessToken->id;

        $this->actingAs($user)
            ->delete(route('profile.tokens.destroy', $tokenId));

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_users_cannot_delete_other_users_tokens(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token = $user2->createToken('User2 Token');

        $this->actingAs($user1)
            ->delete(route('profile.tokens.destroy', $token->accessToken->id))
            ->assertStatus(404);
    }

    public function test_delete_nonexistent_token_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('profile.tokens.destroy', 999999))
            ->assertStatus(404);
    }

    public function test_token_validation_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [])
            ->assertSessionHasErrors(['name']);
    }

    public function test_token_validation_name_must_be_string(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => 12345,
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_token_validation_name_max_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.tokens.store'), [
                'name' => str_repeat('a', 256),
            ])
            ->assertSessionHasErrors(['name']);
    }
}
