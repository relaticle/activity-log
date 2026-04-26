# Installation

> Install relaticle/activity-log, index your tables, and wire the panel plugin.

## Requirements

- **PHP** 8.4+
- **Laravel** 12
- **Filament** 5
- **spatie/laravel-activitylog** ^5

## Install the package

```bash [Terminal]
composer require relaticle/activity-log
```

## What auto-discovery wires up

The service provider (`Relaticle\ActivityLog\ActivityLogServiceProvider`) is auto-discovered and registers:

- Config file at `config/activity-log.php` (via `spatie/laravel-package-tools`)
- Views under the `activity-log::*` namespace
- Translations under the `activity-log::messages.*` namespace
- `RendererRegistry` and `TimelineCache` singletons
- The `activity-log` Livewire component (`Relaticle\ActivityLog\Filament\Livewire\ActivityLogLivewire`)
- The built-in `Relaticle\ActivityLog\Renderers\ActivityLogRenderer`, auto-registered for the `'activity_log'` type

## Publish the config (optional)

Only needed if you want to override defaults:

```bash [Terminal]
php artisan vendor:publish --tag=activity-log-config
```

See [Configuration](/essentials/configuration) for the full key reference.

## Database & indexing

The package owns **no migrations**; it reads from tables already present in your application.

<table>
<thead>
  <tr>
    <th>
      Table
    </th>
    
    <th>
      Owner
    </th>
    
    <th>
      Role
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
      <code>
        spatie/laravel-activitylog
      </code>
    </td>
    
    <td>
      Primary source of <code>
        activity_log
      </code>
      
       (own log) and related-log entries via <code>
        fromActivityLog()
      </code>
      
       and <code>
        fromActivityLogOf()
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      Your related tables
    </td>
    
    <td>
      Your app
    </td>
    
    <td>
      Sources registered via <code>
        fromRelation()
      </code>
      
       read their own timestamp columns.
    </td>
  </tr>
</tbody>
</table>

### Required index

Add this compound index to the spatie `activity_log` table for responsive timeline queries:

```php
Schema::table('activity_log', function (Blueprint $table) {
    $table->index(['subject_type', 'subject_id', 'created_at']);
});
```

Without it, paginating large logs scans significantly more rows than necessary.

### Related-model timestamp columns

When you register `fromRelation('tasks', fn ($s) => $s->event(column: 'completed_at', ...))`, the source filters the related table by the configured timestamp column. Index those columns when the related table is large.

## Tailwind theme integration

If your panel uses a custom `theme.css`, add the plugin's views to the Tailwind source list so the utilities used by the Blade templates are compiled:

```css [resources/css/filament/{panel}/theme.css][resources/css/filament//theme.css]
@source '../../../../vendor/relaticle/activity-log/resources/views/**/*';
```

Without this line, you may see unstyled or partially-styled timeline entries in production builds. Skip this step if your panel uses the default Filament theme — Tailwind is already wired.

## Register the panel plugin (optional)

Only needed when you want to register custom renderers; auto-discovery covers everything else. Use the **Filament-namespaced plugin** (not the orphan root `Relaticle\ActivityLog\ActivityLogPlugin` — see [issue #13](https://github.com/relaticle/activity-log/issues/13)):

```php
use Relaticle\ActivityLog\Filament\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(ActivityLogPlugin::make())
        // ... rest of panel config
    ;
}
```

See [Customization](/essentials/customization) for what to pass to `->renderers()`.

## Next

Head to [Quick start](/getting-started/quick-start) for the 5-minute working timeline.
