<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Smoke coverage for the Filament admin panel.
 *
 * The panel has no other test coverage, so these tests guard the routes and
 * the Users resource schema against breakage during Filament upgrades.
 */
final class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminPageProvider(): array
    {
        return [
            'dashboard' => ['/admin'],
            'users index' => ['/admin/users'],
            'users create' => ['/admin/users/create'],
        ];
    }

    public function test_guests_are_redirected_to_the_panel_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_panel_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_non_admin_users_cannot_access_the_panel(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->get('/admin')->assertForbidden();
    }

    #[DataProvider('adminPageProvider')]
    public function test_admin_users_can_open_panel_pages(string $path): void
    {
        $this->actingAs($this->admin());

        $this->get($path)->assertOk();
    }

    public function test_admin_users_can_open_the_user_view_and_edit_pages(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create();

        $this->get("/admin/users/{$user->id}")->assertOk();
        $this->get("/admin/users/{$user->id}/edit")->assertOk();
    }

    public function test_users_table_lists_records(): void
    {
        $this->actingAs($this->admin());
        $users = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($users);
    }

    public function test_users_table_can_be_searched_by_email(): void
    {
        $this->actingAs($this->admin());
        $needle = User::factory()->create();
        $others = User::factory()->count(2)->create();

        Livewire::test(ListUsers::class)
            ->searchTable($needle->email)
            ->assertCanSeeTableRecords([$needle])
            ->assertCanNotSeeTableRecords($others);
    }

    public function test_a_user_can_be_created_through_the_panel(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Created By Admin',
                'email' => 'created-by-admin@example.com',
                'password' => 'password',
                'settings.paginate' => 20,
                'settings.main_language' => 'RU',
                'settings.default_language' => 'DE',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(User::class, [
            'email' => 'created-by-admin@example.com',
            'name' => 'Created By Admin',
        ]);
    }

    public function test_creating_a_user_validates_required_fields(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => null,
                'email' => 'not-an-email',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'email',
            ]);
    }

    public function test_a_user_can_be_edited_through_the_panel(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create(['name' => 'Before']);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm(['name' => 'After', 'password' => 'password'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'name' => 'After',
        ]);
    }

    /**
     * Documents current behaviour, not desired behaviour: UserForm marks the
     * password field required() for every operation, so saving the edit form
     * without retyping a password fails validation.
     */
    public function test_editing_a_user_currently_requires_retyping_the_password(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create(['name' => 'Before']);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm(['name' => 'After'])
            ->call('save')
            ->assertHasFormErrors(['password' => 'required']);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}
