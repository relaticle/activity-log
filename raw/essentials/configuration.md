# Configuration

> Full reference for config/activity-log.php.

Every knob the package exposes lives in `config/activity-log.php`. Publish it with `php artisan vendor:publish --tag=activity-log-config` (covered in [/getting-started/installation](/getting-started/installation)) and edit in place. The table below is the canonical reference — every key, every default, every effect.

## Keys

<table>
<thead>
  <tr>
    <th>
      Key
    </th>
    
    <th>
      Type
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
        default_per_page
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      <code>
        20
      </code>
    </td>
    
    <td>
      Used by <code>
        $builder->paginate(null)
      </code>
      
       only. The Filament UI surfaces (infolist, relation manager, action) have their own defaults — see <a href="/essentials/filament-ui#defaults-at-a-glance">
        /essentials/filament-ui#defaults-at-a-glance
      </a>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        pagination_buffer
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      <code>
        2
      </code>
    </td>
    
    <td>
      Per-source over-fetch multiplier: <code>
        cap = perPage × (page + buffer)
      </code>
      
      . See <a href="/essentials/refining-the-timeline#pagination-buffer-mechanics">
        /essentials/refining-the-timeline#pagination-buffer-mechanics
      </a>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        deduplicate_by_default
      </code>
    </td>
    
    <td>
      <code>
        bool
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
    
    <td>
      Builder dedup default; overridable per-call via <code>
        ->deduplicate(false)
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        source_priorities.activity_log
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      <code>
        10
      </code>
    </td>
    
    <td>
      Priority for <code>
        ActivityLogSource
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        source_priorities.related_activity_log
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      <code>
        10
      </code>
    </td>
    
    <td>
      Priority for <code>
        RelatedActivityLogSource
      </code>
      
      . <strong>
        Note
      </strong>
      
      : priority key only — entries from this source carry <code>
        type='activity_log'
      </code>
      
      , NOT <code>
        'related_activity_log'
      </code>
      
      . See <a href="/concepts/how-it-works#type-taxonomy">
        /concepts/how-it-works#type-taxonomy
      </a>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        source_priorities.related_model
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      <code>
        20
      </code>
    </td>
    
    <td>
      Priority for <code>
        RelatedModelSource
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        source_priorities.custom
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      <code>
        30
      </code>
    </td>
    
    <td>
      Priority for <code>
        CustomEventSource
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        renderers
      </code>
    </td>
    
    <td>
      <code>
        array
      </code>
    </td>
    
    <td>
      <code>
        []
      </code>
    </td>
    
    <td>
      Event-or-type → renderer map. See <a href="/essentials/customization#registration-channels">
        /essentials/customization#registration-channels
      </a>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        cache.store
      </code>
    </td>
    
    <td>
      <code>
        ?string
      </code>
    </td>
    
    <td>
      <code>
        null
      </code>
      
       (default cache)
    </td>
    
    <td>
      Laravel cache store name. <strong>
        Recommended: dedicated store
      </strong>
      
       to avoid <code>
        forgetTimelineCache
      </code>
      
       cross-contamination. See <a href="/essentials/caching">
        /essentials/caching
      </a>
      
      .
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
        int
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
        string
      </code>
    </td>
    
    <td>
      <code>
        'activity-log'
      </code>
    </td>
    
    <td>
      Prefix for all cache keys.
    </td>
  </tr>
</tbody>
</table>

## Removed key: `date_groups`

<callout color="warning" icon="i-lucide-alert-triangle">

Earlier docs (and the published config still ships) a `date_groups` key listing 6 bucket labels (`today`, `yesterday`, `this_week`, `last_week`, `this_month`, `older`).

**The key is dead.** `Grep src/` returns zero references. The actual buckets emitted by `ActivityLogLivewire::bucketFor()` are `this_week`, `last_week`, and `week_of <date>` (3 buckets, not 6).

Do NOT add `date_groups` to your published config expecting the buckets to change — it has no effect.

Tracked by [issue #10](https://github.com/relaticle/activity-log/issues/10).

</callout>

## See also

- Renderer registration channels: [/essentials/customization](/essentials/customization).
- Cache invalidation caveats: [/essentials/caching](/essentials/caching).
- Pagination buffer mechanics: [/essentials/refining-the-timeline](/essentials/refining-the-timeline).
