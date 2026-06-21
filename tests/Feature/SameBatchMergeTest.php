<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Relaticle\ActivityLog\Tests\Fixtures\Models\Person;
use Relaticle\ActivityLog\Timeline\Sources\ActivityLogSource;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Relaticle\ActivityLog\Timeline\Window;
use Spatie\Activitylog\Models\Activity;

/**
 * Create a controlled activity row, bypassing the model's own logging so each
 * test starts from a known set of rows.
 */
function makeActivity(Person $person, array $attributes): Activity
{
    return Activity::create([
        'log_name' => 'default',
        'description' => $attributes['description'] ?? 'updated',
        'subject_type' => $person->getMorphClass(),
        'subject_id' => $person->getKey(),
        'event' => $attributes['event'] ?? null,
        'attribute_changes' => $attributes['attribute_changes'] ?? null,
        'properties' => $attributes['properties'] ?? null,
        'batch_uuid' => $attributes['batch_uuid'] ?? null,
    ]);
}

it('merges same-batch rows into one entry carrying both payloads and the merged renderer', function (): void {
    $person = Person::factory()->create();
    Activity::query()->delete();

    $batch = '11111111-1111-1111-1111-111111111111';

    makeActivity($person, [
        'event' => 'updated',
        'attribute_changes' => ['attributes' => ['name' => 'New'], 'old' => ['name' => 'Old']],
        'batch_uuid' => $batch,
    ]);
    makeActivity($person, [
        'event' => 'custom',
        'description' => 'custom_field_updated',
        'properties' => ['custom_field' => 'value'],
        'batch_uuid' => $batch,
    ]);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLog(mergedRenderer: 'x')
        ->get();

    expect($entries)->toHaveCount(1);

    $entry = $entries->first();

    expect($entry->renderer)->toBe('x')
        ->and($entry->event)->toBe('updated')
        ->and($entry->properties)->toHaveKey('attributes')
        ->and($entry->properties)->toHaveKey('old')
        ->and($entry->properties)->toHaveKey('custom_field')
        ->and($entry->properties['custom_field'])->toBe('value');
});

it('concatenates repeated list-valued properties across grouped rows', function (): void {
    $person = Person::factory()->create();
    Activity::query()->delete();

    $batch = '44444444-4444-4444-4444-444444444444';

    // One save touching several custom fields emits a separate row per field, each
    // under the same `custom_field_changes` key. The merge must keep them all.
    makeActivity($person, [
        'event' => 'custom_field_changes',
        'properties' => ['custom_field_changes' => [['code' => 'icp']]],
        'batch_uuid' => $batch,
    ]);
    makeActivity($person, [
        'event' => 'custom_field_changes',
        'properties' => ['custom_field_changes' => [['code' => 'domains']]],
        'batch_uuid' => $batch,
    ]);
    makeActivity($person, [
        'event' => 'custom_field_changes',
        'properties' => ['custom_field_changes' => [['code' => 'linkedin']]],
        'batch_uuid' => $batch,
    ]);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLog(mergedRenderer: 'x')
        ->get();

    expect($entries)->toHaveCount(1);

    $codes = array_map(
        static fn (array $change): string => $change['code'],
        $entries->first()->properties['custom_field_changes'],
    );

    expect($codes)->toContain('icp')
        ->and($codes)->toContain('domains')
        ->and($codes)->toContain('linkedin')
        ->and($codes)->toHaveCount(3);
});

it('does not merge rows with different batch_uuids', function (): void {
    $person = Person::factory()->create();
    Activity::query()->delete();

    makeActivity($person, ['event' => 'updated', 'batch_uuid' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa']);
    makeActivity($person, ['event' => 'updated', 'batch_uuid' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb']);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLog(mergedRenderer: 'x')
        ->get();

    expect($entries)->toHaveCount(2);
});

it('does not merge rows without a batch_uuid', function (): void {
    $person = Person::factory()->create();
    Activity::query()->delete();

    makeActivity($person, ['event' => 'updated']);
    makeActivity($person, ['event' => 'updated']);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLog(mergedRenderer: 'x')
        ->get();

    expect($entries)->toHaveCount(2);
});

it('preserves newest-first ordering across groups', function (): void {
    $person = Person::factory()->create();
    Activity::query()->delete();

    $older = '11111111-1111-1111-1111-111111111111';
    $newer = '22222222-2222-2222-2222-222222222222';

    $a = makeActivity($person, ['event' => 'a_one', 'attribute_changes' => ['attributes' => ['name' => '1']], 'batch_uuid' => $older]);
    $b = makeActivity($person, ['event' => 'a_two', 'properties' => ['k' => 'v'], 'batch_uuid' => $older]);
    $c = makeActivity($person, ['event' => 'b_one', 'batch_uuid' => $newer]);

    Activity::query()->whereKey([$a->id, $b->id])->update(['created_at' => CarbonImmutable::parse('2026-04-17T10:00:00Z')]);
    Activity::query()->whereKey($c->id)->update(['created_at' => CarbonImmutable::parse('2026-04-17T11:00:00Z')]);

    $source = (new ActivityLogSource(priority: 10))->withSameBatchMerge('x');
    $entries = collect($source->resolve($person->fresh(), new Window(cap: 10)));

    expect($entries)->toHaveCount(2)
        ->and($entries->pluck('event')->all())->toBe(['b_one', 'a_one']);
});

it('leaves behavior unchanged when no merged renderer is given', function (): void {
    $person = Person::factory()->create();
    Activity::query()->delete();

    $batch = '11111111-1111-1111-1111-111111111111';
    makeActivity($person, ['event' => 'updated', 'attribute_changes' => ['attributes' => ['name' => 'New']], 'batch_uuid' => $batch]);
    makeActivity($person, ['event' => 'custom', 'properties' => ['custom_field' => 'value'], 'batch_uuid' => $batch]);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLog()
        ->get();

    expect($entries)->toHaveCount(2)
        ->and($entries->pluck('renderer')->unique()->all())->toBe([null]);
});
