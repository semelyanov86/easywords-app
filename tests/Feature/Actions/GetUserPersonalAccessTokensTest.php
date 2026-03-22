<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\GetUserPersonalAccessTokens;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetUserPersonalAccessTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_collection_when_user_has_no_tokens(): void
    {
        $user = User::factory()->create();
        $action = new GetUserPersonalAccessTokens();

        $tokens = $action->handle($user->id);

        $this->assertCount(0, $tokens);
    }

    public function test_returns_all_user_tokens(): void
    {
        $user = User::factory()->create();
        $action = new GetUserPersonalAccessTokens();

        // Create 3 tokens for the user
        $user->createToken('Token 1');
        $user->createToken('Token 2');
        $user->createToken('Token 3');

        $tokens = $action->handle($user->id);

        $this->assertCount(3, $tokens);
    }

    public function test_returns_only_user_tokens(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $action = new GetUserPersonalAccessTokens();

        // Create tokens for both users
        $user1->createToken('User 1 Token 1');
        $user1->createToken('User 1 Token 2');
        $user2->createToken('User 2 Token 1');

        $user1Tokens = $action->handle($user1->id);
        $user2Tokens = $action->handle($user2->id);

        $this->assertCount(2, $user1Tokens);
        $this->assertCount(1, $user2Tokens);
    }

    public function test_returns_tokens_as_data_objects(): void
    {
        $user = User::factory()->create();
        $action = new GetUserPersonalAccessTokens();

        $user->createToken('Test Token');

        $tokens = $action->handle($user->id);

        $token = $tokens->first();
        $this->assertNotNull($token);
        $this->assertArrayHasKey('id', $token->toArray());
        $this->assertArrayHasKey('name', $token->toArray());
        $this->assertArrayHasKey('tokenable_type', $token->toArray());
        $this->assertArrayHasKey('tokenable_id', $token->toArray());
        $this->assertArrayHasKey('abilities', $token->toArray());
        $this->assertArrayHasKey('last_used_at', $token->toArray());
        $this->assertArrayHasKey('created_at', $token->toArray());
        $this->assertArrayHasKey('updated_at', $token->toArray());
        $this->assertArrayHasKey('expires_at', $token->toArray());
    }

    public function test_throws_exception_for_non_existent_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $action = new GetUserPersonalAccessTokens();
        $action->handle(999999);
    }

    public function test_token_data_has_correct_types(): void
    {
        $user = User::factory()->create();
        $action = new GetUserPersonalAccessTokens();

        $token = $user->createToken('Test Token');

        $tokens = $action->handle($user->id);
        $tokenData = $tokens->first();

        $this->assertNotNull($tokenData);
        $this->assertInstanceOf(CarbonImmutable::class, $tokenData->created_at);
        $this->assertInstanceOf(CarbonImmutable::class, $tokenData->updated_at);
    }

    public function test_last_used_at_is_carbon_instance(): void
    {
        $user = User::factory()->create();
        $action = new GetUserPersonalAccessTokens();

        $user->createToken('Unused Token');

        $tokens = $action->handle($user->id);
        $tokenData = $tokens->first();
        $this->assertNotNull($tokenData);

        $this->assertInstanceOf(CarbonImmutable::class, $tokenData->last_used_at);
    }
}
