# 

> 

<u-page-hero>
<template v-slot:title="">

Activity Log

</template>

<template v-slot:description="">

A unified chronological timeline for any Eloquent model.

Aggregates spatie activity logs, related-model timestamps, and custom sources into a single Filament-native feed.

</template>

<template v-slot:links="">
<u-button color="primary" size="xl" to="/getting-started/installation" trailing-icon="i-lucide-arrow-right">

Get started

</u-button>

<u-button color="neutral" size="xl" to="/getting-started/quick-start" trailing-icon="i-lucide-rocket" variant="subtle">

Get started in 5 minutes

</u-button>

<u-button color="neutral" size="xl" to="https://github.com/relaticle/activity-log" variant="outline" icon="simple-icons:github">

GitHub

</u-button>
</template>
</u-page-hero>

<div className="text-center,max-w-4xl,mx-auto">

![Activity Log Preview](/preview.png)

</div>

<u-page-section>
<template v-slot:title="">

Why Activity Log?

</template>

<template v-slot:features="">
<u-page-feature icon="i-lucide-timer">
<template v-slot:title="">

Unified Timeline

</template>

<template v-slot:description="">

Merge spatie activity logs, related-model timestamps, and custom events into one chronological stream.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-layers">
<template v-slot:title="">

Pluggable Sources

</template>

<template v-slot:description="">

Compose any number of sources per model - own log, related logs, timestamp columns, custom closures.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-paintbrush">
<template v-slot:title="">

Per-Event Renderers

</template>

<template v-slot:description="">

Register Blade views, closures, or renderer classes per event or type. Falls back to a sensible default.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-zap">
<template v-slot:title="">

Smart Pagination

</template>

<template v-slot:description="">

Over-fetch buffer keeps dedup and filtering correct at higher pages without unbounded queries.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-blocks">
<template v-slot:title="">

Filament-Native UX

</template>

<template v-slot:description="">

Infolist component, relation manager, and header-action slide-over - drop it in wherever you need a timeline.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-database">
<template v-slot:title="">

Opt-In Caching

</template>

<template v-slot:description="">

Cache per call with a TTL, invalidate explicitly on mutations. No model observers required.

</template>
</u-page-feature>
</template>
</u-page-section>

<u-page-section>
<template v-slot:title="">

Our Ecosystem

</template>

<template v-slot:description="">

Extend your Laravel applications with our ecosystem of complementary tools

</template>

<card-group>
<card icon="i-simple-icons-laravel" target="_blank" title="FilaForms" to="https://filaforms.app">

![FilaForms](https://filaforms.app/img/og-image.png)

Visual form builder for all your public-facing forms.

</card>

<card icon="i-lucide-sliders" target="_blank" title="Custom Fields" to="https://relaticle.github.io/custom-fields">

![Custom Fields](https://github.com/Relaticle/custom-fields/raw/2.x/art/preview.png)

Let users add custom fields to any model without code changes.

</card>

<card icon="i-lucide-kanban" target="_blank" title="Flowforge" to="https://relaticle.github.io/flowforge">

![Flowforge](https://github.com/relaticle/flowforge/raw/4.x/art/preview.png)

Drag-and-drop Kanban for any Laravel model.

</card>
</card-group>
</u-page-section>
