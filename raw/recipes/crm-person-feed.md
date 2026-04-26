# CRM person feed

> End-to-end person profile timeline — own log, related logs, timestamp events, custom renderer.

You're building a CRM. Each `Person` profile shows a unified feed: profile changes (own spatie log) plus activity on attached emails, notes, and tasks (related spatie logs), plus canonical "email sent" / "task completed" events derived from timestamp columns. A custom renderer makes the email-sent entries scannable.

## Setup

Assuming you've completed [/getting-started/installation](/getting-started/installation), this recipe expects:

- A `Person` model with `HasMany` relations: `emails`, `notes`, `tasks`.
- All three related models (`Email`, `Note`, `Task`) use `Spatie\Activitylog\Traits\LogsActivity`.
- `Person` itself uses `LogsActivity` so profile changes show up.
- The `Email` model has a `sent_at` timestamp column; `Task` has `completed_at`.

## Step 1 — `Person` model with composed `timeline()`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Relaticle\ActivityLog\Concerns\InteractsWithTimeline;
use Relaticle\ActivityLog\Contracts\HasTimeline;
use Relaticle\ActivityLog\Timeline\Sources\RelatedModelSource;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Person extends Model implements HasTimeline
{
    use InteractsWithTimeline;
    use LogsActivity;

    protected $fillable = ['name', 'email', 'company'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeline(): TimelineBuilder
    {
        return TimelineBuilder::make($this)
            ->fromActivityLog()
            ->fromActivityLogOf(['emails', 'notes', 'tasks'])
            ->fromRelation('emails', function (RelatedModelSource $source): void {
                $source
                    ->event(column: 'sent_at', event: 'email_sent', icon: 'heroicon-o-paper-airplane', color: 'primary')
                    ->title(fn (Email $email): string => $email->subject ?? 'Email')
                    ->description(fn (Email $email): ?string => "to {$email->recipient}")
                    ->causer(fn (Email $email) => $email->sender);
            })
            ->fromRelation('tasks', function (RelatedModelSource $source): void {
                $source
                    ->event(column: 'completed_at', event: 'task_completed', icon: 'heroicon-o-check-circle', color: 'success')
                    ->title(fn (Task $task): string => $task->title);
            });
    }
}
```

Each chained source contributes one "lane" to the merged feed. The two spatie sources cover whatever events your `LogOptions` log; the two `fromRelation` calls add canonical timestamp events that aren't in spatie. Dedup happens automatically on `(class, id, occurredAt)` — no double-counting if a related model is also spatie-logged. See [/concepts/how-it-works](/concepts/how-it-works) for the merge mechanics.

## Step 2 — Custom renderer for `email_sent`

The renderer class implements `Relaticle\ActivityLog\Contracts\TimelineRenderer`:

```php
namespace App\Timeline\Renderers;

use Illuminate\Contracts\View\View;
use Relaticle\ActivityLog\Contracts\TimelineRenderer;
use Relaticle\ActivityLog\Timeline\TimelineEntry;

final class EmailSentRenderer implements TimelineRenderer
{
    public function render(TimelineEntry $entry): View
    {
        return view('app.timeline.email-sent', ['entry' => $entry]);
    }
}
```

The Blade view at `resources/views/app/timeline/email-sent.blade.php`:

```blade
<div class="flex items-start gap-3">
    <x-filament::icon icon="heroicon-o-paper-airplane" class="size-5 text-primary-500"/>
    <div class="min-w-0 flex-1">
        <div class="font-medium">{{ $entry->title }}</div>
        <div class="text-sm text-gray-500">{{ $entry->description }} · {{ $entry->occurredAt->diffForHumans() }}</div>
    </div>
</div>
```

Register the renderer via the panel plugin in your `AppPanelProvider`:

```php
use App\Timeline\Renderers\EmailSentRenderer;
use Relaticle\ActivityLog\Filament\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(ActivityLogPlugin::make()->renderers([
            'email_sent' => EmailSentRenderer::class,
        ]))
        // ...
    ;
}
```

The registry resolves event-keyed bindings before type-keyed ones, so only `email_sent` entries get this renderer — everything else falls through to the defaults. See [/essentials/customization](/essentials/customization#renderer-resolution-order) for the full lookup order.

## Step 3 — Wire the Filament UI

Two surfaces — the full feed on the view page, and a slide-over from the table.

On the Person view page, drop the `ActivityLog` infolist component:

```php
use Relaticle\ActivityLog\Filament\Infolists\Components\ActivityLog;

public function infolist(Schema $schema): Schema
{
    return $schema->components([
        // ... your existing entries
        ActivityLog::make('feed')
            ->heading('Activity')
            ->groupByDate()
            ->perPage(20)
            ->columnSpanFull(),
    ]);
}
```

On the Person table, add the bundled action for quick access from the list view:

```php
use Filament\Tables\Table;
use Relaticle\ActivityLog\Filament\Actions\ActivityLogAction;

public function table(Table $table): Table
{
    return $table
        ->columns([/* ... */])
        ->actions([
            ActivityLogAction::make(),
        ]);
}
```

## What you get

Profile creates and updates show up as spatie diffs (rendered by the built-in `ActivityLogRenderer`); attached email, note, and task lifecycle events appear alongside them; "email sent" entries get the branded render you wrote in Step 2. Bookmark a filtered view via URL query params (`?type=activity_log&from=2026-01-01`) — see [/essentials/filament-ui#built-in-url-filter-ui](/essentials/filament-ui#built-in-url-filter-ui).
