# Filtering, Sorting, Dedup

> Chainable filters, sort order, and deduplication behaviour.

All methods are chainable on `TimelineBuilder`:

```php
$record->timeline()
    ->between(now()->subMonth(), now())            // CarbonInterface|null on each side
    ->ofType(['related_model', 'activity_log'])     // allow-list
    ->exceptType(['custom'])                        // deny-list
    ->ofEvent(['email_sent', 'task_completed'])
    ->exceptEvent(['draft_saved'])
    ->sortByDateDesc()                              // default; use sortByDateAsc() for ascending
    ->deduplicate(false)                            // default: true
    ->dedupKeyUsing(fn ($entry) => $entry->type.':'.$entry->event.':'.$entry->occurredAt->toDateString())
    ->paginate(perPage: 20, page: 1);
```

Dedup behaviour: entries sharing a `dedupKey` collapse to the highest `sourcePriority` (first occurrence wins on ties). Override the key with `dedupKeyUsing()` if the default identity isn't right for your use case.

## Methods that run the query

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
      
       — all entries up to the internal 10 000 cap.
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
