# Quick Start

> Wire HasTimeline and render the timeline.

## 1. Mark the model as timeline-capable

Implement the `HasTimeline` contract, use the `InteractsWithTimeline` trait for the helper methods, and define a `timeline(): TimelineBuilder` method:

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

## 2. Render the timeline

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

That's the minimum wiring. The [Essentials](/essentials/core-concepts) and [Customization](/customization/custom-renderers) sections cover the rest.
