# Refining the timeline

> Filter, sort, deduplicate, and paginate the merged stream.

Every method on this page is a chainable call on `Relaticle\ActivityLog\Timeline\TimelineBuilder` between source registration and result consumption. For source registration see [/essentials/sources](/essentials/sources); for the lifecycle and dedup model see [/concepts/how-it-works](/concepts/how-it-works).

## Filtering

Five filters are available — one window, two type filters (allow/deny), two event filters (allow/deny):

```php
$record->timeline()
    ->between(now()->subMonth(), now())
    ->ofType(['activity_log'])
    ->exceptType(['custom'])
    ->ofEvent(['created', 'updated'])
    ->exceptEvent(['draft_saved'])
    ->get();
```

<table>
<thead>
  <tr>
    <th>
      Method
    </th>
    
    <th>
      Purpose
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        between(?CarbonInterface $from, ?CarbonInterface $to)
      </code>
    </td>
    
    <td>
      Passes through to each source's <code>
        Window
      </code>
      
      . Either side may be <code>
        null
      </code>
      
       for an open-ended range.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        ofType(array $types)
      </code>
    </td>
    
    <td>
      Entry-type allow-list. Anything not in the list is dropped.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        exceptType(array $types)
      </code>
    </td>
    
    <td>
      Entry-type deny-list. Anything in the list is dropped.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        ofEvent(array $events)
      </code>
    </td>
    
    <td>
      Event-name allow-list.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        exceptEvent(array $events)
      </code>
    </td>
    
    <td>
      Event-name deny-list.
    </td>
  </tr>
</tbody>
</table>

Type and event filters are mirrored onto every source's `Window` so sources can push them into SQL where possible. The builder also re-applies them post-yield as a safety net, so a source that ignores the `Window` filters still produces correct output.

<callout color="warning" icon="i-lucide-alert-triangle">

**There are only three entry types today: activity_log, related_model, custom.** Entries from `RelatedActivityLogSource` carry `type='activity_log'` — calling `->ofType(['related_activity_log'])` will silently match nothing. See the [type taxonomy](/concepts/how-it-works#type-taxonomy) for the full breakdown.

</callout>

## Sorting

```php
$record->timeline()->sortByDateDesc()->get(); // default — newest first

$record->timeline()->sortByDateAsc()->get();  // oldest first
```

Sorting happens **after** sources merge, so individual sources may yield in any order. The sort key is `$entry->occurredAt->getTimestamp()`.

## Deduplication

```php
$record->timeline()->deduplicate(false)->get(); // disable dedup entirely
```

Override the dedup key with a closure that receives each `TimelineEntry`:

```php
$record->timeline()
    ->dedupKeyUsing(fn ($entry) => "{$entry->type}:{$entry->event}:{$entry->occurredAt->toDateString()}");
```

The default dedup key is `{class}:{id}:{occurredAt-iso}`. See [/concepts/how-it-works#dedup-behavior](/concepts/how-it-works#dedup-behavior) for what wins when keys collide (highest `sourcePriority`; ties broken by registration order).

## Running the query

<table>
<thead>
  <tr>
    <th>
      Method
    </th>
    
    <th>
      Returns
    </th>
    
    <th>
      Notes
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        get()
      </code>
    </td>
    
    <td>
      <code>
        Collection<int, TimelineEntry>
      </code>
    </td>
    
    <td>
      Caps at 10000 entries internally.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        paginate(?int $perPage = null, int $page = 1)
      </code>
    </td>
    
    <td>
      <code>
        LengthAwarePaginator<int, TimelineEntry>
      </code>
    </td>
    
    <td>
      <code>
        $perPage = null
      </code>
      
       falls back to <code>
        default_per_page
      </code>
      
       config (20).
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        count()
      </code>
    </td>
    
    <td>
      <code>
        int
      </code>
    </td>
    
    <td>
      Runs <code>
        get()
      </code>
      
       internally — no separate <code>
        COUNT
      </code>
      
       query. Use sparingly on large datasets.
    </td>
  </tr>
</tbody>
</table>

```php
$entries = $record->timeline()->fromActivityLog()->get();

$page = $record->timeline()->fromActivityLog()->paginate(perPage: 25, page: 2);

$total = $record->timeline()->fromActivityLog()->count();
```

<callout color="info" icon="i-lucide-lightbulb">

For in-Filament rendering you almost never call these directly — the infolist component, relation manager, and action all mount the Livewire timeline component, which calls `paginate()` for you. Direct calls are useful for tests, queue workers, exports, and other non-UI consumers.

</callout>

## Trait helper: `paginateTimeline()`

The `Relaticle\ActivityLog\Concerns\InteractsWithTimeline` trait exposes `paginateTimeline(?int $perPage = null, int $page = 1)` as a pass-through to `$this->timeline()->paginate(...)`. It's required by the `Relaticle\ActivityLog\Contracts\HasTimeline` interface, so consumers can paginate without going through the builder explicitly:

```php
$page = $opportunity->paginateTimeline(perPage: 20, page: 1);
```

This is the entry point the Livewire timeline component uses internally. Override `timeline()` on the model to customize source registration once and have every caller — including `paginateTimeline()` — pick it up.

## Pagination buffer mechanics

Pagination uses an over-fetch strategy controlled by the `pagination_buffer` config key (default `2`):

```text
cap = perPage × (page + buffer)
```

Each source is asked for at most `cap` entries via `Window::cap`. After dedup and post-source filtering drop entries, the remaining set is sliced down to the requested page. Over-fetching keeps deeper pages from coming up short when entries are removed during the pipeline.

**Trade-off:**

- Higher `buffer` — more memory, more rows scanned per source, but exact pagination at deep pages even when sources collide heavily.
- Lower `buffer` — less memory, fewer rows scanned, but risk of a deep page returning fewer than `perPage` entries when dedup removes a lot.

**When to lower it:** sources rarely collide (no overlapping `dedupKey`s) and your filters are permissive. The default `2` is overkill for single-source timelines.

**When to raise it:** sources overlap heavily — for example, a custom source that re-emits events also covered by spatie activity log — and you need exact page sizes at high page numbers.

See [/essentials/configuration](/essentials/configuration) for the config key.
