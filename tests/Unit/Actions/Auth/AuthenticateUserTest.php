<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для action аутентификации пользователя.
 */
class AuthenticateUserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private AuthenticateUser $authenticateUser;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateUser = new AuthenticateUser();
        $this->user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Тест успешной аутентификации и создания токена.
     */
    public function test_can_authenticate_user(): void
    {
        $token = $this->authenticateUser->handle(
            email: $this->user->email,
            password: 'password123',
        );

        $this->assertNotEmpty($token->plainTextToken);
        $this->assertNotNull($token->accessToken->tokenable);
        $tokenable = $token->accessToken->tokenable;
        $this->assertInstanceOf(\App\Models\User::class, $tokenable);
        $this->assertEquals($this->user->id, $tokenable->id);
        $this->assertEquals('api-token', $token->accessToken->name);
    }

    /**
     * Тест ошибки при неверном email.
     */
    public function test_cannot_authenticate_with_invalid_email(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->authenticateUser->handle(
            email: 'nonexistent@example.com',
            password: 'password123',
        );
    }

    /**
     * Тест ошибки при неверном пароле.
     */
    public function test_cannot_authenticate_with_invalid_password(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->authenticateUser->handle(
            email: $this->user->email,
            password: 'wrong-password',
        );
    }

    /**
     * Тест создания нового токена при каждом вызове.
     */
    public function test_creates_new_token_on_each_call(): void
    {
        $token1 = $this->authenticateUser->handle(
            email: $this->user->email,
            password: 'password123',
        );

        $token2 = $this->authenticateUser->handle(
            email: $this->user->email,
            password: 'password123',
        );

        $this->assertNotEquals($token1->plainTextToken, $token2->plainTextToken);
        $this->assertCount(2, $this->user->tokens);
    }
}
