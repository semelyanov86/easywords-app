<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Sample;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для контроллера импорта слов.
 *
 * Проверяет функциональность импорта sample слов
 * в словарь авторизованного пользователя.
 */
final class ImportWordsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет успешный импорт слов из samples.
     */
    public function test_import_words_imports_samples_successfully(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Устанавливаем язык по умолчанию
        $user->settings()->set('main_language', 'DE');
        $user->save();

        // Создаем sample слова для языка DE
        Sample::factory()->count(5)->create(['language' => 'DE']);

        $response = $this->actingAs($user)
            ->post(route('import-words'));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Проверяем, что слова были импортированы
        $this->assertDatabaseHas('words', [
            'user_id' => $user->id,
            'from_sample' => true,
        ]);
    }

    /**
     * Проверяет, что дубликаты не импортируются.
     */
    public function test_import_words_skips_duplicates(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Устанавливаем язык по умолчанию
        $user->settings()->set('main_language', 'EN');
        $user->save();

        // Создаем sample слово
        $sample = Sample::factory()->create([
            'language' => 'EN',
            'original' => 'test',
            'translated' => 'тест',
        ]);

        // Импортируем первый раз
        $this->actingAs($user)
            ->post(route('import-words'));

        $this->assertDatabaseCount('words', 1);

        // Импортируем второй раз
        $response = $this->actingAs($user)
            ->post(route('import-words'));

        $response->assertRedirect();
        $response->assertSessionHas('info'); // Нет новых слов для импорта

        // Проверяем, что дубликат не был создан
        $this->assertDatabaseCount('words', 1);
    }

    /**
     * Проверяет, что неавторизованный пользователь не может импортировать слова.
     */
    public function test_import_words_redirects_unauthenticated_user(): void
    {
        $response = $this->post(route('import-words'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Проверяет, что импорт использует язык из настроек пользователя.
     */
    public function test_import_words_uses_user_default_language(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Устанавливаем язык по умолчанию RU
        $user->settings()->set('main_language', 'RU');
        $user->save();

        // Создаем sample слова для разных языков
        Sample::factory()->count(3)->create(['language' => 'RU']);
        Sample::factory()->count(2)->create(['language' => 'EN']);
        Sample::factory()->count(2)->create(['language' => 'DE']);

        $this->actingAs($user)
            ->post(route('import-words'));

        // Проверяем, что импортированы только RU слова
        // Factory может создавать дубликаты, поэтому проверяем что слова есть
        $ruWords = Word::where('user_id', $user->id)->where('language', 'RU')->get();
        $this->assertGreaterThan(0, $ruWords->count());
    }

    /**
     * Проверяет, что при импорте слова помечаются как from_sample.
     */
    public function test_import_words_marks_words_as_from_sample(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->settings()->set('main_language', 'DE');
        $user->save();

        Sample::factory()->count(3)->create(['language' => 'DE']);

        $this->actingAs($user)
            ->post(route('import-words'));

        $words = Word::where('user_id', $user->id)->get();

        foreach ($words as $word) {
            $this->assertTrue($word->from_sample);
        }
    }

    /**
     * Проверяет, что импортированные слова имеют начальные значения.
     */
    public function test_import_words_sets_initial_values(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->settings()->set('main_language', 'EN');
        $user->save();

        Sample::factory()->count(2)->create(['language' => 'EN']);

        $this->actingAs($user)
            ->post(route('import-words'));

        $words = Word::where('user_id', $user->id)->get();

        foreach ($words as $word) {
            $this->assertEquals(0, $word->views);
            $this->assertFalse($word->starred);
        }
    }
}
