<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Тесты для WordController.
 *
 * Проверяет функционал создания слов через web-интерфейс:
 * - Отображение формы создания слова
 * - Сохранение нового слова с валидацией
 * - Проверка авторизации и прав доступа
 * - Проверка передаваемых данных через Inertia
 */
final class WordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_on_create_word_page(): void
    {
        $this->get(route('words.create'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_create_word_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('words.create'))
            ->assertOk();
    }

    public function test_create_word_page_renders_inertia_component(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->component('Words/Create');
        };

        $this->actingAs($user)
            ->get(route('words.create'))
            ->assertInertia($callback);
    }

    public function test_create_word_page_shares_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $callback = function (AssertableInertia $page) use ($user): void {
            $page
                ->where('user.id', $user->id)
                ->where('user.name', 'Test User')
                ->where('user.email', 'test@example.com');
        };

        $this->actingAs($user)
            ->get(route('words.create'))
            ->assertInertia($callback);
    }

    public function test_create_word_page_shares_languages_list(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->has('languages_list')
                ->whereType('languages_list', 'array');
        };

        $this->actingAs($user)
            ->get(route('words.create'))
            ->assertInertia($callback);
    }

    public function test_guests_cannot_store_word(): void
    {
        $this->post(route('words.store'), [
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_store_word(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ]);

        $response->assertOk();
    }

    public function test_storing_word_creates_word_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ]);

        $this->assertDatabaseHas('words', [
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
            'views' => 0,
            'starred' => false,
            'from_sample' => false,
        ]);
    }

    public function test_stored_word_renders_create_page(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->component('Words/Create');
        };

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertInertia($callback);
    }

    public function test_store_response_includes_created_word_data(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page
                ->has('word')
                ->has('word.id')
                ->where('word.original', 'hello')
                ->where('word.translated', 'привет')
                ->where('word.language', 'EN');
        };

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertInertia($callback);
    }

    public function test_store_response_includes_user_data(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page) use ($user): void {
            $page->where('user.id', $user->id);
        };

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertInertia($callback);
    }

    public function test_store_response_includes_languages_list(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page->has('languages_list');
        };

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertInertia($callback);
    }

    public function test_stored_word_has_correct_initial_values(): void
    {
        $user = User::factory()->create();

        $callback = function (AssertableInertia $page): void {
            $page
                ->where('word.views', 0)
                ->where('word.starred', false)
                ->where('word.from_sample', false);
        };

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertInertia($callback);
    }

    public function test_store_word_with_different_languages(): void
    {
        $user = User::factory()->create();

        $languages = ['EN', 'DE', 'ES', 'FR', 'IT', 'RU'];

        foreach ($languages as $language) {
            $this->actingAs($user)
                ->post(route('words.store'), [
                    'original' => "word_{$language}",
                    'translated' => "translation_{$language}",
                    'language' => $language,
                ])
                ->assertOk();

            $this->assertDatabaseHas('words', [
                'user_id' => $user->id,
                'original' => "word_{$language}",
                'translated' => "translation_{$language}",
                'language' => $language,
            ]);
        }
    }

    public function test_store_validation_requires_original(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['original']);
    }

    public function test_store_validation_requires_translated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['translated']);
    }

    public function test_store_validation_requires_language(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
            ])
            ->assertSessionHasErrors(['language']);
    }

    public function test_store_validation_original_must_be_string(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 12345,
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['original']);
    }

    public function test_store_validation_translated_must_be_string(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 12345,
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['translated']);
    }

    public function test_store_validation_language_must_be_string(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 12345,
            ])
            ->assertSessionHasErrors(['language']);
    }

    public function test_store_validation_original_max_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => str_repeat('a', 256),
                'translated' => 'привет',
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['original']);
    }

    public function test_store_validation_translated_max_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => str_repeat('а', 256),
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['translated']);
    }

    public function test_store_validation_language_max_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'INVALID_LANGUAGE_CODE',
            ])
            ->assertSessionHasErrors(['language']);
    }

    public function test_store_validation_language_must_be_valid(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'привет',
                'language' => 'XX',
            ])
            ->assertSessionHasErrors(['language']);
    }

    public function test_store_validation_word_must_be_unique_per_user_and_language(): void
    {
        $user = User::factory()->create();

        // Создаем первое слово
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        // Пытаемся создать дубликат
        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'приветствие',
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['original']);
    }

    public function test_store_validation_allows_same_word_for_different_languages(): void
    {
        $user = User::factory()->create();

        // Создаем слово на английском
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        // Создаем то же слово на немецком (должно пройти)
        $response = $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'Hallo',
                'language' => 'DE',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('words', [
            'user_id' => $user->id,
            'original' => 'hello',
            'language' => 'DE',
        ]);
    }

    public function test_store_validation_allows_same_word_for_different_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Пользователь 1 создает слово
        Word::factory()->create([
            'user_id' => $user1->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        // Пользователь 2 может создать такое же слово
        $response = $this->actingAs($user2)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'Hi',
                'language' => 'EN',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('words', [
            'user_id' => $user2->id,
            'original' => 'hello',
            'language' => 'EN',
        ]);
    }

    public function test_store_validation_duplicate_word_message(): void
    {
        $user = User::factory()->create();

        // Создаем первое слово
        Word::factory()->create([
            'user_id' => $user->id,
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'EN',
        ]);

        // Пытаемся создать дубликат и проверяем сообщение об ошибке
        $this->actingAs($user)
            ->post(route('words.store'), [
                'original' => 'hello',
                'translated' => 'приветствие',
                'language' => 'EN',
            ])
            ->assertSessionHasErrors(['original' => 'Это слово уже есть в вашем словаре для выбранного языка.']);
    }
}
