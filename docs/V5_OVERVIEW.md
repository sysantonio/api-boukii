# Boukii V5 Overview

Boukii V5 centralizes new API modules under `app/V5` and exposes them through the `/api/v5` prefix.
This document summarizes the architecture, routing conventions and context headers required for multi‑school and multi‑season operation.

## Access Flow

```text
login → school → season → admin
```

1. **Login** – authenticate the user and issue a token.
2. **School** – the user selects a school; requests must include `X-School-ID`.
3. **Season** – optionally select a season; protected routes also require `X-Season-ID`.
4. **Admin** – once context is set the user can access administrative modules.

## Modular Structure

Modules live in `app/V5/Modules/{Module}` and contain:

- `Controllers/` extending `BaseV5Controller` for JSON responses.
- `Services/` extending `BaseService` to encapsulate business logic.
- `Repositories/` extending `BaseRepository` as data access points.

This mirrors the directory hierarchy and keeps modules loosely coupled.

## Routing and Controllers

All routes are defined in `routes/api/v5-unified.php` using the `/api/v5` prefix.
Routes follow four layers:

1. **Public** – authentication and health check endpoints.
2. **Authenticated** – require `auth:sanctum`.
3. **School Context** – add `school.context.v5` middleware and expect `X-School-ID`.
4. **School + Season** – add `season.permission` middleware and expect `X-Season-ID`.

Controllers reside in `app/Http/Controllers/Api/V5/` (or module equivalents) and follow RESTful naming, e.g. `DashboardController`, `SeasonController`.

## Header Examples

Include the school and season context headers on protected requests:

```http
GET /api/v5/dashboard/stats
X-School-ID: 1
X-Season-ID: 2024
Authorization: Bearer <token>
```

```http
POST /api/v5/seasons
X-School-ID: 1
Authorization: Bearer <token>
```

`X-Season-ID` is optional unless the route operates within a season.

---

Run the test suite with:

```bash
php artisan test --filter=V5
```
