<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeleteWord;
use App\Actions\GoToNextWord;
use App\Actions\GoToPrevWord;
use App\Actions\MarkWordAsLearned;
use App\Actions\MarkWordUnlearned;
use App\Actions\ShareWord;
use App\Actions\ToggleWordStarred;
use App\Http\Requests\NextWordRequest;
use App\Models\Word;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

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

    public function markUnlearned(Word $word): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $action = resolve(MarkWordUnlearned::class);
        $action->handle($word, $user->id);

        return back()->with('success', __('words.word_unlearned'));
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
    public function goToNext(NextWordRequest $request, GoToNextWord $goToNextWord): Response|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $result = $goToNextWord->handle(
                $user,
                $request->string('language')->toString(),
                $request->boolean('reverse')
            );
        } catch (\DomainException $e) {
            return redirect(route('dashboard'))->with('success', $e->getMessage());
        }
        $nextId = $result['word']->id;

        return Inertia::render('Words/Show', [
            'word' => $result['word'],
            'user' => $user,
            'meta' => [
                'total' => $result['meta']['total'],
                'next_id' => $result['meta']['next_id'],
                'prev_id' => $result['meta']['prev_id'],
                'current_index' => $result['meta']['current_index'],
                'language' => $request->string('language')->toString(),
                'reverse' => $request->boolean('reverse'),
            ],
        ]);
    }

    /**
     * Переходит к предыдущему слову.
     *
     * Находит предыдущее слово в списке и перенаправляет на него.
     */
    public function goToPrev(NextWordRequest $request, GoToPrevWord $goToPrevWord): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $result = $goToPrevWord->handle($user, $request->string('language')->toString(), $request->boolean('reverse'));
        $prevId = $result['word']->id;

        return Inertia::render('Words/Show', [
            'word' => $result['word'],
            'user' => $user,
            'meta' => [
                'total' => $result['meta']['total'],
                'next_id' => $result['meta']['next_id'],
                'prev_id' => $result['meta']['prev_id'],
                'current_index' => $result['meta']['current_index'],
                'language' => $request->string('language')->toString(),
                'reverse' => $request->boolean('reverse'),
            ],
        ]);
    }
}
