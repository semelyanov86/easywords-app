<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class NextWordRequest extends FormRequest
{
    /**
     * @return array<string, string[]>
     */
    public function rules(): array
    {
        return [
            'language' => ['required', 'string', 'max:255'],
            'reverse' => ['required', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return Auth::check();
    }
}
