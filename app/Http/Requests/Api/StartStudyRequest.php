<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json([
                'errors' => $validator->errors(),
            ], \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/vnd.api+json'])
        );
    }
}
