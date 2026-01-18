<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request для валидации параметров поиска слов.
 *
 * Проверяет обязательный параметр query для поиска слов по полям original и translated.
 */
final class SearchUserWordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для запроса.
     *
     * @return array<string, string[]>
     */
    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'min:1', 'max:255'],
        ];
    }

    /**
     * Возвращает валидированную строку запроса.
     *
     * @return string Строка для поиска
     */
    public function validatedQuery(): string
    {
        return $this->string('query')->toString();
    }
}
