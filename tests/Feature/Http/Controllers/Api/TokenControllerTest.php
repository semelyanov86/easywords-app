<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для контроллера токенов авторизации.
 */
final class TokenControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Тест успешного создания токена.
     */
    public function test_can_create_token(): void
    {
        $response = $this->postJson(route('api.v1.token'), [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'token',
                        'token_type',
                    ],
                    'relationships' => [
                        'user' => [
                            'data' => [
                                'type',
                                'id',
                            ],
                        ],
                    ],
                ],
                'included' => [
                    [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'email',
                            'is_admin',
                            'has_premium',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]);

        /** @var array{data: array{type: string, id: string, attributes: array{token: string, token_type: string}}} $responseData */
        $responseData = $response->json();
        $this->assertEquals('auth-token', $responseData['data']['type']);
        $this->assertEquals($this->user->id, (int) $responseData['data']['id']);
        $this->assertEquals('Bearer', $responseData['data']['attributes']['token_type']);
        $this->assertNotEmpty($responseData['data']['attributes']['token']);
    }

    /**
     * Тест ошибки при неверном email.
     */
    public function test_cannot_create_token_with_invalid_email(): void
    {
        $response = $this->postJson(route('api.v1.token'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест ошибки при неверном пароле.
     */
    public function test_cannot_create_token_with_invalid_password(): void
    {
        $response = $this->postJson(route('api.v1.token'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест валидации - отсутствующий email.
     */
    public function test_validation_requires_email(): void
    {
        $response = $this->postJson(route('api.v1.token'), [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест валидации - отсутствующий пароль.
     */
    public function test_validation_requires_password(): void
    {
        $response = $this->postJson(route('api.v1.token'), [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест успешного выхода из системы (удаление токена).
     */
    public function test_can_signout(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson(route('api.v1.signout'));

        $response->assertStatus(204)
            ->assertNoContent();

        // Проверяем, что токен удален
        $this->assertCount(0, $this->user->tokens);
    }

    /**
     * Тест ошибки при попытке выхода без токена.
     */
    public function test_cannot_signout_without_token(): void
    {
        $response = $this->postJson(route('api.v1.signout'));

        $response->assertStatus(401);
    }
}
