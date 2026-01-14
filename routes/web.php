<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');
    Route::get('words/create', [\App\Http\Controllers\WordController::class, 'create'])->name('words.create');
    Route::post('words', [\App\Http\Controllers\WordController::class, 'store'])->name('words.store');
    Route::get('words/translate', [\App\Http\Controllers\WordTranslationController::class, 'translate'])->name('words.translate');
});

require __DIR__ . '/settings.php';
