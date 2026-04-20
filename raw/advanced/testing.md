# Testing

> Run the package test suite locally.

```bash
cd Plugins/ActivityLog
composer install
vendor/bin/pest
```

The package ships fixtures (`Person`, `Email`, `Note`, `Task`) in `tests/Fixtures/` and uses Orchestra Testbench for isolation.
