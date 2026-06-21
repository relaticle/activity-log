<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Timeline\Sources;

use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Relaticle\ActivityLog\Timeline\TimelineEntry;
use Relaticle\ActivityLog\Timeline\Window;
use Spatie\Activitylog\Models\Activity as ActivityModel;

final class ActivityLogSource extends AbstractTimelineSource
{
    private bool $mergeSameBatch = false;

    private ?string $mergedRenderer = null;

    /**
     * Opt in to collapsing rows that share a non-empty `batch_uuid` into a single
     * timeline entry. The optional renderer is set on merged entries so consumers
     * can render the grouped save without overriding any global event renderer.
     */
    public function withSameBatchMerge(?string $mergedRenderer = null): self
    {
        $this->mergeSameBatch = true;
        $this->mergedRenderer = $mergedRenderer;

        return $this;
    }

    public function resolve(Model $subject, Window $window): iterable
    {
        throw_if($subject->getKey() === null, DomainException::class, 'ActivityLogSource cannot resolve entries for an unsaved subject.');

        $modelClass = $this->getActivityModelClass();
        
        $query = $modelClass::query()
            ->with(['causer', 'subject'])
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())->latest()
            ->limit($window->cap);

        if ($window->from instanceof CarbonImmutable) {
            $query->where('created_at', '>=', $window->from);
        }

        if ($window->to instanceof CarbonImmutable) {
            $query->where('created_at', '<=', $window->to);
        }

        $activities = $query->get();

        if (! $this->mergeSameBatch) {
            foreach ($activities as $activity) {
                yield $this->makeEntry($subject, $activity);
            }

            return;
        }

        foreach ($this->groupByBatch($activities) as $group) {
            yield $this->makeMergedEntry($subject, $group);
        }
    }

    /**
     * Group rows that share a non-empty `batch_uuid`, preserving the query's
     * newest-first order. Rows without a batch_uuid each form their own group.
     * First-seen order is the output order.
     *
     * @param  Collection<int, ActivityModel>  $activities
     * @return list<list<ActivityModel>>
     */
    private function groupByBatch(Collection $activities): array
    {
        $groups = [];

        foreach ($activities as $activity) {
            $batch = $activity->batch_uuid;
            $key = ($batch === null || $batch === '')
                ? 'id:'.$activity->getKey()
                : 'batch:'.$batch;

            $groups[$key][] = $activity;
        }

        return array_values($groups);
    }

    /**
     * @param  list<ActivityModel>  $group
     */
    private function makeMergedEntry(Model $subject, array $group): TimelineEntry
    {
        if (count($group) === 1) {
            $entry = $this->makeEntry($subject, $group[0]);

            if ($this->mergedRenderer === null) {
                return $entry;
            }

            return new TimelineEntry(
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
                renderer: $this->mergedRenderer,
                properties: $entry->properties,
            );
        }

        $base = null;
        foreach ($group as $activity) {
            if (($activity->attribute_changes?->toArray() ?? []) !== []) {
                $base = $activity;
                break;
            }
        }
        $base ??= $group[0];

        $properties = [];
        foreach ($group as $activity) {
            foreach ($this->extractProperties($activity) as $key => $value) {
                // Repeated array-valued keys must accumulate, not overwrite: a save
                // touching several custom fields emits one row each, all under the
                // same `custom_field_changes` key — a plain spread would keep only
                // the last. array_merge concatenates list payloads (e.g. those
                // change lists) while still letting associative maps (native
                // `attributes`/`old`) union per field key.
                $properties[$key] = isset($properties[$key]) && is_array($properties[$key]) && is_array($value)
                    ? array_merge($properties[$key], $value)
                    : $value;
            }
        }

        $occurredAt = CarbonImmutable::parse($base->created_at);
        $event = (string) ($base->event ?? $base->description);

        return new TimelineEntry(
            id: sprintf('activity_log:%s:%s', $base->id, $event),
            type: 'activity_log',
            event: $event,
            occurredAt: $occurredAt,
            dedupKey: $this->dedupKeyForActivity($subject->getMorphClass(), (string) $subject->getKey(), $occurredAt, (string) $base->getKey()),
            sourcePriority: $this->priority,
            subject: $subject,
            causer: $base->causer,
            relatedModel: null,
            title: $base->description,
            renderer: $this->mergedRenderer,
            properties: $properties,
        );
    }

    private function makeEntry(Model $subject, ActivityModel $activity): TimelineEntry
    {
        $occurredAt = CarbonImmutable::parse($activity->created_at);
        $event = (string) ($activity->event ?? $activity->description);

        return new TimelineEntry(
            id: sprintf('activity_log:%s:%s', $activity->id, $event),
            type: 'activity_log',
            event: $event,
            occurredAt: $occurredAt,
            dedupKey: $this->dedupKeyForActivity($subject->getMorphClass(), (string) $subject->getKey(), $occurredAt, (string) $activity->getKey()),
            sourcePriority: $this->priority,
            subject: $subject,
            causer: $activity->causer,
            relatedModel: null,
            title: $activity->description,
            properties: $this->extractProperties($activity),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function extractProperties(ActivityModel $activity): array
    {
        $changes = $activity->attribute_changes?->toArray() ?? [];
        $properties = $activity->properties?->toArray() ?? [];

        return [...$properties, ...$changes];
    }
}
