<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

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

    public function test_calculates_progress_today_percent(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->count(10)->create([
            'user_id' => $user->id,
            'updated_at' => now(),
        ]);

        $statistics = $action->handle($user);

        $this->assertEquals(20, $statistics->progress_today_percent);
    }

    public function test_progress_today_percent_caps_at_100(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->count(60)->create([
            'user_id' => $user->id,
            'updated_at' => now(),
        ]);

        $statistics = $action->handle($user);

        $this->assertEquals(100, $statistics->progress_today_percent);
    }

    public function test_calculates_progress_today_percent_correctly(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->count(25)->create([
            'user_id' => $user->id,
            'updated_at' => now(),
        ]);

        $statistics = $action->handle($user);

        $this->assertEquals(50, $statistics->progress_today_percent);
    }

    public function test_calculates_streak_days_correctly(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->create(['user_id' => $user->id, 'updated_at' => today()]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => today()->subDay()]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => today()->subDays(2)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(5)]);

        $statistics = $action->handle($user);

        $this->assertEquals(3, $statistics->streak_days);
    }

    public function test_streak_days_returns_zero_when_no_updates_today(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(2)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(3)]);

        $statistics = $action->handle($user);

        $this->assertEquals(0, $statistics->streak_days);
    }

    public function test_streak_days_calculates_correctly_for_consecutive_days(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDay()]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(2)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(5)]);

        $statistics = $action->handle($user);

        $this->assertEquals(3, $statistics->streak_days);
    }

    public function test_streak_days_is_zero_when_no_words(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        $statistics = $action->handle($user);

        $this->assertEquals(0, $statistics->streak_days);
    }

    public function test_streak_days_is_zero_when_last_update_not_today_or_yesterday(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(3)]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(4)]);

        $statistics = $action->handle($user);

        $this->assertEquals(0, $statistics->streak_days);
    }

    public function test_streak_days_counts_only_today(): void
    {
        $user = User::factory()->create();
        $action = new GetUserStatistics();

        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);
        Word::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDays(5)]);

        $statistics = $action->handle($user);

        $this->assertEquals(1, $statistics->streak_days);
    }
}
