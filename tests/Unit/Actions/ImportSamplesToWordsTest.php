<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\ImportSamplesToWords;
use App\Models\Sample;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

final class ImportSamplesToWordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_all_samples_for_new_user(): void
    {
        $user = User::factory()->create();

        Sample::factory()->count(3)->create([
            'language' => 'EN',
        ]);

        $action = resolve(ImportSamplesToWords::class);
        $createdWords = $action->handle($user, 'EN');

        $this->assertCount(3, $createdWords);
        $this->assertCount(3, Word::where('user_id', $user->id)->where('language', 'EN')->get());

        foreach ($createdWords as $word) {
            $this->assertTrue($word->from_sample);
            $this->assertFalse($word->starred);
            $this->assertEquals(0, $word->views);
        }
    }

    public function test_skips_existing_words_for_user(): void
    {
        $user = User::factory()->create();

        Sample::factory()->create([
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        Sample::factory()->create([
            'original' => 'world',
            'translated' => 'мир',
            'language' => 'EN',
        ]);

        // Создаём одно слово у пользователя заранее
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
            'from_sample' => false,
        ]);

        $action = resolve(ImportSamplesToWords::class);
        $createdWords = $action->handle($user, 'EN');

        $this->assertCount(1, $createdWords);
        $firstWord = $createdWords->first();
        $this->assertNotNull($firstWord);
        $this->assertEquals('world', $firstWord->original);

        // Проверяем, что всего у пользователя 2 слова
        $userWords = Word::where('user_id', $user->id)->where('language', 'EN')->get();
        $this->assertCount(2, $userWords);

        // Проверяем, что существующее слово не изменилось
        $existingWord = Word::where('user_id', $user->id)
            ->where('original', 'hello')
            ->where('language', 'EN')
            ->first();

        $this->assertNotNull($existingWord);
        $this->assertFalse($existingWord->from_sample);
    }

    public function test_only_imports_samples_for_specified_language(): void
    {
        $user = User::factory()->create();

        Sample::factory()->create(['original' => 'hello', 'translated' => 'привет', 'language' => 'EN']);
        Sample::factory()->create(['original' => 'world', 'translated' => 'мир', 'language' => 'EN']);
        Sample::factory()->create(['original' => 'hallo', 'translated' => 'привет', 'language' => 'DE']);
        Sample::factory()->create(['original' => 'welt', 'translated' => 'мир', 'language' => 'DE']);
        Sample::factory()->create(['original' => 'tschüss', 'translated' => 'пока', 'language' => 'DE']);

        $action = resolve(ImportSamplesToWords::class);
        $createdWords = $action->handle($user, 'EN');

        $this->assertCount(2, $createdWords);
        $this->assertCount(2, Word::where('user_id', $user->id)->where('language', 'EN')->get());
        $this->assertCount(0, Word::where('user_id', $user->id)->where('language', 'DE')->get());
    }

    public function test_creates_words_with_correct_attributes(): void
    {
        $user = User::factory()->create();

        $sample = Sample::factory()->create([
            'original' => 'test',
            'translated' => 'тест',
            'language' => 'EN',
        ]);

        $action = resolve(ImportSamplesToWords::class);
        $createdWords = $action->handle($user, 'EN');

        $word = $createdWords->first();

        $this->assertNotNull($word);
        $this->assertEquals($sample->original, $word->original);
        $this->assertEquals($sample->translated, $word->translated);
        $this->assertEquals($sample->language, $word->language);
        $this->assertEquals($user->id, $word->user_id);
        $this->assertTrue($word->from_sample);
        $this->assertFalse($word->starred);
        $this->assertEquals(0, $word->views);
    }

    public function test_returns_empty_collection_when_no_samples_exist(): void
    {
        $user = User::factory()->create();
        $this->expectException(NotFoundHttpException::class);

        $action = resolve(ImportSamplesToWords::class);
        $createdWords = $action->handle($user, 'FR');

        $this->assertCount(0, $createdWords);
        $this->assertCount(0, Word::where('user_id', $user->id)->get());
    }

    public function test_respects_case_sensitivity_in_language(): void
    {
        $user = User::factory()->create();

        Sample::factory()->count(2)->create(['language' => 'EN']);
        $this->expectException(NotFoundHttpException::class);

        $action = resolve(ImportSamplesToWords::class);

        // С заглавной буквой
        $createdWordsUpper = $action->handle($user, 'EN');
        $this->assertCount(2, $createdWordsUpper);

        // С маленькой буквой - ничего не должно создаться
        $user2 = User::factory()->create();
        $createdWordsLower = $action->handle($user2, 'en');
        $this->assertCount(0, $createdWordsLower);
    }
}
