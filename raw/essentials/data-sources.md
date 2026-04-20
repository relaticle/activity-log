# Data Sources

> Compose sources for your timeline.

All sources are registered fluently on `TimelineBuilder`. You can mix any number of them in one timeline.

## The record's own spatie log

Use `fromActivityLog()` to pull the subject's own `activity_log` rows:

```php
TimelineBuilder::make($this)->fromActivityLog();
```

Reads rows from `activity_log` where `subject_type` + `subject_id` match `$this`. Entry `event` = the spatie `event` column (or `description` as fallback).

## Related models' spatie logs

Use `fromActivityLogOf(array $relations)` to include spatie logs from related records:

```php
TimelineBuilder::make($this)->fromActivityLogOf(['emails', 'notes', 'tasks']);
```

For each named relation, reads `activity_log` rows whose subject matches any related record. Useful for "show me everything that happened to anything attached to this person."

## Timestamp columns on related models

Use `fromRelation(string $relation, Closure $configure)` to turn rows on a related model into timeline entries keyed by a timestamp column. Ideal when related records already carry canonical timestamps (`sent_at`, `completed_at`, `created_at`) and you don't need spatie-style change logs.

```php
->fromRelation('tasks', function (RelatedModelSource $source): void {
    $source
        ->event(
            column: 'completed_at',
            event: 'task_completed',
            icon: 'heroicon-o-check-circle',
            color: 'success',
        )
        ->event(
            column: 'created_at',
            event: 'task_created',
            icon: 'heroicon-o-plus-circle',
        )
        // Eager-load related rows to avoid N+1 inside renderers.
        ->with(['creator', 'assignee'])
        // Extra query constraints (scopes, tenant filters, etc.).
        ->using(fn ($query) => $query->whereNull('archived_at'))
        ->title(fn ($task): string => $task->title ?? 'Task')
        ->description(fn ($task): ?string => $task->summary)
        // Relation name on the row, or a Closure returning Model|null.
        ->causer('creator');
})
```

### `RelatedModelSource` API

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
        event($column, $event, $icon?, $color?, $when?)
      </code>
    </td>
    
    <td>
      Register one event per timestamp column. <code>
        $when
      </code>
      
       is an optional row-level filter returning <code>
        bool
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        with($relations)
      </code>
    </td>
    
    <td>
      Eager-loads relations on every event query - prevents N+1 in renderers.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        using($modifier)
      </code>
    </td>
    
    <td>
      Arbitrary query modifier (scopes, tenant filters, etc.).
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        title($resolver)
      </code>
      
       / <code>
        description($resolver)
      </code>
    </td>
    
    <td>
      Per-row resolver for display fields.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        causer($resolver)
      </code>
    </td>
    
    <td>
      Relation-name string, or Closure returning <code>
        Model|null
      </code>
      
      .
    </td>
  </tr>
</tbody>
</table>

## Custom or external data

Use `fromCustom(Closure $resolver)` when the data isn't in `activity_log` and isn't a relation (e.g. entries coming from an external API). Yield your own `TimelineEntry` objects:

```php
->fromCustom(function (Model $subject, Window $window): iterable {
    $rows = ExternalApi::events(
        subject: $subject,
        from: $window->from,
        to: $window->to,
        limit: $window->cap,
    );

    foreach ($rows as $row) {
        yield new TimelineEntry(
            id: 'external:'.$row['id'],
            type: 'custom',
            event: $row['event'],
            occurredAt: CarbonImmutable::parse($row['at']),
            dedupKey: 'external:'.$row['id'],
            sourcePriority: 30,
            title: $row['title'],
        );
    }
})
```

## Reusable custom source classes

Use `addSource(TimelineSource $source)` for sources you want to reuse across models. Implement `Relaticle\ActivityLog\Contracts\TimelineSource` and pass it directly - useful when the resolution logic warrants its own class.

The contract has two methods - `priority()` for dedup ordering, and `resolve()` which yields `TimelineEntry` objects for the given `$subject` within the `Window`'s date range and cap:

```php
namespace App\Timeline\Sources;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Relaticle\ActivityLog\Contracts\TimelineSource;
use Relaticle\ActivityLog\Timeline\TimelineEntry;
use Relaticle\ActivityLog\Timeline\Window;

final class StripePaymentSource implements TimelineSource
{
    public function priority(): int
    {
        return 30;
    }

    public function resolve(Model $subject, Window $window): iterable
    {
        $payments = StripeClient::paymentsFor(
            customer: $subject->stripe_id,
            from: $window->from,
            to: $window->to,
            limit: $window->cap,
        );

        foreach ($payments as $payment) {
            yield new TimelineEntry(
                id: "stripe:{$payment->id}",
                type: 'custom',
                event: 'payment_succeeded',
                occurredAt: CarbonImmutable::parse($payment->created_at),
                dedupKey: "stripe:{$payment->id}",
                sourcePriority: $this->priority(),
                title: "Payment of {$payment->amount_formatted}",
            );
        }
    }
}
```

Then register it on the builder:

```php
public function timeline(): TimelineBuilder
{
    return TimelineBuilder::make($this)
        ->fromActivityLog()
        ->addSource(new StripePaymentSource());
}
```
