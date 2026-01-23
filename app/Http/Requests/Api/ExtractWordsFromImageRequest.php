<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request для извлечения слов из изображения.
 *
 * Валидирует загруженный файл изображения и язык текста на изображении.
 */
final class ExtractWordsFromImageRequest extends FormRequest
{
    /**
     * Определяет авторизацию пользователя.
     */
    public function authorize(): bool
    {
        return Auth::user()->has_premium ?? false;
    }

    /**
     * Правила валидации для запроса.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', 'max:10240'], // max 10MB
            'language' => ['required', 'string', 'size:2'],
            'target_language' => ['sometimes', 'string', 'size:2'],
        ];
    }
}
