# CLAUDE.md

Este archivo proporciona instrucciones y contexto para **Claude Code** (claude.ai/code) al trabajar con el repositorio backend de **Boukii V5**.

Incluye información de **backend (Laravel 10+)**, arquitectura V5, sistema multi-escuela/temporada, comandos de desarrollo y sincronización de documentación.

---

## 📂 Ubicaciones principales
- **Backend Laravel**: `C:\laragon\www\api-boukii` (este repositorio)
- **Frontend Angular**: `C:\Users\aym14\Documents\WebstormProjects\boukii\boukii-admin-panel`

---

## 💻 Development Commands

### Backend (Laravel)
- `php artisan serve` - Iniciar servidor local (no usar, ya está en Laragon) la url es: http://api-boukii.test
- `php artisan migrate` - Ejecutar migraciones
- `php artisan test` - Ejecutar suite de tests
- `php artisan route:list` - Listar rutas
- `php artisan tinker` - Consola interactiva

### Testing
```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos de V5
php artisan test --group=v5
php artisan test --group=context

# Tests con coverage
php artisan test --coverage

# Test específico
php artisan test tests/Feature/V5/AuthTest.php
```

### Code Quality
```bash
# PHP CS Fixer (estilo de código)
./vendor/bin/php-cs-fixer fix

# PHPStan (análisis estático)
./vendor/bin/phpstan analyse

# Laravel Pint
vendor/bin/pint
```

---

## 🏗 Arquitectura V5

### Sistema Multi-Escuela y Multi-Temporada
- **Context Headers**: `X-School-ID` y `X-Season-ID` obligatorios en rutas admin
- **Middleware**: `ContextMiddleware` unificado para validación
- **Roles**: Sistema granular por escuela y temporada (`user_season_roles`)
- **Tokens**: Sanctum con contexto embebido

### Backend (Laravel 10+)
- **Controladores V5**: Extienden `BaseV5Controller`
- **Rutas**: `routes/api/v5.php` con middleware `context`
- **Middleware stack**:
  - `auth:sanctum`
  - `context.middleware`
  - `role.permission.middleware`
- **Logs**: Canal `v5_enterprise` con contexto (`user_id`, `school_id`, `season_id`)

### Base de Datos
- **Tabla clave**: `user_season_roles` para permisos granulares
- **Seeds seguros**: Usar `updateOrCreate` siempre
- **Usuarios de prueba**:
  - `admin@boukii-v5.com` (multi-school)
  - `multi@boukii-v5.com` (school 2 only)
- **School 2**: Debe tener al menos una season activa

### Flujo de API
```
Request → auth:sanctum → context.middleware → role.permission → Controller
```

---

## 🎨 Domain Models V5

### Core Entities
- **School**: Entidad multi-tenant principal
- **Season**: Temporadas por escuela con configuración independiente
- **User**: Con roles por escuela/temporada
- **Booking**: Reservas contextualizadas por school/season
- **Course**: Cursos con contexto temporal
- **Client**: Clientes multi-escuela
- **Monitor**: Instructores con asignaciones por temporada multi-escuela

### Key Services V5
- **BaseV5Controller**: Controlador base con contexto automático
- **ContextMiddleware**: Validación de headers y permisos
- **SeasonService**: Lógica de temporadas y contexto

---

## ⚠️ Buenas Prácticas V5

### API Development
- Extender `BaseV5Controller` para endpoints V5
- Usar Form Requests para validación
- Respuestas JSON estándar: `{success, data, meta}`
- Logs con contexto en canal `v5_enterprise`

### Database
- Seeds con `updateOrCreate` para idempotencia
- Probar en **staging** antes de producción
- Migraciones reversibles obligatorias
- Context filtering en queries

### Error Handling
- `V5ExceptionHandler` para respuestas consistentes
- Separar logs: API errors, permisos, auditoría
- Headers de contexto validados siempre

---

## 🔄 Sincronización de Documentación

### Carpetas Editables por Claude
- `docs/shared/` - Documentación sincronizada entre frontend y backend
- `docs/backend/` - Documentación específica del backend (solo en este repo)
- `CLAUDE.md` - Instrucciones para IA (en ambos repos)

### Reglas de Commits
- **Cambios normales**: `docs: descripción del cambio`
- **Commits de sync automática**: `docs-sync: descripción` (NUNCA usar manualmente)
- **Anti-bucle**: Commits con `docs-sync:` NO disparan nueva sincronización

### Proceso de Sync
1. Editar documentación en `/docs/shared/` del repo actual
2. Commit con prefijo `docs:`
3. GitHub Actions sincroniza automáticamente al otro repo
4. Para sync inmediata usar script: `.docs-sync/ROBUST_SYNC.ps1`

### ⚠️ Importante
- NUNCA tocar código sin crear PR primero
- Nunca usar prefijo `docs-sync:` manualmente
- Si el commit contiene `docs-sync:`, no se dispara otra sincronización

---

## 🧪 Testing Strategy V5

### Test Groups
```bash
# Context y middleware
php artisan test --group=context

# API endpoints V5
php artisan test --group=v5

# Tests de integración
php artisan test --group=integration
```

### Critical Test Cases
- Login multi-escuela → tokens con contexto
- Context middleware → validación headers
- Role permissions → acceso granular por escuela/temporada  
- API responses → formato JSON estándar

---

## 📚 Legacy Context

### Domain Models (Legacy)
- **Booking**: Central entity managing course reservations with complex pricing, payments, and voucher systems
- **Course**: Sports courses with flexible pricing models (collective/private/activities) organized in groups and subgroups  
- **Client**: Customer management with multi-language support and utilizer relationships
- **Monitor**: Instructors with sport-specific degrees and availability management

### Key Services (Legacy)
- **BookingPriceCalculatorService**: Sophisticated pricing engine handling flexible vs fixed pricing
- **AnalyticsService**: Financial reporting and dashboard generation
- **PayrexxService**: Payment gateway integration
- **SeasonFinanceService**: Seasonal financial analytics

### Repository Pattern
All models use the Repository pattern with a `BaseRepository` providing common CRUD operations with multi-tenant filtering.

### API Organization (Legacy)
- `/api/admin/` - Admin panel endpoints
- `/api/teach/` - Monitor/instructor mobile app endpoints  
- `/api/sports/` - Client sports app endpoints
- `/api/v5/` - **V5 endpoints** (preferred for new development)

---

## 🚀 Migration to V5

### Priority
- **New features**: Usar siempre endpoints `/api/v5/`
- **Legacy endpoints**: Mantener compatibilidad pero migrar gradualmente
- **Context required**: Todos los endpoints admin V5 requieren contexto

### V5 Development Only
- Nuevos controladores deben extender `BaseV5Controller`
- Middleware stack V5 obligatorio
- Context headers en todas las requests admin
- Logs estructurados con contexto

---

*Última actualización: 2025-08-13*
