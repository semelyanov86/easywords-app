<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeleteWord;
use App\Actions\GoToNextWord;
use App\Actions\GoToPrevWord;
use App\Actions\MarkWordAsLearned;
use App\Actions\ShareWord;
use App\Actions\ToggleWordStarred;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер для действий с словами.
 *
 * Обрабатывает операции с конкретными словами: удаление, отметка как выученное,
 * переключение статуса избранного, шаринг с другими пользователями.
 */
final class WordActionController
{
    /**
     * Отмечает слово как выученное.
     *
     * Устанавливает дату завершения изучения для слова.
     * Перенаправляет на предыдущую страницу с уведомлением.
     */
    public function markLearned(Request $request, MarkWordAsLearned $markWordAsLearned, int $id): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $markWordAsLearned->handle($id, $user->id);

            return back()->with('success', __('words.word_learned'));
        } catch (\Exception) {
            return back()->with('error', __('words.error_marking_learned'));
        }
    }

    /**
     * Удаляет слово.
     *
     * Удаляет слово из словаря пользователя.
     * Перенаправляет на предыдущую страницу с уведомлением.
     */
    public function delete(Request $request, DeleteWord $deleteWord, int $id): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $deleteWord->handle($id, $user->id);

            return back()->with('success', __('words.word_deleted'));
        } catch (\Exception) {
            return back()->with('error', __('words.error_deleting'));
        }
    }

    /**
     * Переключает статус избранного для слова.
     *
     * Добавляет или удаляет слово из избранного.
     * Перенаправляет на предыдущую страницу с уведомлением.
     */
    public function toggleStarred(Request $request, ToggleWordStarred $toggleWordStarred, int $id): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $isStarred = $toggleWordStarred->handle($id, $user->id);
            $message = $isStarred ? __('words.word_starred') : __('words.word_unstarred');

            return back()->with('success', $message);
        } catch (\Exception) {
            return back()->with('error', __('words.error_toggling_starred'));
        }
    }

    /**
     * Делится словом с другим пользователем.
     *
     * Создает копию слова для указанного пользователя.
     * Перенаправляет на предыдущую страницу с уведомлением.
     */
    public function share(Request $request, ShareWord $shareWord, int $id): RedirectResponse
    {
        /** @var array{user_id: int} $validated */
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $word = \App\Models\Word::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        /** @var \App\Models\User $targetUser */
        $targetUser = \App\Models\User::query()->findOrFail($validated['user_id']);

        try {
            $shareWord->handle($word, $targetUser, $user);

            return back()->with('success', __('words.word_shared'));
        } catch (\Exception) {
            return back()->with('error', __('words.error_sharing'));
        }
    }

    /**
     * Переходит к следующему слову.
     *
     * Находит следующее слово в списке и перенаправляет на него.
     */
    public function goToNext(Request $request, GoToNextWord $goToNextWord): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $result = $goToNextWord->handle($user);
        $nextId = $result['word']->id;

        return to_route('words.show', ['id' => $nextId]);
    }

    /**
     * Переходит к предыдущему слову.
     *
     * Находит предыдущее слово в списке и перенаправляет на него.
     */
    public function goToPrev(Request $request, GoToPrevWord $goToPrevWord, int $id): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $result = $goToPrevWord->handle($user);
        $prevId = $result['word']->id;

        return to_route('words.show', ['id' => $prevId]);
    }
}
