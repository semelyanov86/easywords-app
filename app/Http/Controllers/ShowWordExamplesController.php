<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateWordExamples;
use App\Models\User;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для отображения AI-генерируемых примеров использования слова.
 *
 * Создан для отдельной страницы с примерами, чтобы не перегружать основную страницу изучения.
 * Примеры генерируются через OpenAI только для премиум-пользователей.
 */
final class ShowWordExamplesController
{
    /**
     * Отображает страницу с примерами использования слова.
     *
     * Генерирует примеры через OpenAI (если их нет) и отображает их на отдельной странице.
     * Требует авторизации и премиум-подписки.
     *
     * @param  Request  $request  HTTP-запрос
     * @param  int  $word  ID слова
     * @return Response Inertia-ответ с примерами
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException если пользователь не премиум
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException если слово не найдено
     */
    public function __invoke(Request $request, int $word): Response
    {
        /** @var User $user */
        $user = auth()->user();

        // Проверка премиум-подписки
        if (! $user->has_premium) {
            abort(403, 'Premium subscription required');
        }

        // Получаем слово с проверкой прав доступа
        $wordModel = Word::query()
            ->where('id', $word)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Генерируем или получаем существующие примеры
        $examples = resolve(GenerateWordExamples::class)->handle($wordModel->id, $user->id);

        return Inertia::render('Words/Examples', [
            'word' => [
                'id' => $wordModel->id,
                'original' => $wordModel->original,
                'translated' => $wordModel->translated,
                'language' => $wordModel->language,
            ],
            'examples' => [
                'original' => $examples->example_original,
                'translated' => $examples->example_translated,
            ],
            'user' => $user,
        ]);
    }
}
