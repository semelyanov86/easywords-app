<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Request для валидации создания слова через web-интерфейс.
 *
 * Проверяет входные данные при создании нового слова через форму на сайте.
 * Гарантирует уникальность оригинального слова в рамках одного пользователя
 * и выбранного языка.
 */
final class StoreWordWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для создания нового слова через web-интерфейс.
     *
     * Проверяет, что оригинальное слово уникально для текущего пользователя
     * и выбранного языка.
     *
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Unique>>
     */
    public function rules(): array
    {
        return [
            'original' => [
                'required',
                'string',
                'max:255',
                Rule::unique('words')
                    ->where('user_id', Auth::id())
                    ->where('language', $this->string('language')->toString()),
            ],
            'translated' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:10', 'in:EN,DE,ES,FR,IT,RU'],
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'original.unique' => 'Это слово уже есть в вашем словаре для выбранного языка.',
        ];
    }
}
