<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateUserSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateUserSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_setting_and_returns_all_settings(): void
    {
        $user = User::factory()->create();

        $result = UpdateUserSetting::make()->handle(
            $user->id,
            'paginate',
            50,
        );

        $this->assertEquals(50, $result->paginate);
    }

    public function test_updates_boolean_setting(): void
    {
        $user = User::factory()->create();

        $result = UpdateUserSetting::make()->handle(
            $user->id,
            'fresh_first',
            false,
        );

        $this->assertFalse($result->fresh_first);
    }

    public function test_updates_string_setting(): void
    {
        $user = User::factory()->create();

        $result = UpdateUserSetting::make()->handle(
            $user->id,
            'default_language',
            'EN',
        );

        $this->assertEquals('EN', $result->default_language);
    }

    public function test_throws_exception_for_nonexistent_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        UpdateUserSetting::make()->handle(99999, 'paginate', 50);
    }

    public function test_saves_setting_to_database(): void
    {
        $user = User::factory()->create();

        UpdateUserSetting::make()->handle($user->id, 'paginate', 30);

        /** @var User $freshUser */
        $freshUser = $user->fresh();
        $this->assertEquals(30, $freshUser->settings()->get('paginate'));
    }

    public function test_updates_and_returns_all_settings(): void
    {
        $user = User::factory()->create();

        // Сначала обновляем одну настройку
        UpdateUserSetting::make()->handle($user->id, 'paginate', 25);

        // Затем другую
        $result = UpdateUserSetting::make()->handle($user->id, 'default_language', 'FR');

        // Проверяем, что все настройки возвращаются корректно
        $this->assertEquals(25, $result->paginate);
        $this->assertEquals('FR', $result->default_language);
        $this->assertTrue($result->fresh_first); // Значение по умолчанию
    }
}
