# 📋 DOCUMENTACIÓN COMPLETA DEL FRONTEND ANGULAR ADMIN - BOUKII

**Sistema de Gestión de Escuelas de Esquí y Deportes**
*Análisis exhaustivo del código fuente en `old-admin-front`*

---

## 🌐 **1. RUTAS COMPLETAS DEL SISTEMA**

### **🔓 Rutas Públicas (Sin Autenticación)**
- `/login` - Página de inicio de sesión
- `/forgot-password` - Recuperación de contraseña  
- `/recover-password/:token` - Restablecimiento de contraseña con token

### **🔒 Rutas Privadas Principales (Con AuthGuard)**

#### **📊 Dashboard y Analytics**
- `/home` - Dashboard principal con widgets de estadísticas
- `/stats` - Sistema de analytics y reportes avanzados (sin subrutas)

#### **📋 Gestión de Reservas (3 Versiones)**
- `/bookings-old` - **Reservas V1 (Legacy)**
  - `/bookings-old` - Lista principal
  - `/bookings-old/create` - Crear nueva reserva
  - `/bookings-old/edit/:id` - Editar reserva
  - `/bookings-old/update/:id` - Ver/actualizar detalles

- `/bookings` - **Reservas V2 (Actual)**
  - `/bookings` - Lista principal con filtros avanzados
  - `/bookings/create` - Crear nueva reserva
  - `/bookings/edit/:id` - Editar reserva existente
  - `/bookings/update/:id` - Ver detalles y actualizar

- `/bookings-v3` - **Reservas V3 (Nueva Generación)**
  - `/bookings-v3/reservas` - Lista principal (redirección por defecto)
  - `/bookings-v3/reservas/nueva` - Wizard para nueva reserva
  - `/bookings-v3/reservas/:id/editar` - Wizard para editar reserva
  - `/bookings-v3/clientes/:id` - Perfil completo del cliente

- `/bookings-dashboard` - Dashboard específico de reservas (sin subrutas)

#### **🎓 Gestión de Cursos (2 Versiones)**
- `/courses-old` - **Cursos V1 (Legacy)**
  - `/courses-old` - Lista principal
  - `/courses-old/create` - Crear curso
  - `/courses-old/update/:id` - Editar curso

- `/courses` - **Cursos V2 (Actual)**
  - `/courses` - Lista principal con filtros
  - `/courses/create` - Crear nuevo curso
  - `/courses/update/:id` - Editar curso existente
  - `/courses/detail/:id` - Ver detalles completos del curso

#### **👥 Gestión de Personas**
- `/monitors` - **Instructores/Monitores**
  - `/monitors` - Lista principal
  - `/monitors/create` - Crear nuevo monitor
  - `/monitors/update/:id` - Ver/editar detalles del monitor
  - `/monitors/:id/calendar` - Calendario específico del monitor

- `/clients` - **Clientes**
  - `/clients` - Lista principal
  - `/clients/create` - Crear nuevo cliente
  - `/clients/update/:id` - Ver/editar detalles del cliente

- `/admins` - **Administradores**
  - `/admins` - Lista principal
  - `/admins/create` - Crear nuevo administrador
  - `/admins/update/:id` - Editar administrador

#### **🎫 Gestión de Bonos y Descuentos**
- `/vouchers` - **Bonos/Vouchers**
  - `/vouchers` - Lista principal
  - `/vouchers/create` - Crear nuevo bono
  - `/vouchers/update/:id` - Editar bono existente

- `/discount-codes` - **Códigos de Descuento**
  - `/discount-codes` - Lista principal
  - `/discount-codes/create` - Crear código de descuento
  - `/discount-codes/update/:id` - Editar código de descuento

#### **📧 Sistema de Comunicación**
- `/mail` - **Sistema de Correo Avanzado**
  - `/mail` - Redirección a `/mail/general`
  - `/mail/:filterId` - Lista de correos por filtro
  - `/mail/:filterId/:mailId` - Ver correo específico

- `/messages` - Sistema de comunicación/chat (alias de `/communications`)
- `/communications` - Sistema de comunicación (sin subrutas)

