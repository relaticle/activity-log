# Changelog

All notable changes to `relaticle/activity-log` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Versioned documentation site (Nuxt Content + Docus) published to GitHub Pages
- Community files: `LICENSE.md`, `CHANGELOG.md`, `.github/CONTRIBUTING.md`, `.github/SECURITY.md`

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
