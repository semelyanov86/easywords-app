<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class StoreWordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для создания нового слова.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'original' => ['required', 'string', 'max:255'],
            'translated' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:10', 'in:EN,DE,ES,FR,IT,RU'],
        ];
    }

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
