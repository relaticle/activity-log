# Refining the Timeline

> Filters, sorting, and deduplication.

Every refinement is a chainable method on `TimelineBuilder`. Compose only what you need - each section below covers one concern.

## Filtering

Narrow the entries by date window, type, or event. All four filters stack and are cumulative.

```php
$record->timeline()
    ->between(now()->subMonth(), now())         // CarbonInterface|null on each side
    ->ofType(['related_model', 'activity_log']) // type allow-list
    ->exceptType(['custom'])                    // type deny-list
    ->ofEvent(['email_sent', 'task_completed']) // event allow-list
    ->exceptEvent(['draft_saved']);             // event deny-list
```

## Sorting

Order the combined stream after sources are merged.

```php
$record->timeline()
    ->sortByDateDesc(); // default; use sortByDateAsc() for ascending
```

## Deduplication

Entries sharing a `dedupKey` collapse to the highest `sourcePriority` (first occurrence wins on ties). Disable entirely, or override the key when the default identity isn't right for your use case.

```php
$record->timeline()
    ->deduplicate(true) // default: true - pass false to keep every entry
    ->dedupKeyUsing(fn ($entry) =>
        "{$entry->type}:{$entry->event}:{$entry->occurredAt->toDateString()}"
    );
```

## Running the query

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
