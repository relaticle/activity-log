# Audit log for admins

> Read-only admin audit trail — own log only, role-gated visibility, sensitive-field redaction.

Your admin team needs a clean audit trail when investigating User accounts — what changed, who changed it, when. No emails, no related-model noise. Sensitive fields like `password` and `remember_token` must never render in the diff. The slide-over should only be visible to admins.

## Setup

Assuming you've completed [/getting-started/installation](/getting-started/installation), this recipe expects:

- The `User` model uses `LogsActivity` with explicit `logFillable()` to control which attributes get logged.
- Admin gating via a Policy or a `User::isAdmin()` boolean — adapt to your authorization stack.
- No relations involved — this recipe stays focused on the User's own log.

## Step 1 — `User` model with focused `timeline()`

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Relaticle\ActivityLog\Concerns\InteractsWithTimeline;
use Relaticle\ActivityLog\Contracts\HasTimeline;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class User extends Authenticatable implements HasTimeline
{
    use InteractsWithTimeline;
    use LogsActivity;

    protected $fillable = ['name', 'email', 'role', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function timeline(): TimelineBuilder
    {
        return TimelineBuilder::make($this)
            ->fromActivityLog()
            ->ofEvent(['created', 'updated', 'deleted', 'restored']);
    }
}
```

`logFillable()` + `logOnlyDirty()` keeps spatie focused on the audit-relevant fields and skips no-op writes. `ofEvent([...])` filters out anything that isn't a CRUD operation (custom events from related models, integration callbacks, etc.). The combo gives you a clean "who-did-what" stream without operational noise.

## Step 2 — Admin-gated header action

Drop the bundled action into the User view page and gate it with `->visible()`:

```php
namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ViewRecord;
use Relaticle\ActivityLog\Filament\Actions\ActivityLogAction;

final class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActivityLogAction::make()
                ->label('Audit log')
                ->icon('heroicon-o-shield-check')
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
        ];
    }
}
```

Non-admins never see the action — Filament evaluates `->visible()` on render. The slide-over uses the built-in `ActivityLogRenderer` (auto-registered for the `'activity_log'` type), so diff rows render automatically without any extra wiring.

## Step 3 — Redact sensitive fields with a wrapping renderer

Replace the built-in renderer with one that filters `$entry->properties` before delegating back to the original. This way you keep all the diff/markup logic from `ActivityLogRenderer` (which uses `ActivityLogSummary` under the hood — see [/essentials/customization](/essentials/customization#replacing-the-built-in-activitylog-renderer)) and only intercept the `properties` array.

```php
namespace App\Timeline\Renderers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Relaticle\ActivityLog\Contracts\TimelineRenderer;
use Relaticle\ActivityLog\Renderers\ActivityLogRenderer;
use Relaticle\ActivityLog\Timeline\TimelineEntry;

final class RedactedAuditRenderer implements TimelineRenderer
{
    /** @var array<int, string> */
    private const REDACTED_KEYS = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    public function __construct(private readonly ActivityLogRenderer $inner) {}

    public function render(TimelineEntry $entry): View|HtmlString
    {
        $properties = $entry->properties;

        foreach (['attributes', 'old'] as $bucket) {
            if (! isset($properties[$bucket]) || ! is_array($properties[$bucket])) {
                continue;
            }

            foreach (self::REDACTED_KEYS as $key) {
                if (array_key_exists($key, $properties[$bucket])) {
                    $properties[$bucket][$key] = '••••••••';
                }
            }
        }

        $redacted = new TimelineEntry(
            id: $entry->id,
            type: $entry->type,
            event: $entry->event,
            occurredAt: $entry->occurredAt,
            dedupKey: $entry->dedupKey,
            sourcePriority: $entry->sourcePriority,
            subject: $entry->subject,
            causer: $entry->causer,
            relatedModel: $entry->relatedModel,
            title: $entry->title,
            description: $entry->description,
            icon: $entry->icon,
            color: $entry->color,
            renderer: $entry->renderer,
            properties: $properties,
        );

        return $this->inner->render($redacted);
    }
}
```

`TimelineEntry` is a `final readonly` class — there's no setter, so the renderer rebuilds the entry from scratch with the filtered `properties`. The constructor is container-resolved, so typehinting `ActivityLogRenderer` is enough to get the original instance injected.

Register via the panel plugin in your `AppPanelProvider`:

```php
use App\Timeline\Renderers\RedactedAuditRenderer;
use Relaticle\ActivityLog\Filament\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(ActivityLogPlugin::make()->renderers([
            'activity_log' => RedactedAuditRenderer::class,
        ]))
        // ...
    ;
}
```

The `'activity_log'` type key replaces the built-in binding wholesale — every `activity_log`-type entry now flows through your wrapper. See [/essentials/customization#renderer-resolution-order](/essentials/customization#renderer-resolution-order) for how the registry resolves bindings.

## What you get

Admins see a clean audit trail; non-admins don't see the action at all; sensitive fields render as `••••••••` regardless of who's viewing. URL filters work normally — admins can bookmark `?from=2026-01-01` to scope an investigation, or `?event=updated` to focus on changes only. See [/essentials/filament-ui#built-in-url-filter-ui](/essentials/filament-ui#built-in-url-filter-ui) for the full list of supported query params.
