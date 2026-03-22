<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Валидация запроса для навигации по словам (next/prev).
 *
 * @property-read bool|null $reverse Параметр для изменения порядка слов (true/false)
 * @property-read string $language Язык слов в сессии
 */
final class NavigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reverse' => ['sometimes', 'boolean'],
            'language' => ['required', 'string', 'max:255'],
        ];
    }

    public function validatedReverse(): bool
    {
        return $this->boolean('reverse');
    }

    public function validatedLanguage(): string
    {
        return $this->string('language')->toString();
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
