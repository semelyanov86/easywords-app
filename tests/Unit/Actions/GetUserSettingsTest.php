<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetUserSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetUserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_user_settings_data_object(): void
    {
        $user = User::factory()->create();

        $result = GetUserSettings::make()->handle($user->id);

        $this->assertEquals(20, $result->paginate);
        $this->assertTrue($result->fresh_first);
        $this->assertTrue($result->show_starred);
        $this->assertEquals('DE', $result->default_language);
    }

    public function test_returns_correct_default_settings_for_new_user(): void
    {
        $user = User::factory()->create();

        $result = GetUserSettings::make()->handle($user->id);

        $this->assertEquals(20, $result->paginate);
        $this->assertTrue($result->fresh_first);
        $this->assertTrue($result->show_starred);
        $this->assertEquals('RU', $result->main_language);
        $this->assertEquals('DE', $result->default_language);
        $this->assertContains('DE', $result->languages_list);
        $this->assertContains('EN', $result->languages_list);
    }

    public function test_throws_exception_for_nonexistent_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        GetUserSettings::make()->handle(99999);
    }

    public function test_returns_updated_settings(): void
    {
        $user = User::factory()->create();
        $user->settings()->set('paginate', 50);
        $user->settings()->set('default_language', 'EN');
        $user->save();

        $result = GetUserSettings::make()->handle($user->id);

        $this->assertEquals(50, $result->paginate);
        $this->assertEquals('EN', $result->default_language);
    }
}
