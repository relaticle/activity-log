# Integration Patterns

> Wire HasTimeline, compose sources, and render the feed.

This page shows how to integrate Activity Log into a model: mark it timeline-capable, compose the sources that feed the timeline, then render the result.

## 1. Mark the model as timeline-capable

Implement the `HasTimeline` contract, use the `InteractsWithTimeline` trait, and define a `timeline(): TimelineBuilder` method:

```php
use Illuminate\Database\Eloquent\Model;
use Relaticle\ActivityLog\Concerns\InteractsWithTimeline;
use Relaticle\ActivityLog\Contracts\HasTimeline;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Relaticle\ActivityLog\Timeline\Sources\RelatedModelSource;
use Spatie\Activitylog\Traits\LogsActivity;

class Person extends Model implements HasTimeline
{
    use InteractsWithTimeline;
    use LogsActivity;

    public function timeline(): TimelineBuilder
    {
        return TimelineBuilder::make($this)
            ->fromActivityLog()
            ->fromActivityLogOf(['emails', 'notes', 'tasks'])
            ->fromRelation('emails', function (RelatedModelSource $source): void {
                $source
                    ->event(
                        column: 'sent_at',
                        event: 'email_sent',
                        icon: 'heroicon-o-paper-airplane',
                        color: 'primary',
                    )
                    ->event(
                        column: 'received_at',
                        event: 'email_received',
                        icon: 'heroicon-o-inbox-arrow-down',
                        color: 'info',
                    )
                    ->title(fn ($email): string => $email->subject ?? 'Email')
                    ->causer(fn ($email) => $email->from->first());
            });
    }
}
```

## 2. Core building blocks

<table>
<thead>
  <tr>
    <th>
      Concept
    </th>
    
    <th>
      What it represents
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <strong>
        <code>
          TimelineBuilder
        </code>
      </strong>
    </td>
    
    <td>
      Fluent builder that composes sources, applies filters, and returns paginated <code>
        TimelineEntry
      </code>
      
       collections. Built per-record via <code>
        $record->timeline()
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        <code>
          TimelineSource
        </code>
      </strong>
    </td>
    
    <td>
      Produces <code>
        TimelineEntry
      </code>
      
       objects from a single origin - spatie log, related timestamps, or custom closure.
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        <code>
          TimelineEntry
        </code>
      </strong>
    </td>
    
    <td>
      Immutable value object describing one event (<code>
        event
      </code>
      
      , <code>
        occurredAt
      </code>
      
      , <code>
        title
      </code>
      
      , <code>
        causer
      </code>
      
      , <code>
        properties
      </code>
      
      , …).
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        <code>
          TimelineRenderer
        </code>
      </strong>
    </td>
    
    <td>
      Converts a <code>
        TimelineEntry
      </code>
      
       into a Blade <code>
        View
      </code>
      
       or <code>
        HtmlString
      </code>
      
      . Register custom ones per <code>
        event
      </code>
      
       or <code>
        type
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        Priority
      </strong>
    </td>
    
    <td>
      Each source carries a priority; on <code>
        dedupKey
      </code>
      
       collisions the higher one wins.
    </td>
  </tr>
</tbody>
</table>

### Default source priorities

<table>
<thead>
  <tr>
    <th>
      Source
    </th>
    
    <th>
      Priority
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        activity_log
      </code>
    </td>
    
    <td>
      10
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        related_activity_log
      </code>
    </td>
    
    <td>
      10
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        related_model
      </code>
    </td>
    
    <td>
      20
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        custom
      </code>
    </td>
    
    <td>
      30
    </td>
  </tr>
</tbody>
</table>

## 3. Source patterns

All sources are registered fluently on `TimelineBuilder`. Mix any number of them in one timeline.

### The record's own spatie log

Use `fromActivityLog()` to pull the subject's own `activity_log` rows:

```php
TimelineBuilder::make($this)->fromActivityLog();
```

Reads rows from `activity_log` where `subject_type` + `subject_id` match `$this`. Entry `event` = the spatie `event` column (or `description` as fallback).

### Related models' spatie logs

Use `fromActivityLogOf(array $relations)` to include spatie logs from related records:

```php
TimelineBuilder::make($this)->fromActivityLogOf(['emails', 'notes', 'tasks']);
```

For each named relation, reads `activity_log` rows whose subject matches any related record. Useful for "show me everything that happened to anything attached to this person."

### Timestamp columns on related models

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

#### `RelatedModelSource` API

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

### Custom or external data

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

### Reusable custom source classes

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

## 4. Render the timeline

Drop the infolist component onto any resource that owns a timeline-capable model:

```php
use Filament\Schemas\Schema;
use Relaticle\ActivityLog\Filament\Infolists\Components\ActivityLog;

public static function infolist(Schema $schema): Schema
{
    return $schema->components([
        ActivityLog::make('activity')
            ->heading('Activity')
            ->groupByDate()
            ->perPage(20)
            ->columnSpanFull(),
    ]);
}
```

See the [API Reference](/reference/api-reference) for every component, filter, and configuration knob; see [Customization](/essentials/customization) to theme the output.
