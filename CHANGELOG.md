# Changelog

All notable changes to `relaticle/activity-log` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-06-20

### Added

- Opt-in same-save merge for `fromActivityLog(mergedRenderer: ...)`: rows sharing a non-empty spatie `batch_uuid` collapse into one `TimelineEntry` whose `properties` union every grouped row's payload and whose `renderer` is set explicitly. Read-side only; requires a `batch_uuid` column on `activity_log` (host-owned)
- Merged `properties` now **accumulate** repeated array-valued keys instead of overwriting them: a save touching several entities under the same key (e.g. one `custom_field_changes` row per field) keeps every payload via `array_merge` — list payloads concatenate, associative maps still union per key

## [1.1.1] - 2026-06-15

### Added

- Visible empty state on the timeline with a host-configurable message

### Fixed

- Replaced remaining Remix icons with Heroicons so the timeline renders with the bundled icon set (#24)
- Timeline dedup no longer over-collapses distinct activities saved in the same second; the dedup key now includes the activity id
- Empty-state card now uses `heroicon-o-clock` instead of the missing Remix `ri-history-line`, so it renders instead of showing a blank card
- Read path resolves the Activity model from the plugin key, then Spatie's `activitylog.activity_model`, then the base model, so a tenant-scoped Activity subclass applies to reads as well as writes

## [1.1.0] - 2026-06-13

### Added

- Configurable activity model via the `activity_model` config key in `activity-log.php`; `ActivityLogSource` and `RelatedActivityLogSource` now resolve the model from config instead of hardcoding it
- Laravel 13 support (`illuminate/* ^13.0`)
- Versioned documentation site (Nuxt Content + Docus) published to GitHub Pages
- Community files: `LICENSE.md`, `CHANGELOG.md`, `.github/CONTRIBUTING.md`, `.github/SECURITY.md`

### Changed

- Reworked the timeline as a continuous border rail with connector lines centered on entry icons for even, symmetric spacing

### Fixed

- `RelatedActivityLogSource` now emits `type='related_activity_log'` so related entries are identifiable
- Removed the dead `date_groups` config key (#10)
- Scoped `TimelineCache::forget()` to a single subject so it no longer over-invalidates other subjects' caches (#12)
- Capped the related-row fetch in `RelatedActivityLogSource` to prevent unbounded queries (#14)
- Removed the orphan root-namespace `ActivityLogPlugin`

## [1.0.0] - Initial release

### Added

- Unified timeline for any Eloquent model via `HasTimeline` contract and `InteractsWithTimeline` trait
- `TimelineBuilder` with pluggable sources: `fromActivityLog`, `fromActivityLogOf`, `fromRelation`, `fromCustom`
- `TimelineSource` contract for custom event streams
- Per-event renderers: Blade views, closures, or renderer classes bound per event or type
- Filament-native UX: infolist component, relation manager, and header-action slide-over
- Type/event allow/deny filtering, date windows, and priority-based dedup with override
- Opt-in caching with per-call TTL and explicit invalidation (no model observers)
- Infinite scroll in the activity log infolist component
