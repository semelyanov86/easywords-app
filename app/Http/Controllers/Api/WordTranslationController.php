<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GetWordTranslation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetWordTranslationRequest;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

/**
 * Контроллер для получения перевода слов.
 *
 * Предоставляет API endpoint для автоматического перевода слов.
 */
#[Middleware('auth:sanctum')]
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
     * @return JsonResponse JSON:API ответ с переводом
     */
    #[Get('api/v1/translate', name: 'api.v1.translate')]
    public function translate(GetWordTranslationRequest $request, GetWordTranslation $getWordTranslation): JsonResponse
    {
        /** @var array{word: string, language: string} $validated */
        $validated = $request->validated();

        $translationData = $getWordTranslation->handle(
            word: $validated['word'],
            language: $validated['language']
        );

        return $translationData->toResponse();
    }
}
