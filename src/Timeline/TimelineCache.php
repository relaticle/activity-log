<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Timeline;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class TimelineCache
{
    public function store(): Repository
    {
        $storeName = config('activity-log.cache.store');

        return $storeName === null ? Cache::store() : Cache::store($storeName);
    }

    public function keyFor(Model $subject, string $filterHash, int $page, int $perPage): string
    {
        return sprintf(
            '%s:%s:p%d:pp%d',
            $this->subjectPrefix($subject),
            $filterHash,
            $page,
            $perPage,
        );
    }

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(Model $subject, string $key, int $ttl, Closure $callback): mixed
    {
        $this->trackKey($subject, $key);

        return $this->store()->remember($key, $ttl, $callback);
    }

    public function forget(Model $subject): void
    {
        $store = $this->store();
        $indexKey = $this->indexKey($subject);

        /** @var array<int, string> $keys */
        $keys = $store->get($indexKey, []);

        foreach ($keys as $key) {
            $store->forget($key);
        }

        $store->forget($indexKey);
    }

    private function trackKey(Model $subject, string $key): void
    {
        $store = $this->store();
        $indexKey = $this->indexKey($subject);

        /** @var array<int, string> $keys */
        $keys = $store->get($indexKey, []);

        if (in_array($key, $keys, true)) {
            return;
        }

        $keys[] = $key;
        $store->forever($indexKey, $keys);
    }

    private function indexKey(Model $subject): string
    {
        return $this->subjectPrefix($subject).':index';
    }

    private function subjectPrefix(Model $subject): string
    {
        $prefix = (string) config('activity-log.cache.key_prefix', 'activity-log');

        return sprintf(
            '%s:%s:%s',
            $prefix,
            str_replace('\\', '_', $subject::class),
            (string) $subject->getKey(),
        );
    }
}
