<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Auth\AuthenticationException;

final class AuthenticateUser
{
    use AsAction;

    /**
     * Аутентифицирует пользователя и создает токен.
     *
     * @param  string  $email  Email пользователя
     * @param  string  $password  Пароль пользователя
     * @return NewAccessToken Созданный токен
     *
     * @throws AuthenticationException Если учетные данные неверны
     */
    public function handle(string $email, string $password): NewAccessToken
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            abort(401, 'The provided credentials are incorrect.');
        }

        return $user->createToken('api-token');
    }
}
