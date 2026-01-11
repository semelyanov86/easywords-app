<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetUserStatistics;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetUserStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_user_statistics(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        // Создаем слова пользователя
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
            'done_at' => now(),
            'views' => 10,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
            'done_at' => null,
            'views' => 5,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
            'done_at' => now(),
            'views' => 15,
        ]);

        $statistics = $action->handle($user);

        $this->assertEquals(3, $statistics->total_words);
        $this->assertEquals(2, $statistics->starred_words);
        $this->assertEquals(1, $statistics->not_done_words);
        $this->assertEquals(2, $statistics->done_words);
        $this->assertEquals(30, $statistics->total_views);
    }

    public function test_returns_total_users(): void
    {
        User::factory()->count(5)->create();
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        $statistics = $action->handle($user);

        $this->assertEquals(6, $statistics->total_users);
    }

    public function test_returns_top_viewed_words(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        $word1 = Word::factory()->create(['user_id' => $user->id, 'views' => 100]);
        $word2 = Word::factory()->create(['user_id' => $user->id, 'views' => 80]);
        $word3 = Word::factory()->create(['user_id' => $user->id, 'views' => 60]);
        Word::factory()->create(['user_id' => $user->id, 'views' => 40]);
        Word::factory()->create(['user_id' => $user->id, 'views' => 20]);

        $statistics = $action->handle($user);

        $this->assertCount(5, $statistics->top_viewed_words);
        $this->assertEquals((string) $word1->id, $statistics->top_viewed_words[0]->id);
        $this->assertEquals((string) $word2->id, $statistics->top_viewed_words[1]->id);
        $this->assertEquals((string) $word3->id, $statistics->top_viewed_words[2]->id);
    }

    public function test_top_viewed_words_limits_to_10(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->count(15)->create(['user_id' => $user->id, 'views' => 10]);

        $statistics = $action->handle($user);

        $this->assertCount(10, $statistics->top_viewed_words);
    }

    public function test_returns_words_added_today(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        $wordToday1 = Word::factory()->create(['user_id' => $user->id, 'created_at' => now()]);
        $wordToday2 = Word::factory()->create(['user_id' => $user->id, 'created_at' => now()->subHours(2)]);
        Word::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(2)]);
        Word::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(5)]);

        $statistics = $action->handle($user);

        $this->assertCount(2, $statistics->words_added_today);
        $wordIds = collect($statistics->words_added_today)->pluck('id')->toArray();
        $this->assertNotEmpty($wordIds);
        $this->assertContains($wordToday1->id, $wordIds);
        $this->assertContains($wordToday2->id, $wordIds);
    }

    public function test_returns_words_updated_today(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        $wordUpdatedToday1 = Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);
        $wordUpdatedToday2 = Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subHours(1)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(2)]);

        $statistics = $action->handle($user);

        $this->assertEquals(2, $statistics->words_updated_today);
    }

    public function test_returns_words_updated_this_month(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(10)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(20)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subMonths(2)]);

        $statistics = $action->handle($user);

        $this->assertEquals(2, $statistics->words_updated_this_month);
    }

    public function test_handles_user_with_no_words(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        $statistics = $action->handle($user);

        $this->assertEquals(0, $statistics->total_words);
        $this->assertEquals(0, $statistics->starred_words);
        $this->assertEquals(0, $statistics->not_done_words);
        $this->assertEquals(0, $statistics->done_words);
        $this->assertEquals(0, $statistics->total_views);
        $this->assertEmpty($statistics->top_viewed_words);
        $this->assertEmpty($statistics->words_added_today);
        $this->assertEquals(0, $statistics->words_updated_today);
        $this->assertEquals(0, $statistics->words_updated_this_month);
    }
}