#### **📅 Planificación**
- `/timeline` - Planificador temporal/timeline (sin subrutas)
- `/calendar` - Vista de calendario para monitores (sin subrutas)

#### **⚙️ Configuración**
- `/settings` - Configuraciones del sistema (ver sección de tabs detallada)

---

## 🔧 **2. SISTEMA AIO-TABLE - FILTROS Y BOTONES DINÁMICOS**

El componente `AioTableComponent` es el núcleo de todas las tablas del sistema, con filtros y acciones específicas según el contexto:

### **🎛️ Filtros Dinámicos por Módulo**

#### **📋 Filtros de Reservas (`entity.includes('bookings')`)**
**Botones de Estado:**
- `Activas` - Reservas activas
- `Finalizadas` - Reservas completadas
- `Canceladas` - Reservas canceladas
- `Todas` - Ver todas las reservas

**Filtros de Tipo:**
- ☑️ `Reserva Individual` - Una sola persona
- ☑️ `Reserva Múltiple` - Grupo/familia

**Filtros de Pago:**
- ☑️ `Pagado` - Reservas pagadas
- ☑️ `No Pagado` - Reservas pendientes de pago

**Filtros de Curso:**
- ☑️ `Curso Colectivo` (🟡 amarillo)
- ☑️ `Curso Privado` (🟢 verde)  
- ☑️ `Actividad` (🔵 azul)

#### **🎓 Filtros de Cursos (`entity.includes('courses')`)**
**Botones de Estado:**
- `Activos` - Cursos en funcionamiento
- `Finalizados` - Cursos completados
- `Corrientes` - Cursos del período actual
- `Todos` - Ver todos los cursos

**Filtros por Tipo:**
- ☑️ `Curso Colectivo` (🟡 amarillo)
- ☑️ `Curso Privado` (🟢 verde)
- ☑️ `Actividad` (🔵 azul)

**Filtros por Deporte:**
- 🏂 Selector múltiple de deportes disponibles

#### **👥 Filtros de Monitores (`entity.includes('monitor')`)**
**Botones de Estado:**
- `Activos` - Monitores disponibles
- `Inactivos` - Monitores no disponibles
- `Todos` - Ver todos los monitores

**Filtros por Deporte:**
- 🏂 Selector múltiple de deportes que enseñan

#### **🎫 Filtros de Vouchers (`entity.includes('vouchers')`)**
**Botones de Navegación:**
- `Vouchers` - Bonos normales
- `Gift Vouchers` - Bonos regalo

**Filtros Especiales:**
- ☑️ `Eliminados` - Ver vouchers borrados

#### **💰 Filtros de Códigos de Descuento (`entity.includes('discount')`)**
**Botones de Navegación:**
- `Vouchers` - Ir a sección de vouchers
- `Gift Vouchers` - Ir a vouchers regalo
- `Códigos de Descuento` - Actual

### **🔍 Elementos de Búsqueda y Acciones**

#### **Barra de Búsqueda Global:**
- 🔍 Campo de búsqueda en tiempo real con debounce
- Búsqueda por texto libre en múltiples campos

#### **Botones de Acción:**
- 🗂️ **Filtro de Datos** - Abrir/cerrar panel de filtros avanzados
- 📋 **Filtro de Columnas** - Mostrar/ocultar columnas específicas
- 📥 **Exportar** - Descargar datos a Excel (solo en estadísticas)
- ➕ **Agregar** - Crear nuevo elemento (botón FAB principal)

#### **Acciones de Selección Múltiple:**
- 🗑️ **Eliminar Seleccionados** - Borrado masivo
- 📁 **Otras Acciones** - Acciones adicionales por lotes

---

## 📑 **3. PANTALLA DE SETTINGS - SISTEMA DE TABS COMPLETO**

La pantalla de configuración tiene un sistema de tabs anidados muy complejo:

### **Tab Principal 1: 📅 TEMPORADA (`season`)**
- Configuración de fechas de temporada
- Horarios de inicio y fin
- Configuración general de la temporada activa

