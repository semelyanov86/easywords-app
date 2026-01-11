<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

/**
 * Контроллер для работы с настройками пользователя.
 *
 * Предоставляет API эндпоинты для получения и обновления настроек
 * авторизованного пользователя через пакет glorand/laravel-model-settings.
 */
#[Middleware('auth:sanctum')]
final class UserSettingsController extends Controller
{
    /**
     * Получить настройки авторизованного пользователя.
     *
     * @return JsonResponse JSON:API ответ с настройками пользователя
     */
    #[Get('api/v1/settings', name: 'api.user-settings.index')]
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $settingsData = \App\Actions\GetUserSettings::make()->handle($user->id);

        return $settingsData->toResponse();
    }

    /**
     * Обновить конкретную настройку авторизованного пользователя.
     *
     * @param  UpdateSettingRequest  $request  Запрос с данными настройки
     * @return JsonResponse JSON:API ответ с обновлёнными настройками
     */
    #[Post('api/v1/settings', name: 'api.user-settings.update')]
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $settingName = $request->string('name')->toString();

        /** @var mixed $settingValue */
        $settingValue = $request->input('value');

        $settingsData = \App\Actions\UpdateUserSetting::make()->handle(
            $user->id,
            $settingName,
            $settingValue,
        );

        return $settingsData->toResponse();
    }
}
