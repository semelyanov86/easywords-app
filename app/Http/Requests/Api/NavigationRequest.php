<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

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
        return \Illuminate\Support\Facades\Auth::check();
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
