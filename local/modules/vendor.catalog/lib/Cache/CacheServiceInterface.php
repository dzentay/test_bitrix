<?php

declare(strict_types=1);

namespace Vendor\Catalog\Cache;

interface CacheServiceInterface
{
    public function remember(string $key, int $ttl, callable $callback): mixed;

    public function deleteByPattern(string $pattern): void;
}