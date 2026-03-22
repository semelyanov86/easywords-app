<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

/**
 * Тесты для контроллера изменения пароля.
 */
final class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt('oldPassword123'),
        ]);
    }

    /**
     * Тест успешного изменения пароля.
     */
    public function test_can_update_password(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson(route('api.v1.user.password.update'), [
                'current_password' => 'oldPassword123',
                'password' => 'newPassword456',
                'password_confirmation' => 'newPassword456',
            ]);

        $response->assertStatus(204)
            ->assertNoContent();

        // Проверяем, что пароль действительно изменился
        $freshUser = $this->user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertTrue(
            Hash::check('newPassword456', $freshUser->password),
        );
    }

    /**
     * Тест ошибки при неверном текущем пароле.
     */
    public function test_cannot_update_password_with_invalid_current_password(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson(route('api.v1.user.password.update'), [
                'current_password' => 'wrongPassword',
                'password' => 'newPassword456',
                'password_confirmation' => 'newPassword456',
            ]);

        $response->assertStatus(403);

        // Проверяем, что пароль не изменился
        $freshUser = $this->user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertFalse(
            Hash::check('newPassword456', $freshUser->password),
        );
        $this->assertTrue(
            Hash::check('oldPassword123', $freshUser->password),
        );
    }

    /**
     * Тест ошибки при попытке изменить пароль без токена.
     */
    public function test_cannot_update_password_without_token(): void
    {
        $response = $this->putJson(route('api.v1.user.password.update'), [
            'current_password' => 'oldPassword123',
            'password' => 'newPassword456',
            'password_confirmation' => 'newPassword456',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест валидации - отсутствующий текущий пароль.
     */
    public function test_validation_requires_current_password(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson(route('api.v1.user.password.update'), [
                'password' => 'newPassword456',
                'password_confirmation' => 'newPassword456',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /**
     * Тест валидации - отсутствующий новый пароль.
     */
    public function test_validation_requires_password(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson(route('api.v1.user.password.update'), [
                'current_password' => 'oldPassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест валидации - пароли не совпадают.
     */
    public function test_validation_requires_password_confirmation(): void
    {
        $token = $this->user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson(route('api.v1.user.password.update'), [
                'current_password' => 'oldPassword123',
                'password' => 'newPassword456',
                'password_confirmation' => 'differentPassword',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
