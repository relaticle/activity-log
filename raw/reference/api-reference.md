# API Reference

> Components, builder refinements, config keys, and caching API.

A reference for every public surface: Filament components, the `TimelineBuilder` refinement API, configuration keys, and caching.

## Filament components

### Infolist component

One infolist entry is shipped. It calls `$record->timeline()` and requires `HasTimeline`.

```php
use Relaticle\ActivityLog\Filament\Infolists\Components\ActivityLog;

ActivityLog::make('activity')
    ->heading('Activity')
    ->groupByDate()                  // bucket by today / this week / older (default: true)
    ->perPage(20)                    // Livewire page size (default: 3)
    ->emptyState('No activity yet.') // custom empty-state message
    ->infiniteScroll(true)           // false renders a "Load more" button (default: true)
    ->columnSpanFull();
```

#### Pagination UX: `infiniteScroll(bool)`

The `infiniteScroll()` fluent flag switches the bottom control:

- `true` (default) - renders a `wire:intersect` sentinel; the next page loads automatically as the user scrolls (Livewire 4).
- `false` - renders a `Load more` button the user clicks.

### Relation manager

A read-only relation manager renders the activity log as a tab on the resource's view/edit page:

```php
use Relaticle\ActivityLog\Filament\RelationManagers\ActivityLogRelationManager;

public static function getRelations(): array
{
    return [
        ActivityLogRelationManager::class,
    ];
}
```

`canViewForRecord()` always returns `true`. It declares a dummy `HasOne` relationship so it doesn't write to the DB - the page just hosts the Livewire component.

The relation manager carries a `protected static bool $infiniteScroll = true` that is forwarded to the Livewire component. Flip it from a service provider if you want the opposite UX:

```php
ActivityLogRelationManager::$infiniteScroll = false;
```

### Header action

Show the activity log in a slide-over modal from any resource table or page header:

```php
use Relaticle\ActivityLog\Filament\Actions\ActivityLogAction;

protected function getHeaderActions(): array
{
    return [
        ActivityLogAction::make(),
    ];
}
```

The action opens a 2XL slide-over with the Livewire component. Customize label/icon/modal width as with any Filament action.

## Refining the timeline

Every refinement is a chainable method on `TimelineBuilder`. Compose only what you need - each section below covers one concern.

### Filtering

Narrow the entries by date window, type, or event. All four filters stack and are cumulative.

```php
$record->timeline()
    ->between(now()->subMonth(), now())         // CarbonInterface|null on each side
    ->ofType(['related_model', 'activity_log']) // type allow-list
    ->exceptType(['custom'])                    // type deny-list
    ->ofEvent(['email_sent', 'task_completed']) // event allow-list
    ->exceptEvent(['draft_saved']);             // event deny-list
```

### Sorting

Order the combined stream after sources are merged.

```php
$record->timeline()
    ->sortByDateDesc(); // default; use sortByDateAsc() for ascending
```

### Deduplication

Entries sharing a `dedupKey` collapse to the highest `sourcePriority` (first occurrence wins on ties). Disable entirely, or override the key when the default identity isn't right for your use case.

```php
$record->timeline()
    ->deduplicate(true) // default: true - pass false to keep every entry
    ->dedupKeyUsing(fn ($entry) =>
        "{$entry->type}:{$entry->event}:{$entry->occurredAt->toDateString()}"
    );
```

### Running the query

After filters/sort/dedup are set, one of these methods executes and returns entries.

<table>
<thead>
  <tr>
    <th>
      Method
    </th>
    
    <th>
      Returns
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
      
       - all entries up to the internal 10 000 cap.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        paginate(?int $perPage, int $page = 1)
      </code>
    </td>
    
    <td>
      <code>
        LengthAwarePaginator<int, TimelineEntry>
      </code>
      
      . Uses <code>
        activity-log.default_per_page
      </code>
      
       if <code>
        $perPage
      </code>
      
       is null.
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
      
       (runs <code>
        get()
      </code>
      
      ).
    </td>
  </tr>
</tbody>
</table>

## Caching

Opt-in per call - disabled by default.

### Caching a query

```php
$record->timeline()->cached(ttlSeconds: 300)->paginate();
```

### Invalidating the cache

Invalidate when mutations occur (consumer-driven; the plugin doesn't observe your models):

```php
$record->forgetTimelineCache();
```

### Cache store and prefix

Configure the cache store and key prefix in `config/activity-log.php` under `cache` (see [Configuration](#configuration) below).

## Configuration

All config keys live in `config/activity-log.php`:

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
