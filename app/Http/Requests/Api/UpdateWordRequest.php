<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

final class UpdateWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для обновления слова.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'starred' => ['required', 'boolean'],
        ];
    }

    /**
     * Возвращает валидированное значение starred.
     */
    public function validatedStarred(): bool
    {
        /** @var array{starred: bool} $validated */
        $validated = $this->validated();

        return $validated['starred'];
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