### **Tab Principal 2: 🏂 DEPORTES (`sports`)**
#### **Sub-tab 2.1: Deportes**
- Lista de deportes disponibles
- Configuración por deporte

#### **Sub-tab 2.2: Niveles (`levels`)**
- Tabla de niveles por deporte
- Objetivos y metas por nivel
- Gestión de progresión

### **Tab Principal 3: 💰 PRECIOS (`courses.title`)**
- Tabla de precios por tipo de curso
- Configuración de tarifas
- Precios en moneda local (CHF)

### **Tab Principal 4: 📄 PÁGINA DE RESERVA (`settings.book_page`)**
#### **Sub-tab 4.1: Configuración (`courses.config`)**
- Configuración de la página de reserva pública
- Textos y elementos visuales

#### **Sub-tab 4.2: Legal (`legal`)**
**Sub-sub-tabs por Idioma:**
- 🇫🇷 Francés
- 🇬🇧 Inglés  
- 🇪🇸 Español
- 🇩🇪 Alemán
- 🇮🇹 Italiano

### **Tab Principal 5: 👨‍🏫 MONITORES (`settings.monitors`)**
#### **Sub-tab 5.1: Salarios (`salarys`)**
- Tabla de niveles salariales
- Modal: `SalaryCreateUpdateModal`

#### **Sub-tab 5.2: Bloques Pagados (`paid_blocks`)**  
- Configuración de días no trabajables pagados
- Gestión de ausencias remuneradas

### **Tab Principal 6: 📋 RESERVAS (`bookings`)**
- Configuración específica de reservas
- Parámetros del sistema de reservas

### **Tab Principal 7: ➕ EXTRAS (`extras`)**
#### **Sub-tab 7.1: Alquiler (`rent`)** *(Oculto)*
- Gestión de material de alquiler

#### **Sub-tab 7.2: Otros (`others`)**
**Secciones:**
- **Forfait** - Tabla de pases de temporada
- **Transporte** - Tabla de servicios de transporte  
- **Comida** - Tabla de servicios de alimentación

### **Tab Principal 8: 📧 MAILS**
**Configuración por Tipo de Email:**
- Confirmación de reserva
- Cancelación de reserva
- Actualización de reserva
- Enlace de pago
- Confirmación de pago
- Recordatorio de pago
- Confirmación de voucher
- Creación de voucher
- Recordatorio de curso

**Sub-tabs por Idioma para cada tipo:**
- 🇫🇷 Francés
- 🇬🇧 Inglés
- 🇪🇸 Español
- 🇩🇪 Alemán
- 🇮🇹 Italiano

---

## 🔧 **4. MODALES Y DIÁLOGOS COMPLETOS**

### **📋 Modales de Reservas**

#### **BookingDetailModal** (Múltiples versiones V1/V2/V3)
- **Ubicación:** `pages/bookings*/components/modals/`
- **Función:** Vista completa de reserva con:
  - Información del cliente con avatar circular
  - Detalles de la reserva y estado
  - Programación y horarios
  - Información de pago
  - Acciones (editar, cancelar)
- **Tipo:** MatDialog - Modal de Angular Material

#### **CancelBookingDialog**
- **Ubicación:** `bookings-v3/components/modals/`
- **Función:** Confirmación de cancelación con:
  - Motivo de cancelación
  - Política de reembolso
  - Confirmación final
- **Tipo:** MatDialog

#### **BookingDialog** 
- **Ubicación:** `bookings-v2/booking-detail/components/`
- **Función:** Gestión general de reservas
- **Tipo:** MatDialog

#### **CreateUserDialog**
- **Ubicación:** `bookings-v2/bookings-create-update/components/`
- **Función:** Crear cliente durante proceso de reserva
- **Tipo:** MatDialog

### **🎓 Modales de Cursos**

#### **CourseDetailModal** (V1 y V2)
- **Ubicación:** `pages/courses*/course-detail-modal/`
- **Función:** Vista completa del curso con:
  - Información básica del curso
  - Horarios y fechas
  - Lista de participantes
  - Estadísticas del curso
- **Tipo:** MatDialog

