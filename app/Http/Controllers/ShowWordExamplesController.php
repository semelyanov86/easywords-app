<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateWordExamples;
use App\Models\User;
use App\Models\Word;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * @return Response|RedirectResponse Inertia-ответ с примерами или редирект с ошибкой
     *
     * @throws AuthorizationException если пользователь не премиум
     * @throws ModelNotFoundException если слово не найдено
     */
    public function __invoke(Request $request, int $word): Response|RedirectResponse
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

        try {
            // Генерируем или получаем существующие примеры
            $examples = resolve(GenerateWordExamples::class)->handle($wordModel->id, $user->id);
        } catch (RequestException) {
            return back()->with('error', 'Не удалось сгенерировать примеры. Сервис AI временно недоступен, попробуйте позже.');
        } catch (\RuntimeException) {
            return back()->with('error', 'Не удалось сгенерировать примеры. Попробуйте ещё раз.');
        }

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
