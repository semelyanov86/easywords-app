<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetWordTranslation;
use App\Http\Requests\Web\GetWordTranslationRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения перевода слов через веб-интерфейс.
 *
 * Предоставляет endpoint для автоматического перевода слов в Inertia приложении.
 * Использует web авторизацию (cookie-based), а не Sanctum.
 */
final class WordTranslationController extends Controller
{
    /**
     * Получает перевод слова.
     *
     * Сначала ищет слово во внутренней базе данных. Если не найдено,
     * запрашивает перевод через OpenAI API. Возвращает краткий перевод (до 100 символов).
     *
     * @param  GetWordTranslationRequest  $request  Валидированный запрос с параметрами word и language
     * @param  GetWordTranslation  $getWordTranslation  Action для получения перевода
     * @return JsonResponse JSON ответ с переводом
     */
    public function translate(
        GetWordTranslationRequest $request,
        GetWordTranslation $getWordTranslation
    ): JsonResponse {
        $translationData = $getWordTranslation->handle(
            word: $request->string('word')->toString(),
            language: $request->string('language')->toString(),
        );

        return $translationData->toResponse();
    }
}
