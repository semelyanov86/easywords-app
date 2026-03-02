<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\WordExampleGenerator;
use App\Contracts\WordTranslator;
use App\Services\ClaudeConnector;
use App\Services\ClaudeTranslator;
use App\Services\OpenAiConnector;
use App\Services\OpenAiTranslator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(function (): WordExampleGenerator {
            /** @var string $provider */
            $provider = config('services.ai.provider', 'openai');

            return match ($provider) {
                'claude' => $this->app->make(ClaudeConnector::class),
                default => $this->app->make(OpenAiConnector::class),
            };
        });

        $this->app->bind(function (): WordTranslator {
            /** @var string $provider */
            $provider = config('services.ai.provider', 'openai');

            return match ($provider) {
                'claude' => $this->app->make(ClaudeTranslator::class),
                default => $this->app->make(OpenAiTranslator::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
