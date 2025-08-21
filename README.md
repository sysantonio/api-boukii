# api-boukii (V5)

Documentación → `docs/shared/` y `docs/backend/`.

- [Overview V5](docs/shared/V5_OVERVIEW.md)
- [Arquitectura Backend](docs/backend/ARCHITECTURE.md)
- [Migración V5](docs/backend/BOUKII_5_MIGRATION.md)
- [API Docs](docs/api/API_DOCUMENTATION.md)
- [Guía Frontend](docs/shared/FRONTEND_ANGULAR.md)
- [Operaciones](docs/backend/OPERATIONS.md)

## How to run tests

### SQLite smoke
```bash
cp .env.example .env
cat .env.ci.sqlite >> .env
touch database/database.sqlite
php artisan migrate:fresh
php artisan test --testsuite=Ci
```

### MySQL full
```bash
cp .env.example .env
cat .env.ci.mysql >> .env
mysql -h 127.0.0.1 -P 3306 -u root -proot boukii_v5 < database/schema/mysql/schema.sql
php artisan migrate
php artisan test --testsuite=Full
```
