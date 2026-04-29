<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\StudySessionCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class StudySessionCacheTest extends TestCase
{
    /**
     * Регрессия: Redis cache store не сериализует numeric-значения,
     * поэтому int может вернуться как string. Геттер обязан кастовать.
     */
    public function test_get_current_id_casts_string_value_from_cache(): void
    {
        $cache = new StudySessionCache();

        Cache::put($cache->key('current', 1, 'DE'), '14080');

        $this->assertSame(14080, $cache->getCurrentId(1, 'DE'));
    }

    public function test_get_next_id_casts_string_value_from_cache(): void
    {
        $cache = new StudySessionCache();

        Cache::put($cache->key('next', 1, 'DE'), '14085');

        $this->assertSame(14085, $cache->getNextId(1, 'DE'));
    }

    public function test_get_prev_id_casts_string_value_from_cache(): void
    {
        $cache = new StudySessionCache();

        Cache::put($cache->key('prev', 1, 'DE'), '14070');

        $this->assertSame(14070, $cache->getPrevId(1, 'DE'));
    }

    public function test_get_current_id_returns_null_when_missing(): void
    {
        $cache = new StudySessionCache();

        $this->assertNull($cache->getCurrentId(1, 'DE'));
        $this->assertNull($cache->getNextId(1, 'DE'));
        $this->assertNull($cache->getPrevId(1, 'DE'));
    }
}
