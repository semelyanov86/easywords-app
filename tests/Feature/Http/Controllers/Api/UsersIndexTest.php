<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для эндпоинта вывода списка пользователей.
 */
class UsersIndexTest extends TestCase
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

        // Создаем дополнительных пользователей
        User::factory()->count(3)->create();
    }

    /**
     * Тест успешного получения списка пользователей в полном режиме.
     */
    public function test_can_get_users_without_short_mode(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.users.index'));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
    }

    /**
     * Тест успешного получения списка пользователей в кратком режиме.
     */
    public function test_can_get_users_with_short_mode(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.users.index', ['short_mode' => 'true']));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/vnd.api+json')
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Тест, что в коротком режиме скрываются дополнительные поля.
     */
    public function test_short_mode_hides_extra_fields(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.users.index', ['short_mode' => '1']));

        /** @var array{data: array<int, array<string, mixed>>} $responseData */
        $responseData = $response->json();

        // Проверяем, что у первого пользователя только нужные поля
        $firstUser = $responseData['data'][0];
        $this->assertArrayHasKey('type', $firstUser);
        $this->assertArrayHasKey('id', $firstUser);
        $this->assertArrayHasKey('attributes', $firstUser);

        /** @var array<string, mixed> $attributes */
        $attributes = $firstUser['attributes'];
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayNotHasKey('email', $attributes);
        $this->assertArrayNotHasKey('is_admin', $attributes);
        $this->assertArrayNotHasKey('has_premium', $attributes);
    }

    /**
     * Тест, что без короткого режима показываются все поля.
     */
    public function test_full_mode_shows_all_fields(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson(route('api.v1.users.index'));

        /** @var array{data: array<int, array<string, mixed>>} $responseData */
        $responseData = $response->json();

        // Проверяем, что у первого пользователя все поля
        $firstUser = $responseData['data'][0];
        $this->assertArrayHasKey('attributes', $firstUser);

        /** @var array<string, mixed> $attributes */
        $attributes = $firstUser['attributes'];
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('email', $attributes);
        $this->assertArrayHasKey('is_admin', $attributes);
        $this->assertArrayHasKey('has_premium', $attributes);
        $this->assertArrayHasKey('created_at', $attributes);
        $this->assertArrayHasKey('updated_at', $attributes);
    }

    /**
     * Тест ошибки при попытке получить список без токена.
     */
    public function test_cannot_get_users_without_token(): void
    {
        $response = $this->getJson(route('api.v1.users.index'));

        $response->assertStatus(401);
    }

    /**
     * Тест ошибки при использовании неверного токена.
     */
    public function test_cannot_get_users_with_invalid_token(): void
    {
        $response = $this->withToken('invalid-token')
            ->getJson(route('api.v1.users.index'));

        $response->assertStatus(401);
    }

    /**
     * Тест, что short_mode работает с любым truthy значением.
     */
    public function test_short_mode_accepts_various_values(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response1 = $this->withToken($token)
            ->getJson(route('api.v1.users.index', ['short_mode' => 'true']));
        $response1->assertStatus(200);

        $response2 = $this->withToken($token)
            ->getJson(route('api.v1.users.index', ['short_mode' => '1']));
        $response2->assertStatus(200);

        $response3 = $this->withToken($token)
            ->getJson(route('api.v1.users.index', ['short_mode' => 'yes']));
        $response3->assertStatus(200);
    }
}
