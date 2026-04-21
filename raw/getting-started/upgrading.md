# Upgrading

> Version-to-version upgrade notes.

<callout color="warning" icon="i-lucide-construction">

This page is a placeholder. Per-version upgrade notes will land here as releases ship.

</callout>

## General upgrade workflow

```bash [Terminal]
composer update relaticle/activity-log
```

Re-publish the config if new keys are introduced:

```bash [Terminal]
php artisan vendor:publish --tag=activity-log-config --force
```

Always review the [CHANGELOG](https://github.com/relaticle/activity-log/releases) before upgrading across minor versions.
