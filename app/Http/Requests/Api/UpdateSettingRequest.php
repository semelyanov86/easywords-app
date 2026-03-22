<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Request для обновления конкретной настройки пользователя.
 *
 * Валидирует входные данные для обновления отдельной настройки
 * через пакет glorand/laravel-model-settings.
 */
final class UpdateSettingRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для обновления настройки.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'value' => ['required'],
        ];
    }

    /**
     * Возвращает валидированные данные запроса.
     *
     * @return array{value: mixed}
     */
    public function validatedData(): array
    {
        /** @var array{name: string, value: mixed} $validated */
        $validated = $this->validated();

        return [
            'value' => $validated['value'],
        ];
    }

    /**
     * Настройка валидатора для JSON API ответов.
     */
    #[\Override]
    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
