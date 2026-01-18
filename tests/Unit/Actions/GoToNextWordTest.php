<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GoToNextWord;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class GoToNextWordTest extends TestCase
{
    use RefreshDatabase;

    private const string LANGUAGE = 'en';

    private User $user;

    private GoToNextWord $action;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->action = resolve(GoToNextWord::class);
    }

    public function test_goes_to_next_word(): void
    {
        $words = Word::factory()->count(3)->for($this->user)->create();
        $wordIds = $words->pluck('id')->toArray();

        // Устанавливаем состояние сессии
        Cache::put('words.start.' . self::LANGUAGE . ".{$this->user->id}", $wordIds);
        Cache::put('words.current.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[0]);
        Cache::put('words.next.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[1]);
        Cache::put('words.prev.' . self::LANGUAGE . ".{$this->user->id}", null);

        $result = $this->action->handle($this->user, self::LANGUAGE);

        $this->assertEquals($wordIds[1], $result['word']->id);
        $this->assertEquals(3, $result['meta']['total']);
        $this->assertEquals($wordIds[2], $result['meta']['next_id']);
        $this->assertEquals($wordIds[0], $result['meta']['prev_id']);
        $this->assertEquals(2, $result['meta']['current_index']);
    }

    public function test_reverses_word_fields_when_reverse_is_true(): void
    {
        $words = Word::factory()->count(2)->for($this->user)->create([
            'original' => 'cat',
            'translated' => 'кошка',
        ]);
        $wordIds = $words->pluck('id')->toArray();

        Cache::put('words.start.' . self::LANGUAGE . ".{$this->user->id}", $wordIds);
        Cache::put('words.current.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[0]);
        Cache::put('words.next.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[1]);
        Cache::put('words.prev.' . self::LANGUAGE . ".{$this->user->id}", null);

        $result = $this->action->handle($this->user, self::LANGUAGE, reverse: true);

        $this->assertEquals('кошка', $result['word']->original);
        $this->assertEquals('cat', $result['word']->translated);
    }

    public function test_does_not_reverse_when_reverse_is_false(): void
    {
        $words = Word::factory()->count(2)->for($this->user)->create([
            'original' => 'cat',
            'translated' => 'кошка',
        ]);
        $wordIds = $words->pluck('id')->toArray();

        Cache::put('words.start.' . self::LANGUAGE . ".{$this->user->id}", $wordIds);
        Cache::put('words.current.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[0]);
        Cache::put('words.next.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[1]);
        Cache::put('words.prev.' . self::LANGUAGE . ".{$this->user->id}", null);

        $result = $this->action->handle($this->user, self::LANGUAGE, reverse: false);

        $this->assertEquals('cat', $result['word']->original);
        $this->assertEquals('кошка', $result['word']->translated);
    }

    public function test_sets_null_next_when_at_end(): void
    {
        $words = Word::factory()->count(2)->for($this->user)->create();
        $wordIds = $words->pluck('id')->toArray();

        // Устанавливаем состояние сессии на последнее слово
        Cache::put('words.start.' . self::LANGUAGE . ".{$this->user->id}", $wordIds);
        Cache::put('words.current.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[0]);
        Cache::put('words.next.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[1]);
        Cache::put('words.prev.' . self::LANGUAGE . ".{$this->user->id}", null);

        $result = $this->action->handle($this->user, self::LANGUAGE);

        $this->assertEquals($wordIds[1], $result['word']->id);
        $this->assertNull($result['meta']['next_id']);
    }

    public function test_throws_exception_when_no_next_word(): void
    {
        $words = Word::factory()->count(2)->for($this->user)->create();
        $wordIds = $words->pluck('id')->toArray();

        // Устанавливаем состояние сессии без следующего слова
        Cache::put('words.start.' . self::LANGUAGE . ".{$this->user->id}", $wordIds);
        Cache::put('words.current.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[1]);
        Cache::put('words.next.' . self::LANGUAGE . ".{$this->user->id}", null);
        Cache::put('words.prev.' . self::LANGUAGE . ".{$this->user->id}", $wordIds[0]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No next word available');

        $this->action->handle($this->user, self::LANGUAGE);
    }

    public function test_throws_exception_when_session_not_found(): void
    {
        $word = Word::factory()->for($this->user)->create();

        // Устанавливаем только следующее слово без сессии
        Cache::put('words.next.' . self::LANGUAGE . ".{$this->user->id}", $word->id);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Study session not found');

        $this->action->handle($this->user, self::LANGUAGE);
    }
}
