<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Lorisleiva\Actions\Concerns\AsAction;

final class DeleteCurrentToken
{
    use AsAction;

    /**
     * Удаляет текущий токен авторизованного пользователя.
     */
    public function handle(): void
    {
        $user = auth('sanctum')->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $user->currentAccessToken()->delete();
    }
}