#### **CoursesCreateUpdateModal**
- **Ubicación:** `pages/courses/courses-create-update-modal/`
- **Función:** Formulario completo para crear/editar cursos
- **Tipo:** MatDialog

### **👥 Modales de Clientes**

#### **ClientCreateUpdateModal**
- **Ubicación:** `pages/clients/client-create-update-modal/`
- **Función:** Formulario completo de cliente con:
  - Datos personales
  - Info de contacto
  - Nivel por deporte
  - Historial
- **Tipo:** MatDialog

### **⚙️ Modales de Configuración**

#### **SalaryCreateUpdateModal**
- **Ubicación:** `pages/settings/salary-create-update-modal/`
- **Función:** Gestión de niveles salariales para monitores
- **Tipo:** MatDialog

#### **ExtraCreateUpdateModal**
- **Ubicación:** `pages/settings/extra-create-update-modal/`
- **Función:** Crear/editar servicios extras:
  - Transporte
  - Comida  
  - Forfait
  - Otros servicios
- **Tipo:** MatDialog

#### **LevelGoalsModal**
- **Ubicación:** `pages/settings/level-goals-modal/`
- **Función:** Configurar objetivos de aprendizaje por nivel
- **Tipo:** MatDialog

#### **LevelSportUpdateModal**
- **Ubicación:** `pages/settings/level-sport-update-modal/`
- **Función:** Actualizar niveles específicos por deporte
- **Tipo:** MatDialog

### **📊 Modales de Analytics**

#### **CourseStatisticsModal**
- **Ubicación:** `pages/analytics-v2/course-statistics-modal/`
- **Función:** Estadísticas detalladas de cursos con:
  - Gráficos de rendimiento
  - Métricas de ocupación
  - Análisis de ingresos
- **Tipo:** MatDialog

#### **BookingListModal**
- **Ubicación:** `pages/analytics-v2/booking-list-modal/`
- **Función:** Lista filtrada de reservas en modal
- **Tipo:** MatDialog

### **🔧 Modales de Sistema**

#### **PreviewModal**
- **Ubicación:** `components/preview-modal/`
- **Función:** Vista previa de:
  - Templates de email
  - Documentos PDF
  - Imágenes
- **Tipo:** MatDialog

#### **ConfirmDialog**
- **Ubicación:** `pages/monitors/monitor-detail/confirm-dialog/`
- **Función:** Confirmaciones generales del sistema
- **Tipo:** MatDialog

#### **SearchModal**
- **Ubicación:** `@vex/components/search-modal/`
- **Función:** Búsqueda global del sistema
- **Tipo:** MatDialog

---

## 🎯 **5. PÁGINAS PRINCIPALES POR MÓDULO FUNCIONAL**

### **📊 Dashboard y Analytics**

#### **DashboardAnalyticsComponent** (`/home`)
- **Widgets principales:**
  - Tarjetas de estadísticas (reservas, ingresos, ocupación)
  - Gráficos de tendencias con ApexCharts
  - KPIs de rendimiento
  - Alertas y notificaciones
- **Interacciones:**
  - Filtros por fecha
  - Drill-down en métricas
  - Navegación rápida a secciones

#### **AnalyticsComponent** (`/stats`)
- **Secciones de reportes:**
  - Analytics de reservas por período
  - Rendimiento de monitores
  - Ocupación de cursos
  - Análisis financiero
- **Características:**
  - Filtros avanzados multi-criterio
  - Exportación a Excel/PDF
  - Gráficos interactivos
  - Comparativas período vs período

### **📋 Gestión de Reservas**

#### **BookingsV2Component** (`/bookings`)
- **Tabla principal con columnas:**
  - ID de reserva
  - Tipo de deporte (con imagen)
  - Información del curso
  - Cliente principal
  - Observaciones
  - Fechas
  - Fecha de registro
  - Seguro de cancelación
  - Boukii Care
  - Precio total
  - Método de pago
  - Bonos aplicados
  - Estado de pago
  - Estado de cancelación
  - Acciones
- **Filtros AIO-Table específicos:**
  - Estado: Activas/Finalizadas/Canceladas/Todas
  - Tipo: Individual/Múltiple
  - Pago: Pagado/No Pagado
  - Curso: Colectivo/Privado/Actividad
