<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');
    Route::get('words/create', [\App\Http\Controllers\WordController::class, 'create'])->name('words.create');
    Route::post('words/create', [\App\Http\Controllers\WordController::class, 'store'])->name('words.store');
    Route::get('words/translate', [\App\Http\Controllers\WordTranslationController::class, 'translate'])->name('words.translate');
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('profile', [\App\Http\Controllers\ProfileController::class, 'storeToken'])->name('profile.tokens.store');
    Route::delete('profile/api-tokens/{token}', [\App\Http\Controllers\ProfileController::class, 'destroyToken'])->name('profile.tokens.destroy');
    Route::get('profile/password/edit', [\App\Http\Controllers\PasswordController::class, 'edit'])->name('profile.password.edit');
    Route::post('profile/password', [\App\Http\Controllers\PasswordController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('settings', [\App\Http\Controllers\SettingsController::class, 'show'])->name('settings.show');
    Route::post('settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::post('import-words', \App\Http\Controllers\ImportWordsController::class)->name('import-words');
    Route::get('statistics', \App\Http\Controllers\StatisticsController::class)->name('statistics.index');
});

require __DIR__ . '/settings.php';
