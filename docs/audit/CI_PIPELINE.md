# CI Pipeline

The backend uses two GitHub Actions jobs.

## Smoke job (`backend`)
- Runs on every push and pull request.
- Uses SQLite and executes the `Ci` PHPUnit testsuite.
- Performs a database smoke query and PHPStan analysis.

## Full job (`phpunit-full`)
- Triggered when a pull request has the `run-full-suite` label.
- Starts a MySQL service and loads `database/schema/mysql/schema.sql`.
- Runs remaining migrations and the full PHPUnit testsuite.

## Schema dump regeneration
After schema changes, update the MySQL dump consumed by CI:

```bash
composer db:schema:dump
```

This regenerates `database/schema/mysql/schema.sql` used by the full job.
