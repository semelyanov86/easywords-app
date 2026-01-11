<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GetUserWordsWithFilters;
use App\Data\WordData;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetUserWordsWithFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_all_words_without_filters(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        Word::factory()->count(3)->create(['user_id' => $user->id]);

        $result = $action->handle($user->id, []);

        $this->assertCount(3, $result);
    }

    public function test_filters_by_done_true(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        $doneWord = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => now(),
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);

        $result = $action->handle($user->id, ['done' => 'true']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($doneWord->id, $firstWord->id);
    }

    public function test_filters_by_done_false(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => now(),
        ]);
        $notDoneWord = Word::factory()->create([
            'user_id' => $user->id,
            'done_at' => null,
        ]);

        $result = $action->handle($user->id, ['done' => 'false']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($notDoneWord->id, $firstWord->id);
    }

    public function test_filters_by_shared_true(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        $sharedWord = Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => 1,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => null,
        ]);

        $result = $action->handle($user->id, ['shared' => 'true']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($sharedWord->id, $firstWord->id);
    }

    public function test_filters_by_shared_false(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => 1,
        ]);
        $notSharedWord = Word::factory()->create([
            'user_id' => $user->id,
            'shared_by' => null,
        ]);

        $result = $action->handle($user->id, ['shared' => 'false']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($notSharedWord->id, $firstWord->id);
    }

    public function test_filters_by_from_sample_true(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        $sampleWord = Word::factory()->create([
            'user_id' => $user->id,
            'from_sample' => true,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'from_sample' => false,
        ]);

        $result = $action->handle($user->id, ['from_sample' => 'true']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($sampleWord->id, $firstWord->id);
    }

    public function test_filters_by_from_sample_false(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        Word::factory()->create([
            'user_id' => $user->id,
            'from_sample' => true,
        ]);
        $notSampleWord = Word::factory()->create([
            'user_id' => $user->id,
            'from_sample' => false,
        ]);

        $result = $action->handle($user->id, ['from_sample' => 'false']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($notSampleWord->id, $firstWord->id);
    }

    public function test_filters_by_starred_true(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        $starredWord = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
        ]);
        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
        ]);

        $result = $action->handle($user->id, ['starred' => 'true']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($starredWord->id, $firstWord->id);
    }

    public function test_filters_by_starred_false(): void
    {
        $user = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        Word::factory()->create([
            'user_id' => $user->id,
            'starred' => true,
        ]);
        $notStarredWord = Word::factory()->create([
            'user_id' => $user->id,
            'starred' => false,
        ]);

        $result = $action->handle($user->id, ['starred' => 'false']);

        $this->assertCount(1, $result);
        $items = $result->all();
        /** @var WordData $firstWord */
        $firstWord = $items[0];
        $this->assertEquals($notStarredWord->id, $firstWord->id);
    }

    public function test_returns_only_user_words(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $action = new GetUserWordsWithFilters();

        Word::factory()->count(3)->create(['user_id' => $user1->id]);
        Word::factory()->count(2)->create(['user_id' => $user2->id]);

        $result = $action->handle($user1->id, []);

        $this->assertCount(3, $result);
    }
}
