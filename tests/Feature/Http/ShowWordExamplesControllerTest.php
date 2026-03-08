<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Actions\GenerateWordExamples;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Inertia\Testing\AssertableInertia;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class ShowWordExamplesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_back_with_error_on_request_exception(): void
    {
        $user = User::factory()->create(['has_premium' => true]);
        $word = Word::factory()->for($user)->create([
            'example_original' => null,
            'example_translated' => null,
        ]);

        $this->actingAs($user);

        /** @var Response&MockInterface $mockResponse */
        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('status')->andReturn(500); // @phpstan-ignore method.notFound
        $mockResponse->shouldReceive('body')->andReturn('{"error":"claude did not return valid JSON"}'); // @phpstan-ignore method.notFound
        $mockResponse->shouldReceive('toPsrResponse')->andReturn(new \GuzzleHttp\Psr7\Response(500)); // @phpstan-ignore method.notFound

        $this->app->instance(
            GenerateWordExamples::class,
            Mockery::mock(GenerateWordExamples::class, function (MockInterface $mock) use ($word, $user, $mockResponse): void {
                /** @var GenerateWordExamples&MockInterface $mockInstance */
                $mockInstance = $mock;
                $mockInstance->shouldReceive('handle')
                    ->once() // @phpstan-ignore method.notFound
                    ->with($word->id, $user->id) // @phpstan-ignore method.nonObject
                    ->andThrow(new RequestException($mockResponse)); // @phpstan-ignore method.nonObject
            })
        );

        $response = $this->from(route('words.show', $word->id))
            ->get(route('words.examples', ['id' => $word->id]));

        $response->assertRedirect(route('words.show', $word->id));
        $response->assertSessionHas('error', 'Не удалось сгенерировать примеры. Сервис AI временно недоступен, попробуйте позже.');
    }

    public function test_redirects_back_with_error_on_runtime_exception(): void
    {
        $user = User::factory()->create(['has_premium' => true]);
        $word = Word::factory()->for($user)->create([
            'example_original' => null,
            'example_translated' => null,
        ]);

        $this->actingAs($user);

        $this->app->instance(
            GenerateWordExamples::class,
            Mockery::mock(GenerateWordExamples::class, function (MockInterface $mock) use ($word, $user): void {
                /** @var GenerateWordExamples&MockInterface $mockInstance */
                $mockInstance = $mock;
                $mockInstance->shouldReceive('handle')
                    ->once() // @phpstan-ignore method.notFound
                    ->with($word->id, $user->id) // @phpstan-ignore method.nonObject
                    ->andThrow(new \RuntimeException('Invalid examples structure')); // @phpstan-ignore method.nonObject
            })
        );

        $response = $this->from(route('words.show', $word->id))
            ->get(route('words.examples', ['id' => $word->id]));

        $response->assertRedirect(route('words.show', $word->id));
        $response->assertSessionHas('error', 'Не удалось сгенерировать примеры. Попробуйте ещё раз.');
    }

    public function test_renders_examples_page_successfully(): void
    {
        $user = User::factory()->create(['has_premium' => true]);
        $word = Word::factory()->for($user)->create([
            'example_original' => ['Example 1', 'Example 2', 'Example 3'],
            'example_translated' => ['Пример 1', 'Пример 2', 'Пример 3'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('words.examples', ['id' => $word->id]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Words/Examples')
                ->has('word')
                ->has('examples')
                ->where('word.id', $word->id)
                ->where('examples.original', ['Example 1', 'Example 2', 'Example 3'])
                ->where('examples.translated', ['Пример 1', 'Пример 2', 'Пример 3'])
        );
    }

    public function test_non_premium_user_gets_403(): void
    {
        $user = User::factory()->create(['has_premium' => false]);
        $word = Word::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->get(route('words.examples', ['id' => $word->id]));

        $response->assertStatus(403);
    }
}
