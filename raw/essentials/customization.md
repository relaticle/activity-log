# Customization

> Custom renderers, registration channels, and Tailwind theme integration.

Renderers turn `TimelineEntry` objects into Blade markup. The package ships sensible defaults for the entries it knows about; everything else falls back to a generic renderer. Customize per event, per type, or wholesale per surface — pick the channel that matches the scope you need.

For the underlying mental model (sources, entries, and how renderers fit into the pipeline), see [/concepts/how-it-works](/concepts/how-it-works).

## Renderer resolution order

For each `TimelineEntry`, the registry looks up a renderer in this exact order and stops at the first match:

1. `$entry->renderer` — explicit per-entry override (sources can set this when constructing the entry).
2. `bindings[$entry->event]` — event-keyed registry binding (e.g., `'email_sent'`).
3. `bindings[$entry->type]` — type-keyed registry binding (e.g., `'activity_log'`, `'related_model'`, `'custom'`).
4. `Relaticle\ActivityLog\Renderers\DefaultRenderer` — fallback (title, description, causer, relative time, colored icon).

The built-in `Relaticle\ActivityLog\Renderers\ActivityLogRenderer` is auto-registered for the `'activity_log'` type by the package service provider, so spatie activity-log entries get diff rendering for free without any opt-in. To replace it, see [Replacing the built-in `activity_log` renderer](#replacing-the-built-in-activity_log-renderer) below.

## Renderer binding forms

The registry accepts three forms for any binding. Pick the lightest one that fits — there's no functional difference at render time.

**Class string** implementing `Relaticle\ActivityLog\Contracts\TimelineRenderer`. Resolved through the container, so you can typehint dependencies in the constructor.

```php
use Relaticle\ActivityLog\Facades\Timeline;

Timeline::registerRenderer('email_sent', \App\Timeline\Renderers\EmailSentRenderer::class);
```

**Closure** with signature `fn (TimelineEntry $entry): View|HtmlString`. Best for tiny per-entry markup or quick prototyping.

```php
use Illuminate\Support\HtmlString;
use Relaticle\ActivityLog\Facades\Timeline;
use Relaticle\ActivityLog\Timeline\TimelineEntry;

Timeline::registerRenderer('task_done', fn (TimelineEntry $entry) => new HtmlString(
    "<strong>{$entry->title}</strong> finished",
));
```

**View name** — a string Blade view path. Receives `$entry` in scope automatically.

```php
use Relaticle\ActivityLog\Facades\Timeline;

Timeline::registerRenderer('note_added', 'app::timeline.note-added');
```

## Registration channels

Three places to register renderers. They write to the same registry — pick by scope, not capability.

### Plugin

Preferred for **panel-scoped overrides**. Use `Relaticle\ActivityLog\Filament\ActivityLogPlugin` (the Filament-namespaced one) and pass renderers directly to the plugin.

```php
use Illuminate\Support\HtmlString;
use Relaticle\ActivityLog\Filament\ActivityLogPlugin;

$panel->plugin(
    ActivityLogPlugin::make()->renderers([
        'email_sent' => \App\Timeline\Renderers\EmailSentRenderer::class,
        'note_added' => 'app::timeline.note-added',
        'task_done'  => fn ($entry) => new HtmlString('...'),
    ]),
);
```

<callout color="warning" icon="i-lucide-triangle-alert">

**Use the Filament-namespaced plugin.** A stale orphan class `Relaticle\ActivityLog\ActivityLogPlugin` (root namespace, no `Filament\`) still ships in the package — never import that one. Tracked by [issue #13](https://github.com/relaticle/activity-log/issues/13).

</callout>

### Facade

Useful from a service provider's `boot()` for **global, panel-agnostic overrides** — or for runtime/conditional registration.

```php
use Relaticle\ActivityLog\Facades\Timeline;

Timeline::registerRenderer('email_sent', \App\Timeline\Renderers\EmailSentRenderer::class);

Timeline::unregisterRenderer('email_sent');
```

`unregisterRenderer($eventOrType)` drops a binding from the registry — handy in tests, or when a feature flag flips off and you want the default renderer back.

### Config

For **static defaults** that don't depend on runtime state. Lives in `config/activity-log.php`.

```php
// config/activity-log.php
'renderers' => [
    'email_sent' => \App\Timeline\Renderers\EmailSentRenderer::class,
],
```

### Resolution precedence between channels

The registry stores all bindings in a single keyed array (`array<string, string|Closure>`). There is **no "plugin > facade > config" priority** — whichever channel registers last for a given key wins.

The boot order in practice:

1. `ActivityLogServiceProvider::packageBooted()` registers the built-in `'activity_log'` type renderer.
2. The plugin's `register(Panel)` runs when the panel boots, applying its renderers.
3. The facade can be called from anywhere (a service provider's `boot()`, middleware, runtime code).

So the actual rule is **last registration wins**.

<callout color="info" icon="i-lucide-lightbulb">

A clean split that avoids surprises: register **stable defaults via config**, **panel-specific overrides via the plugin**, and **runtime/conditional overrides via the facade**.

</callout>

## Replacing the built-in `activity_log` renderer

The service provider auto-registers `Relaticle\ActivityLog\Renderers\ActivityLogRenderer` for the `'activity_log'` type. To replace it wholesale — say, to redact sensitive fields, change the diff format, or embed approval buttons — register your own class for the same type key.

```php
use Relaticle\ActivityLog\Filament\ActivityLogPlugin;

ActivityLogPlugin::make()->renderers([
    'activity_log' => \App\Timeline\Renderers\AuditedActivityLogRenderer::class,
]);
```

You don't have to rebuild the diff parsing from scratch. The package exposes a small helper layer you can reuse from a custom renderer:

- `Relaticle\ActivityLog\Support\ActivityLogSummary::from(TimelineEntry $entry)` — pre-built summary with causer name, operation enum, changed-field labels, a one-line summary sentence, and diff rows.
- `Relaticle\ActivityLog\Support\ActivityLogDiffRow` — single before/after row with `formattedOld()` and `formattedNew()` accessors.
- `Relaticle\ActivityLog\Support\ActivityLogOperation` — `Created` / `Updated` / `Deleted` / `Restored` enum with `icon()` and `verb()` accessors.
- `Relaticle\ActivityLog\Support\AttributeFormatter::format(mixed $value)` — turns null / bool / enum / Carbon / scalar / array values into a readable string.

A minimal custom renderer that delegates to `ActivityLogSummary` and renders your own Blade view:

```php
use Illuminate\Contracts\View\View;
use Relaticle\ActivityLog\Contracts\TimelineRenderer;
use Relaticle\ActivityLog\Support\ActivityLogSummary;
use Relaticle\ActivityLog\Timeline\TimelineEntry;

final class AuditedActivityLogRenderer implements TimelineRenderer
{
    public function render(TimelineEntry $entry): View
    {
        $summary = ActivityLogSummary::from($entry);

        return view('audit.timeline.entry', [
            'entry' => $entry,
            'summary' => $summary,
        ]);
    }
}
```

Inside `audit.timeline.entry`, you have full access to `$summary->causerName`, `$summary->operation`, `$summary->changedFieldLabels`, `$summary->summarySentence`, `$summary->diffRows`, and `$summary->hasDiff` — render whatever markup your audit surface needs.

## Tailwind theme integration

If your panel ships a custom Tailwind theme, add the plugin's views to its `@source` list — see [/getting-started/installation](/getting-started/installation#tailwind-theme-integration).
