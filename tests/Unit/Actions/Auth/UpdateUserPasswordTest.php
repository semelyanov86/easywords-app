<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для action изменения пароля.
 */
class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UpdateUserPassword $updateUserPassword;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->updateUserPassword = new UpdateUserPassword();
        $this->user = User::factory()->create([
            'password' => bcrypt('oldPassword123'),
        ]);
    }

    /**
     * Тест успешного изменения пароля.
     */
    public function test_can_update_password(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $this->updateUserPassword->handle(
            currentPassword: 'oldPassword123',
            newPassword: 'newPassword456',
        );

        // Проверяем, что пароль действительно изменился
        $freshUser = $this->user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('newPassword456', $freshUser->password),
        );
        $this->assertFalse(
            \Illuminate\Support\Facades\Hash::check('oldPassword123', $freshUser->password),
        );
    }

    /**
     * Тест ошибки при неверном текущем пароле.
     */
    public function test_cannot_update_password_with_invalid_current_password(): void
    {
        $this->actingAs($this->user, 'sanctum');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->updateUserPassword->handle(
            currentPassword: 'wrongPassword',
            newPassword: 'newPassword456',
        );
    }

    /**
     * Тест ошибки при неавторизованном доступе.
     */
    public function test_cannot_update_password_without_authentication(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->updateUserPassword->handle(
            currentPassword: 'oldPassword123',
            newPassword: 'newPassword456',
        );
    }

    /**
     * Тест, что старый токен остается валидным после смены пароля.
     */
    public function test_token_remains_valid_after_password_change(): void
    {
        $token = $this->user->createToken('test-token');
        $this->actingAs($this->user, 'sanctum');

        $this->updateUserPassword->handle(
            currentPassword: 'oldPassword123',
            newPassword: 'newPassword456',
        );

        // Проверяем, что токен все еще существует
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
