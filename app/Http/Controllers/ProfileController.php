<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetUserPersonalAccessTokens;
use App\Http\Requests\StoreTokenRequest;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Контроллер для управления профилем пользователя и API токенами.
 *
 * Отвечает за отображение информации о пользователе и управление
 * личными токенами доступа Sanctum.
 */
final class ProfileController
{
    /**
     * Отображает страницу профиля пользователя.
     *
     * Показывает информацию о текущем пользователе и список его API токенов.
     */
    public function show(GetUserPersonalAccessTokens $getUserTokens): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('profile/Show', [
            'user' => $user,
            'tokens' => $getUserTokens->handle($user->id),
        ]);
    }

    /**
     * Создает новый API токен для пользователя.
     *
     * Возвращает созданный токен через Inertia, так как Sanctum
     * показывает токен только один раз при создании.
     */
    public function storeToken(StoreTokenRequest $request, GetUserPersonalAccessTokens $getUserTokens): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $accessToken = $user->createToken($request->string('name')->toString());
        $plainTextToken = $accessToken->plainTextToken;

        return Inertia::render('profile/Show', [
            'user' => $user,
            'tokens' => $getUserTokens->handle($user->id),
            'token' => [
                'id' => $accessToken->accessToken->id + 1,
                'name' => $request->string('name')->toString(),
                'token' => $plainTextToken,
                'created_at' => $accessToken->accessToken->created_at?->toIso8601String() ?? now()->toIso8601String(),
                'updated_at' => $accessToken->accessToken->updated_at?->toIso8601String() ?? now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Удаляет API токен пользователя.
     */
    public function destroyToken(Request $request, string $token): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->tokens()->findOrFail($token)->delete();

        return back();
    }
}
