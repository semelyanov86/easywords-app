<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class WordImageExtractRequest extends FormRequest
{
    /**
     * @return array<string, string[]>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', 'max:10240'],
            'language' => ['required', 'string', 'size:2'],
            'target_language' => ['sometimes', 'string', 'size:2'],
        ];
    }

    public function authorize(): bool
    {
        return Auth::user()->has_premium ?? false;
    }
}
