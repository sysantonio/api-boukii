# 📅 PLAN SEMANAL BOUKII V5 - DESARROLLO PROGRESIVO

## 🎯 ESTADO ACTUAL (Baseline)

### ✅ Completado
- **Backend**: Estructura V5 con BaseController/Service/Repository
- **Módulo HealthCheck**: Controller + Service + Repository + Test (/api/v5/health-check)
- **Frontend**: Angular V5Module con layout base y routing
- **Documentación**: Arquitectura V5 completa en boukii-5.0.md

### 🎯 Objetivo Semanal
Establecer la **base crítica** del sistema V5 con módulos fundamentales que permitan el desarrollo de funcionalidades avanzadas.

---

## 📋 SEMANA 1: FUNDACIÓN CORE (Crítico)

### 🗓️ **LUNES-MARTES: Módulo Seasons (Prioridad Máxima)**

#### Backend Tasks
```bash
# Día 1
- Crear Migration: create_seasons_table + season_snapshots
- Model Season con relaciones y business logic
- SeasonRepository con queries season-aware
- SeasonService con season management

# Día 2  
- SeasonController V5 con CRUD completo
- APIs: GET/POST/PUT/DELETE /v5/seasons
- Endpoint especial: /v5/seasons/current
- Tests unitarios + feature tests
```

#### Frontend Tasks
```bash
# Día 1
- SeasonContextService para manejo global
- SeasonSelectorComponent (dropdown + stats)
- Season interfaces y tipos TypeScript

# Día 2
- Integración SeasonSelector en V5Layout
- Season switching funcional
- Observable patterns para context changes
```

#### 🎯 **Entregables Lunes-Martes**
- ✅ API /v5/seasons completamente funcional
- ✅ Season context switching en frontend
- ✅ Tests passing al 100%

---

### 🗓️ **MIÉRCOLES: Schools + Season Context**

#### Backend Tasks
```bash
- Refactorizar SchoolService para V5
- Añadir school_season_settings table
- SchoolV5Controller con season context
- Middleware: SeasonContextMiddleware
- APIs: /v5/schools?season_id=X
```

#### Frontend Tasks
```bash
- SchoolSeasonService con V5 endpoints
- SchoolSeasonComponent para settings
- Integración con SeasonContext global
```

#### 🎯 **Entregables Miércoles**
- ✅ Schools funcionando con season context
- ✅ Middleware season automático en todas las rutas V5

---

### 🗓️ **JUEVES-VIERNES: Auth + Roles V5**

#### Backend Tasks
```bash
# Día 1 (Jueves)
- Migration: user_season_roles table
- Model UserSeasonRole
- AuthV5Service con season-aware permissions
- AuthV5Controller refactorizado

# Día 2 (Viernes)
- AuthV5Middleware con role checking
- APIs: /v5/auth/login, /v5/auth/permissions
- Season-specific role assignment
- Tests de autorización por season
```

#### Frontend Tasks
```bash
# Día 1
- AuthV5Service con nuevos endpoints
- User season role interfaces
- AuthGuards V5 con season permissions

# Día 2  
- Role management component
- Season role assignment UI
- Permission matrix per season
```

#### 🎯 **Entregables Jueves-Viernes**
- ✅ Sistema auth V5 con permisos por temporada
- ✅ Role management funcional
- ✅ Guards y middleware working

---

## 📋 SEMANA 2: MÓDULOS CRÍTICOS DE NEGOCIO

### 🗓️ **LUNES-MIÉRCOLES: Courses V5 (Rediseño Total)**

#### Backend Architecture
```bash
# Nuevos Models
- SeasonCourse (curso por temporada)
- SeasonCourseGroup (grupos por temporada)  
- CourseSeasonPricing (precios variables)
- CourseSeasonAvailability (disponibilidad)

# Services Refactorizados
- CourseSeasonService (business logic nuevo)
- CoursePricingService (cálculos complejos)
- CourseAvailabilityService (slots y horarios)
```

#### APIs Críticas
```bash
GET /v5/courses?season_id=X&school_id=Y
POST /v5/courses (crear curso en temporada activa)
PUT /v5/courses/{id}/pricing (actualizar precios)
GET /v5/courses/{id}/availability (disponibilidad real-time)
```

