# USUARIOS & ROLES - Especificación Técnica v1

## 🎯 Objetivo
Implementar pantalla "Usuarios & Roles" siguiendo **exactamente** las convenciones del proyecto Boukii V5: componentes standalone, signals, theming CSS tokens, i18n, y patrones E2E estabilizados.

## 📐 Alcance Técnico

### Rutas (integradas con AppShell)
```typescript
// Bajo authGuard + schoolSelectedGuard
/admin/users           // Lista con filtros y paginación
/admin/users/:id       // Detalle + asignación de roles  
/admin/roles           // Lista de roles (opcional CRUD en v1)
```

### DTOs TypeScript
```typescript
interface UserListItem {
  id: number;
  name: string;
  email: string;
  status: 'active' | 'inactive' | 'pending';
  roles: string[];
  createdAt: string;
}

interface UserDetail {
  id: number;
  name: string;
  email: string;
  phone?: string;
  status: 'active' | 'inactive' | 'pending';
  roles: string[];
  permissions?: string[];
}

interface Role {
  id: number;
  name: string;
  description: string;
  permissions: string[];
}

interface UsersListResponse {
  data: UserListItem[];
  meta: {
    total: number;
    page: number;
    perPage: number;
    lastPage: number;
  };
}
```

## 🏗️ Arquitectura de Archivos

### Servicios (siguiendo patrón ApiHttpService)
```
front/src/app/features/admin/
├── users/
│   ├── users-list.page.ts           // Standalone, OnPush, Signals
│   ├── users-list.page.html         // PageHeader + DataTable
│   ├── users-list.page.scss         // Tokens CSS
│   ├── user-detail.page.ts          // Standalone, OnPush, Signals  
│   ├── user-detail.page.html        // PageHeader + Card + RolesList
│   ├── user-detail.page.scss        // Tokens CSS
│   ├── services/
│   │   ├── users.service.ts         // Extiende ApiHttpService
│   │   └── roles.service.ts         // Extiende ApiHttpService
│   └── users.routes.ts              // Lazy routes con guards
└── roles/
    ├── roles-list.page.ts           // Standalone (opcional CRUD v1)
    ├── roles-list.page.html
    └── roles-list.page.scss
```

## 🎨 UI Components & Styling

### Componentes Base a Reutilizar
- `PageHeaderComponent` (breadcrumbs, título, actions)
- `ButtonComponent` (primary, secondary, variants)  
- `TableComponent` / DataTable pattern
- `FilterComponent` / SearchBox pattern
- `LoadingStateComponent`
- `ErrorStateComponent`

### Design Tokens (seguir sistema existente)
```scss
// Usar tokens CSS definidos en styles/tokens.css
.users-page {
  --page-padding: var(--space-lg);
  --table-header-bg: var(--color-surface-secondary);
  --status-active-color: var(--color-success);
  --status-inactive-color: var(--color-warning);
}
```

### Data-testid Pattern (CI estable)
```html
<!-- Lista -->
<div data-testid="users-page">
  <div data-testid="users-filters">
    <input data-testid="filter-text" type="text" />
    <select data-testid="filter-role">
    <select data-testid="filter-status">
  </div>
  <table data-testid="users-table">
    <tr data-testid="user-row-{{user.id}}">
</div>

<!-- Detalle -->
<div data-testid="user-detail-page">
  <div data-testid="user-info-card">
  <div data-testid="user-roles-section">
    <label data-testid="role-checkbox-{{role.id}}">
  <button data-testid="save-roles-btn">
</div>
```

## 🔌 Servicios (patrón ApiHttpService)

### UsersService
```typescript
@Injectable({ providedIn: 'root' })
export class UsersService {
  constructor(private apiHttp: ApiHttpService) {}

  getUsers(filters?: UsersFilters): Observable<UsersListResponse> {
    const params = this.buildParams(filters);
    return this.apiHttp.get<UsersListResponse>('/users', { params });
  }

  getUserById(id: number): Observable<UserDetail> {
    return this.apiHttp.get<UserDetail>(`/users/${id}`);
  }

  updateUserRoles(userId: number, roleIds: number[]): Observable<void> {
    return this.apiHttp.put<void>(`/users/${userId}/roles`, { roleIds });
  }
}
```

