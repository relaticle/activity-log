# relaticle/activity-log

<img src="art/activity-log-cover.jpg?v=1" alt="Activity Log" width="800">

A unified chronological timeline for any Eloquent model. Aggregates `spatie/laravel-activitylog` events (own log + related logs), timestamp columns on related models, and any custom source you define — all in one Filament-native feed.

[![Latest Version](https://img.shields.io/packagist/v/relaticle/activity-log.svg?style=for-the-badge)](https://packagist.org/packages/relaticle/activity-log)
[![Total Downloads](https://img.shields.io/packagist/dt/relaticle/activity-log.svg?style=for-the-badge)](https://packagist.org/packages/relaticle/activity-log)
[![PHP 8.4+](https://img.shields.io/badge/php-8.4%2B-blue.svg?style=for-the-badge)](https://php.net)
[![Laravel 12+](https://img.shields.io/badge/laravel-12%2B-red.svg?style=for-the-badge)](https://laravel.com)
[![Filament 5+](https://img.shields.io/badge/filament-5%2B-f59e0b.svg?style=for-the-badge)](https://filamentphp.com)
[![Tests](https://img.shields.io/github/actions/workflow/status/relaticle/activity-log/tests.yml?branch=1.x&style=for-the-badge&label=tests)](https://github.com/relaticle/activity-log/actions)

## Features

- **Unified timeline** — merge spatie logs, related-model timestamps, and custom events into one stream
- **Pluggable sources** — `fromActivityLog`, `fromActivityLogOf`, `fromRelation`, `fromCustom`, and custom `TimelineSource` classes
- **Per-event renderers** — Blade views, closures, or renderer classes bound per event or type
- **Filament-native UX** — infolist component, relation manager, and header-action slide-over
- **Dedup + filtering** — type/event allow/deny lists, date windows, priority-based dedup with override
- **Opt-in caching** — per-call TTL with explicit invalidation — no model observers

## Requirements

- PHP 8.4+
- Laravel 12
- Filament 5
- `spatie/laravel-activitylog` ^5

## Installation

```bash
composer require relaticle/activity-log
```

## Usage

Make the model timeline-capable, then render it on a Filament resource:

```php
use Relaticle\ActivityLog\Concerns\InteractsWithTimeline;
use Relaticle\ActivityLog\Contracts\HasTimeline;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Spatie\Activitylog\Traits\LogsActivity;

class Person extends Model implements HasTimeline
{
    use InteractsWithTimeline;
    use LogsActivity;

    public function timeline(): TimelineBuilder
    {
        return TimelineBuilder::make($this)->fromActivityLog();
    }
}
```

```php
use Filament\Schemas\Schema;
use Relaticle\ActivityLog\Filament\Infolists\Components\ActivityLog;

public static function infolist(Schema $schema): Schema
{
    return $schema->components([
        ActivityLog::make('activity')->columnSpanFull(),
    ]);
}
```

**[View Complete Documentation →](https://relaticle.github.io/activity-log/)**

## Our Ecosystem

<table>
<tr>
<td width="33%" valign="top">

### FilaForms
[<img src="https://filaforms.app/img/og-image.png" width="100%" />](https://filaforms.app/)

Visual form builder for all your public-facing forms.
[Learn more →](https://filaforms.app)

</td>
<td width="33%" valign="top">

### Custom Fields
[<img src="https://github.com/Relaticle/custom-fields/raw/2.x/art/preview.png" width="100%" />](https://relaticle.github.io/custom-fields)

Let users add custom fields to any model without code changes.
[Learn more →](https://relaticle.github.io/custom-fields)

</td>
<td width="33%" valign="top">

### Flowforge
[<img src="https://github.com/relaticle/flowforge/raw/4.x/art/preview.png" width="100%" />](https://relaticle.github.io/flowforge)

Drag-and-drop Kanban for any Laravel model.
[Learn more →](https://relaticle.github.io/flowforge)

</td>
</tr>
</table>

## Contributing

Contributions are welcome. Please see [CONTRIBUTING.md](.github/CONTRIBUTING.md) before opening an issue or pull request.

## Security

If you discover a security issue, please review our [Security Policy](.github/SECURITY.md).

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.
