<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json([
                'errors' => $validator->errors(),
            ], \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
