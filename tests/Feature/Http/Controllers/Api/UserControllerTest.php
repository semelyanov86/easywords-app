<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для контроллера пользователя.
 */
final class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Тест успешного получения информации о пользователе.
     */
    public function test_can_get_current_user(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.me'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
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
            ]);

        /** @var array{data: array{type: string, id: string, attributes: array<string, mixed>}} $responseData */
        $responseData = $response->json();
        $this->assertEquals('users', $responseData['data']['type']);
        $this->assertEquals($this->user->id, (int) $responseData['data']['id']);
        $this->assertEquals('Test User', $responseData['data']['attributes']['name']);
        $this->assertEquals('test@example.com', $responseData['data']['attributes']['email']);
    }

    /**
     * Тест ошибки при попытке получить информацию без токена.
     */
    public function test_cannot_get_current_user_without_token(): void
    {
        $response = $this->getJson(route('api.v1.me'));

        $response->assertStatus(401);
    }

    /**
     * Тест ошибки при использовании неверного токена.
     */
    public function test_cannot_get_current_user_with_invalid_token(): void
    {
        $response = $this->withToken('invalid-token')
            ->getJson(route('api.v1.me'));

        $response->assertStatus(401);
    }

    /**
     * Тест, что возвращаются правильные данные пользователя.
     */
    public function test_returns_correct_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'has_premium' => true,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.me'));

        /** @var array{data: array{attributes: array<string, mixed>}} $responseData */
        $responseData = $response->json();
        $this->assertTrue($responseData['data']['attributes']['is_admin']);
        $this->assertTrue($responseData['data']['attributes']['has_premium']);
        $this->assertEquals('Admin User', $responseData['data']['attributes']['name']);
        $this->assertEquals('admin@example.com', $responseData['data']['attributes']['email']);
    }
}
