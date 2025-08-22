import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { ReactiveFormsModule } from '@angular/forms';

import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ClientsV5Service } from '@core/services/clients-v5.service';
import { ContextService } from '@core/services/context.service';
import { ToastService } from '@core/services/toast.service';

// Tab components
import { ClientDataTabComponent } from './tabs/client-data-tab.component';
import { ClientUtilizadoresTabComponent } from './tabs/client-utilizadores-tab.component';
import { ClientSportsTabComponent } from './tabs/client-sports-tab.component';
import { ClientObservationsTabComponent } from './tabs/client-observations-tab.component';
import { ClientHistoryTabComponent } from './tabs/client-history-tab.component';

export interface ClientDetail {
  id: number;
  email?: string;
  first_name: string;
  last_name: string;
  birth_date?: string;
  phone?: string;
  telephone?: string;
  address?: string;
  cp?: string;
  city?: string;
  province?: string;
  country?: string;
  image?: string;
  created_at: string;
  updated_at: string;
  // Relations
  utilizadores?: ClientUtilizador[];
  client_sports?: ClientSport[];
  observations?: ClientObservation[];
  booking_history?: BookingHistoryItem[];
}

export interface ClientUtilizador {
  id: number;
  client_id: number;
  main_id?: number;
  first_name: string;
  last_name: string;
  birth_date?: string;
  image?: string;
  created_at: string;
  updated_at: string;
}

export interface ClientSport {
  id: number;
  client_id: number;
  person_type: 'client' | 'utilizador';
  person_id: number;
  sport_id: number;
  degree_id?: number;
  created_at: string;
  updated_at: string;
  sport?: {
    id: number;
    name: string;
  };
  degree?: {
    id: number;
    name: string;
    level?: number;
    color?: string;
  };
}

export interface ClientObservation {
  id: number;
  client_id: number;
  title: string;
  content: string;
  created_at: string;
  updated_at: string;
}

export interface BookingHistoryItem {
  id: number;
  client_id: number;
  type: 'booking' | 'course';
  status: 'completed' | 'active' | 'confirmed' | 'cancelled' | 'pending';
  title: string;
  description?: string;
  service?: string;
  instructor?: string;
  date: string;
  amount?: number;
  duration_hours?: number;
}

type TabType = 'datos' | 'utilizadores' | 'deportes' | 'observaciones' | 'historial';

