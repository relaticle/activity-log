# Filament UI

> The infolist component, relation manager, header action, and URL filter UI.

The package ships three Filament surfaces — an infolist component, a relation manager, and a header action. All three mount the same `Relaticle\ActivityLog\Filament\Livewire\ActivityLogLivewire` component under the hood, but each exposes a different override mechanism and ships with different defaults. This page covers every knob, plus the URL-bound filter UI that the Livewire component renders for free.

## Defaults at a glance

<table>
<thead>
  <tr>
    <th>
      Surface
    </th>
    
    <th>
      <code>
        groupByDate
      </code>
    </th>
    
    <th>
      <code>
        perPage
      </code>
    </th>
    
    <th>
      <code>
        infiniteScroll
      </code>
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        ActivityLog
      </code>
      
       infolist
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
    
    <td>
      <code>
        3
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        ActivityLogRelationManager
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
      
       (static <code>
        $groupByDate
      </code>
      
      )
    </td>
    
    <td>
      <code>
        20
      </code>
      
       (static <code>
        $perPage
      </code>
      
      )
    </td>
    
    <td>
      <code>
        true
      </code>
      
       (static <code>
        $infiniteScroll
      </code>
      
      )
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        ActivityLogAction
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
      
       (hardcoded)
    </td>
    
    <td>
      builder default (20 from <code>
        default_per_page
      </code>
      
      )
    </td>
    
    <td>
      n/a
    </td>
  </tr>
</tbody>
</table>

The `default_per_page` config key only governs `$builder->paginate(null)` calls made directly without UI — see [/essentials/configuration](/essentials/configuration). The infolist and relation manager pass an explicit `perPage` to the Livewire component and bypass the config value.

## Infolist component

`Relaticle\ActivityLog\Filament\Infolists\Components\ActivityLog` is the standard way to embed the timeline inside an existing Filament infolist (resource view page, custom page, or modal).

```php
use Relaticle\ActivityLog\Filament\Infolists\Components\ActivityLog;

ActivityLog::make('timeline')
    ->groupByDate()
    ->perPage(10)
    ->emptyState('Nothing here yet — give it time.')
    ->infiniteScroll()
    ->columnSpanFull(),
```

### Fluent surface

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
        make(string $name)
      </code>
    </td>
    
    <td>
      Standard Filament constructor. Name is the schema key.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        groupByDate(bool $enabled = true)
      </code>
    </td>
    
    <td>
      Buckets entries by <code>
        this_week
      </code>
      
       / <code>
        last_week
      </code>
      
       / <code>
        week_of <date>
      </code>
      
      . Defaults to <code>
        true
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        perPage(int $perPage)
      </code>
    </td>
    
    <td>
      Page size for the timeline. Defaults to <code>
        3
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        emptyState(string $message)
      </code>
    </td>
    
    <td>
      Replaces the default <code>
        "No activity yet."
      </code>
      
       message.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        infiniteScroll(bool $enabled = true)
      </code>
    </td>
    
    <td>
      <code>
        true
      </code>
      
       (default) renders a <code>
        wire:intersect
      </code>
      
       sentinel that auto-loads on scroll; <code>
        false
      </code>
      
       renders an explicit "Load more" button.
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        record(Model $record)
      </code>
    </td>
    
    <td>
      Bind a non-current record. Alias for <code>
        model()
      </code>
      
      . Useful when the record can't be inferred from the form context.
    </td>
  </tr>
</tbody>
</table>

The component extends `Filament\Infolists\Components\Entry`, so all standard methods (`columnSpanFull()`, `visible()`, `hidden()`, etc.) work as expected.

<callout color="info" icon="i-lucide-info">

The bound record must implement `Relaticle\ActivityLog\Contracts\HasTimeline`. The component throws a `LogicException` at render time if the record is missing or doesn't implement the contract.

</callout>

## Relation manager

`Relaticle\ActivityLog\Filament\RelationManagers\ActivityLogRelationManager` adds a dedicated "Activity" tab to a resource. Register it in your resource's `getRelations()`:

```php
use Relaticle\ActivityLog\Filament\RelationManagers\ActivityLogRelationManager;

public static function getRelations(): array
{
    return [
        ActivityLogRelationManager::class,
    ];
}
```

