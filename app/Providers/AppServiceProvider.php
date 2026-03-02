<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\WordExampleGenerator;
use App\Services\ClaudeConnector;
use App\Services\OpenAiConnector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(function (): \App\Contracts\WordExampleGenerator {
            /** @var string $provider */
            $provider = config('services.ai.provider', 'openai');

            return match ($provider) {
                'claude' => $this->app->make(ClaudeConnector::class),
                default => $this->app->make(OpenAiConnector::class),
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
