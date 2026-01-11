<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Auth\AuthenticateUser;
use App\Data\TokenResponseData;
use App\Data\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\CreateTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

final class TokenController extends Controller
{
    public function __construct(
        private readonly AuthenticateUser $authenticateUser,
    ) {}

    /**
     * Создает токен авторизации.
     *
     * @param  CreateTokenRequest  $request  Запрос с email и паролем
     * @return JsonResponse JSON:API ответ с токеном и данными пользователя
     */
    #[Post('api/v1/token', name: 'api.v1.token')]
    public function store(CreateTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Создаем токен
        $token = $this->authenticateUser->handle(
            email: $validated['email'],
            password: $validated['password'],
        );

        // Получаем пользователя из токена
        $user = $token->accessToken->tokenable;

        // Формируем ответ в формате JSON:API
        $responseData = TokenResponseData::fromTokenAndUser(
            token: $token->plainTextToken,
            user: UserData::from($user),
        );

        return $responseData->toResponse();
    }

    /**
     * Удаляет текущий токен (signout).
     *
     * @return JsonResponse Пустой JSON:API ответ с кодом 204
     */
    #[Post('api/v1/signout', name: 'api.v1.signout', middleware: 'auth:sanctum')]
    public function destroy(): JsonResponse
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
            ], 401, ['Content-Type' => 'application/vnd.api+json']);
        }

        // Удаляем текущий токен
        $user->currentAccessToken()->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT, ['Content-Type' => 'application/vnd.api+json']);
    }
}