#### Frontend Completo
```bash
- CourseV5Module completo
- CourseSeasonComponent con pricing dinámico
- Course availability calendar
- Pricing management interface
```

### 🗓️ **JUEVES-VIERNES: Bookings V5 (Nueva Arquitectura)**

#### Backend Revolution
```bash
# Models Nuevos
- SeasonBooking (booking por temporada)
- BookingPriceSnapshot (precios inmutables)
- BookingSeasonPayment (pagos versionados)

# Services Críticos  
- BookingSeasonService (lógica completa)
- BookingPriceCalculatorV5 (cálculos season-aware)
- BookingSnapshotService (immutable data)
```

#### 🎯 **Entregables Semana 2**
- ✅ Courses V5 completamente funcional con pricing por temporada
- ✅ Bookings V5 con snapshot inmutable de precios
- ✅ Migración de datos legacy funcionando

---

## 📋 SEMANA 3: MÓDULO ALQUILER (Nuevo Negocio)

### 🗓️ **LUNES-MIÉRCOLES: Rental Module Backend**

#### Nuevos Models
```bash
- RentalItem (equipos disponibles)
- RentalCategory (categorías de material)
- RentalBooking (reservas de alquiler)
- SeasonRentalPricing (precios por temporada)
- RentalAvailability (disponibilidad real-time)
```

#### Services Especializados
```bash
- RentalAvailabilityService (engine de disponibilidad)
- RentalPricingService (cálculos complejos)
- RentalBookingService (reservas y conflicts)
```

#### APIs Completas
```bash
GET /v5/rental/items?category=ski&season_id=X
POST /v5/rental/bookings (nueva reserva)
GET /v5/rental/availability?item_id=X&dates=Y
PUT /v5/rental/bookings/{id}/status (pickup/return)
```

### 🗓️ **JUEVES-VIERNES: Rental Frontend**

#### Componentes Críticos
```bash
- RentalCatalogComponent (búsqueda de material)
- RentalBookingComponent (proceso de reserva)
- RentalCalendarComponent (disponibilidad visual)
- RentalManagementComponent (admin panel)
```

#### 🎯 **Entregables Semana 3**
- ✅ Módulo Rental completamente funcional
- ✅ Booking engine con conflict detection
- ✅ Interface admin para gestión de material

---

## 🚀 CHECKLIST DE PROGRESO SEMANAL

### Semana 1 - Foundation ✅
- [ ] Season management funcional
- [ ] School-season context working
- [ ] Auth V5 con roles por temporada
- [ ] Tests passing al 100%

### Semana 2 - Core Business ✅
- [ ] Courses V5 con pricing dinámico
- [ ] Bookings V5 con snapshots inmutables
- [ ] Migración de datos legacy
- [ ] Performance tests passed

### Semana 3 - New Business ✅
- [ ] Rental module completamente funcional
- [ ] Availability engine working
- [ ] Admin interfaces terminadas
- [ ] Integration tests passed

---

## 🛠️ COMANDOS DE DESARROLLO

### Testing Continuo
```bash
# Cada día al finalizar
vendor/bin/phpunit tests/V5/
npm run test:v5

# Cada viernes
vendor/bin/phpunit --coverage-html coverage
```

### Code Quality
```bash
# Antes de cada commit
vendor/bin/pint
php artisan l5-swagger:generate
```

### Deployment Preparation
```bash
# Final de cada semana
php artisan migrate:status
php artisan config:cache
php artisan route:cache
```

---

## 🎯 CRITERIOS DE ÉXITO

### Técnicos
- ✅ Test coverage > 90% en módulos V5
- ✅ Response time < 200ms en APIs críticas
- ✅ Cero breaking changes en APIs legacy
- ✅ Documentación Swagger completa

### Funcionales  
- ✅ Season switching sin pérdida de contexto
- ✅ Pricing calculations precisos al céntimo
- ✅ Rental conflicts detection 100% fiable
- ✅ Data migration sin pérdida de información

### UX/UI
- ✅ Interfaces responsivas y accesibles
- ✅ Loading states en todas las operaciones async
- ✅ Error handling user-friendly
- ✅ Performance percibida < 2s en operaciones

---

*Este plan se actualizará diariamente con el progreso real y ajustes necesarios.*