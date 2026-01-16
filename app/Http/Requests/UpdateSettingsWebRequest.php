<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request для валидации обновления настроек пользователя.
 *
 * Проверяет входные данные при обновлении настроек через web-интерфейс.
 * Гарантирует корректность всех полей настроек.
 */
final class UpdateSettingsWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Правила валидации для обновления настроек.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'paginate' => ['required', 'integer', 'min:1', 'max:100'],
            'main_language' => ['required', 'string', 'in:RU,EN,DE'],
            'show_starred' => ['required', 'boolean'],
            'known_enabled' => ['required', 'boolean'],
            'latest_first' => ['required', 'boolean'],
            'show_imported' => ['required', 'boolean'],
            'show_shared' => ['required', 'boolean'],
            'fresh_first' => ['required', 'boolean'],
        ];
    }
}
