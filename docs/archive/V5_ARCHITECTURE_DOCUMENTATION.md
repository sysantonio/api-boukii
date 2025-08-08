# 📚 DOCUMENTACIÓN ARQUITECTURA V5 - SISTEMA BOUKII

## 🎯 VISIÓN GENERAL

El sistema V5 de Boukii es una arquitectura limpia y escalable que sigue principios RESTful y patrones de diseño modernos. Está diseñado para soportar operaciones multi-escuela y multi-temporada con un sistema granular de permisos.

## 📁 ESTRUCTURA DE CONTROLADORES

### Ubicación Unificada: `app/Http/Controllers/Api/V5/`

```
app/Http/Controllers/Api/V5/
├── Auth/
│   └── AuthController.php           # Gestión de autenticación completa
├── Dashboard/
│   └── DashboardController.php      # Analytics y vista general
├── Seasons/
│   └── SeasonController.php         # Gestión de temporadas
├── Schools/                         # (Futuro)
│   └── SchoolController.php
├── Bookings/                        # (Futuro)
│   └── BookingController.php
├── Clients/                         # (Futuro)
│   └── ClientController.php
├── Monitors/                        # (Futuro)
│   └── MonitorController.php
├── Courses/                         # (Futuro)
│   └── CourseController.php
└── Analytics/                       # (Futuro)
    └── AnalyticsController.php
```

## 🔧 SISTEMA DE MIDDLEWARES

### Middlewares V5 en `app/Http/Middleware/V5/`

| Middleware | Función | Alias | Uso |
|---|---|---|---|
| `SchoolContextMiddleware` | Inyecta contexto de escuela desde token | `school.context.v5` | Rutas que requieren escuela |
| `SeasonPermissionGuard` | Valida permisos de temporada | `season.permission` | Rutas con recursos de temporada |
| `RolePermissionMiddleware` | Sistema granular de permisos | `role.permission` | Control de acceso granular |
| `LoggingMiddleware` | Logging de requests V5 | `v5.logging` | Auditoría y debugging |

## 🛣️ ESTRUCTURA DE RUTAS RESTful

### Archivo: `routes/api/v5-unified.php`

#### **Nivel 1: Rutas Públicas** (sin autenticación)
```
POST /api/v5/auth/check-user      # Verificar credenciales
POST /api/v5/auth/login           # Login completo
POST /api/v5/auth/initial-login   # Login inicial
GET  /api/v5/health-check         # Estado del sistema
```

#### **Nivel 2: Rutas Autenticadas** (`auth:sanctum`)
```
POST /api/v5/auth/logout          # Cerrar sesión
GET  /api/v5/auth/me              # Info usuario
POST /api/v5/auth/select-school   # Seleccionar escuela
GET  /api/v5/auth/permissions     # Permisos usuario
```

#### **Nivel 3: Contexto de Escuela** (`school.context.v5`)
```
# Gestión de escuela
GET  /api/v5/schools/current      # Escuela actual
PUT  /api/v5/schools/current      # Actualizar escuela

# Gestión de temporadas (NO requiere contexto de temporada)
GET    /api/v5/seasons            # Listar temporadas
POST   /api/v5/seasons            # Crear temporada
GET    /api/v5/seasons/current    # Temporada actual
GET    /api/v5/seasons/{id}       # Ver temporada
PUT    /api/v5/seasons/{id}       # Actualizar temporada
DELETE /api/v5/seasons/{id}       # Eliminar temporada
POST   /api/v5/seasons/{id}/close # Cerrar temporada

# Selección de temporada
POST /api/v5/auth/select-season   # Seleccionar temporada
POST /api/v5/auth/switch-season   # Cambiar temporada
```

#### **Nivel 4: Contexto de Escuela + Temporada** (`season.permission`)
```
# Dashboard
GET    /api/v5/dashboard/stats              # Estadísticas
GET    /api/v5/dashboard/recent-activity    # Actividad reciente
GET    /api/v5/dashboard/alerts             # Alertas
DELETE /api/v5/dashboard/alerts/{id}        # Descartar alerta
GET    /api/v5/dashboard/daily-sessions     # Sesiones diarias
GET    /api/v5/dashboard/today-reservations # Reservas de hoy

# Recursos principales (FUTURO)
GET    /api/v5/bookings          # Listar reservas
POST   /api/v5/bookings          # Crear reserva
GET    /api/v5/bookings/{id}     # Ver reserva
PUT    /api/v5/bookings/{id}     # Actualizar reserva
DELETE /api/v5/bookings/{id}     # Eliminar reserva

# Similarmente para clients, monitors, courses, equipment...
```

#### **Compatibilidad Legacy** (DEPRECADO)
```
GET /api/v5/welcome/stats         # → /api/v5/dashboard/stats
GET /api/v5/welcome/alerts        # → /api/v5/dashboard/alerts
GET /api/v5/welcome/recent-activity # → /api/v5/dashboard/recent-activity
```

## 🔐 SISTEMA DE ROLES Y PERMISOS

### **Niveles de Permisos:**

