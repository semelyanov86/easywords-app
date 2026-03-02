<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class StatisticsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('statistics.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_statistics_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertOk();
    }

    public function test_statistics_renders_inertia_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page->component('statistics/index');
            });
    }

    public function test_statistics_shares_total_users_count(): void
    {
        User::factory()->count(5)->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page->where('statistics.total_users', 6);
            });
    }

    public function test_statistics_shares_word_counts(): void
    {
        $user = User::factory()->create();

        // Создаем слова пользователя
        Word::factory()->for($user)->create(['done_at' => null, 'starred' => false]);
        Word::factory()->for($user)->create(['done_at' => now(), 'starred' => false]);
        Word::factory()->for($user)->create(['done_at' => now(), 'starred' => false]);
        Word::factory()->for($user)->create(['done_at' => now(), 'starred' => false]);

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page
                    ->where('statistics.total_words', 4)
                    ->where('statistics.starred_words', 0)
                    ->where('statistics.not_done_words', 1)
                    ->where('statistics.done_words', 3);
            });
    }

    public function test_statistics_shares_views_counts(): void
    {
        $this->travelTo(now()->setDay(15));

        $user = User::factory()->create();

        Word::factory()
            ->for($user)
            ->create(['views' => 10, 'updated_at' => today()]);
        Word::factory()
            ->for($user)
            ->create(['views' => 5, 'updated_at' => now()->subDays(5)]);
        Word::factory()
            ->for($user)
            ->create(['views' => 3, 'updated_at' => now()->startOfMonth()]);

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page
                    ->where('statistics.total_views', 18)
                    ->where('statistics.words_updated_today', 1)
                    ->where('statistics.words_updated_this_month', 3);
            });
    }

    public function test_statistics_shares_streak_days(): void
    {
        $user = User::factory()->create();

        Word::factory()->for($user)->create(['updated_at' => now()]);
        Word::factory()->for($user)->create(['updated_at' => now()->subDay()]);
        Word::factory()->for($user)->create(['updated_at' => now()->subDays(2)]);

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page->where('statistics.streak_days', 3);
            });
    }

    public function test_statistics_shares_top_viewed_words(): void
    {
        $user = User::factory()->create();

        $word1 = Word::factory()->for($user)->create([
            'original' => 'apple',
            'translated' => 'яблоко',
            'views' => 100,
        ]);

        $word2 = Word::factory()->for($user)->create([
            'original' => 'banana',
            'translated' => 'банан',
            'views' => 50,
        ]);

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page) use ($word1, $word2): void {
                $page
                    ->has('statistics.top_viewed_words', 2)
                    ->where('statistics.top_viewed_words.0.id', $word1->id)
                    ->where('statistics.top_viewed_words.0.original', 'apple')
                    ->where('statistics.top_viewed_words.0.translated', 'яблоко')
                    ->where('statistics.top_viewed_words.1.id', $word2->id)
                    ->where('statistics.top_viewed_words.1.original', 'banana')
                    ->where('statistics.top_viewed_words.1.translated', 'банан');
            });
    }

    public function test_statistics_shares_words_added_today(): void
    {
        $user = User::factory()->create();

        $word = Word::factory()->for($user)->create([
            'original' => 'today',
            'translated' => 'сегодня',
            'created_at' => today(),
        ]);

        Word::factory()->for($user)->create([
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page) use ($word): void {
                $page
                    ->has('statistics.words_added_today', 1)
                    ->where('statistics.words_added_today.0.id', $word->id)
                    ->where('statistics.words_added_today.0.original', 'today')
                    ->where('statistics.words_added_today.0.translated', 'сегодня');
            });
    }

    public function test_statistics_calculates_progress_percent(): void
    {
        $user = User::factory()->create();

        // Создаем 25 обновленных слов сегодня (50% от дневной цели)
        Word::factory()->for($user)->count(25)->create([
            'updated_at' => today(),
        ]);

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page->where('statistics.progress_today_percent', 50);
            });
    }

    public function test_statistics_shows_empty_arrays_when_no_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('statistics.index'))
            ->assertInertia(function (AssertableInertia $page): void {
                $page
                    ->where('statistics.total_words', 0)
                    ->where('statistics.top_viewed_words', [])
                    ->where('statistics.words_added_today', [])
                    ->where('statistics.streak_days', 0);
            });
    }
}
