<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtractWordsFromImageController;
use App\Http\Controllers\ImportWordsController;
use App\Http\Controllers\LearnedWordsController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShowWordExamplesController;
use App\Http\Controllers\StartStudyController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\WordActionController;
use App\Http\Controllers\WordController;
use App\Http\Controllers\WordSearchController;
use App\Http\Controllers\WordTranslationController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth'])->group(function (): void {
    Route::get('learned-words', LearnedWordsController::class)->name('learned-words.index');
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('start', StartStudyController::class)->name('study.start');
    Route::get('words/create', [WordController::class, 'create'])->name('words.create');
    Route::post('words/create', [WordController::class, 'store'])->name('words.store');
    Route::get('words/extract-from-image', [ExtractWordsFromImageController::class, 'index'])->name('words.extract-from-image.index');
    Route::post('words/extract-from-image', [ExtractWordsFromImageController::class, 'extract'])->name('words.extract-from-image.extract');
    Route::get('words/search', WordSearchController::class)->name('words.search');
    Route::get('words/next', [WordActionController::class, 'goToNext'])->name('words.next');
    Route::get('words/prev', [WordActionController::class, 'goToPrev'])->name('words.prev');
    Route::get('words/translate', [WordTranslationController::class, 'translate'])->name('words.translate');
    Route::get('words/{id}', [WordController::class, 'show'])->name('words.show');
    Route::get('words/{id}/examples', ShowWordExamplesController::class)->name('words.examples');

    // Word actions
    Route::post('words/{id}/mark-learned', [WordActionController::class, 'markLearned'])->name('words.mark-learned');
    Route::post('/words/{word}/unlearned', [WordActionController::class, 'markUnlearned'])
        ->name('words.unlearned');
    Route::post('words/{id}/toggle-starred', [WordActionController::class, 'toggleStarred'])->name('words.toggle-starred');
    Route::post('words/{id}/share', [WordActionController::class, 'share'])->name('words.share');
    Route::delete('words/{id}', [WordActionController::class, 'delete'])->name('words.delete');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('profile', [ProfileController::class, 'storeToken'])->name('profile.tokens.store');
    Route::delete('profile/api-tokens/{token}', [ProfileController::class, 'destroyToken'])->name('profile.tokens.destroy');
    Route::get('profile/password/edit', [PasswordController::class, 'edit'])->name('profile.password.edit');
    Route::post('profile/password', [PasswordController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('settings', [SettingsController::class, 'show'])->name('settings.show');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('import-words', ImportWordsController::class)->name('import-words');
    Route::get('statistics', StatisticsController::class)->name('statistics.index');
});

require __DIR__ . '/settings.php';
