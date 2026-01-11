<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Auth\UpdateUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\UpdatePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Put;

#[Middleware('auth:sanctum')]
final class PasswordController extends Controller
{
    public function __construct(
        private readonly UpdateUserPassword $updateUserPassword,
    ) {}

    /**
     * Изменяет пароль авторизованного пользователя.
     *
     * @param  UpdatePasswordRequest  $request  Запрос с текущим и новым паролями
     * @return JsonResponse Пустой JSON:API ответ с кодом 204
     */
    #[Put('api/v1/user/password', name: 'api.v1.user.password.update')]
    public function update(UpdatePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Обновляем пароль
        $this->updateUserPassword->handle(
            currentPassword: $validated['current_password'],
            newPassword: $validated['password'],
        );

        return response()->json(null, Response::HTTP_NO_CONTENT, ['Content-Type' => 'application/vnd.api+json']);
    }
}
