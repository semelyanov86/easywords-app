<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Запрос для запуска сессии изучения слов.
 *
 * Валидирует параметры запроса для начала сессии:
 * - limit: количество слов для изучения (опционально, по умолчанию 20)
 * - reverse: порядок слов (опционально, true/false, по умолчанию false)
 */
final class StartStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для запуска сессии.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'reverse' => ['sometimes', 'boolean'],
            'language' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Возвращает валидированное количество слов с дефолтным значением.
     */
    public function validatedLimit(): int
    {
        return $this->integer('limit', 20);
    }

    /**
     * Возвращает флаг обратного порядка слов с дефолтным значением.
     */
    public function validatedReverse(): bool
    {
        return $this->boolean('reverse');
    }

    #[\Override]
    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/vnd.api+json'])
        );
    }
}