#### **1. Permisos Globales**
- `global.admin` - Administrador total del sistema
- `global.support` - Soporte técnico

#### **2. Permisos de Escuela**
- `school.admin` - Admin total de la escuela
- `school.manager` - Manager de la escuela  
- `school.staff` - Personal de la escuela
- `school.view` - Solo visualización
- `school.settings` - Configuración de escuela
- `school.users` - Gestión de usuarios
- `school.billing` - Facturación

#### **3. Permisos de Temporada**
- `season.admin` - Admin total de temporada
- `season.manager` - Gestión de temporada
- `season.view` - Solo visualización
- `season.bookings` - Gestión de reservas
- `season.clients` - Gestión de clientes
- `season.monitors` - Gestión de monitores
- `season.courses` - Gestión de cursos
- `season.analytics` - Ver analytics
- `season.equipment` - Gestión de equipamiento

#### **4. Permisos Granulares por Recurso**
```php
// Bookings
'booking.create', 'booking.read', 'booking.update', 'booking.delete', 'booking.payment'

// Clients  
'client.create', 'client.read', 'client.update', 'client.delete', 'client.export'

// Monitors
'monitor.create', 'monitor.read', 'monitor.update', 'monitor.delete', 'monitor.schedule'

// Courses
'course.create', 'course.read', 'course.update', 'course.delete', 'course.pricing'
```

### **Jerarquía de Permisos:**
1. **Global** > **Escuela** > **Temporada** > **Recurso**
2. Permisos superiores **heredan** automáticamente permisos inferiores
3. `school.admin` obtiene automáticamente permisos de `season.manager`
4. `season.admin` obtiene automáticamente todos los permisos de recursos

### **Uso en Rutas:**
```php
// Requiere permiso específico
Route::get('bookings', [BookingController::class, 'index'])
     ->middleware('role.permission:booking.read');

// Requiere múltiples permisos
Route::post('bookings', [BookingController::class, 'store'])
     ->middleware('role.permission:booking.create,season.bookings');

// Permiso de admin de temporada
Route::delete('seasons/{id}', [SeasonController::class, 'destroy'])
     ->middleware('role.permission:season.admin');
```

## 📊 CORRESPONDENCIA FRONTEND-BACKEND

### **Rutas y Componentes:**

| Frontend Route | Frontend Component | Backend Controller | Backend Endpoint |
|---|---|---|---|
| `/v5/dashboard` | `DashboardComponent` | `DashboardController` | `GET /v5/dashboard/stats` |
| `/v5/auth/login` | `LoginV5Component` | `AuthController` | `POST /v5/auth/login` |
| `/v5/seasons` | `SeasonListComponent` | `SeasonController` | `GET /v5/seasons` |
| `/v5/seasons/new` | `SeasonFormComponent` | `SeasonController` | `POST /v5/seasons` |
| `/v5/bookings` | `BookingListComponent` | `BookingController` | `GET /v5/bookings` |
| `/v5/clients` | `ClientListComponent` | `ClientController` | `GET /v5/clients` |
| `/v5/monitors` | `MonitorListComponent` | `MonitorController` | `GET /v5/monitors` |
| `/v5/courses` | `CourseListComponent` | `CourseController` | `GET /v5/courses` |
| `/v5/analytics` | `AnalyticsComponent` | `AnalyticsController` | `GET /v5/analytics/*` |

### **Servicios Frontend:**

| Frontend Service | Backend Endpoints | Funcionalidad |
|---|---|---|
| `AuthV5Service` | `/v5/auth/*` | Login, logout, permisos |
| `DashboardService` | `/v5/dashboard/*` | Stats, alertas, actividad |
| `SeasonService` | `/v5/seasons/*` | CRUD temporadas |
| `BookingService` | `/v5/bookings/*` | CRUD reservas |
| `ClientService` | `/v5/clients/*` | CRUD clientes |
| `MonitorService` | `/v5/monitors/*` | CRUD monitores |
| `CourseService` | `/v5/courses/*` | CRUD cursos |

## 🔄 FLUJO DE AUTENTICACIÓN

### **Flujo Multi-Paso:**

1. **Check User** (`POST /v5/auth/check-user`)
   - Verifica credenciales
   - Retorna escuelas disponibles
   - No crea token aún

2. **Select School** (`POST /v5/auth/select-school`)
   - Usuario selecciona escuela
   - Retorna temporadas disponibles
   - Opción de crear temporada

3. **Select Season** (`POST /v5/auth/select-season`)
   - Usuario selecciona/crea temporada
   - Crea token con contexto completo
   - Login completo

4. **Switch Season** (`POST /v5/auth/switch-season`)
   - Cambia contexto de temporada
   - Mantiene misma escuela
   - Actualiza permisos

### **Flujo Directo:**

1. **Login Completo** (`POST /v5/auth/login`)
   - Email, password, school_id, season_id
   - Validaciones completas
   - Token con contexto inmediato

### **Token Context Data:**
```json
{
  "school_id": 2,
  "school_slug": "escuela-test-v5",
  "season_id": 11,
  "login_at": "2025-01-15T10:30:00Z",
  "ip": "192.168.1.100",
  "user_agent": "Mozilla/5.0..."
}
```

