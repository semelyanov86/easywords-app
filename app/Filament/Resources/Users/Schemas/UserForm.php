<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at'),
                Toggle::make('is_admin')
                    ->required(),
                Toggle::make('has_premium')
                    ->required(),
                TextInput::make('settings.paginate')
                    ->label('Items per page')
                    ->numeric()
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('paginate'))
                    ->default(20)
                    ->required(),
                Toggle::make('settings.fresh_first')
                    ->label('Fresh first')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('fresh_first'))
                    ->default(true),
                Toggle::make('settings.show_starred')
                    ->label('Show starred')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('show_starred'))
                    ->default(true),
                Toggle::make('settings.latest_first')
                    ->label('Latest first')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('latest_first'))
                    ->default(true),
                Toggle::make('settings.known_enabled')
                    ->label('Known enabled')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('known_enabled'))
                    ->default(false),
                TextInput::make('settings.main_language')
                    ->label('Main language')
                    ->default('RU')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('main_language'))
                    ->required(),
                Toggle::make('settings.show_imported')
                    ->label('Show imported')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('show_imported'))
                    ->default(true),
                /*                Select::make('settings.languages_list')->multiple()
                    ->label('Languages list')
                    ->formatStateUsing(fn(?User $user) => $user?->settings()->get('languages_list'))
                    ->options(fn(?User $user) => $user?->settings()->get('languages_list')),*/
                Toggle::make('settings.starred_enabled')
                    ->label('Starred enabled')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('starred_enabled'))
                    ->default(true),
                TextInput::make('settings.default_language')
                    ->label('Default language')
                    ->default('DE')
                    ->formatStateUsing(fn (?User $user) => $user?->settings()->get('default_language'))
                    ->required(),
            ]);
    }
}
