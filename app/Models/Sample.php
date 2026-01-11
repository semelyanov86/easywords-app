<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SampleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $original
 * @property string $translated
 * @property string $language
 *
 * Модель для хранения примеров слов (samples).
 *
 * Используется как глобальная библиотека готовых слов, которые пользователи могут импортировать.
 * Не привязана к конкретному пользователю.
 */
class Sample extends Model
{
    /** @use HasFactory<SampleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'original',
        'translated',
        'language',
    ];
}
