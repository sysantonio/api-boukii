# PLAN DE REFACTOR V5 API
## Limpieza y Reorganización Completa

### 🎯 OBJETIVOS:
1. Eliminar archivos duplicados
2. Establecer nomenclatura consistente (Dashboard = Welcome)
3. Consolidar controladores V5
4. Organizar estructura modular clara
5. Mantener flujo de permisos consistente

---

## 📋 PROBLEMAS IDENTIFICADOS:

### 1. INCONSISTENCIA DASHBOARD vs WELCOME
**Problema:** Frontend Angular llama a "Welcome" pero backend tiene "Dashboard"
- ❌ Frontend: `/v5/welcome` component
- ❌ Backend: `/dashboard` endpoint
- ❌ Controlador: `DashboardV5Controller` 

**Solución:** Unificar bajo nombre "Dashboard" (más estándar)

### 2. CONTROLADORES DUPLICADOS
- ✅ `app/V5/Modules/Dashboard/Controllers/DashboardV5Controller.php` (MANTENER)
- ❌ `routes/api/v5.php` endpoint hardcodeado (ELIMINAR)
- ❌ Posibles duplicados en `app/Http/Controllers/API/V5/`

### 3. RUTAS DUPLICADAS
- ❌ `routes/api/v5.php` 
- ❌ `routes/api_v5.php`
- ✅ Consolidar en una sola estructura

### 4. MIDDLEWARE/GUARDS DUPLICADOS
- ✅ `app/V5/Guards/SeasonPermissionGuard.php` (MANTENER - ya arreglado)
- ❓ `app/V5/Middleware/SeasonPermissionMiddleware.php` (REVISAR)

### 5. LLAMADAS FALTANTES
- ❌ `alerts` endpoint no encontrado en DashboardV5Controller
- ❌ `recent-activity` endpoint existe pero puede necesitar ajustes

---

## 🔧 PLAN DE EJECUCIÓN:

### FASE 1: CONSOLIDAR NOMENCLATURA
1. Mantener "Dashboard" como término estándar
2. Actualizar frontend Angular para usar `/dashboard` en lugar de `/welcome`
3. Crear alias de compatibilidad temporal

### FASE 2: LIMPIAR CONTROLADORES
1. Consolidar en `app/V5/Modules/Dashboard/Controllers/DashboardV5Controller.php`
2. Eliminar controladores duplicados
3. Agregar métodos faltantes (`alerts`, `notifications`)

### FASE 3: REORGANIZAR RUTAS
1. Consolidar todas las rutas V5 en `routes/api/v5.php`
2. Eliminar `routes/api_v5.php`
3. Usar controladores modulares

### FASE 4: VERIFICAR PERMISOS
1. Consolidar middleware de permisos
2. Verificar flujo de roles consistente
3. Testear endpoints con nuevos permisos

---

## 📁 ESTRUCTURA OBJETIVO:

```
app/V5/
├── Modules/
│   ├── Auth/
│   │   ├── Controllers/AuthV5Controller.php
│   │   └── Services/AuthV5Service.php
│   ├── Dashboard/
│   │   └── Controllers/DashboardV5Controller.php
│   ├── Season/
│   │   └── Controllers/SeasonV5Controller.php
│   └── School/
│       └── Controllers/SchoolV5Controller.php
├── Guards/
│   └── SeasonPermissionGuard.php
├── Middleware/ (limpiar duplicados)
└── Services/
    └── BaseService.php

routes/
└── api/
    └── v5.php (ÚNICO archivo de rutas V5)
```

---

## ⚠️ CONSIDERACIONES:
- Mantener compatibilidad temporal con rutas existentes
- Verificar todos los tests después del refactor  
- Actualizar documentación de API
- Coordinar cambios con frontend Angular