<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\StartStudySession;
use App\Http\Requests\NextWordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\User;
use Inertia\Response;

/**
 * Контроллер для запуска сессии изучения слов.
 *
 * Обрабатывает запрос на начало изучения слов с выбранным языком и режимом.
 * Вызывает StartStudySession Action и передает данные в Inertia-компонент Words/Show.
 */
final readonly class StartStudyController
{
    /**
     * Запускает сессию изучения слов.
     *
     * Получает параметры из запроса (язык и режим reverse), вызывает StartStudySession Action
     * и передает полученные данные в компонент Words/Show.
     */
    public function __invoke(
        NextWordRequest $request,
        StartStudySession $startStudySession,
    ): Response|RedirectResponse {
        /** @var User $user */
        $user = auth()->user();

        $settings = $user->getSettingsValue();
        /** @var int $limit */
        $limit = $settings['paginate'];
        try {
            $sessionData = $startStudySession->handle(
                userId: $user->id,
                language: $request->string('language')->toString(),
                limit: $limit,
                reverse: $request->boolean('reverse'),
            );
        } catch (\DomainException $e) {
            return back()->with('success', $e->getMessage());
        }

        return Inertia::render('Words/Show', [
            'word' => $sessionData['word'],
            'user' => $user,
            'meta' => [
                'total' => $sessionData['total'],
                'next_id' => $sessionData['next_id'],
                'prev_id' => $sessionData['prev_id'],
                'current_index' => $sessionData['current_index'],
                'language' => $request->string('language')->toString(),
                'reverse' => $request->boolean('reverse'),
                'main_language' => $settings['main_language'],
            ],
        ]);
    }
}
