# 🔄 Migration Cleanup Summary

## ✅ Problem Solved
- **Issue**: Duplicate `school_users` table creation causing conflicts
- **Root Cause**: Migration `2024_06_15_000000_create_school_user_table.php` was trying to create/modify a table that already existed in the base migration

## 🗂️ New Organization Structure

### 📁 `/database/migrations/old-legacy/` 
**Purpose**: Historical reference only - DO NOT RUN
- Contains original migrations that were causing conflicts
- Includes the problematic `school_users` creation
- Kept for historical reference and debugging

**Files moved here**: 11 files
- `2000_01_01_000000_ci_core_tables.php` (original base tables)
- `2010_01_01_000000_create_permission_tables.php`  
- Various legacy migrations from 2025_01_* to 2025_07_*

### 📁 `/database/migrations/old/`
**Purpose**: Clean base structure - RUN FIRST
- Recreated base structure without conflicts
- Proper foreign keys and indexes
- Modern Laravel conventions

**New clean migrations**:
- `2023_01_01_000001_create_base_users_table.php`
- `2023_01_01_000002_create_base_schools_table.php`  
- `2023_01_01_000003_create_school_user_pivot_table.php`
- `2023_01_01_000004_create_permission_tables.php`

**Plus existing old migrations**: 7 files moved from previous `/old/` directory

### 📁 `/database/migrations/` (root)
**Purpose**: V5-specific features - RUN SECOND  
- Only contains true V5 functionality
- Depends on base structure from `/old/`
- Clean, conflict-free migrations

**V5 Migrations remaining**: 14 files
- V5 Bookings system (2025_01_31_*)
- Seasons system (2025_07_28_*)  
- V5 enhancements (2025_08_*)

## 🚀 Execution Order

1. **First**: `php artisan migrate --path=database/migrations/old`
2. **Second**: `php artisan migrate --path=database/migrations`

## 🛠️ Tools Created

### 📖 Documentation
- `database/migrations/README.md` - Detailed structure explanation
- `MIGRATION_CLEANUP_SUMMARY.md` - This summary

### 🔧 Helper Script  
- `scripts/migrate-v5.sh` - Migration management script

**Usage**:
```bash
./scripts/migrate-v5.sh old    # Run base structure
./scripts/migrate-v5.sh v5     # Run V5 features  
./scripts/migrate-v5.sh all    # Run everything in order
./scripts/migrate-v5.sh status # Check status
```

## 🧠 Contexto V5

- **Persistencia**: el contexto (school_id, season_id) se guarda en `personal_access_tokens.meta['context']`, por lo que no se requieren nuevas migraciones para soportarlo.
- **Sin contexto**: si el token de acceso no existe o no contiene información, la API devuelve `school_id` y `season_id` como `null`.
- **Endpoints**:
  - `GET /api/v5/context` – obtiene el contexto actual.
  - `POST /api/v5/context/school` – cambia la escuela y reinicia la temporada.
  - *Política aplicada*: protegidos por `auth:sanctum` y limitados por `throttle:context` (30 solicitudes/minuto por usuario o IP).

## 🎯 Benefits

1. **✅ No More Conflicts**: Eliminated duplicate table creation
2. **📋 Clear Structure**: Logical separation of legacy, base, and V5
3. **🔄 Safe Execution**: Proper dependency order guaranteed
4. **📚 Documentation**: Clear instructions for future developers
5. **🛠️ Automation**: Helper scripts for easy management

## ⚠️ Important Notes

- **Never run migrations in `old-legacy/`** - they contain duplicates
- **Always run `old/` before root migrations** - V5 depends on base structure  
- **The problematic `school_user` migration has been completely removed**
- **New `school_user` table uses proper pivot table conventions**

## 🔍 What Was The Problem?

The migration `2024_06_15_000000_create_school_user_table.php` was:
1. Trying to rename `school_users` to `school_user`
2. But `school_users` was already defined in the base migration
3. Creating complex conditional logic that could fail
4. Using inconsistent naming conventions

## ✨ What's The Solution?

1. **Removed** the problematic migration entirely
2. **Created** a clean `school_user` pivot table in `/old/`  
3. **Organized** all migrations by purpose and execution order
4. **Provided** clear documentation and tooling

The database structure is now clean, organized, and ready for V5 development! 🎉