`canViewForRecord()` always returns `true`, so the tab shows for every record. Override the method in a subclass if you need permission gating.

### Overriding defaults

The relation manager exposes three public static properties. Set them once from a service provider's `boot()` to apply project-wide:

```php
use Relaticle\ActivityLog\Filament\RelationManagers\ActivityLogRelationManager;

public function boot(): void
{
    ActivityLogRelationManager::$infiniteScroll = false;
    ActivityLogRelationManager::$groupByDate = false;
    ActivityLogRelationManager::$perPage = 50;
}
```

<callout color="info" icon="i-lucide-info">

The relation manager doesn't need a real database relation. `getRelationship()` returns a self-referential `HasOne` against the owner record itself, which satisfies Filament's machinery while letting the embedded `ActivityLogLivewire` component own the data fetching. `getTableQuery()` returns `null` for the same reason.

</callout>

## Header action

`Relaticle\ActivityLog\Filament\Actions\ActivityLogAction` opens the timeline in a slide-over from any page header. Register it in `getHeaderActions()`:

```php
use Relaticle\ActivityLog\Filament\Actions\ActivityLogAction;

protected function getHeaderActions(): array
{
    return [
        ActivityLogAction::make(),
    ];
}
```

### Defaults

`ActivityLogAction::getDefaultName()` returns `'activityLog'`. Use that name when calling the action in tests (`->callAction('activityLog')`) or when overriding it in a panel via `Action::configureUsing()`.

`setUp()` applies the following defaults:

- `label` — `__('activity-log::messages.title')`
- `icon` — `heroicon-o-bars-3-bottom-left`
- `color` — `gray`
- `modalWidth` — `Filament\Support\Enums\Width::TwoExtraLarge`
- `slideOver()` — yes
- `modalSubmitAction(false)` and `modalCancelActionLabel(__('activity-log::messages.close'))` — no submit, just a close button

Standard Filament action methods (`->visible()`, `->color()`, `->modalHeading()`, `->icon()`, etc.) work normally and override the defaults above.

<callout color="warning" icon="i-lucide-alert-triangle">

**groupByDate is hardcoded to true** inside the action's `Livewire::make()` mount, and **perPage is not exposed at all** — the Livewire component falls back to its own default of `20`. If you need different defaults inside the slide-over, either use the relation manager (configurable via the three statics above) or build a custom action that mounts `Livewire::make(ActivityLogLivewire::class, [...])` with your own arguments.

</callout>

## Built-in URL filter UI

The `ActivityLogLivewire` component ships three filters bound to URL query parameters via Livewire's `#[Url]` attribute:

```php
#[Url(as: 'type')]  public ?string $typeFilter = null;
#[Url(as: 'from')]  public ?string $fromDate = null;
#[Url(as: 'to')]    public ?string $toDate = null;
```

The timeline view renders the filter chrome (entry-type select, date range, clear button) automatically. State syncs to the URL on every change, which means filtered views are shareable and bookmarkable:

```text
/admin/people/42?type=activity_log&from=2026-01-01&to=2026-04-30
```

Each filter mutates the underlying `Relaticle\ActivityLog\Timeline\TimelineBuilder` before the entries are resolved:

- `typeFilter` → `$builder->ofType([$typeFilter])`
- `fromDate` and `toDate` → `$builder->between(CarbonImmutable::parse($fromDate), CarbonImmutable::parse($toDate))` (either or both may be `null`)

A `resetFilters()` Livewire action is wired to the clear button — it nulls all three properties and rewinds `visibleCount` to `perPage`.

<callout color="info" icon="i-lucide-info">

The `typeFilter` value is matched against `$entry->type`, so only the three real entry-type values resolve (`activity_log`, `related_model`, `custom`). Filtering by `'related_activity_log'` matches nothing — see the type taxonomy in [/concepts/how-it-works](/concepts/how-it-works#type-taxonomy).

</callout>

For programmatic equivalents — pre-applying filters from PHP rather than the URL — see [/essentials/refining-the-timeline](/essentials/refining-the-timeline).
