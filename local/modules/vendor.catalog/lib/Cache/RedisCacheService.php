<?php

declare(strict_types=1);

namespace Vendor\Catalog\Cache;

use Predis\Client;
use Throwable;

final class RedisCacheService implements CacheServiceInterface
{
    private ?Client $client = null;
    private bool $enabled = false;

    public function __construct(
        private readonly string $host,
        private readonly int $port
    ) {
        try {
            $this->client = new Client([
                'host' => $this->host,
                'port' => $this->port,
                'timeout' => 1.0,
            ]);

            $this->client->ping();
            $this->enabled = true;
        } catch (Throwable) {
            $this->enabled = false;
        }
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if (!$this->enabled || $this->client === null) {
            return $callback();
        }

        try {
            $raw = $this->client->get($key);

            if (is_string($raw)) {
                $decoded = json_decode($raw, true);

                if (is_array($decoded) && array_key_exists('data', $decoded)) {
                    return $decoded['data'];
                }
            }
        } catch (Throwable) {
            $this->enabled = false;

            return $callback();
        }

        $value = $callback();

        try {
            $payload = json_encode([
                'data' => $value,
                'time' => time(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $this->client->setex($key, $ttl, $payload);
        } catch (Throwable) {
            $this->enabled = false;
        }

        return $value;
    }

    public function deleteByPattern(string $pattern): void
    {
        if (!$this->enabled || $this->client === null) {
            return;
        }

        try {
            $keys = $this->client->keys($pattern);

            if ($keys !== []) {
                $this->client->del($keys);
            }
        } catch (Throwable) {
            $this->enabled = false;
        }
    }
}