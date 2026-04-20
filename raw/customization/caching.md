# Caching

> Opt-in per-call caching.

Opt-in per call - disabled by default.

## Caching a query

```php
$record->timeline()->cached(ttlSeconds: 300)->paginate();
```

## Invalidating the cache

Invalidate when mutations occur (consumer-driven; the plugin doesn't observe your models):

```php
$record->forgetTimelineCache();
```

## Cache store and prefix

Configure the cache store and key prefix in `config/activity-log.php` under `cache`.
