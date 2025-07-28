# Boukii V5 Overview

This documentation introduces the structure and conventions used in the `app/V5` namespace. Boukii V5 groups all new modules under this directory and exposes them via the `/api/v5` route prefix.

## Modular Structure

Modules live in `app/V5/Modules`. Each module contains `Controllers`, `Services` and `Repositories`. Base classes located under `app/V5` provide common behaviour:

- `BaseV5Controller` – extends Laravel's base controller and wraps JSON responses via the `respond()` helper.
- `BaseService` – holds a reference to a module's repository and encapsulates business logic.
- `BaseRepository` – entry point for database models or other data sources.

This layout encourages loose coupling and mirrors the folder hierarchy under `Modules/*`.

## Naming Conventions

- Classes inside `Modules/*` follow the singular module name, e.g. `HealthCheckService`.
- Controllers must extend `BaseV5Controller` and are grouped under `Controllers`.
- Services extend `BaseService` and live under `Services`.
- Repositories extend `BaseRepository` and live under `Repositories`.

## Base Route

All endpoints are prefixed with `/api/v5`. The first endpoint implemented is the health check:

```http
GET /api/v5/health-check
```

It returns:

```json
{ "status": "ok" }
```

Use this route to confirm that the V5 API is reachable.

The Season module manages school terms and is exposed under `/api/v5/seasons`. See `docs/v5/season.md` for all Season endpoints.
## Running V5 Tests

Feature tests for V5 reside under `tests/Feature`, currently starting with `V5HealthCheckTest.php`. Run all tests with:

```bash
php artisan test
```

You can run only V5 tests by filtering:

```bash
php artisan test --filter=V5
```

Make sure Composer dependencies are installed before executing the test suite.