- **Acciones por fila:**
  - Ver detalles (modal)
  - Editar reserva
  - Cancelar reserva
  - Generar PDF/QR
  - Enviar email de confirmación

#### **BookingsListComponent** (`/bookings-v3/reservas`)
- **Vista moderna con KPIs:**
  - Métricas en tiempo real
  - Vista de tarjetas y lista
  - Filtros inteligentes
  - Búsqueda avanzada

#### **BookingWizardComponent** (`/bookings-v3/reservas/nueva`)
- **Wizard multi-paso:**
  1. Selección de actividad
  2. Selección de fechas/horarios
  3. Datos de participantes
  4. Servicios extras
  5. Confirmación y pago

### **🎓 Gestión de Cursos**

#### **CoursesComponent** (`/courses`)
- **Tabla con información completa:**
  - Información básica del curso
  - Deporte y nivel
  - Fechas y horarios
  - Capacidad y ocupación actual
  - Monitor asignado
  - Estado (activo/inactivo/finalizado)
  - Precios
- **Filtros específicos:**
  - Estado: Activos/Finalizados/Corrientes/Todos
  - Tipo: Colectivo/Privado/Actividad
  - Deporte: Selector múltiple
- **Acciones:**
  - Ver estadísticas (modal)
  - Editar curso
  - Clonar curso
  - Ver participantes
  - Exportar lista

#### **CourseDetailComponent** (`/courses/detail/:id`)
- **Vista detallada con tabs:**
  - Información general
  - Participantes inscritos
  - Horario detallado
  - Estadísticas de asistencia
  - Monitor y sustitutos

### **👥 Gestión de Personas**

#### **MonitorsComponent** (`/monitors`)
- **Gestión completa de instructores:**
  - Información personal y profesional
  - Deportes autorizados y niveles
  - Disponibilidad por temporada
  - Evaluaciones y rendimiento
  - Historial de cursos impartidos
- **Filtros:**
  - Estado: Activos/Inactivos/Todos
  - Deporte: Selector múltiple
- **Acciones:**
  - Ver calendario personal
  - Editar información
  - Gestionar ausencias
  - Ver estadísticas de rendimiento

#### **MonitorDetailComponent** (`/monitors/update/:id`)
- **Vista detallada con secciones:**
  - Datos personales
  - Certificaciones y grados
  - Calendario de disponibilidad
  - Historial de evaluaciones
  - Gestión de días no disponibles

#### **ClientsComponent** (`/clients`)
- **Base de datos de clientes:**
  - Información personal completa
  - Historial de reservas
  - Niveles por deporte
  - Preferencias y observaciones
  - Datos de facturação

#### **ClientDetailComponent** (`/clients/update/:id`)
- **Perfil completo del cliente:**
  - Datos de contacto
  - Historial de reservas con estadísticas
  - Evolución de niveles
  - Vouchers y bonificaciones
  - Comunicaciones enviadas

### **⚙️ Configuración del Sistema**

#### **SettingsComponent** (`/settings`)
- **Sistema de tabs anidados complejo** (ver sección anterior)
- **Gestión de:**
  - Temporadas y períodos
  - Deportes y niveles
  - Precios y tarifas
  - Templates de email multiidioma
  - Configuración legal por país
  - Salarios de monitores
  - Servicios extras

### **📧 Sistema de Comunicación**

#### **MailComponent** (`/mail`)
- **Cliente de correo completo:**
  - Bandeja de entrada organizada
  - Filtros por tipo de correo
  - Vista previa y lectura completa
  - Sistema de templates
  - Envío masivo
- **Estructura de navegación:**
  - `/mail/general` - Bandeja general
  - `/mail/booking-confirmations` - Confirmaciones
  - `/mail/payment-notices` - Avisos de pago
  - `/mail/reminders` - Recordatorios

#### **CommunicationsComponent** (`/communications`)
- **Chat y mensajería interna:**
  - Chat entre administradores
  - Notificaciones push
  - Historial de conversaciones
  - Estados de lectura