### RolesService  
```typescript
@Injectable({ providedIn: 'root' })
export class RolesService {
  constructor(private apiHttp: ApiHttpService) {}

  getRoles(): Observable<Role[]> {
    return this.apiHttp.get<Role[]>('/roles');
  }
}
```

## 🧭 Routing Integration

### users.routes.ts
```typescript
import { Routes } from '@angular/router';
import { authGuard } from '../../../core/guards/auth.guard';
import { schoolSelectedGuard } from '../../../core/guards/school-selected.guard';

export const USERS_ROUTES: Routes = [
  {
    path: '',
    canActivate: [authGuard, schoolSelectedGuard],
    children: [
      {
        path: '',
        loadComponent: () => import('./users-list.page').then(m => m.UsersListPageComponent)
      },
      {
        path: ':id',
        loadComponent: () => import('./user-detail.page').then(m => m.UserDetailPageComponent)
      }
    ]
  }
];
```

### Integración con app.routes.ts
```typescript
// En AppShell children, añadir:
{
  path: 'admin/users',
  loadChildren: () => import('./features/admin/users/users.routes').then(m => m.USERS_ROUTES)
},
{
  path: 'admin/roles', 
  loadComponent: () => import('./features/admin/roles/roles-list.page').then(m => m.RolesListPageComponent),
  canActivate: [authGuard, schoolSelectedGuard]
}
```

## 🌐 Internacionalización

### assets/i18n/es.json (añadir)
```json
{
  "users": {
    "title": "Gestión de Usuarios",
    "list": {
      "filters": {
        "searchPlaceholder": "Buscar usuarios...",
        "allRoles": "Todos los roles",
        "allStatuses": "Todos los estados"
      },
      "table": {
        "name": "Nombre",
        "email": "Email", 
        "roles": "Roles",
        "status": "Estado",
        "createdAt": "Creado",
        "actions": "Acciones"
      }
    },
    "detail": {
      "title": "Detalle de Usuario",
      "rolesSection": "Asignación de Roles",
      "saveRoles": "Guardar Roles",
      "userInfo": "Información del Usuario"
    }
  },
  "roles": {
    "title": "Gestión de Roles"
  }
}
```

## 🧪 Testing E2E (patrón estabilizado)

### cypress/fixtures/admin/users/list.json
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin Principal", 
      "email": "admin@boukii.dev",
      "status": "active",
      "roles": ["admin"],
      "createdAt": "2024-01-15T10:30:00Z"
    },
    {
      "id": 2,
      "name": "Manager Operaciones",
      "email": "manager@boukii.dev", 
      "status": "active",
      "roles": ["manager"],
      "createdAt": "2024-02-01T14:20:00Z"
    },
    {
      "id": 3,
      "name": "Staff Recepción",
      "email": "staff@boukii.dev",
      "status": "inactive", 
      "roles": ["staff"],
      "createdAt": "2024-01-20T09:15:00Z"
    }
  ],
  "meta": {
    "total": 3,
    "page": 1,
    "perPage": 20,
    "lastPage": 1
  }
}
```

### cypress/fixtures/admin/users/detail.json
```json
{
  "id": 2,
  "name": "Manager Operaciones",
  "email": "manager@boukii.dev",
  "phone": "+34 666 555 444",
  "status": "active",
  "roles": ["manager"],
  "permissions": ["bookings:*", "clients:*", "reports:view"]
}
```

### cypress/fixtures/admin/roles/list.json
```json
{
  "data": [
    {
      "id": 1,
      "name": "admin",
      "description": "Acceso completo al sistema",
      "permissions": ["*"]
    },
    {
      "id": 2, 
      "name": "manager",
      "description": "Gestión de operaciones",
      "permissions": ["bookings:*", "clients:*", "reports:view", "users:view"]
    },
    {
      "id": 3,
      "name": "staff", 
      "description": "Personal operativo",
      "permissions": ["bookings:view", "bookings:create", "clients:view"]
    }
  ]
}
```

## 🎯 E2E Tests (cypress/e2e/admin-users.cy.ts)

```typescript
import { visitAndWait } from '../support/utils/bootstrap';

