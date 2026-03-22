<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

final class GetUserWordsWithFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для фильтрации слов.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'done' => ['nullable', 'in:true,false'],
            'shared' => ['nullable', 'in:true,false'],
            'from_sample' => ['nullable', 'in:true,false'],
            'starred' => ['nullable', 'in:true,false'],
        ];
    }

    /**
     * Возвращает массив фильтров из запроса.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'done' => $this->query('done'),
            'shared' => $this->query('shared'),
            'from_sample' => $this->query('from_sample'),
            'starred' => $this->query('starred'),
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