---

## 🔄 **6. INTERACCIONES COMPLEJAS Y FUNCIONALIDADES AVANZADAS**

### **🎯 Formularios Multi-paso**

#### **Booking Wizard (V3)**
- **Paso 1 - Selección de Actividad:**
  - Catálogo visual de deportes
  - Filtros por tipo y nivel
  - Vista previa de horarios disponibles

- **Paso 2 - Programación:**  
  - Calendario interactivo con disponibilidad
  - Selección de múltiples fechas
  - Gestión de horarios y monitores

- **Paso 3 - Participantes:**
  - Formulario dinámico por participante
  - Selección de niveles por deporte
  - Gestión de menores y adultos

- **Paso 4 - Extras:**
  - Selección de servicios adicionales
  - Cálculo de precios en tiempo real
  - Aplicación de descuentos y vouchers

- **Paso 5 - Confirmación:**
  - Resumen completo de la reserva
  - Términos y condiciones
  - Procesamiento de pago

### **📊 Tablas Avanzadas con AIO-Table**

#### **Funcionalidades Comunes:**
- **Selección múltiple** con checkbox
- **Ordenamiento** por cualquier columna
- **Paginación** configurable (10/25/50 elementos)
- **Filtros por columna** dinámicos
- **Búsqueda global** con debounce
- **Exportación** a Excel/PDF
- **Acciones masivas** (eliminar, modificar estado)

#### **Tipos de Columna Especializados:**
- `booking_users_image` - Avatar del deporte con imagen
- `booking_users` - Información de participantes
- `client` - Datos del cliente con avatar
- `booking_dates` - Fechas formateadas  
- `price` - Precios con formato de moneda
- `payment_method` - Métodos de pago con iconos
- `payment_status` - Estados de pago con colores
- `cancelation_status` - Estados de cancelación

### **📈 Visualizaciones Dinámicas**

#### **Gráficos con ApexCharts:**
- **Dashboard principal:**
  - Gráficos de área para tendencias de reservas
  - Gráficos de dona para distribución por deporte
  - Barras para comparativas mensuales
  - Métricas en tiempo real

- **Analytics avanzados:**
  - Heat maps de ocupación por horario
  - Gráficos de línea para rendimiento de monitores
  - Comparativas año sobre año
  - Proyecciones y forecasting

### **📅 Calendarios Interactivos**

#### **Monitor Calendar Component:**
- **Vista mensual** con eventos arrastrables
- **Gestión de disponibilidad** por día/hora
- **Asignación de cursos** drag & drop
- **Vista de conflictos** automática
- **Exportación** a calendarios externos

#### **Timeline/Planner:**
- **Vista temporal** de todos los cursos
- **Gestión de recursos** (monitores/instalaciones)
- **Detección de solapamientos**
- **Optimización automática** de horarios

### **🔍 Búsqueda y Filtrado Avanzado**

#### **Sistema de Búsqueda Global:**
- **Búsqueda multi-entidad** (reservas, clientes, cursos)
- **Sugerencias** en tiempo real
- **Búsqueda por campos específicos**
- **Historial de búsquedas**
- **Filtros guardados** para consultas frecuentes

#### **Filtros Contextuales:**
- **Rangos de fecha** con presets (hoy, esta semana, este mes)
- **Filtros por estado** con contadores
- **Filtros por características** (nivel, deporte, edad)
- **Combinación de filtros** con lógica AND/OR

---

## 📁 **7. ARQUITECTURA DE ARCHIVOS Y ESTRUCTURA**

### **🎯 Archivos de Definición de Rutas**
```
📁 Rutas Principales:
├── src/app/app-routing.module.ts ← Rutas principales y redirecciones
├── src/app/pages/*/[module]-routing.module.ts ← Rutas por módulo

📁 Rutas Específicas:
├── bookings-v2/bookings-routing.module.ts ← /bookings con CRUD
├── bookings-v3/bookings-v3-routing.module.ts ← /bookings-v3 con wizard
├── courses-v2/courses-routing.module.ts ← /courses con detalles
├── monitors/monitors-routing.module.ts ← /monitors con calendario
├── clients/clients-routing.module.ts ← /clients con CRUD
├── mail/mail-routing.module.ts ← /mail con estructura anidada
└── settings/settings-routing.module.ts ← /settings (sin subrutas)
```

