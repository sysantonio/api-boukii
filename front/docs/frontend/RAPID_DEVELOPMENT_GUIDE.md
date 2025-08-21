# 🚀 Guía de Desarrollo Rápido - Boukii V5

> **Objetivo:** Crear pantallas nuevas en menos de 2 horas siguiendo patrones establecidos y manteniendo consistencia visual y funcional.

---

## 📋 Índice

1. [Arquitectura de Desarrollo Rápido](#arquitectura)
2. [Patrones de Componentes](#patrones)
3. [Workflow Estandarizado](#workflow)
4. [Templates y Generadores](#templates)
5. [Design System](#design-system)
6. [Testing Automatizado](#testing)
7. [Performance y Optimización](#performance)
8. [Troubleshooting](#troubleshooting)

---

## 🏗 Arquitectura de Desarrollo Rápido {#arquitectura}

### Estructura de Proyecto V5

```
src/app/
├─ core/                    # Servicios base (auth, api, context)
│  ├─ services/
│  ├─ guards/
│  └─ interceptors/
├─ shared/                  # Utilities compartidas
│  ├─ components/          # Componentes reutilizables
│  ├─ pipes/
│  └─ types/
├─ ui/                     # Design System
│  ├─ atoms/               # Button, Input, Icon
│  ├─ molecules/           # SearchBox, Pagination
│  ├─ organisms/           # DataTable, PageHeader
│  └─ templates/           # Layout templates
├─ features/               # Módulos de negocio
│  ├─ auth/
│  ├─ dashboard/
│  ├─ clients/            # Ejemplo: gestión de clientes
│  │  ├─ pages/
│  │  │  ├─ clients-list.page.ts
│  │  │  ├─ client-detail.page.ts
│  │  │  └─ client-edit.page.ts
│  │  ├─ components/      # Componentes específicos
│  │  ├─ services/
│  │  └─ types/
│  └─ bookings/           # Otro módulo
└─ layout/                # AppShell + routing
```

### Principios de Desarrollo

#### 1. **Convention over Configuration**
- **Naming**: `feature-action.type.ts` (e.g., `clients-list.page.ts`)
- **Structure**: Estructura de carpetas predecible
- **Imports**: Alias path mapping (`@core`, `@shared`, `@ui`)

#### 2. **Component Composition**
```typescript
// ✅ Composición de componentes existentes
@Component({
  template: `
    <bk-page-header [title]="'clients.title' | translate">
      <bk-button [action]="'add'" [route]="'/clients/create'">
        {{ 'clients.actions.create' | translate }}
      </bk-button>
    </bk-page-header>

    <bk-data-table 
      [data]="clients()" 
      [columns]="columns"
      [loading]="isLoading()"
      [pagination]="pagination()"
      (sort)="onSort($event)"
      (filter)="onFilter($event)">
    </bk-data-table>
  `
})
export class ClientsListPage {
  // Minimal logic - composición de componentes
}
```

#### 3. **Reactive State Management**
```typescript
// ✅ Signals + Computed para reactividad
export class ClientsListPage {
  // State signals
  private readonly _clients = signal<Client[]>([]);
  private readonly _isLoading = signal(false);
  private readonly _filters = signal<ClientFilters>({});

  // Computed values
  readonly clients = this._clients.asReadonly();
  readonly isLoading = this._isLoading.asReadonly();
  readonly filteredClients = computed(() => 
    this.filterClients(this._clients(), this._filters())
  );

  // Actions
  async loadClients() {
    this._isLoading.set(true);
    try {
      const clients = await this.clientsService.getAll();
      this._clients.set(clients);
    } finally {
      this._isLoading.set(false);
    }
  }
}
```

---

## 🧩 Patrones de Componentes {#patrones}

### 1. **Page Component Pattern**

```typescript
// Template base para páginas
@Component({
  selector: 'app-feature-action-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslatePipe,
    PageHeaderComponent,
    DataTableComponent,
    ButtonComponent
  ],
  template: `
    <bk-page-header [title]="title">
      <ng-content select="[slot=actions]"></ng-content>
    </bk-page-header>

    <main class="page-content">
      <ng-content></ng-content>
    </main>

    <bk-loading-overlay [visible]="isLoading()"/>
    <bk-error-boundary [error]="error()"/>
  `,
  styleUrls: ['./feature-action.page.scss']
})
export class FeatureActionPage {
  // Common page functionality
  protected readonly isLoading = signal(false);
  protected readonly error = signal<Error | null>(null);
  
  @Input() title: string = '';
}
```

### 2. **Service Pattern**

```typescript
// Template base para servicios
@Injectable({
  providedIn: 'root'
})
export class FeatureService {
  private readonly apiService = inject(ApiV5Service);
  private readonly contextService = inject(ContextService);
  private readonly loggingService = inject(LoggingService);

  // State management
  private readonly _items = signal<Feature[]>([]);
  private readonly _isLoading = signal(false);

  // Public API
  readonly items = this._items.asReadonly();
  readonly isLoading = this._isLoading.asReadonly();

  async getAll(filters?: FeatureFilters): Promise<Feature[]> {
    const requestId = this.startLoading('feature.getAll');
    
    try {
      const response = await this.apiService.get<Feature[]>('/features', { 
        params: { ...filters, ...this.contextService.getParams() }
      });
      
      this._items.set(response.data);
      return response.data;
    } catch (error) {
      this.handleError('Failed to load features', error);
      throw error;
    } finally {
      this.endLoading(requestId);
    }
  }

  async create(data: CreateFeatureRequest): Promise<Feature> {
    const requestId = this.startLoading('feature.create');
    
    try {
      const response = await this.apiService.post<Feature>('/features', data);
      
      // Optimistic update
      this._items.update(items => [...items, response.data]);
      
      this.loggingService.logUserAction('feature.created', { 
        featureId: response.data.id 
      });
      
      return response.data;
    } catch (error) {
      this.handleError('Failed to create feature', error);
      throw error;
    } finally {
      this.endLoading(requestId);
    }
  }

  private startLoading(operation: string): string {
    const requestId = `${operation}-${Date.now()}`;
    this._isLoading.set(true);
    return requestId;
  }

  private endLoading(requestId: string): void {
    this._isLoading.set(false);
  }

  private handleError(message: string, error: any): void {
    this.loggingService.logError(message, error, {
      context: this.contextService.getCurrentContext()
    });
  }
}
```

### 3. **Form Pattern**

```typescript
// Template para formularios
@Component({
  selector: 'bk-feature-form',
  template: `
    <form [formGroup]="form" (ngSubmit)="onSubmit()" novalidate>
      
      <div class="form-grid">
        <bk-input-field
          label="feature.fields.name"
          formControlName="name"
          [error]="getFieldError('name')"
          required>
        </bk-input-field>

        <bk-select-field
          label="feature.fields.category"
          formControlName="categoryId"
          [options]="categories()"
          [error]="getFieldError('categoryId')"
          required>
        </bk-select-field>
      </div>

      <div class="form-actions">
        <bk-button type="button" variant="secondary" (click)="onCancel()">
          {{ 'common.cancel' | translate }}
        </bk-button>
        
        <bk-button 
          type="submit" 
          variant="primary" 
          [loading]="isSubmitting()"
          [disabled]="form.invalid">
          {{ submitLabel | translate }}
        </bk-button>
      </div>
    </form>
  `
})
export class FeatureFormComponent {
  private readonly fb = inject(FormBuilder);
  
  @Input() initialData?: Partial<Feature>;
  @Input() submitLabel = 'common.save';
  @Output() save = new EventEmitter<Feature>();
  @Output() cancel = new EventEmitter<void>();

  readonly isSubmitting = signal(false);
  readonly categories = signal<Category[]>([]);

  readonly form = this.fb.group({
    name: ['', [Validators.required, Validators.minLength(2)]],
    categoryId: ['', Validators.required],
    description: ['']
  });

  ngOnInit() {
    if (this.initialData) {
      this.form.patchValue(this.initialData);
    }
  }

  async onSubmit() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.isSubmitting.set(true);
    try {
      const formData = this.form.value as Feature;
      this.save.emit(formData);
    } finally {
      this.isSubmitting.set(false);
    }
  }

  getFieldError(field: string): string {
    const control = this.form.get(field);
    if (control?.invalid && control?.touched) {
      const errors = control.errors;
      if (errors?.['required']) return `feature.errors.${field}Required`;
      if (errors?.['minlength']) return `feature.errors.${field}TooShort`;
      if (errors?.['email']) return `feature.errors.${field}Invalid`;
    }
    return '';
  }
}
```

---

## 🔄 Workflow Estandarizado {#workflow}

### Proceso de Desarrollo de Nueva Pantalla

#### Paso 1: Planificación (5 min)
```bash
# 1. Definir feature scope
Feature: Client Management
Pages needed:
  - clients-list.page.ts      (CRUD list)
  - client-detail.page.ts     (View/Edit)
  - client-create.page.ts     (Create form)

# 2. Verificar endpoints API
GET /api/v5/clients          ✓ Exists
POST /api/v5/clients         ✓ Exists  
PUT /api/v5/clients/:id      ✓ Exists
DELETE /api/v5/clients/:id   ✓ Exists
```

#### Paso 2: Generación de Estructura (10 min)
```bash
# Usar generator (futuro) o crear manualmente
ng generate @boukii/schematics:feature clients

# Genera estructura base:
# src/app/features/clients/
# ├─ pages/
# │  ├─ clients-list.page.ts
# │  ├─ client-detail.page.ts
# │  └─ client-create.page.ts
# ├─ components/
# │  └─ client-form.component.ts
# ├─ services/
# │  └─ clients.service.ts
# ├─ types/
# │  └─ client.types.ts
# └─ clients.routes.ts
```

#### Paso 3: Implementación Core (30 min)

**3.1. Types (5 min)**
```typescript
// client.types.ts
export interface Client {
  id: number;
  name: string;
  email: string;
  phone?: string;
  schoolId: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateClientRequest {
  name: string;
  email: string;
  phone?: string;
}

export interface ClientFilters {
  search?: string;
  page?: number;
  limit?: number;
}
```

**3.2. Service (10 min)**
```typescript
// clients.service.ts - Copy from template, customize endpoints
@Injectable({
  providedIn: 'root'
})
export class ClientsService {
  private readonly baseUrl = '/clients';
  
  // Copy service template methods
  // Customize for Client specific logic
}
```

**3.3. Pages (15 min)**
```typescript
// clients-list.page.ts - Copy from template
@Component({
  template: `
    <bk-page-header title="clients.title">
      <bk-button action="add" route="/clients/create">
        {{ 'clients.actions.create' | translate }}
      </bk-button>
    </bk-page-header>

    <bk-data-table 
      [data]="clients()" 
      [columns]="columns"
      [loading]="isLoading()">
    </bk-data-table>
  `
})
export class ClientsListPage {
  // Copy page template implementation
}
```

#### Paso 4: Routing y Navigation (10 min)
```typescript
// clients.routes.ts
export const clientsRoutes: Routes = [
  {
    path: '',
    component: ClientsListPage,
    canActivate: [authV5Guard, contextGuard]
  },
  {
    path: 'create',
    component: ClientCreatePage,
    canActivate: [authV5Guard, contextGuard]
  },
  {
    path: ':id',
    component: ClientDetailPage,
    canActivate: [authV5Guard, contextGuard]
  }
];

// app.routes.ts - Add route
{
  path: 'clients',
  loadChildren: () => import('./features/clients/clients.routes').then(m => m.clientsRoutes)
}
```

#### Paso 5: I18n y Styling (15 min)
```json
// assets/i18n/es.json
{
  "clients": {
    "title": "Clientes",
    "actions": {
      "create": "Crear Cliente",
      "edit": "Editar",
      "delete": "Eliminar"
    },
    "fields": {
      "name": "Nombre",
      "email": "Email",
      "phone": "Teléfono"
    }
  }
}
```

```scss
// clients-list.page.scss - Use design tokens
.clients-page {
  .search-section {
    margin-bottom: var(--space-6);
  }
  
  .table-container {
    background: var(--surface);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
  }
}
```

#### Paso 6: Testing (20 min)
```typescript
// clients.service.spec.ts - Copy test template
describe('ClientsService', () => {
  // Standard service tests
});

// clients-list.page.spec.ts - Copy test template  
describe('ClientsListPage', () => {
  // Standard page tests
});
```

### Tiempo Total: **90 minutos** para feature completa

---

## 🎨 Design System {#design-system}

### Componentes Base Disponibles

#### Atoms
```typescript
// Button
<bk-button 
  variant="primary|secondary|danger" 
  size="sm|md|lg"
  [loading]="isLoading()"
  [disabled]="isDisabled()">
  Button Text
</bk-button>

// Input
<bk-input
  type="text|email|password|number"
  placeholder="Enter value"
  [error]="errorMessage"
  [disabled]="isDisabled()">
</bk-input>

// Select
<bk-select
  [options]="options()"
  [multiple]="false"
  placeholder="Select option"
  [error]="errorMessage">
</bk-select>
```

#### Molecules
```typescript
// Search Box
<bk-search-box
  placeholder="Search clients..."
  [value]="searchTerm()"
  (search)="onSearch($event)"
  [loading]="isSearching()">
</bk-search-box>

// Pagination
<bk-pagination
  [currentPage]="page()"
  [totalPages]="totalPages()"
  [pageSize]="pageSize()"
  (pageChange)="onPageChange($event)">
</bk-pagination>

// Form Field (Input + Label + Error)
<bk-form-field
  label="client.fields.name"
  [error]="getFieldError('name')"
  [required]="true">
  <input bkInput formControlName="name" />
</bk-form-field>
```

#### Organisms
```typescript
// Data Table
<bk-data-table
  [data]="items()"
  [columns]="columns"
  [loading]="isLoading()"
  [pagination]="pagination()"
  [sortable]="true"
  [filterable]="true"
  (sort)="onSort($event)"
  (filter)="onFilter($event)"
  (rowClick)="onRowClick($event)">
</bk-data-table>

// Page Header
<bk-page-header
  [title]="'clients.title' | translate"
  [breadcrumbs]="breadcrumbs()">
  <ng-content></ng-content>
</bk-page-header>

// Modal
<bk-modal
  [open]="isModalOpen()"
  [title]="'clients.delete.confirm' | translate"
  (close)="onModalClose()">
  <p>{{ 'clients.delete.message' | translate }}</p>
  
  <div slot="actions">
    <bk-button variant="secondary" (click)="onCancel()">
      {{ 'common.cancel' | translate }}
    </bk-button>
    <bk-button variant="danger" (click)="onConfirm()">
      {{ 'common.delete' | translate }}
    </bk-button>
  </div>
</bk-modal>
```

### Design Tokens
```css
/* Spacing */
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-6: 24px;
--space-8: 32px;

/* Colors */
--brand-50: #eff6ff;
--brand-500: #3b82f6;
--brand-600: #2563eb;
--brand-700: #1d4ed8;

/* Typography */
--text-xs: 12px;
--text-sm: 14px;
--text-base: 16px;
--text-lg: 18px;
--text-xl: 20px;

/* Border Radius */
--radius-sm: 4px;
--radius-md: 6px;
--radius-lg: 8px;
--radius-xl: 12px;

/* Shadows */
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
```

---

## 🧪 Testing Automatizado {#testing}

### Test Templates

#### Service Tests
```typescript
// feature.service.spec.ts template
describe('FeatureService', () => {
  let service: FeatureService;
  let apiService: jest.Mocked<ApiV5Service>;

  beforeEach(() => {
    const apiMock = {
      get: jest.fn(),
      post: jest.fn(),
      put: jest.fn(),
      delete: jest.fn()
    };

    TestBed.configureTestingModule({
      providers: [
        FeatureService,
        { provide: ApiV5Service, useValue: apiMock }
      ]
    });

    service = TestBed.inject(FeatureService);
    apiService = TestBed.inject(ApiV5Service) as jest.Mocked<ApiV5Service>;
  });

  describe('getAll', () => {
    it('should fetch and set items', async () => {
      const mockData = [{ id: 1, name: 'Test' }];
      apiService.get.mockResolvedValue({ data: mockData });

      await service.getAll();

      expect(service.items()).toEqual(mockData);
      expect(apiService.get).toHaveBeenCalledWith('/features', expect.any(Object));
    });

    it('should handle API errors', async () => {
      apiService.get.mockRejectedValue(new Error('API Error'));

      await expect(service.getAll()).rejects.toThrow('API Error');
      expect(service.items()).toEqual([]);
    });
  });
});
```

#### Page Tests
```typescript
// feature-list.page.spec.ts template
describe('FeatureListPage', () => {
  let component: FeatureListPage;
  let fixture: ComponentFixture<FeatureListPage>;
  let service: jest.Mocked<FeatureService>;

  beforeEach(async () => {
    const serviceMock = {
      items: signal([]),
      isLoading: signal(false),
      getAll: jest.fn(),
      delete: jest.fn()
    };

    await TestBed.configureTestingModule({
      imports: [FeatureListPage],
      providers: [
        { provide: FeatureService, useValue: serviceMock }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(FeatureListPage);
    component = fixture.componentInstance;
    service = TestBed.inject(FeatureService) as jest.Mocked<FeatureService>;
  });

  it('should load items on init', () => {
    component.ngOnInit();
    expect(service.getAll).toHaveBeenCalled();
  });

  it('should display loading state', () => {
    service.isLoading.set(true);
    fixture.detectChanges();

    const loadingEl = fixture.debugElement.query(By.css('[data-testid="loading"]'));
    expect(loadingEl).toBeTruthy();
  });
});
```

#### E2E Tests
```typescript
// clients.cy.ts template
describe('Clients Management', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
    cy.selectSchool('Test School');
    cy.visit('/clients');
  });

  it('should list clients', () => {
    cy.get('[data-testid="clients-table"]').should('be.visible');
    cy.get('[data-testid="client-row"]').should('have.length.at.least', 1);
  });

  it('should create new client', () => {
    cy.get('[data-testid="create-client-btn"]').click();
    cy.get('[data-testid="client-name-input"]').type('New Client');
    cy.get('[data-testid="client-email-input"]').type('client@test.com');
    cy.get('[data-testid="save-btn"]').click();

    cy.url().should('include', '/clients');
    cy.get('[data-testid="success-message"]').should('contain', 'Client created');
  });
});
```

### Storybook Stories
```typescript
// feature-form.stories.ts template
export default {
  title: 'Features/FeatureForm',
  component: FeatureFormComponent,
  parameters: {
    layout: 'centered'
  }
} as Meta<FeatureFormComponent>;

export const Default: Story = {
  args: {
    submitLabel: 'Save Feature'
  }
};

export const EditMode: Story = {
  args: {
    initialData: { id: 1, name: 'Existing Feature' },
    submitLabel: 'Update Feature'
  }
};

export const Loading: Story = {
  args: {
    submitLabel: 'Saving...'
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement);
    const submitBtn = canvas.getByRole('button', { name: /save/i });
    
    await userEvent.click(submitBtn);
    await expect(submitBtn).toBeDisabled();
  }
};
```

---

## ⚡ Performance y Optimización {#performance}

### Lazy Loading Strategy
```typescript
// app.routes.ts - Route-level code splitting
const routes: Routes = [
  {
    path: 'clients',
    loadChildren: () => import('./features/clients/clients.routes').then(m => m.clientsRoutes),
    canActivate: [authV5Guard, contextGuard]
  },
  {
    path: 'bookings',
    loadChildren: () => import('./features/bookings/bookings.routes').then(m => m.bookingsRoutes),
    canActivate: [authV5Guard, contextGuard]
  }
];
```

### Caching Strategy
```typescript
// feature.service.ts - Service-level caching
@Injectable({
  providedIn: 'root'
})
export class FeatureService {
  private readonly cache = new Map<string, { data: any; timestamp: number }>();
  private readonly CACHE_TTL = 5 * 60 * 1000; // 5 minutes

  async getAll(filters?: FeatureFilters): Promise<Feature[]> {
    const cacheKey = this.getCacheKey('getAll', filters);
    const cached = this.cache.get(cacheKey);
    
    if (cached && (Date.now() - cached.timestamp) < this.CACHE_TTL) {
      this._items.set(cached.data);
      return cached.data;
    }

    const data = await this.fetchFromAPI('/features', filters);
    this.cache.set(cacheKey, { data, timestamp: Date.now() });
    this._items.set(data);
    
    return data;
  }

  invalidateCache(pattern?: string): void {
    if (pattern) {
      for (const [key] of this.cache) {
        if (key.includes(pattern)) {
          this.cache.delete(key);
        }
      }
    } else {
      this.cache.clear();
    }
  }
}
```

### Virtual Scrolling para Listas Grandes
```typescript
// large-data-table.component.ts
@Component({
  template: `
    <cdk-virtual-scroll-viewport 
      itemSize="50" 
      class="table-viewport"
      [style.height.px]="viewportHeight">
      
      <div *cdkVirtualFor="let item of items; trackBy: trackByFn" 
           class="table-row">
        <ng-container [ngTemplateOutlet]="rowTemplate" 
                      [ngTemplateOutletContext]="{ item: item }">
        </ng-container>
      </div>
    </cdk-virtual-scroll-viewport>
  `
})
export class LargeDataTableComponent {
  @Input() items: any[] = [];
  @Input() rowTemplate!: TemplateRef<any>;
  @Input() viewportHeight = 400;

  trackByFn(index: number, item: any): any {
    return item.id || index;
  }
}
```

### Image Optimization
```typescript
// optimized-image.component.ts
@Component({
  selector: 'bk-image',
  template: `
    <img 
      [src]="optimizedSrc()"
      [alt]="alt"
      [loading]="lazy ? 'lazy' : 'eager'"
      (error)="onError()"
      (load)="onLoad()" />
  `
})
export class OptimizedImageComponent {
  @Input() src!: string;
  @Input() alt!: string;
  @Input() width?: number;
  @Input() height?: number;
  @Input() lazy = true;

  optimizedSrc = computed(() => {
    if (!this.src) return '';
    
    // Add responsive image params
    const params = new URLSearchParams();
    if (this.width) params.set('w', this.width.toString());
    if (this.height) params.set('h', this.height.toString());
    params.set('q', '85'); // Quality
    params.set('f', 'webp'); // Format
    
    return `${this.src}?${params.toString()}`;
  });
}
```

---

## 🔍 Troubleshooting {#troubleshooting}

### Problemas Comunes

#### 1. Context No Disponible
```typescript
// Error: School/Season not set
// Solución: Verificar guards y middleware

// context.guard.ts
export const contextGuard: CanActivateFn = (route, state) => {
  const contextService = inject(ContextService);
  
  if (!contextService.hasCompleteContext()) {
    const router = inject(Router);
    router.navigate(['/select-school']);
    return false;
  }
  
  return true;
};
```

#### 2. Memory Leaks en Signals
```typescript
// ❌ Problema: Signal subscriptions no limpiadas
export class BadComponent {
  ngOnInit() {
    // Signal que nunca se limpia
    effect(() => {
      console.log(this.dataService.items());
    });
  }
}

// ✅ Solución: Usar DestroyRef
export class GoodComponent {
  private readonly destroyRef = inject(DestroyRef);

  ngOnInit() {
    const effectRef = effect(() => {
      console.log(this.dataService.items());
    });
    
    this.destroyRef.onDestroy(() => {
      effectRef.destroy();
    });
  }
}
```

#### 3. Performance Issues en DataTable
```typescript
// ❌ Problema: Re-renders excesivos
@Component({
  template: `
    <tr *ngFor="let item of items">
      <td>{{ item.name }}</td>
      <td>{{ formatDate(item.createdAt) }}</td> <!-- Función en template -->
    </tr>
  `
})

// ✅ Solución: Computed values + TrackBy
@Component({
  template: `
    <tr *ngFor="let item of formattedItems(); trackBy: trackByFn">
      <td>{{ item.name }}</td>
      <td>{{ item.formattedDate }}</td>
    </tr>
  `
})
export class OptimizedComponent {
  formattedItems = computed(() => 
    this.items().map(item => ({
      ...item,
      formattedDate: this.formatDate(item.createdAt)
    }))
  );

  trackByFn(index: number, item: any): any {
    return item.id;
  }
}
```

### Debugging Tools

#### 1. Angular DevTools
```typescript
// Habilitar en development
import { enableProdMode } from '@angular/core';
import { environment } from './environments/environment';

if (environment.production) {
  enableProdMode();
} else {
  // Enable Angular DevTools
  window['ng'] = require('@angular/core');
}
```

#### 2. Console Helpers
```typescript
// debug.service.ts
@Injectable({
  providedIn: 'root'
})
export class DebugService {
  logState(componentName: string, state: any): void {
    if (!environment.production) {
      console.group(`🔍 ${componentName} State`);
      console.log(state);
      console.groupEnd();
    }
  }

  logPerformance(operation: string, startTime: number): void {
    if (!environment.production) {
      const duration = performance.now() - startTime;
      console.log(`⏱️ ${operation}: ${duration.toFixed(2)}ms`);
    }
  }
}
```

---

## 📚 Recursos Adicionales

### Templates de Código
- [Component Templates](./templates/components/)
- [Service Templates](./templates/services/)
- [Test Templates](./templates/tests/)

### Storybook Components
- [Live Components](http://localhost:6040)
- [Design Tokens](http://localhost:6040/docs/design-tokens)

### API Documentation
- [OpenAPI Spec](../shared/boukii-v5.yaml)
- [Postman Collection](../shared/boukii-v5.postman.json)

---

## 🎯 Checklist de Nueva Feature

- [ ] **Planning** (5 min)
  - [ ] Define scope y páginas necesarias
  - [ ] Verifica endpoints API disponibles
  - [ ] Revisa permisos requeridos

- [ ] **Structure** (10 min)
  - [ ] Crea estructura de carpetas
  - [ ] Define types/interfaces
  - [ ] Configura routing

- [ ] **Implementation** (45 min)
  - [ ] Service con CRUD operations
  - [ ] List page con tabla y filtros
  - [ ] Detail/Edit page con formulario
  - [ ] Error handling y loading states

- [ ] **UI/UX** (15 min)
  - [ ] Aplica design tokens
  - [ ] Añade traducciones i18n
  - [ ] Verifica responsiveness
  - [ ] Testea accessibility

- [ ] **Testing** (20 min)
  - [ ] Unit tests para service
  - [ ] Component tests para páginas
  - [ ] E2E test para flujo principal
  - [ ] Storybook stories

- [ ] **Performance** (10 min)
  - [ ] Lazy loading configurado
  - [ ] TrackBy functions en listas
  - [ ] Caching implementado si necesario

- [ ] **Documentation** (5 min)
  - [ ] Comments en código complejo
  - [ ] README actualizado si necesario
  - [ ] Changelog entry

**Total Time: ~2 hours para feature completa con tests**