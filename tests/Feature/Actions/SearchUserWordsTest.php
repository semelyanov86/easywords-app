<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\SearchUserWords;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SearchUserWordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_searches_words_by_original_field(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'world',
            'translated' => 'мир',
        ]);

        $action = new SearchUserWords();

        // Act
        $results = $action->handle($user->id, 'hello');

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals('hello', $results[0]->original);
        $this->assertEquals('привет', $results[0]->translated);
    }

    public function test_searches_words_by_translated_field(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'goodbye',
            'translated' => 'до свидания',
        ]);

        $action = new SearchUserWords();

        // Act
        $results = $action->handle($user->id, 'привет');

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals('hello', $results[0]->original);
        $this->assertEquals('привет', $results[0]->translated);
    }

    public function test_returns_words_matching_in_both_fields(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'hi',
            'translated' => 'приветствие',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'greeting',
            'translated' => 'привет',
        ]);

        $action = new SearchUserWords();

        // Act - ищем "привет"
        $results = $action->handle($user->id, 'привет');

        // Assert
        $this->assertCount(3, $results);
    }

    public function test_performs_partial_match_search(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'приветствие',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'help',
            'translated' => 'помощь',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'world',
            'translated' => 'мир',
        ]);

        $action = new SearchUserWords();

        // Act - ищем частичное совпадение "hel"
        $results = $action->handle($user->id, 'hel');

        // Assert
        $this->assertCount(2, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals('hello', $results[0]->original);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[1]);
        $this->assertEquals('help', $results[1]->original);
    }

    public function test_searches_only_within_user_scope(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Word::factory()->for($user1)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->for($user2)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);

        $action = new SearchUserWords();

        // Act - ищем слова пользователя 1
        $resultsUser1 = $action->handle($user1->id, 'hello');

        // Act - ищем слова пользователя 2
        $resultsUser2 = $action->handle($user2->id, 'hello');

        // Assert
        $this->assertCount(1, $resultsUser1);
        $this->assertInstanceOf(\App\Data\WordData::class, $resultsUser1[0]);
        $this->assertEquals($user1->id, $resultsUser1[0]->user_id);

        $this->assertCount(1, $resultsUser2);
        $this->assertInstanceOf(\App\Data\WordData::class, $resultsUser2[0]);
        $this->assertEquals($user2->id, $resultsUser2[0]->user_id);
    }

    public function test_returns_empty_array_when_no_matches_found(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);

        $action = new SearchUserWords();

        // Act
        $results = $action->handle($user->id, 'nonexistent');

        // Assert
        $this->assertEmpty($results);
    }

    public function test_results_are_sorted_alphabetically_by_original(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'zebra',
            'translated' => 'зебра',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'apple',
            'translated' => 'яблоко',
        ]);
        Word::factory()->for($user)->create([
            'original' => 'banana',
            'translated' => 'банан',
        ]);

        $action = new SearchUserWords();

        // Act - ищем все слова с пустым запросом
        $results = $action->handle($user->id, '');

        // Assert - проверяем сортировку
        $this->assertCount(3, $results);
        $this->assertEquals('apple', $results[0]->original);
        $this->assertEquals('banana', $results[1]->original);
        $this->assertEquals('zebra', $results[2]->original);
    }

    public function test_returns_word_data_objects(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'привет',
            'language' => 'en',
        ]);

        $action = new SearchUserWords();

        // Act
        $results = $action->handle($user->id, 'hello');

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals('hello', $results[0]->original);
        $this->assertEquals('привет', $results[0]->translated);
        $this->assertEquals('en', $results[0]->language);
    }

    public function test_handles_special_characters_in_query(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'café',
            'translated' => 'кафе',
        ]);

        $action = new SearchUserWords();

        // Act
        $results = $action->handle($user->id, 'café');

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals('café', $results[0]->original);
    }

    public function test_searches_with_unicode_characters(): void
    {
        // Arrange
        $user = User::factory()->create();
        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'こんにちは',
        ]);

        $action = new SearchUserWords();

        // Act
        $results = $action->handle($user->id, 'こんに');

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals('こんにちは', $results[0]->translated);
    }

    public function test_does_not_return_words_from_other_users(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Word::factory()->for($user)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->for($otherUser)->create([
            'original' => 'hello',
            'translated' => 'привет',
        ]);
        Word::factory()->for($otherUser)->create([
            'original' => 'help',
            'translated' => 'помощь',
        ]);

        $action = new SearchUserWords();

        // Act - ищем слова текущего пользователя
        $results = $action->handle($user->id, 'hello');

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(\App\Data\WordData::class, $results[0]);
        $this->assertEquals($user->id, $results[0]->user_id);
    }
}
