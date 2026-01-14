<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request для получения перевода слова через веб-интерфейс.
 *
 * Валидирует параметры word и language для запроса перевода.
 * Используется в веб-приложении с Inertia.
 */
final class GetWordTranslationRequest extends FormRequest
{
    /**
     * Определяет авторизацию пользователя.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для запроса.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'word' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'size:2'],
        ];
    }
}
