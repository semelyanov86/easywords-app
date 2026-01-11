<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $this->get(route('api.user-settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_get_their_settings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get(route('api.user-settings.index'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes',
                ],
            ]);

        /** @var array{type: string, id: string, attributes: array<string, mixed>} $responseData */
        $responseData = $response->json('data');

        $this->assertEquals('user-settings', $responseData['type']);

        // Проверяем наличие настроек по умолчанию
        $this->assertArrayHasKey('paginate', $responseData['attributes']);
        $this->assertEquals(20, $responseData['attributes']['paginate']);
        $this->assertArrayHasKey('fresh_first', $responseData['attributes']);
        $this->assertTrue($responseData['attributes']['fresh_first']);
        $this->assertArrayHasKey('default_language', $responseData['attributes']);
        $this->assertEquals('DE', $responseData['attributes']['default_language']);
    }

    public function test_unauthenticated_user_cannot_update_settings(): void
    {
        $this->post(route('api.user-settings.update'), [
            'name' => 'paginate',
            'value' => 50,
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_a_setting(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'name' => 'paginate',
                'value' => 50,
            ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json');

        /** @var array{type: string, id: string, attributes: array<string, mixed>} $responseData */
        $responseData = $response->json('data');

        $this->assertEquals(50, $responseData['attributes']['paginate']);

        // Проверяем, что настройка сохранена в базе данных
        /** @var User $freshUser */
        $freshUser = $user->fresh();
        $this->assertEquals(50, $freshUser->settings()->get('paginate'));
    }

    public function test_update_setting_returns_jsonapi_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'name' => 'fresh_first',
                'value' => false,
            ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes',
                ],
            ]);

        /** @var array{type: string, id: string, attributes: array<string, mixed>} $responseData */
        $responseData = $response->json('data');

        $this->assertEquals('user-settings', $responseData['type']);
        $this->assertEquals('1', $responseData['id']);
        $this->assertFalse($responseData['attributes']['fresh_first']);
    }

    public function test_update_setting_validates_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'value' => 50,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_setting_validates_name_is_string(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'name' => 123,
                'value' => 50,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_setting_validates_value_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'name' => 'paginate',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    public function test_user_can_update_multiple_settings_sequentially(): void
    {
        $user = User::factory()->create();

        // Обновляем первую настройку
        $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'name' => 'paginate',
                'value' => 30,
            ])->assertOk();

        // Обновляем вторую настройку
        $this->actingAs($user)
            ->post(route('api.user-settings.update'), [
                'name' => 'default_language',
                'value' => 'EN',
            ])->assertOk();

        // Проверяем, что обе настройки обновлены
        /** @var User $freshUser */
        $freshUser = $user->fresh();

        $this->assertEquals(30, $freshUser->settings()->get('paginate'));
        $this->assertEquals('EN', $freshUser->settings()->get('default_language'));
    }
}
