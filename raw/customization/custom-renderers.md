# Custom Renderers

> Register renderers per event or type via plugin, facade, or config.

Out of the box, entries from spatie's activity log render via the built-in `ActivityLogRenderer` (which understands `updated`/`created`/etc. events and renders field diffs), and everything else falls back to `DefaultRenderer` (title, description, causer, relative time, colored icon). For branded output per event type, register a custom renderer.

## Registering via the panel plugin

```php
use Relaticle\ActivityLog\Filament\ActivityLogPlugin;

$panel->plugin(
    ActivityLogPlugin::make()->renderers([
        'email_sent'   => \App\Timeline\Renderers\EmailSentRenderer::class,
        'note_added'   => 'my-app::timeline.note-added',          // view name
        'task_done'    => fn ($entry) => new HtmlString('...'),   // closure
    ]),
);
```

## Registering via the facade (e.g., from a service provider)

```php
use Relaticle\ActivityLog\Facades\Timeline;

Timeline::registerRenderer('email_sent', \App\Timeline\Renderers\EmailSentRenderer::class);
Timeline::registerRenderer('note_added', 'my-app::timeline.note-added');
Timeline::registerRenderer('task_done', fn ($entry) => new HtmlString('...'));
```

## Registering via config

```php
// config/activity-log.php
'renderers' => [
    'email_sent' => \App\Timeline\Renderers\EmailSentRenderer::class,
],
```

## Renderer resolution order

For each `TimelineEntry`, the registry checks:

1. `$entry->renderer` (an explicit override the source set)
2. `bindings[$entry->event]`
3. `bindings[$entry->type]`
4. `DefaultRenderer` fallback

## Renderer forms

A renderer binding can be any of:

- **Class string** implementing `Relaticle\ActivityLog\Contracts\TimelineRenderer`
- **Closure** `fn (TimelineEntry $entry): View|HtmlString => ...`
- **View name** (e.g., `'my-app::timeline.email-sent'`) — receives `$entry` in scope

```php
final class EmailSentRenderer implements \Relaticle\ActivityLog\Contracts\TimelineRenderer
{
    public function render(\Relaticle\ActivityLog\Timeline\TimelineEntry $entry): \Illuminate\Contracts\View\View
    {
        return view('app.timeline.email-sent', ['entry' => $entry]);
    }
}
```
