<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SampleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    /** @use HasFactory<SampleFactory> */
    use HasFactory;

    protected $fillable = [
        'original',
        'translated',
        'language',
    ];
}
