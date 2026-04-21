# Contributing

> Run the suite, follow conventions, and submit changes.

<callout color="warning" icon="i-lucide-construction">

A full contributor guide is coming. The essentials below will get you running locally and pointed at the right places.

</callout>

## Running the test suite

```bash [Terminal]
cd Plugins/ActivityLog
composer install
vendor/bin/pest
```

The package ships fixtures (`Person`, `Email`, `Note`, `Task`) in `tests/Fixtures/` and uses Orchestra Testbench for isolation.

## Reporting bugs

Open an issue on [GitHub](https://github.com/relaticle/activity-log/issues). Include:

- Laravel, Filament, PHP, and `relaticle/activity-log` versions
- A minimal reproduction (the smaller the better)
- The actual vs expected behavior

## Submitting pull requests

1. Fork the repo and create a topic branch off `1.x`.
2. Add tests that fail without your change and pass with it.
3. Run the full suite locally before opening the PR.
4. Describe the motivation and the user-visible impact in the PR body.

## Discussions

For open-ended questions, design proposals, or "is this a bug?" triage, use [GitHub Discussions](https://github.com/relaticle/activity-log/discussions).
