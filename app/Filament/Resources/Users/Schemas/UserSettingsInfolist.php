<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

/**
 * Схема для отображения настроек пользователя в Filament.
 *
 * Выделена в отдельный класс для поддержания чистоты основного UserInfolist
 * и для переиспользования, если настройки нужно будет показать в другом месте.
 */
final class UserSettingsInfolist
{
    public static function make(): Section
    {
        return Section::make('Settings')
            ->schema([
                TextEntry::make('settings.paginate')
                    ->label('Items per page')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('paginate'))
                    ->numeric(),
                IconEntry::make('settings.fresh_first')
                    ->label('Fresh first')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('fresh_first'))
                    ->boolean(),
                IconEntry::make('settings.show_starred')
                    ->label('Show starred')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('show_starred'))
                    ->boolean(),
                IconEntry::make('settings.latest_first')
                    ->label('Latest first')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('latest_first'))
                    ->boolean(),
                IconEntry::make('settings.known_enabled')
                    ->label('Known enabled')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('known_enabled'))
                    ->boolean(),
                TextEntry::make('settings.main_language')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('main_language'))
                    ->label('Main language'),
                IconEntry::make('settings.show_imported')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('show_imported'))
                    ->label('Show imported')
                    ->boolean(),
                TextEntry::make('settings.languages_list')
                    ->label('Languages list')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('languages_list'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
                IconEntry::make('settings.starred_enabled')
                    ->label('Starred enabled')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('starred_enabled'))
                    ->boolean(),
                TextEntry::make('settings.default_language')
                    ->getStateUsing(fn (User $user) => $user->settings()->get('default_language'))
                    ->label('Default language'),
            ])
            ->columns(2);
    }
}
