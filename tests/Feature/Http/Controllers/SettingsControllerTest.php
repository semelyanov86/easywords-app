<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для контроллера настроек пользователя.
 *
 * Проверяет функциональность отображения и обновления настроек
 * через Inertia-интерфейс.
 */
final class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет, что неавторизованный пользователь не может обновить настройки.
     */
    public function test_settings_update_redirects_unauthenticated_user(): void
    {
        $response = $this->post(route('settings.update'), [
            'paginate' => 20,
            'main_language' => 'EN',
            'show_starred' => true,
            'known_enabled' => false,
            'latest_first' => true,
            'show_imported' => false,
            'show_shared' => true,
            'fresh_first' => false,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Проверяет, что авторизованный пользователь может обновить настройки.
     */
    public function test_settings_update_works_for_authenticated_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $settingsData = [
            'paginate' => 30,
            'main_language' => 'DE',
            'show_starred' => false,
            'known_enabled' => true,
            'latest_first' => false,
            'show_imported' => true,
            'show_shared' => false,
            'fresh_first' => true,
        ];

        $response = $this->actingAs($user)
            ->post(route('settings.update'), $settingsData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }
}
