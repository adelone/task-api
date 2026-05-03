<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    private const DEFAULT_TTL = 300;
    private array $tags = [];
    private ?int $ttl = null;

    public function tags(string|array $tags): self
    {
        $this->tags = is_array($tags) ? $tags : [$tags];
        return $this;
    }

    public function ttl(int $seconds): self
    {
        $this->ttl = $seconds;
        return $this;
    }

    public function remember(string $key, callable $callback): mixed
    {
        $ttl = $this->ttl ?? self::DEFAULT_TTL;

        if (!empty($this->tags)) {
            $result = Cache::tags($this->tags)->remember($key, $ttl, $callback);
        } else {
            $result = Cache::remember($key, $ttl, $callback);
        }

        $this->reset();

        return $result;
    }

    public function flush(): void
    {
        if (!empty($this->tags)) {
            Cache::tags($this->tags)->flush();
        } else {
            Cache::flush();
        }

        $this->reset();
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function forgetByPattern(string $pattern): void
    {
        $keys = Redis::connection()->keys($pattern);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->ttl ?? self::DEFAULT_TTL;

        if (!empty($this->tags)) {
            return Cache::tags($this->tags)->put($key, $value, $ttl);
        }

        return Cache::put($key, $value, $ttl);
    }

    public function makeKey(string $prefix, array $params): string
    {
        ksort($params);
        return $prefix . ':' . md5(json_encode($params));
    }

    private function reset(): void
    {
        $this->tags = [];
        $this->ttl = null;
    }
}