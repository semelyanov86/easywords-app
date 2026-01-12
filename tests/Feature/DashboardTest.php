<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_renders_inertia_component(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->component('dashboard');
        };

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia($callback);
    }

    public function test_dashboard_shares_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $callback = function (AssertableInertia $page) use ($user): void {
            $page
                ->where('user.id', $user->id)
                ->where('user.name', 'John Doe')
                ->where('user.email', 'john@example.com');
        };

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia($callback);
    }

    public function test_dashboard_shares_user_settings(): void
    {
        $user = User::factory()->create([
            'settings' => [
                'main_language' => 'RU',
                'languages_list' => ['EN', 'DE'],
                'paginate' => 20,
                'fresh_first' => true,
                'show_starred' => true,
                'latest_first' => false,
                'known_enabled' => false,
                'show_imported' => true,
                'starred_enabled' => false,
                'default_language' => 'DE',
            ],
        ]);

        $callback = function (AssertableInertia $page): void {
            $page
                ->has('settings')
                ->where('settings.main_language', 'RU')
                ->where('settings.languages_list', ['EN', 'DE']);
        };

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia($callback);
    }
}
