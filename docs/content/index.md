---
seo:
  title: Filament Activity Timeline
  description: Unified chronological timeline for any Eloquent model, powered by spatie/laravel-activitylog and Filament v5.
  ogImage: /og-image.png
---

::u-page-hero
#title
Activity Log

#description
A unified chronological timeline for any Eloquent model.

Aggregates spatie activity logs, related-model timestamps, and custom sources into a single Filament-native feed.

#links
  :::u-button
  ---
  color: primary
  size: xl
  to: /getting-started/installation
  trailing-icon: i-lucide-arrow-right
  ---
  Get started
  :::

  :::u-button
  ---
  color: neutral
  size: xl
  to: /getting-started/quick-start
  trailing-icon: i-lucide-rocket
  variant: subtle
  ---
  Get started in 5 minutes
  :::

  :::u-button
  ---
  color: neutral
  icon: simple-icons:github
  size: xl
  to: https://github.com/relaticle/activity-log
  variant: outline
  ---
  GitHub
  :::
::

<div class="text-center max-w-4xl mx-auto">
  <img src="/preview.png" alt="Activity Log Preview" class="mx-auto max-w-full h-auto rounded-lg shadow-lg" />
</div>

::u-page-section
#title
Why Activity Log?

#features
  :::u-page-feature
  ---
  icon: i-lucide-timer
  ---
  #title
  Unified Timeline

  #description
  Merge spatie activity logs, related-model timestamps, and custom events into one chronological stream.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-layers
  ---
  #title
  Pluggable Sources

  #description
  Compose any number of sources per model - own log, related logs, timestamp columns, custom closures.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-paintbrush
  ---
  #title
  Per-Event Renderers

  #description
  Register Blade views, closures, or renderer classes per event or type. Falls back to a sensible default.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-zap
  ---
  #title
  Smart Pagination

  #description
  Over-fetch buffer keeps dedup and filtering correct at higher pages without unbounded queries.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-blocks
  ---
  #title
  Filament-Native UX

  #description
  Infolist component, relation manager, and header-action slide-over - drop it in wherever you need a timeline.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-database
  ---
  #title
  Opt-In Caching

  #description
  Cache per call with a TTL, invalidate explicitly on mutations. No model observers required.
  :::
::

::u-page-section
#title
Our Ecosystem

#description
Extend your Laravel applications with our ecosystem of complementary tools

#default
  ::card-group
    :::card
    ---
    title: FilaForms
    icon: i-simple-icons-laravel
    to: https://filaforms.app
    target: _blank
    ---
    ![FilaForms](https://filaforms.app/img/og-image.png)

    Visual form builder for all your public-facing forms.
    :::

    :::card
    ---
    title: Custom Fields
    icon: i-lucide-sliders
    to: https://relaticle.github.io/custom-fields
    target: _blank
    ---
    ![Custom Fields](https://github.com/Relaticle/custom-fields/raw/2.x/art/preview.png)

    Let users add custom fields to any model without code changes.
    :::

    :::card
    ---
    title: Flowforge
    icon: i-lucide-kanban
    to: https://relaticle.github.io/flowforge
    target: _blank
    ---
    ![Flowforge](https://github.com/relaticle/flowforge/raw/4.x/art/preview.png)

    Drag-and-drop Kanban for any Laravel model.
    :::
  ::
::
