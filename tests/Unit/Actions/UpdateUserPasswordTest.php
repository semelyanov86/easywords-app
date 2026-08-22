<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Тесты для Action обновления пароля пользователя.
 */
final class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет успешное обновление пароля.
     */
    public function test_it_updates_user_password_successfully(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $action = new UpdateUserPassword();

        // Act
        $action->handle(
            userId: $user->id,
            currentPassword: 'old-password',
            newPassword: 'new-secure-password',
        );

        // Assert
        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));
    }

    /**
     * Проверяет, что не обновляет пароль при неверном текущем пароле.
     */
    public function test_it_does_not_update_password_with_wrong_current_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $action = new UpdateUserPassword();

        // Expect
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIsOrContains('The provided password does not match your current password.');

        // Act
        $action->handle(
            userId: $user->id,
            currentPassword: 'wrong-password',
            newPassword: 'new-password',
        );

        // Assert
        $user->refresh();
        $this->assertTrue(Hash::check('correct-password', $user->password));
    }

    /**
     * Проверяет, что выбрасывает исключение если пользователь не найден.
     */
    public function test_it_throws_exception_if_user_not_found(): void
    {
        // Arrange
        $action = new UpdateUserPassword();

        // Expect
        $this->expectException(ModelNotFoundException::class);

        // Act
        $action->handle(
            userId: 999,
            currentPassword: 'old-password',
            newPassword: 'new-password',
        );
    }

    /**
     * Проверяет, что хеширует новый пароль.
     */
    public function test_it_hashes_new_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $action = new UpdateUserPassword();

        // Act
        $action->handle(
            userId: $user->id,
            currentPassword: 'old-password',
            newPassword: 'plain-text-password',
        );

        // Assert
        $user->refresh();
        $this->assertNotEquals('plain-text-password', $user->password);
        $this->assertTrue(Hash::check('plain-text-password', $user->password));
    }
}
