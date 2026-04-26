# Troubleshooting

> Common pitfalls, known limitations, and how to work around them.

The entries below cover the most common issues people hit with `relaticle/activity-log`. For anything not listed, open a [GitHub discussion](https://github.com/relaticle/activity-log/discussions) — maintainers and community triage there.

## Timeline looks unstyled in production

The plugin's Blade views use Tailwind utilities. If you see plain HTML, your panel theme isn't compiling the plugin's views. Add the `@source` directive — see [/getting-started/installation#tailwind-theme-integration](/getting-started/installation#tailwind-theme-integration).

## Custom renderer isn't picked up

Renderers resolve in this order: explicit `$entry->renderer` → `bindings[$entry->event]` → `bindings[$entry->type]` → `DefaultRenderer`. Most common cause: the binding key doesn't match. Check `$entry->event` against your registered key (it's the spatie `event` column for `activity_log` entries, the second arg to `event()` for `RelatedModelSource`, or the `event` field you set on a `TimelineEntry` for custom sources). Full resolution rules at [/essentials/customization#renderer-resolution-order](/essentials/customization#renderer-resolution-order).

## Duplicate entries appearing

Dedup uses `dedupKey` + `sourcePriority`. If two sources emit the same logical event with different keys, they won't collapse. Either align `dedupKey` on both sources (use `->dedupKeyUsing()` on the builder) or raise the preferred source's priority so the loser is dropped. Mechanics at [/concepts/how-it-works#dedup-behavior](/concepts/how-it-works#dedup-behavior).

## Type filter doesn't match anything

<callout color="warning" icon="i-lucide-alert-triangle">

- **Symptom:** `$record->timeline()->fromActivityLogOf(['emails'])->ofType(['related_activity_log'])->get()` returns empty.
- **Cause:** `RelatedActivityLogSource` emits entries with `type='activity_log'` (NOT `'related_activity_log'`). The `related_activity_log` key only exists in `source_priorities` config — for priority configuration only.
- **Workaround:** filter with `->ofType(['activity_log'])` to include both own- and related-log entries. To distinguish them at the entry level, inspect `$entry->relatedModel` (`null` for `ActivityLogSource`, an Eloquent model for `RelatedActivityLogSource`).
- **Tracking:** [issue #11](https://github.com/relaticle/activity-log/issues/11).

</callout>

## Cache invalidation flushed unrelated caches

<callout color="warning" icon="i-lucide-alert-triangle">

- **Symptom:** after calling `$record->forgetTimelineCache()`, sessions / queue locks / application caches are gone too.
- **Cause:** `TimelineCache::forget()` calls `Cache::store(...)->getStore()->flush()` — flushes the entire cache store, not just this subject's keys.
- **Workaround:** configure `cache.store` to a dedicated Laravel cache store used only by the timeline. Or skip explicit invalidation and let TTL expire naturally (set a short `->cached($ttl)`).
- **Tracking:** [issue #12](https://github.com/relaticle/activity-log/issues/12).
- Full caveat at [/essentials/caching#invalidation--known-limitation](/essentials/caching#invalidation--known-limitation).

</callout>

## `fromActivityLog()` throws on a fresh model

<callout color="warning" icon="i-lucide-alert-triangle">

- **Symptom:** `DomainException: ActivityLogSource cannot resolve entries for an unsaved subject` when calling `timeline()` on a model that hasn't been persisted.
- **Cause:** `ActivityLogSource::resolve()` requires `$subject->getKey() !== null`.
- **Workaround:** don't call `$model->timeline()` from a `creating` Eloquent event or before `$model->save()`. Move the call to a `created` event or to a controller after persistence.

</callout>

Issue not listed here? Open a [GitHub discussion](https://github.com/relaticle/activity-log/discussions).