### **🔧 Sistema de Navegación**
```
📁 Configuración de Menú:
├── src/app/app.component.ts (líneas 185-333) ← Definición del menú
├── src/@vex/services/navigation.service.ts ← Servicio de navegación
├── src/@vex/layout/sidenav/sidenav.component.ts ← Sidebar
└── src/@vex/layout/navigation/navigation.component.ts ← Navegación horizontal
```

### **🎭 Modales y Diálogos**
```
📁 Estructura de Modales:
├── src/app/pages/*/[module]-modal/ ← Modales específicos por módulo
├── src/app/components/preview-modal/ ← Modal global de preview
├── src/@vex/components/search-modal/ ← Modal de búsqueda global
└── Angular Material MatDialog ← Sistema base de modales
```

### **📊 Componentes de Tabla**
```
📁 Sistema AIO-Table:
├── src/@vex/components/aio-table/aio-table.component.ts ← Componente principal
├── src/@vex/interfaces/table-column.interface.ts ← Definición de columnas
└── Uso en todos los módulos con configuraciones específicas
```

### **🎨 Servicios Core**
```
📁 Servicios Principales:
├── src/service/api.service.ts ← Cliente HTTP base
├── src/service/crud.service.ts ← Operaciones CRUD genéricas
├── src/service/auth.service.ts ← Autenticación y autorización
├── src/service/school.service.ts ← Gestión de escuelas
├── src/service/analytics.service.ts ← Análisis y reportes
└── src/service/excel.service.ts ← Exportación de datos
```

---

## 🌟 **8. CARACTERÍSTICAS TÉCNICAS DESTACADAS**

### **🎨 Sistema de Temas y UI**
- **Vex Framework** - Theme system profesional
- **Angular Material** - Componentes UI consistentes  
- **TailwindCSS** - Utilidades de diseño responsive
- **Modo oscuro/claro** - Cambio dinámico de tema
- **Personalización por escuela** - Logo y colores dinámicos
- **Responsive design** - Adaptativo a todos los dispositivos

### **🌍 Internacionalización**
- **5 idiomas soportados:** Español, Inglés, Francés, Alemán, Italiano
- **Detección automática** del idioma del navegador
- **Templates de email** localizados por idioma
- **Interfaz completamente traducida**
- **Formatos de fecha/hora** por región

### **⚡ Rendimiento y UX**
- **Lazy loading** de módulos para carga rápida
- **Virtual scrolling** para listas grandes
- **Skeleton loaders** durante cargas
- **Debounce** en búsquedas para optimizar API calls
- **Caching** de datos frecuentemente consultados
- **Progressive Web App** capabilities

### **🔐 Seguridad**
- **JWT token-based** authentication
- **Role-based access control** (Admin/Monitor/Client)
- **Route guards** para protección de rutas
- **CSRF protection** integrada
- **Sanitización** de inputs para XSS prevention

### **📱 Funcionalidades Móviles**
- **Responsive design** completo
- **Touch gestures** en calendarios y tablas
- **Mobile-first** approach en nuevos componentes
- **PWA** con capacidades offline básicas

---

## 🎯 **9. FLUJOS DE TRABAJO PRINCIPALES**

### **📋 Flujo Completo de Reserva**
1. **Selección inicial** - Cliente/deporte/fechas
2. **Búsqueda de disponibilidad** - Cursos disponibles
3. **Configuración de participantes** - Datos y niveles
4. **Servicios adicionales** - Extras, seguros, transporte
5. **Aplicación de descuentos** - Vouchers y códigos promocionales
6. **Confirmación y pago** - Resumen y procesamiento
7. **Confirmación por email** - Template localizado
8. **Gestión post-reserva** - Modificaciones, cancelaciones

