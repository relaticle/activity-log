# Configuration

> All config keys at a glance.

## `config/activity-log.php`

```php
return [
    // Default page size when ->perPage() isn't called.
    'default_per_page' => 20,

    // Per-source over-fetch buffer: cap = perPage * (page + buffer).
    // Higher = safer dedup/filtering at higher pages; more DB work.
    'pagination_buffer' => 2,

    // Whether dedup is on by default (builder->deduplicate(bool) overrides).
    'deduplicate_by_default' => true,

    // Per-source priority. Higher wins on dedup collisions.
    'source_priorities' => [
        'activity_log'         => 10,
        'related_activity_log' => 10,
        'related_model'        => 20,
        'custom'               => 30,
    ],

    // Labels the infolist component uses when ->groupByDate() is enabled.
    'date_groups' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'older'],

    // Event-or-type → renderer binding. Merged with bindings from the plugin/facade.
    // 'email_sent' => EmailSentRenderer::class (add `use App\Timeline\Renderers\EmailSentRenderer;` at top of file).
    'renderers' => [],

    'cache' => [
        'store'       => null,           // null = default cache store
        'ttl_seconds' => 0,              // 0 = no caching (use ->cached() per call)
        'key_prefix'  => 'activity-log',
    ],
];
```
