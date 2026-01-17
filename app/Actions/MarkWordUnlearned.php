<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Word;
use Lorisleiva\Actions\Concerns\AsAction;

final readonly class MarkWordUnlearned
{
    use AsAction;

    public function handle(Word $word, int $userId): ?Word
    {
        if ($word->user_id !== $userId) {
            abort(403, 'You are not authorized to do this action');
        }
        $word->update([
            'done_at' => null,
        ]);

        return $word->fresh();
    }
}
