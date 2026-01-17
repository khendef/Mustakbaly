<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CachesQueries Trait
 *
 * Provides reusable caching methods for services and controllers.
 * Centralizes cache key generation and cache management logic.
 * Supports cache tags for better invalidation (requires Redis).
 */
trait CachesQueries
{
    /**
     * Remember a value in cache with a given key and duration.
     * Supports cache tags for better invalidation.
     *
     * @param string $key Cache key
     * @param int $seconds Cache duration in seconds
     * @param callable $callback Callback to execute if cache miss
     * @param array $tags Optional cache tags for grouped invalidation
     * @return mixed
     */
    protected function remember(string $key, int $seconds, callable $callback, array $tags = [])
    {
        try {
            if (!empty($tags) && $this->supportsCacheTags()) {
                return Cache::tags($tags)->remember($key, $seconds, $callback);
            }
            return Cache::remember($key, $seconds, $callback);
        } catch (\Exception $e) {
            // If cache fails, execute callback directly
            Log::warning("Cache operation failed, executing callback directly", [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return $callback();
        }
    }

    /**
     * Forget a cache key.
     *
     * @param string $key Cache key to forget
     * @param array $tags Optional cache tags
     * @return bool
     */
    protected function forget(string $key, array $tags = []): bool
    {
        try {
            if (!empty($tags) && $this->supportsCacheTags()) {
                return Cache::tags($tags)->forget($key);
            }
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::warning("Cache forget operation failed", [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Forget multiple cache keys.
     *
     * @param array $keys Array of cache keys to forget
     * @param array $tags Optional cache tags
     * @return void
     */
    protected function forgetMany(array $keys, array $tags = []): void
    {
        foreach ($keys as $key) {
            $this->forget($key, $tags);
        }
    }

    /**
     * Flush all cache entries with given tags.
     *
     * @param array $tags Cache tags to flush
     * @return bool
     */
    protected function flushTags(array $tags): bool
    {
        if (!$this->supportsCacheTags()) {
            Log::warning("Cache tags not supported, cannot flush by tags", ['tags' => $tags]);
            return false;
        }

        try {
            Cache::tags($tags)->flush();
            Log::info("Cache tags flushed", ['tags' => $tags]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to flush cache tags", [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a cache key with consistent prefix.
     *
     * @param string ...$parts Key parts to join
     * @return string
     */
    protected function cacheKey(string ...$parts): string
    {
        return implode('.', $parts);
    }

    /**
     * Check if the current cache driver supports tags.
     *
     * @return bool
     */
    protected function supportsCacheTags(): bool
    {
        $driver = config('cache.default');
        $supportedDrivers = ['redis', 'memcached'];

        return in_array($driver, $supportedDrivers);
    }
}