### **🎓 Gestión de Cursos**
1. **Creación de curso** - Información básica y programación
2. **Asignación de monitor** - Disponibilidad y capacidades
3. **Configuración de precios** - Tarifas por tipo y extras
4. **Publicación** - Activación para reservas
5. **Gestión de inscripciones** - Lista de espera, confirmaciones
6. **Seguimiento** - Asistencia, evaluaciones, feedback
7. **Cierre y análisis** - Estadísticas finales

### **👨‍🏫 Gestión de Monitores**
1. **Registro inicial** - Datos personales y profesionales
2. **Certificaciones** - Grados, autorizaciones, renovaciones
3. **Configuración de disponibilidad** - Calendario personal
4. **Asignación de cursos** - Matching automático/manual
5. **Seguimiento de rendimiento** - KPIs, evaluaciones
6. **Gestión salarial** - Cálculos, pagos, informes

---

## 📈 **10. MÉTRICAS Y ANALYTICS**

### **📊 KPIs del Dashboard**
- **Reservas activas** - Número total y tendencia
- **Ingresos del período** - Facturación y proyecciones  
- **Ocupación promedio** - Porcentaje de capacidad utilizada
- **Satisfacción del cliente** - NPS y ratings promedio
- **Rendimiento de monitores** - Cursos completados y evaluaciones

### **📈 Reportes Disponibles**
- **Análisis de reservas** - Por período, deporte, cliente
- **Rendimiento financiero** - Ingresos, costos, márgenes
- **Utilización de recursos** - Monitores, instalaciones, equipment
- **Análisis de clientes** - Segmentación, retención, LTV
- **Operacional** - Cancelaciones, no-shows, problemas

---

## 🔄 **11. INTEGRACIONES Y APIS**

### **💳 Sistema de Pagos**
- **Payrexx** - Gateway de pagos principal
- **Múltiples métodos** - Tarjeta, transferencia, efectivo
- **Monedas múltiples** - CHF, EUR, USD
- **Webhooks** - Confirmación automática de pagos

### **📧 Sistema de Email**
- **Templates personalizables** - Por tipo y idioma
- **Envío masivo** - Campañas y notificaciones automáticas
- **Tracking** - Apertura, clics, bounce rates
- **SMTP configurable** - Por escuela

### **📅 Calendarios Externos**
- **Export a ICS** - Integración con calendarios personales
- **Sincronización** - Con sistemas de gestión de instalaciones
- **API REST** - Para integraciones de terceros

---

## 📋 **RESUMEN EJECUTIVO**

Este frontend Angular es un **sistema de gestión integral** para escuelas de deportes de nieve con:

### **🎯 Funcionalidades Clave:**
- **3 versiones de gestión de reservas** (legacy, actual, next-gen)
- **Sistema de configuración multi-tab** extremadamente complejo
- **Componente AIO-Table** universal con filtros dinámicos por contexto
- **15+ modales especializados** para diferentes flujos de trabajo
- **Sistema de navegación de 40+ rutas** con subrutas anidadas
- **Dashboard analítico** con métricas en tiempo real
- **Gestión completa** de clientes, monitores, cursos y reservas

### **💪 Fortalezas Técnicas:**
- **Arquitectura modular** con lazy loading
- **Internacionalización completa** (5 idiomas)
- **Sistema de temas** personalizable por escuela
- **Componentes reutilizables** de alta calidad
- **Filtrado y búsqueda avanzada** en todas las secciones
- **Responsive design** con soporte móvil completo

### **🔍 Complejidad del Sistema:**
- **Módulos principales:** 12+ con rutas anidadas
- **Componentes totales:** 200+ archivos
- **Modales y diálogos:** 15+ especializados
- **Rutas totales:** 40+ rutas principales + subrutas
- **Configuración de settings:** 8 tabs principales con sub-tabs anidados
- **Filtros AIO-Table:** Sistema dinámico adaptativo por contexto

Este es un sistema **empresarial robusto** con funcionalidades avanzadas que maneja todo el ciclo de vida de una escuela de deportes de nieve, desde la reserva inicial hasta el análisis de rendimiento final.

---

*Documentación generada mediante análisis exhaustivo del código fuente*
*Fecha: Enero 2025 | Versión: Frontend Angular Admin v3*