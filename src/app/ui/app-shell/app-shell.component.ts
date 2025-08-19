import { Component, inject, OnInit, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { ThemeToggleComponent } from '@ui/theme-toggle/theme-toggle.component';
import { ToastComponent } from '@shared/components/toast/toast.component';
import { LanguageSelectorComponent } from '@shared/components/language-selector/language-selector.component';
import { FeatureFlagPanelComponent } from '@shared/components/feature-flag-panel/feature-flag-panel.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { UiStore } from '@core/stores/ui.store';
import { AuthStore } from '@core/stores/auth.store';
import { LoadingStore } from '@core/stores/loading.store';
import { TranslationService } from '@core/services/translation.service';
import { EnvironmentService } from '@core/services/environment.service';
import { AuthV5Service } from '@core/services/auth-v5.service';

@Component({
  selector: 'app-shell',
  standalone: true,
  imports: [
    CommonModule,
    RouterOutlet,
    ThemeToggleComponent,
    ToastComponent,
    LanguageSelectorComponent,
    FeatureFlagPanelComponent,
    TranslatePipe,
  ],
  styleUrl: './app-shell.styles.scss',
  template: `
    <!-- Show full layout only when authenticated and has school selected -->
    @if (shouldShowFullLayout()) {
      <div class="app-shell" [class.sidebar-collapsed]="ui.sidebarCollapsed()">
        <!-- Header -->
        <header class="app-header">
          <div class="header-start">
            <!-- Search Bar -->
            <div class="search-container">
              <svg class="search-icon" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="21 21-4.35-4.35" />
              </svg>
              <input
                type="text"
                class="search-input"
                [placeholder]="'nav.searchPlaceholder' | translate"
              />
            </div>
          </div>

          <div class="header-end">
            <app-language-selector />
            <app-theme-toggle />
            
            <!-- Notifications -->
            <button class="notification-btn">
              <svg class="notification-icon" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
              </svg>
              <span class="notification-badge">3</span>
            </button>

            <!-- User menu -->
            <div class="user-menu">
              @if (authV5.isAuthenticated() && authV5.user()) {
                <div class="user-info">
                  <div class="user-details hidden-mobile">
                    <span class="user-name">{{ authV5.user()?.name }}</span>
                    <span class="user-role">Administrador</span>
                  </div>
                  <button class="user-avatar" [title]="authV5.user()?.name || 'User Menu'">
                    <img 
                      src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                      [alt]="authV5.user()?.name" 
                      class="avatar-image"
                    />
                  </button>
                  <button class="dropdown-trigger">
                    <svg viewBox="0 0 24 24" class="dropdown-icon">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </button>
                </div>
              }
            </div>
          </div>
        </header>

        <!-- Sidebar -->
        <aside class="app-sidebar">
          <!-- Logo -->
          <div class="sidebar-header">
            <div class="sidebar-logo">
              <span class="logo-text">bouk<span class="logo-accent">ii</span></span>
            </div>
            <button class="sidebar-collapse" (click)="ui.toggleSidebar()" [class.collapsed]="ui.sidebarCollapsed()">
              <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
              </svg>
            </button>
          </div>

          <nav class="sidebar-nav" role="navigation">
            <ul class="nav-list">
              <li class="nav-item">
                <a href="#" class="nav-link active">
                  <div class="nav-icon dashboard-icon"></div>
                  <span class="nav-text">Dashboard</span>
                  <span class="nav-badge gold">3</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon reservas-icon"></div>
                  <span class="nav-text">Reservas</span>
                  <span class="nav-badge">12</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon clientes-icon"></div>
                  <span class="nav-text">Clientes</span>
                  <span class="nav-badge yellow">5</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon planificador-icon"></div>
                  <span class="nav-text">Planificador</span>
                  <span class="nav-badge red">2</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon instructores-icon"></div>
                  <span class="nav-text">Instructores</span>
                  <span class="nav-badge yellow">1</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon cursos-icon"></div>
                  <span class="nav-text">Cursos y Actividades</span>
                  <span class="nav-badge green">8</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon material-icon"></div>
                  <span class="nav-text">Alquiler de Material</span>
                  <span class="nav-badge yellow">5</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon bonos-icon"></div>
                  <span class="nav-text">Bonos y códigos</span>
                  <span class="nav-badge">12</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon comunicacion-icon"></div>
                  <span class="nav-text">Comunicación</span>
                  <span class="nav-badge red">6</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon pagos-icon"></div>
                  <span class="nav-text">Pagos</span>
                  <span class="nav-badge yellow">4</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon reportes-icon"></div>
                  <span class="nav-text">Reportes</span>
                  <span class="nav-badge green">1</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon admin-icon"></div>
                  <span class="nav-text">Administradores</span>
                  <span class="nav-badge">2</span>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <div class="nav-icon config-icon"></div>
                  <span class="nav-text">Configuración</span>
                </a>
              </li>
            </ul>
          </nav>

          <!-- Support Section -->
          <div class="sidebar-support">
            <p class="support-text">¿Necesitas ayuda?</p>
            <p class="support-subtitle">Contacta nuestro soporte técnico</p>
            <button class="support-btn">Soporte</button>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main" role="main">
          <div class="main-content">
            <router-outlet />
          </div>
        </main>

        <!-- Global loading indicator -->
        @if (loadingStore.isLoading()) {
          <div
            class="global-loading"
            [attr.aria-label]="loadingStore.longestRunningRequest()?.description || 'Loading...'"
          >
            <div class="loading-spinner"></div>
          </div>
        }
      </div>
    } @else {
      <!-- Simplified layout for auth pages and school selection -->
      <div class="simple-layout">
        <router-outlet />
      </div>
    }

    <!-- Toast notifications -->
    <app-toast-container />

    <!-- Feature Flag Panel (development only) -->
    <app-feature-flag-panel />
  `,
})
export class AppShellComponent implements OnInit {
  protected readonly ui = inject(UiStore);
  protected readonly auth = inject(AuthStore);
  protected readonly authV5 = inject(AuthV5Service);
  protected readonly loadingStore = inject(LoadingStore);
  protected readonly translationService = inject(TranslationService);
  protected readonly environmentService = inject(EnvironmentService);

  // Computed to determine when to show full layout
  protected readonly shouldShowFullLayout = computed(() => {
    return this.authV5.isAuthenticated() && !!this.authV5.currentSchool();
  });

  ngOnInit(): void {
    // Initialize theme system
    this.ui.initializeTheme();

    // Try to load user session if token exists
    this.auth.loadMe();
  }

  protected getUserInitials(name: string): string {
    if (!name) return '';
    return name
      .split(' ')
      .map((part) => part[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }
}
