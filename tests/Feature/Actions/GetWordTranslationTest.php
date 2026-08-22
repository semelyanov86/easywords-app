<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\GetWordTranslation;
use App\Contracts\WordTranslator;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetWordTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_translation_from_database_if_word_exists(): void
    {
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'language' => 'en',
            'translated' => 'привет',
        ]);

        Http::fake();

        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('translate');
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('hello', 'en');

        $this->assertEquals('привет', $result->translation);
        Http::assertNothingSent();
    }

    public function test_requests_translation_from_translator_if_word_not_found(): void
    {
        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate')
                ->once()
                ->with('testword', 'en')
                ->andReturn('тестовый перевод');
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('testword', 'en');

        $this->assertEquals('тестовый перевод', $result->translation);
    }

    public function test_removes_citation_marks_from_translation(): void
    {
        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate')
                ->once()
                ->andReturn('инвесторы[1][2][3]');
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('investors', 'en');

        $this->assertEquals('инвесторы', $result->translation);
    }

    public function test_removes_multiple_citation_marks_from_middle_of_text(): void
    {
        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate')
                ->once()
                ->andReturn('финансовые[1] инвесторы[2][3] и партнеры[10]');
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('investors', 'en');

        $this->assertEquals('финансовые инвесторы и партнеры', $result->translation);
    }

    public function test_truncates_long_translations_to_100_characters(): void
    {
        $longTranslation = str_repeat('очень длинный перевод ', 5);
        $this->assertGreaterThan(100, mb_strlen($longTranslation));

        $this->mock(WordTranslator::class, function (MockInterface $mock) use ($longTranslation): void {
            $mock->shouldReceive('translate')
                ->once()
                ->andReturn($longTranslation);
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('testword', 'en');

        $this->assertLessThanOrEqual(100, mb_strlen($result->translation));
        $this->assertEquals('...', mb_substr($result->translation, -3));
    }

    public function test_handles_exact_100_character_translation(): void
    {
        $exactTranslation = str_repeat('а', 100);

        $this->mock(WordTranslator::class, function (MockInterface $mock) use ($exactTranslation): void {
            $mock->shouldReceive('translate')
                ->once()
                ->andReturn($exactTranslation);
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('testword', 'en');

        $this->assertEquals(100, mb_strlen($result->translation));
        $this->assertEquals($exactTranslation, $result->translation);
    }

    public function test_trims_whitespace_is_handled_by_translator(): void
    {
        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate')
                ->once()
                ->andReturn('перевод с пробелами');
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('testword', 'en');

        $this->assertEquals('перевод с пробелами', $result->translation);
    }

    public function test_removes_citations_and_then_truncates_if_needed(): void
    {
        $longTranslation = str_repeat('слово[1] ', 20) . '[2][3][4]';

        $this->mock(WordTranslator::class, function (MockInterface $mock) use ($longTranslation): void {
            $mock->shouldReceive('translate')
                ->once()
                ->andReturn($longTranslation);
        });

        $action = resolve(GetWordTranslation::class);

        $result = $action->handle('testword', 'en');

        $this->assertLessThanOrEqual(100, mb_strlen($result->translation));
        $this->assertStringNotContainsString('[1]', $result->translation);
        $this->assertStringNotContainsString('[2]', $result->translation);
    }

    public function test_finds_word_by_case_sensitive_match(): void
    {
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'Hello',
            'language' => 'en',
            'translated' => 'Привет',
        ]);

        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('translate')
                ->once()
                ->with('hello', 'en')
                ->andReturn('другой перевод');
        });

        $action = resolve(GetWordTranslation::class);

        // Точное совпадение — из БД
        $result1 = $action->handle('Hello', 'en');
        $this->assertEquals('Привет', $result1->translation);

        // Разный регистр — через переводчик
        $result2 = $action->handle('hello', 'en');
        $this->assertEquals('другой перевод', $result2->translation);
    }

    public function test_matches_both_word_and_language(): void
    {
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'en',
            'translated' => 'тест (en)',
        ]);

        Word::factory()->for($user)->create([
            'original' => 'test',
            'language' => 'de',
            'translated' => 'тест (de)',
        ]);

        $this->mock(WordTranslator::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('translate');
        });

        $action = resolve(GetWordTranslation::class);

        $resultEn = $action->handle('test', 'en');
        $resultDe = $action->handle('test', 'de');

        $this->assertEquals('тест (en)', $resultEn->translation);
        $this->assertEquals('тест (de)', $resultDe->translation);
    }
}