## 🧪 SISTEMA DE TESTING

### **Tests de Middleware:**
```php
// tests/Unit/V5/Middleware/
SchoolContextMiddlewareTest.php
RolePermissionMiddlewareTest.php
SeasonPermissionMiddlewareTest.php
```

### **Tests de Controladores:**
```php  
// tests/Feature/V5/Controllers/
AuthControllerTest.php
DashboardControllerTest.php
SeasonControllerTest.php
```

### **Tests de Rutas Protegidas:**
```php
// tests/Feature/V5/Routes/
RouteProtectionTest.php
PermissionTest.php
ContextTest.php
```

### **Ejemplo de Test:**
```php
public function test_dashboard_stats_requires_season_permission()
{
    $user = User::factory()->create();
    $school = School::factory()->create();
    $season = Season::factory()->create(['school_id' => $school->id]);
    
    // User without permission
    $response = $this->actingAs($user, 'api_v5')
        ->withHeaders(['X-Season-ID' => $season->id])
        ->get('/api/v5/dashboard/stats');
        
    $response->assertStatus(403);
    
    // User with permission
    $this->assignSeasonRole($user, $season, 'manager');
    
    $response = $this->actingAs($user, 'api_v5')
        ->withHeaders(['X-Season-ID' => $season->id])
        ->get('/api/v5/dashboard/stats');
        
    $response->assertStatus(200);
}
```

## 🚀 PATRONES PARA NUEVOS MÓDULOS

### **1. Crear Controlador**
```php
// app/Http/Controllers/Api/V5/NewModule/NewModuleController.php
<?php

namespace App\Http\Controllers\Api\V5\NewModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewModuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schoolId = $this->getSchoolIdFromContext($request);
        $seasonId = $this->getSeasonIdFromContext($request);
        
        // Logic here...
        
        return $this->successResponse($data, 'Success message');
    }
    
    // Helper methods...
    private function getSchoolIdFromContext(Request $request): int
    private function getSeasonIdFromContext(Request $request): ?int
    private function successResponse($data, string $message): JsonResponse
    private function errorResponse(string $message, int $status = 400): JsonResponse
}
```

### **2. Agregar Rutas**
```php
// En routes/api/v5-unified.php
Route::middleware(['season.permission'])->group(function () {
    Route::apiResource('new-resources', NewModuleController::class)->names('v5.new-resources');
    
    // Rutas específicas
    Route::prefix('new-resources')->name('v5.new-resources.')->group(function () {
        Route::post('{id}/special-action', [NewModuleController::class, 'specialAction'])->name('special-action');
    });
});
```

### **3. Agregar Permisos**
```php
// En RolePermissionMiddleware.php
const NEW_RESOURCE_CREATE = 'new-resource.create';
const NEW_RESOURCE_READ = 'new-resource.read';
const NEW_RESOURCE_UPDATE = 'new-resource.update';
const NEW_RESOURCE_DELETE = 'new-resource.delete';
```

### **4. Crear Tests**
```php
// tests/Feature/V5/NewModuleControllerTest.php
class NewModuleControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_list_resources_with_proper_permissions()
    {
        // Test implementation...
    }
}
```

### **5. Servicio Frontend**
```typescript
// src/app/v5/core/services/new-module.service.ts
@Injectable({ providedIn: 'root' })
export class NewModuleService {
  constructor(private apiV5: ApiV5Service) {}
  
  getResources(seasonId: number): Observable<ApiV5Response<any[]>> {
    return this.apiV5.get<ApiV5Response<any[]>>('new-resources', { season_id: seasonId.toString() });
  }
}
```

## ⚠️ CONSIDERACIONES IMPORTANTES

### **Seguridad:**
- Nunca exponer datos de otras escuelas
- Validar siempre contexto de escuela y temporada
- Usar middlewares de permisos en todas las rutas sensibles
- Logging exhaustivo para auditoría

### **Performance:**
- Cache en consultas pesadas (dashboard stats)
- Índices apropiados en base de datos
- Paginación en listados grandes
- Lazy loading en frontend

### **Mantenibilidad:**
- Seguir convenciones de naming establecidas
- Documentar cambios importantes
- Tests unitarios y de integración
- Versionado apropiado de API

### **Escalabilidad:**
- Estructura modular para nuevas funcionalidades
- Separación clara de responsabilidades
- APIs RESTful estándar
- Microservicios future-ready

## 🎉 BENEFICIOS DE LA ARQUITECTURA V5

✅ **Consistencia** - Convenciones unificadas en todo el sistema
✅ **Escalabilidad** - Estructura modular y extensible  
✅ **Seguridad** - Sistema granular de permisos
✅ **Mantenibilidad** - Código limpio y bien organizado
✅ **Performance** - Cache y optimizaciones incluidas
✅ **Testing** - Suite de tests completa
✅ **Documentación** - Documentación exhaustiva y actualizada

---

**Versión:** 1.0  
**Última actualización:** Enero 2025  
**Autor:** Claude Code Assistant  
**Revisado por:** Equipo Boukii V5