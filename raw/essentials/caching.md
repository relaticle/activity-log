# Caching

> Opt-in per-call caching, key composition, and invalidation caveats.

Caching is **opt-in per call** — disabled by default. Reach for it on hot pages where the timeline doesn't change often: audit views, public profiles, dashboards rendered on every request. The simplest form wraps a `paginate()` in a TTL:

```php
$entries = $record->timeline()->cached(ttlSeconds: 300)->paginate();
```

## `->cached(int $ttlSeconds)`

Position in the chain: call **before** `paginate()` or `get()`. It has no effect on `count()` (which runs through `get()` un-cached internally — see [/essentials/refining-the-timeline](/essentials/refining-the-timeline) for why).

The cache lookup uses the active store, configured via `cache.store` (see [Configuration knobs](#configuration-knobs) below).

<callout color="info" icon="i-lucide-lightbulb">

**cached(0) is a no-op.** The TTL=0 short-circuit is intentional — useful for conditionally enabling cache via a flag without changing the call site:

```php
$record->timeline()
    ->cached(ttlSeconds: $cacheEnabled ? 300 : 0)
    ->paginate();
```

</callout>

## Cache key composition

Each cached `paginate()` call composes its key from the subject, the active filter set, and the page coordinates:

```text
{prefix}:{model_class}:{key}:{filter_hash}:p{page}:pp{perPage}
```

Where:

- `{prefix}` — the `cache.key_prefix` config value (default `'activity-log'`).
- `{model_class}` — the subject's class name with `\` replaced by `_` (so `App_Models_User` rather than `App\Models\User`).
- `{key}` — `$subject->getKey()`.
- `{filter_hash}` — a stringified hash of all builder filters: `between`, type allow/deny, event allow/deny, sort direction, source count.
- `{page}` and `{perPage}` — pagination params.

Changing any filter — `->ofType(...)`, `->between(...)`, `->sortByDateAsc()` — produces a different key, so re-running the same builder with different chain state does **not** collide on a stale entry.

## Invalidation — known limitation

<callout color="warning" icon="i-lucide-alert-triangle">

**$record->forgetTimelineCache() flushes the entire cache store, not just this subject's timeline entries.**

Internally it calls `Cache::store(...)->getStore()->flush()`. If you share the default cache store with sessions, application caches, queue locks, or anything else, calling `forgetTimelineCache()` clears all of them.

Tracked by [issue #12](https://github.com/relaticle/activity-log/issues/12). The recommended fix is tagged-cache invalidation keyed on the per-subject prefix.

**Workarounds until the fix lands:**

- **Use a dedicated cache store.** Set `cache.store` to a Redis database, file path, or memory store dedicated to the timeline. Flushing it then only affects timeline entries — see [Configuration knobs](#configuration-knobs) below.
- **Skip explicit invalidation.** Pick a TTL short enough that staleness is acceptable (e.g. 60 seconds for a high-traffic dashboard) and let entries expire naturally. No `forgetTimelineCache()` call needed.

</callout>

## Configuration knobs

Short reference here; the full table lives on [/essentials/configuration#cache](/essentials/configuration#cache).

<table>
<thead>
  <tr>
    <th>
      Key
    </th>
    
    <th>
      Default
    </th>
    
    <th>
      Effect
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        cache.store
      </code>
    </td>
    
    <td>
      <code>
        null
      </code>
      
       (default cache)
    </td>
    
    <td>
      Which Laravel cache store to use. <strong>
        Strongly recommended: a dedicated store
      </strong>
      
       (see invalidation caveat above).
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        cache.ttl_seconds
      </code>
    </td>
    
    <td>
      <code>
        0
      </code>
    </td>
    
    <td>
      Reserved; not currently consulted by <code>
        TimelineCache
      </code>
      
      . The per-call <code>
        ->cached($ttl)
      </code>
      
       is the working knob.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        cache.key_prefix
      </code>
    </td>
    
    <td>
      <code>
        'activity-log'
      </code>
    </td>
    
    <td>
      Namespace for cache keys.
    </td>
  </tr>
</tbody>
</table>
