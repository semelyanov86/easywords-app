<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Auth\GetUsers;
use App\Data\ShortUserData;
use App\Data\UserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware('auth:sanctum')]
final class UserController extends Controller
{
    /**
     * Возвращает информацию о текущем авторизованном пользователе.
     *
     * @return JsonResponse JSON:API ответ с данными пользователя
     */
    #[Get('api/v1/me', name: 'api.v1.me')]
    public function me(): JsonResponse
    {
        $user = auth('sanctum')->user();

        // Middleware уже проверяет авторизацию, но добавим дополнительную проверку
        if (! $user) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '401',
                        'title' => 'Unauthenticated',
                    ],
                ],
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/vnd.api+json']);
        }

        return UserData::from($user)->toResponse(request());
    }

    /**
     * Возвращает список всех пользователей.
     *
     * @return JsonResponse JSON:API ответ со списком пользователей
     */
    #[Get('api/v1/users', name: 'api.v1.users.index')]
    public function index(): JsonResponse
    {
        $shortMode = request()->boolean('short_mode', false);

        $data = resolve(GetUsers::class)->handle($shortMode);

        return response()->json([
            'data' => $data->map(fn (UserData|ShortUserData $user) => $user->toJsonArray()),
        ], Response::HTTP_OK, ['Content-Type' => 'application/vnd.api+json']);
    }
}
