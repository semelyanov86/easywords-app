<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetUserWords;
use App\Data\UserSettingsData;
use App\Models\User;
use App\Models\Word;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetUserWordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_words_filtered_by_language(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
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
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertCount(1, $resultArray);
        $this->assertEquals('DE', $resultArray[0]->language);
    }

    public function test_excludes_known_words_when_known_enabled_is_false(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => CarbonImmutable::now(),
        ]);

        $knownWord = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => null,
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: false,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertCount(1, $resultArray);
        $this->assertNull($resultArray[0]->done_at);
        $this->assertEquals($knownWord->original, $resultArray[0]->original);
    }

    public function test_includes_known_words_when_known_enabled_is_true(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'done_at' => CarbonImmutable::now(),
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
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
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        $this->assertCount(2, $result);
    }

    public function test_excludes_imported_words_when_show_imported_is_false(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'from_sample' => true,
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'from_sample' => false,
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: false,
            languages_list: ['DE', 'EN'],
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertCount(1, $resultArray);
        $this->assertFalse($resultArray[0]->from_sample);
    }

    public function test_excludes_shared_words_when_show_shared_is_false(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'shared_by' => $otherUser->id,
        ]);

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'shared_by' => null,
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
            starred_enabled: false,
            default_language: 'DE',
            show_shared: false,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertCount(1, $resultArray);
        // Проверяем в базе данных, так как shared_by не включено в WordData
        $word = Word::where('id', $resultArray[0]->id)->first();
        $this->assertNotNull($word);
        $this->assertNull($word->shared_by);
    }

    public function test_sorts_by_created_at_desc_when_latest_first_is_true(): void
    {
        $user = User::factory()->create();

        $word1 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'created_at' => CarbonImmutable::now()->subDays(2),
        ]);

        $word2 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'created_at' => CarbonImmutable::now()->subDays(1),
        ]);

        $word3 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'created_at' => CarbonImmutable::now(),
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
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        $this->assertCount(3, $result);

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertEquals($word3->id, $resultArray[0]->id);
        $this->assertEquals($word2->id, $resultArray[1]->id);
        $this->assertEquals($word1->id, $resultArray[2]->id);
    }

    public function test_sorts_by_created_at_asc_when_latest_first_is_false(): void
    {
        $user = User::factory()->create();

        $word1 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'created_at' => CarbonImmutable::now()->subDays(2),
        ]);

        $word2 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'created_at' => CarbonImmutable::now()->subDays(1),
        ]);

        $word3 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'created_at' => CarbonImmutable::now(),
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: true,
            show_starred: true,
            latest_first: false,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        $this->assertCount(3, $result);

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertEquals($word1->id, $resultArray[0]->id);
        $this->assertEquals($word2->id, $resultArray[1]->id);
        $this->assertEquals($word3->id, $resultArray[2]->id);
    }

    public function test_sorts_by_views_when_fresh_first_is_false(): void
    {
        $user = User::factory()->create();

        $word1 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'views' => 10,
        ]);

        $word2 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'views' => 5,
        ]);

        $word3 = Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'DE',
            'views' => 15,
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 20,
            fresh_first: false,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        $this->assertCount(3, $result);

        /** @var array<int, \App\Data\WordData> $resultArray */
        $resultArray = $result->all();
        $this->assertEquals($word2->id, $resultArray[0]->id);
        $this->assertEquals($word1->id, $resultArray[1]->id);
        $this->assertEquals($word3->id, $resultArray[2]->id);
    }

    public function test_respects_paginate_setting(): void
    {
        $user = User::factory()->create();

        Word::factory()->count(25)->create([
            'user_id' => $user->id,
            'language' => 'DE',
        ]);

        $user->settings()->apply(new UserSettingsData(
            paginate: 10,
            fresh_first: true,
            show_starred: true,
            latest_first: true,
            known_enabled: true,
            main_language: 'RU',
            show_imported: true,
            languages_list: ['DE', 'EN'],
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        $this->assertCount(10, $result);
    }

    public function test_returns_empty_collection_when_no_words_match(): void
    {
        $user = User::factory()->create();

        Word::factory()->create([
            'user_id' => $user->id,
            'language' => 'EN',
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
            starred_enabled: false,
            default_language: 'DE',
            show_shared: true,
        )->toArray());

        $result = GetUserWords::make()->handle($user->id, 'DE');

        $this->assertCount(0, $result);
    }
}