@Component({
  selector: 'app-client-detail-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslatePipe,
    ClientDataTabComponent,
    ClientUtilizadoresTabComponent,
    ClientSportsTabComponent,
    ClientObservationsTabComponent,
    ClientHistoryTabComponent
  ],
  template: `
    <div class="client-detail-page">
      @if (loading()) {
        <div class="loading-state">
          <div class="skeleton-header"></div>
          <div class="skeleton-tabs"></div>
          <div class="skeleton-content"></div>
        </div>
      }

      @if (!loading() && client()) {
        <!-- Page Header -->
        <div class="page-header">
          <div class="client-info">
            <div class="client-avatar">
              @if (client()?.image) {
                <img [src]="client()!.image" [alt]="clientFullName()" />
              } @else {
                <div class="avatar-placeholder">
                  {{ getInitials(clientFullName()) }}
                </div>
              }
            </div>
            <div class="client-meta">
              <h1 class="client-name">{{ clientFullName() }}</h1>
              @if (client()?.email) {
                <p class="client-email">{{ client()!.email }}</p>
              }
              <div class="client-stats">
                <span class="stat">
                  <strong>{{ client()?.utilizadores?.length || 0 }}</strong>
                  {{ 'clients.utilizadores' | translate }}
                </span>
                <span class="stat">
                  <strong>{{ client()?.client_sports?.length || 0 }}</strong>
                  {{ 'clients.sports' | translate }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="page-actions">
            <button type="button" class="btn btn--outline" (click)="goBack()">
              {{ 'common.back' | translate }}
            </button>
          </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <nav class="tabs" role="tablist">
            <button
              *ngFor="let tab of availableTabs; trackBy: trackTab"
              type="button"
              class="tab"
              [class.active]="activeTab() === tab.id"
              [attr.aria-selected]="activeTab() === tab.id"
              [attr.aria-controls]="tab.id + '-panel'"
              role="tab"
              (click)="setActiveTab(tab.id)">
              <span class="tab-icon" [innerHTML]="tab.icon"></span>
              <span class="tab-label">{{ tab.label | translate }}</span>
              @if (tab.badge && tab.badge > 0) {
                <span class="tab-badge">{{ tab.badge }}</span>
              }
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          @switch (activeTab()) {
            @case ('datos') {
              <div
                id="datos-panel"
                class="tab-panel"
                role="tabpanel"
                aria-labelledby="datos-tab">
                <app-client-data-tab
                  [client]="client()!"
                  (clientUpdated)="onClientUpdated($event)" />
              </div>
            }
            @case ('utilizadores') {
              <div
                id="utilizadores-panel"
                class="tab-panel"
                role="tabpanel"
                aria-labelledby="utilizadores-tab">
                <app-client-utilizadores-tab
                  [client]="client()!"
                  (utilizadoresUpdated)="onUtilizadoresUpdated($event)" />
              </div>
            }
            @case ('deportes') {
              <div
                id="deportes-panel"
                class="tab-panel"
                role="tabpanel"
                aria-labelledby="deportes-tab">
                <app-client-sports-tab
                  [client]="client()!"
                  (sportsUpdated)="onSportsUpdated($event)" />
              </div>
            }
            @case ('observaciones') {
              <div
                id="observaciones-panel"
                class="tab-panel"
                role="tabpanel"
                aria-labelledby="observaciones-tab">
                <app-client-observations-tab
                  [client]="client()!"
                  (observationsUpdated)="onObservationsUpdated($event)" />
              </div>
            }
            @case ('historial') {
              <div
                id="historial-panel"
                class="tab-panel"
                role="tabpanel"
                aria-labelledby="historial-tab">
                <app-client-history-tab [client]="client()!" />
              </div>
            }
          }
        </div>
      }

      @if (!loading() && !client()) {
        <div class="error-state">
          <div class="error-icon">❌</div>
          <h2>{{ 'clients.detail.notFound' | translate }}</h2>
          <p>{{ 'clients.detail.notFoundMessage' | translate }}</p>
          <button type="button" class="btn btn--primary" (click)="goBack()">
            {{ 'common.back' | translate }}
          </button>
        </div>
      }
    </div>
  `,
  styleUrls: ['./client-detail.page.scss']
})
export class ClientDetailPageComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly clientsService = inject(ClientsV5Service);
  private readonly contextService = inject(ContextService);
  private readonly toastService = inject(ToastService);

  // State
  readonly loading = signal(true);
  readonly client = signal<ClientDetail | null>(null);
  readonly activeTab = signal<TabType>('datos');

  // Computed values
  readonly clientFullName = computed(() => {
    const c = this.client();
    return c ? `${c.first_name} ${c.last_name}`.trim() : '';
  });

  // Tab configuration
  readonly availableTabs = computed(() => [
    {
      id: 'datos' as TabType,
      label: 'clients.tabs.datos',
      icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
      badge: 0
    },
    {
      id: 'utilizadores' as TabType,
      label: 'clients.tabs.utilizadores',
      icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A3 3 0 0 0 17.24 7H14.5c-1.3 0-2.42.84-2.83 2.01L9.35 12 8 10.65V9a1 1 0 0 0-2 0v2.5L9.5 15 8 16v2a1 1 0 0 0 2 0v-1.65L11.35 15l1.92 5.11c.19.5.69.85 1.23.89H20z"/></svg>',
      badge: this.client()?.utilizadores?.length || 0
    },
    {
      id: 'deportes' as TabType,
      label: 'clients.tabs.deportes',
      icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
      badge: this.client()?.client_sports?.length || 0
    },
    {
      id: 'observaciones' as TabType,
      label: 'clients.tabs.observaciones',
      icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>',
      badge: this.client()?.observations?.length || 0
    },
    {
      id: 'historial' as TabType,
      label: 'clients.tabs.historial',
      icon: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>',
      badge: 0
    }
  ]);

  ngOnInit(): void {
    const clientId = this.route.snapshot.paramMap.get('id');
    if (!clientId || isNaN(Number(clientId))) {
      this.router.navigate(['/clients']);
      return;
    }

    // Check for tab in query params
    const tab = this.route.snapshot.queryParamMap.get('tab') as TabType;
    if (tab && ['datos', 'utilizadores', 'deportes', 'observaciones', 'historial'].includes(tab)) {
      this.activeTab.set(tab);
    }

    this.loadClient(Number(clientId));
  }

  private loadClient(id: number): void {
    this.loading.set(true);
    
    // For now, we'll use a mock implementation since the service doesn't have getClient yet
    // TODO: Replace with actual API call when ClientsV5Service.getClient is implemented
    setTimeout(() => {
      const mockClient: ClientDetail = {
        id,
        first_name: 'Juan',
        last_name: 'Pérez García',
        email: 'juan.perez@example.com',
        phone: '+34 123 456 789',
        telephone: '987 654 321',
        address: 'Calle Mayor, 123',
        cp: '28001',
        city: 'Madrid',
        province: 'Madrid',
        country: 'España',
        birth_date: '1985-03-15',
        created_at: '2023-01-15T10:30:00Z',
        updated_at: '2024-12-01T14:20:00Z',
        utilizadores: [
          {
            id: 1,
            client_id: id,
            first_name: 'Ana',
            last_name: 'Pérez',
            birth_date: '2010-05-20',
            created_at: '2023-02-01T09:00:00Z',
            updated_at: '2024-11-15T16:45:00Z'
          },
          {
            id: 2,
            client_id: id,
            first_name: 'Carlos',
            last_name: 'Pérez',
            birth_date: '2012-09-10',
            created_at: '2023-02-01T09:00:00Z',
            updated_at: '2024-11-15T16:45:00Z'
          }
        ],
        client_sports: [
          {
            id: 1,
            client_id: id,
            sport_id: 1,
            degree_id: 2,
            created_at: '2023-03-01T10:00:00Z',
            updated_at: '2024-10-01T12:30:00Z',
            sport: { id: 1, name: 'Esquí Alpino' },
            degree: { id: 2, name: 'Intermedio' }
          }
        ],
        observations: [
          {
            id: 1,
            client_id: id,
            title: 'Preferencias dietéticas',
            content: 'Es vegetariano y tiene alergia a los frutos secos.',
            created_at: '2024-01-15T14:30:00Z',
            updated_at: '2024-01-15T14:30:00Z'
          }
        ],
        booking_history: [
          {
            id: 1,
            course_name: 'Esquí Principiantes - Grupo A',
            course_date: '2024-12-15',
            status: 'completed',
            created_at: '2024-11-20T10:00:00Z'
          }
        ]
      };

      this.client.set(mockClient);
      this.loading.set(false);
    }, 800);
  }

  setActiveTab(tab: TabType): void {
    this.activeTab.set(tab);
    
    // Update URL with tab parameter
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { tab },
      queryParamsHandling: 'merge'
    });
  }

  trackTab(index: number, tab: any): string {
    return tab.id;
  }

  getInitials(name: string): string {
    return name
      .split(' ')
      .map(part => part.charAt(0).toUpperCase())
      .slice(0, 2)
      .join('');
  }

  goBack(): void {
    this.router.navigate(['/clients']);
  }

  // Event handlers for tab updates
  onClientUpdated(updatedClient: ClientDetail): void {
    this.client.set(updatedClient);
    this.toastService.success('clients.detail.updated');
  }

  onUtilizadoresUpdated(utilizadores: ClientUtilizador[]): void {
    const currentClient = this.client();
    if (currentClient) {
      this.client.set({ ...currentClient, utilizadores });
    }
  }

  onSportsUpdated(sports: ClientSport[]): void {
    const currentClient = this.client();
    if (currentClient) {
      this.client.set({ ...currentClient, client_sports: sports });
    }
  }

  onObservationsUpdated(observations: ClientObservation[]): void {
    const currentClient = this.client();
    if (currentClient) {
      this.client.set({ ...currentClient, observations });
    }
  }
}