# Database Migrations Structure

Este directorio contiene las migraciones organizadas en la siguiente estructura:

## 📁 Estructura de Carpetas

### `/old-legacy/` 
Migraciones que existían antes de la reorganización V5. **NO EJECUTAR** - Solo para referencia histórica.

### `/old/`  
Migraciones que recrean la estructura base antigua de forma limpia. Estas se deben ejecutar **ANTES** que las migraciones V5.

### `/` (raíz)
Migraciones específicas de V5 que añaden nueva funcionalidad. Se ejecutan **DESPUÉS** de las migraciones `/old/`.

## 🚀 Orden de Ejecución

1. **Primero**: Ejecutar migraciones en `/old/` para crear la estructura base
2. **Segundo**: Ejecutar migraciones en `/` (raíz) para añadir funcionalidad V5

## 📋 Contenido por Carpeta

### old-legacy/ (Solo referencia - NO ejecutar)
- `2000_01_01_000000_ci_core_tables.php` - Tablas core originales
- `2010_01_01_000000_create_permission_tables.php` - Sistema de permisos original
- Otras migraciones legacy...

### old/ (Ejecutar primero)
- `2023_01_01_000001_create_base_users_table.php` - Tabla users limpia
- `2023_01_01_000002_create_base_schools_table.php` - Tabla schools limpia  
- `2023_01_01_000003_create_school_user_pivot_table.php` - Tabla pivot school_user
- `2023_01_01_000004_create_permission_tables.php` - Sistema de permisos

### / (raíz - Ejecutar segundo)
- `2025_01_31_*` - Tablas V5 de reservas
- `2025_07_28_*` - Sistema de temporadas
- `2025_08_*` - Mejoras y logs V5

## ⚠️ Importante

- Las migraciones en `old-legacy/` están duplicadas y **NO deben ejecutarse**
- Siempre ejecutar primero las migraciones `old/` antes que las de la raíz
- Las migraciones V5 dependen de la estructura creada en `old/`

## 🔧 Scripts de Ayuda

Para facilitar la ejecución, puedes usar:

```bash
# Ejecutar solo migraciones 'old' (estructura base)
php artisan migrate --path=database/migrations/old

# Ejecutar solo migraciones V5 (raíz)  
php artisan migrate --path=database/migrations

# Ver estado de todas las migraciones
php artisan migrate:status
```