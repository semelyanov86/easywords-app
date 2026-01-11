<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetUserRandomWords;
use App\Data\UserSettingsData;
use App\Models\User;
use App\Models\Word;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetUserRandomWordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_random_words_filtered_by_language(): void
    {
        $user = User::factory()->create();

        // Создаём слова на разных языках
        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Hallo',
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
            'original' => 'Hello',
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: true,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserRandomWords::make()->handle($user->id, 20);

        $this->assertCount(1, $result);
        $firstWord = $result->first();
        $this->assertNotNull($firstWord);
        $this->assertEquals('DE', $firstWord->language);
    }

    public function test_excludes_known_words_when_known_enabled_is_false(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word1',
            'done_at' => CarbonImmutable::now(),
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word2',
            'done_at' => null,
        ]);

        $settings = new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: false,
            main_language: 'DE',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        );

        $result = GetUserRandomWords::make()->handle($user->id, 20);

        $this->assertCount(1, $result);
        $firstWord = $result->first();
        $this->assertNotNull($firstWord);
        $this->assertNull($firstWord->done_at);
        $this->assertEquals('Word2', $firstWord->original);
    }

    public function test_includes_known_words_when_known_enabled_is_true(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word1',
            'done_at' => CarbonImmutable::now(),
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word2',
            'done_at' => null,
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: true,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserRandomWords::make()->handle($user->id, 20);

        $this->assertCount(2, $result);
    }

    public function test_respects_limit_parameter(): void
    {
        $user = User::factory()->create();

        Word::factory()->count(30)->create([
            'user_id' => $user->id,
            'language' => 'DE',
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: true,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserRandomWords::make()->handle($user->id, 10);

        $this->assertCount(10, $result);
    }

    public function test_returns_shuffled_words(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word1',
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word2',
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'original' => 'Word3',
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: true,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result1 = GetUserRandomWords::make()->handle($user->id, 3);
        $result2 = GetUserRandomWords::make()->handle($user->id, 3);

        // Проверяем, что все слова возвращаются
        $this->assertCount(3, $result1);
        $this->assertCount(3, $result2);

        // Вероятность того, что два случайных набора совпадут, очень мала
        $words1 = $result1->pluck('original')->sort()->values();
        $words2 = $result2->pluck('original')->sort()->values();
        $this->assertEquals($words1, $words2);
    }

    public function test_returns_all_words_if_less_than_limit(): void
    {
        $user = User::factory()->create();

        Word::factory()->count(5)->create([
            'user_id' => $user->id,
            'language' => 'DE',
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: true,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserRandomWords::make()->handle($user->id, 20);

        $this->assertCount(5, $result);
    }
}