describe('Admin: Users & Roles Management', () => {
  beforeEach(() => {
    // Intercepts con patrón estabilizado (delay: 0)
    cy.intercept('GET', '/api/v5/users*', { 
      fixture: 'admin/users/list.json',
      delay: 0 
    }).as('usersList');
    
    cy.intercept('GET', /\/api\/v5\/users\/\d+$/, { 
      fixture: 'admin/users/detail.json',
      delay: 0
    }).as('userDetail');
    
    cy.intercept('GET', '/api/v5/roles', { 
      fixture: 'admin/roles/list.json',
      delay: 0
    }).as('rolesList');
    
    cy.intercept('PUT', /\/api\/v5\/users\/\d+\/roles/, { 
      statusCode: 200, 
      body: { success: true },
      delay: 0
    }).as('saveUserRoles');

    // Autenticación simulada
    cy.login();
  });

  describe('Users List Page', () => {
    it('should display users list with filters', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Verificar estructura de página
      cy.get('[data-testid="users-page"]').should('be.visible');
      cy.get('[data-testid="page-header"]').should('contain.text', 'Gestión de Usuarios');
      cy.get('[data-testid="users-table"]').should('be.visible');
      
      // Verificar datos cargados
      cy.get('[data-testid="user-row-1"]').should('contain', 'Admin Principal');
      cy.get('[data-testid="user-row-2"]').should('contain', 'Manager Operaciones');
      cy.get('[data-testid="user-row-3"]').should('contain', 'Staff Recepción');
    });

    it('should filter users by text search', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Filtro por texto
      cy.get('[data-testid="filter-text"]').type('Manager');
      cy.get('[data-testid="users-table"]').should('contain', 'Manager Operaciones');
      cy.get('[data-testid="users-table"]').should('not.contain', 'Admin Principal');
    });

    it('should filter users by role', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Filtro por rol
      cy.get('[data-testid="filter-role"]').select('staff');
      cy.get('[data-testid="users-table"]').should('contain', 'Staff Recepción');
      cy.get('[data-testid="users-table"]').should('not.contain', 'Manager Operaciones');
    });

    it('should navigate to user detail', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      cy.get('[data-testid="user-row-2"]').click();
      cy.url().should('include', '/admin/users/2');
    });
  });

  describe('User Detail Page', () => {
    it('should display user information and roles', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Verificar información del usuario
      cy.get('[data-testid="user-detail-page"]').should('be.visible');
      cy.get('[data-testid="user-info-card"]').should('contain', 'Manager Operaciones');
      cy.get('[data-testid="user-info-card"]').should('contain', 'manager@boukii.dev');
      
      // Verificar sección de roles
      cy.get('[data-testid="user-roles-section"]').should('be.visible');
      cy.get('[data-testid="role-checkbox-2"]').should('be.checked'); // manager role
      cy.get('[data-testid="role-checkbox-1"]').should('not.be.checked'); // admin role
    });

    it('should update user roles', () => {
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      // Cambiar roles
      cy.get('[data-testid="role-checkbox-1"]').check(); // Añadir admin
      cy.get('[data-testid="role-checkbox-3"]').check(); // Añadir staff
      
      // Guardar cambios
      cy.get('[data-testid="save-roles-btn"]').should('not.be.disabled');
      cy.get('[data-testid="save-roles-btn"]').click();
      cy.wait('@saveUserRoles');
      
      // Verificar éxito
      cy.get('[data-testid="success-message"]').should('be.visible');
    });

    it('should handle role assignment errors', () => {
      // Mock error response
      cy.intercept('PUT', /\/api\/v5\/users\/\d+\/roles/, { 
        statusCode: 422,
        body: { 
          error: 'Validation failed',
          message: 'Cannot assign admin role' 
        }
      }).as('saveUserRolesError');
      
      cy.visitAndWait('/admin/users/2');
      cy.wait(['@userDetail', '@rolesList']);
      
      cy.get('[data-testid="role-checkbox-1"]').check();
      cy.get('[data-testid="save-roles-btn"]').click();
      cy.wait('@saveUserRolesError');
      
      // Verificar manejo de error
      cy.get('[data-testid="error-message"]').should('be.visible');
      cy.get('[data-testid="error-message"]').should('contain', 'Cannot assign admin role');
    });
  });

  describe('Accessibility & UX', () => {
    it('should be keyboard navigable', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Navegación por teclado
      cy.get('[data-testid="filter-text"]').focus();
      cy.get('body').tab();
      cy.focused().should('have.attr', 'data-testid', 'filter-role');
    });

    it('should maintain theme consistency', () => {
      cy.visitAndWait('/admin/users');
      cy.wait('@usersList');
      
      // Verificar aplicación del tema
      cy.get('[data-testid="users-page"]').should('have.class', 'theme-applied');
      cy.get('html').should('have.attr', 'data-theme');
    });
  });
});
```

## 📋 Definition of Done

### ✅ Funcionalidad
- [ ] Navegación `/admin/users` y `/admin/users/:id` funcional
- [ ] Filtros por texto, rol y estado operativos  
- [ ] Paginación implementada
- [ ] Asignación/desasignación de roles funcional
- [ ] Manejo de estados de carga y error

### ✅ Calidad Técnica
- [ ] Componentes standalone con `OnPush`
- [ ] Uso de signals para estado reactivo
- [ ] Servicios extienden `ApiHttpService` 
- [ ] Guards `authGuard` + `schoolSelectedGuard` aplicados
- [ ] Integración correcta con `AppShellComponent`

### ✅ UI/UX & Design System
- [ ] `PageHeaderComponent` implementado
- [ ] Tokens CSS utilizados (no colores hardcoded)
- [ ] Responsive design funcionando
- [ ] Temas light/dark aplicados correctamente
- [ ] Accesibilidad: navegación por teclado, ARIA labels

### ✅ Internacionalización
- [ ] Textos usando `TranslatePipe`
- [ ] Keys i18n añadidas a es.json, en.json, etc.
- [ ] Fallbacks a español funcionando

### ✅ Testing & CI
- [ ] E2E tests pasando en local y CI
- [ ] Intercepts con `delay: 0` para estabilidad
- [ ] Uso de `cy.visitAndWait()` 
- [ ] Data-testids consistentes y robustos
- [ ] Fixtures realistas y completas

### ✅ Integración
- [ ] Routes añadidas a `app.routes.ts`
- [ ] Servicios registrados correctamente
- [ ] No rompe tests E2E existentes (72/72 siguen pasando)

## 🚀 Commit Message
```
feat(admin): implement Users & Roles management v1

- Add users list with filters (text, role, status) and pagination
- Add user detail with role assignment interface
- Create UsersService and RolesService extending ApiHttpService
- Implement standalone components with OnPush and signals
- Add comprehensive E2E tests with stable CI patterns
- Include i18n support and responsive design
- Follow Boukii V5 conventions: theming, routing, guards

test(e2e): add admin-users.cy.ts with 72/72 stability maintained
feat(fixtures): add realistic user and role data for testing

Closes #XXX
```

---

## 🎯 **Mejoras Clave Implementadas**

1. **Arquitectura**: Standalone components, OnPush, signals
2. **Routing**: Guards integrados, lazy loading consistente  
3. **UI/UX**: PageHeader, tokens CSS, responsive, a11y
4. **Testing**: Patrón `cy.visitAndWait()`, intercepts estables
5. **i18n**: TranslatePipe, keys estructuradas
6. **Servicios**: Extensión de ApiHttpService, manejo de errores
7. **Integration**: Seamless con AppShell, no breaking changes

¿Procedo con la implementación siguiendo esta especificación mejorada?