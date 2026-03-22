<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Запрос на шаринг слова с другим пользователем.
 *
 * Валидирует параметры для создания копии слова:
 * - word_id: ID слова, которое нужно поделиться
 * - user_id: ID пользователя, с которым делимся
 */
final class ShareWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для шаринга слова.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'word_id' => ['required', 'integer', 'exists:words,id'],
            'user_id' => ['required', 'integer', 'exists:users,id', 'not_in:' . auth()->id()],
        ];
    }

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
