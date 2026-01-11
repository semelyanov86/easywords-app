<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $is_admin,
        public bool $has_premium,
        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}
}
