<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class GetUserWordsRequest extends FormRequest
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
            'language' => ['required', 'string', 'size:2'],
        ];
    }

    public function validatedLanguage(): string
    {
        /** @var string $language */
        $language = $this->validated()['language'];

        return $language;
    }
}
