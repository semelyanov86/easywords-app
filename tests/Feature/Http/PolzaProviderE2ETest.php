<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Сквозные тесты провайдера polza.ai через реальные HTTP-роуты.
 *
 * Провайдер выбирается через services.ai.provider=polza, а исходящий
 * запрос к polza.ai подменяется через Http::fake — проверяется весь путь
 * route → controller → action → PolzaConnector → PolzaApiClient.
 */
final class PolzaProviderE2ETest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = 'https://polza.test/api/v1/chat/completions';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.ai.provider' => 'polza',
            'services.polza.url' => 'https://polza.test/api/v1',
            'services.polza.key' => 'test-key',
            'services.polza.models.translate' => 'google/gemini-2.5-flash-lite',
            'services.polza.models.examples' => 'openai/gpt-5-mini',
            'services.polza.models.image' => 'google/gemini-2.5-flash-lite',
            'services.polza.temperature.translate' => '0.3',
            'services.polza.temperature.examples' => null,
            'services.polza.temperature.image' => '0.2',
        ]);
    }

    public function test_translate_route_uses_polza_provider(): void
    {
        $this->actingAs(User::factory()->create());

        Http::fake([
            self::ENDPOINT => Http::response([
                'choices' => [['message' => ['content' => 'яблоко']]],
            ]),
        ]);

        $response = $this->getJson(route('words.translate', ['word' => 'Apfel', 'language' => 'de']));

        $response->assertOk();
        $response->assertJsonPath('data.attributes.translation', 'яблоко');

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'polza.test')
            && $request->data()['model'] === 'google/gemini-2.5-flash-lite');
    }

    public function test_examples_route_uses_polza_provider(): void
    {
        $user = User::factory()->create(['has_premium' => true]);
        $word = Word::factory()->for($user)->create([
            'language' => 'en',
            'example_original' => null,
            'example_translated' => null,
        ]);

        $this->actingAs($user);

        Http::fake([
            self::ENDPOINT => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'example_original' => ['Example 1', 'Example 2', 'Example 3'],
                    'example_translated' => ['Пример 1', 'Пример 2', 'Пример 3'],
                ])]]],
            ]),
        ]);

        $response = $this->get(route('words.examples', ['id' => $word->id]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Words/Examples')
                ->where('examples.original', ['Example 1', 'Example 2', 'Example 3'])
                ->where('examples.translated', ['Пример 1', 'Пример 2', 'Пример 3'])
        );

        $word->refresh();
        $this->assertSame(['Example 1', 'Example 2', 'Example 3'], $word->example_original);

        Http::assertSent(fn (Request $request): bool => $request->data()['model'] === 'openai/gpt-5-mini'
            && ! array_key_exists('temperature', $request->data()));
    }

    public function test_image_extraction_route_uses_polza_provider(): void
    {
        $this->actingAs(User::factory()->create(['has_premium' => true]));

        Http::fake([
            self::ENDPOINT => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'words' => [
                        ['original' => 'Haus', 'translation' => 'дом', 'language' => 'de'],
                    ],
                ])]]],
            ]),
        ]);

        $response = $this->post(route('words.extract-from-image.extract'), [
            'image' => UploadedFile::fake()->image('photo.jpg'),
            'language' => 'de',
        ]);

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Words/ExtractFromImage')
                ->has('words', 1)
                ->where('words.0.original', 'Haus')
                ->where('words.0.translation', 'дом')
        );

        Http::assertSent(fn (Request $request): bool => $request->data()['model'] === 'google/gemini-2.5-flash-lite'
            && str_contains($request->body(), 'image_url'));
    }
}
