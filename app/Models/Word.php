<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $original
 * @property string $translated
 * @property string $language
 * @property int $user_id
 * @property bool $from_sample
 * @property bool $starred
 * @property int $views
 * @property string[]|null $example_original
 * @property string[]|null $example_translated
 */
class Word extends Model
{
    /** @use HasFactory<WordFactory> */
    use HasFactory;

    protected $fillable = [
        'original',
        'translated',
        'done_at',
        'starred',
        'user_id',
        'language',
        'views',
        'example_original',
        'example_translated',
        'from_sample',
        'shared_by',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'done_at' => 'datetime',
            'starred' => 'boolean',
            'example_original' => 'array',
            'example_translated' => 'array',
            'from_sample' => 'boolean',
        ];
    }
}
