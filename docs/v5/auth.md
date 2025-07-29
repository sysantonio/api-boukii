# Auth Module

The Auth module handles authentication using season context and role permissions.

## Endpoints

### Login

```http
POST /api/v5/auth/login
```

Parameters:
- `email` (string, required)
- `password` (string, required)
- `season_id` (integer, required if `school_id` not provided)

`season.context` middleware will fill `season_id` when only `school_id` is sent.

Response:
```json
{
  "token": "<access-token>",
  "user": { /* User fields */ },
  "role": "manager",
  "season_id": 1,
  "permissions": ["view schools"]
}
```

### Permissions

```http
GET /api/v5/auth/permissions?season_id={id}
```

Returns an array of permission strings for the authenticated user. Requires the
`season.context` **and** `season.permission` middleware.

### Switch Season Role

```http
POST /api/v5/auth/season/switch
```

Body:
```json
{
  "season_id": 1
}
```

Sets the user's role for the season to `active`. Protected by `season.context`
and `season.permission` middleware.

## Season Context

All endpoints depend on the season context. The middleware determines the
current season when only `school_id` is provided and ensures the user has the
necessary permissions via `SeasonPermissionGuard`.
