import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata, applicationConfig } from '@storybook/angular';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideRouter } from '@angular/router';
import { within, userEvent } from '@storybook/testing-library';
import { signal, computed } from '@angular/core';

import { AppShellComponent } from './app-shell.component';
import { UiStore } from '@core/stores/ui.store';
import { AuthStore } from '@core/stores/auth.store';
import { LoadingStore } from '@core/stores/loading.store';
import { AuthV5Service } from '@core/services/auth-v5.service';
import { TranslationService, SupportedLanguage } from '@core/services/translation.service';
import { EnvironmentService } from '@core/services/environment.service';

// Enhanced Mock Services for Stories
class MockTranslationService {
  private lang: SupportedLanguage = 'es';
  currentLanguage(): SupportedLanguage { return this.lang; }
  setLanguage(lang: SupportedLanguage): void { this.lang = lang; }
  get(key: string): string { return key; }
  instant(key: string): string { return key; }
  private langSignal = signal<SupportedLanguage>('es');
  
  currentLanguage = computed(() => this.langSignal());

  setLanguage(lang: SupportedLanguage): void {
    this.langSignal.set(lang);
  }

  get(key: string): string {
    const translations: Record<string, string> = {
      'nav.search': 'Buscar...',
      'nav.notifications': 'Notificaciones',
      'nav.user': 'Usuario',
      'nav.logout': 'Cerrar sesión',
      'nav.dashboard': 'Dashboard',
      'nav.reservations': 'Reservas',
      'nav.clients': 'Clientes',
      'nav.resources': 'Recursos',
      'nav.support': 'Soporte',
      'nav.support.subtitle': 'Centro de ayuda',
      'notifications.empty': 'No hay notificaciones',
    };
    return translations[key] || key;
  }

  instant(key: string): string {
    return this.get(key);
  }
}

class MockAuthV5Service {
  private userSignal = signal({ 
    id: 1, 
    name: 'Usuario Demo', 
    email: 'demo@boukii.com', 
    role: 'Administrador' 
  });
  
  user = computed(() => this.userSignal());
  
  isAuthenticated(): boolean {
    return true;
  }
  
  currentSchool() {
    return { id: 1, name: 'Escuela Demo' };
  }
  
  currentSchoolIdSignal() {
    return signal(1);
  }
}

class MockUiStore {
  private themeSignal = signal<'light' | 'dark'>('light');
  private sidebarCollapsedSignal = signal(false);
  private notificationsSignal = signal([
    { id: 1, type: 'info', title: 'Nueva reserva', message: 'Cliente ha hecho una reserva', unread: true },
    { id: 2, type: 'warning', title: 'Recurso ocupado', message: 'Pista 1 ocupada hasta las 18h', unread: true },
    { id: 3, type: 'success', title: 'Pago confirmado', message: 'Reserva #123 pagada', unread: false }
  ]);
  
  theme = computed(() => this.themeSignal());
  sidebarCollapsed = computed(() => this.sidebarCollapsedSignal());
  notifications = computed(() => this.notificationsSignal());
  unreadNotificationsCount = computed(() => 
    this.notificationsSignal().filter(n => n.unread).length
  );
  
  toggleTheme(): void {
    const current = this.themeSignal();
    this.themeSignal.set(current === 'light' ? 'dark' : 'light');
    document.body.setAttribute('data-theme', this.themeSignal());
  }
  
  toggleSidebar(): void {
    this.sidebarCollapsedSignal.update(collapsed => !collapsed);
  }
  
  initializeTheme(): void {
    document.body.setAttribute('data-theme', this.themeSignal());
  }
}

const meta: Meta<AppShellComponent> = {
  title: 'App/AppShell',
  component: AppShellComponent,
  decorators: [
    moduleMetadata({ imports: [AppShellComponent] }),
    applicationConfig({
      providers: [
        provideAnimations(),
        provideRouter([]),
        { provide: UiStore, useClass: MockUiStore },
        { provide: AuthStore, useValue: { loadMe: () => {} } },
        { provide: LoadingStore, useValue: { isLoading: () => false, longestRunningRequest: () => null } },
        { provide: AuthV5Service, useValue: { isAuthenticated: () => true, currentSchool: () => ({}), currentSchoolIdSignal: () => 1, user: () => ({ name: 'Test User' }), permissions: () => [] } },
        { provide: AuthV5Service, useClass: MockAuthV5Service },
        { provide: TranslationService, useClass: MockTranslationService },
        { provide: EnvironmentService, useValue: { isProduction: () => true, envName: () => 'production' } },
      ],
    }),
  ],
  parameters: {
    layout: 'fullscreen',
    docs: {
      description: {
        component: 'AppShell principal de Boukii V5 con navbar 56px y sidebar 264px/72px. Chevron siempre visible con rotación 180°.',
      },
    },
  },
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<AppShellComponent>;

function setup(theme: 'light' | 'dark', collapsed: boolean) {
  localStorage.setItem('theme', theme);
  localStorage.setItem('sidebarCollapsed', JSON.stringify(collapsed));
  return { template: `<app-shell></app-shell>` };
}

export const DefaultLight: Story = {
  render: () => setup('light', false),
};

export const DefaultDark: Story = {
  render: () => setup('dark', false),
};

export const CollapsedLight: Story = {
  render: () => setup('light', true),
};

export const CollapsedDark: Story = {
  render: () => setup('dark', true),
};

export const MenusOpen: Story = {
  render: () => setup('light', false),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement);
    const user = userEvent.setup();
    await user.click(canvas.getByTitle('Language'));
    await user.click(canvas.getByTitle('Notifications'));
    const userBtn = canvasElement.querySelector('.user-trigger') as HTMLElement;
    if (userBtn) {
      await user.click(userBtn);
    }
  },
};

// === Estados base ===
export const Default: Story = {
  name: '🏠 Estado Base',
  parameters: {
    docs: {
      description: {
        story: 'AppShell en estado expandido por defecto con navbar 56px y sidebar 264px.',
      },
    },
  },
  render: () => {
    localStorage.clear();
    localStorage.setItem('sidebar-collapsed', 'false');
    localStorage.setItem('theme', 'light');
    return { template: `<app-shell></app-shell>` };
  },
};

export const SidebarCollapsed: Story = {
  name: '📐 Sidebar Colapsado',
  parameters: {
    docs: {
      description: {
        story: 'AppShell con sidebar colapsado (72px). El chevron debe estar visible y rotado 180°.',
      },
    },
  },
  render: () => {
    localStorage.setItem('sidebar-collapsed', 'true');
    localStorage.setItem('theme', 'light');
    return { template: `<app-shell></app-shell>` };
  },
};

// === Temas ===
export const DarkTheme: Story = {
  name: '🌙 Tema Oscuro',
  parameters: {
    docs: {
      description: {
        story: 'AppShell con tema oscuro activado. Solo cambian colores, manteniendo dimensiones exactas.',
      },
    },
  },
  render: () => {
    localStorage.setItem('theme', 'dark');
    localStorage.setItem('sidebar-collapsed', 'false');
    document.body.setAttribute('data-theme', 'dark');
    return { template: `<app-shell></app-shell>` };
  },
};

export const DarkCollapsed: Story = {
  name: '🌙📐 Oscuro + Colapsado',
  parameters: {
    docs: {
      description: {
        story: 'Combinación de tema oscuro con sidebar colapsado.',
      },
    },
  },
  render: () => {
    localStorage.setItem('theme', 'dark');
    localStorage.setItem('sidebar-collapsed', 'true');
    document.body.setAttribute('data-theme', 'dark');
    return { template: `<app-shell></app-shell>` };
  },
};

// === Idiomas ===
export const EnglishLanguage: Story = {
  name: '🇬🇧 Idioma Inglés',
  decorators: [
    applicationConfig({
      providers: [
        { provide: TranslationService, useValue: { 
          currentLanguage: () => 'en', 
          setLanguage: () => {}, 
          get: (key: string) => key.replace('.', ' ').toUpperCase(),
          instant: (key: string) => key.replace('.', ' ').toUpperCase()
        }},
      ],
    }),
  ],
  render: () => {
    localStorage.setItem('language', 'en');
    return { template: `<app-shell></app-shell>` };
  },
};

export const GermanLanguage: Story = {
  name: '🇩🇪 Idioma Alemán',
  decorators: [
    applicationConfig({
      providers: [
        { provide: TranslationService, useValue: { 
          currentLanguage: () => 'de', 
          setLanguage: () => {}, 
          get: (key: string) => key.replace('.', ' ').toUpperCase() + ' (DE)',
          instant: (key: string) => key.replace('.', ' ').toUpperCase() + ' (DE)'
        }},
      ],
    }),
  ],
  render: () => {
    localStorage.setItem('language', 'de');
    return { template: `<app-shell></app-shell>` };
  },
};

// === Estados de entorno ===
export const StagingEnvironment: Story = {
  name: '🚧 Entorno Staging',
  parameters: {
    docs: {
      description: {
        story: 'AppShell con badge de entorno visible (no production). Badge fijo abajo-derecha.',
      },
    },
  },
  decorators: [
    applicationConfig({
      providers: [
        { provide: EnvironmentService, useValue: { isProduction: () => false, envName: () => 'staging' } },
      ],
    }),
  ],
  render: () => ({ template: `<app-shell></app-shell>` }),
};

export const DevelopmentEnvironment: Story = {
  name: '🔧 Entorno Desarrollo',
  decorators: [
    applicationConfig({
      providers: [
        { provide: EnvironmentService, useValue: { isProduction: () => false, envName: () => 'development' } },
      ],
    }),
  ],
  render: () => ({ template: `<app-shell></app-shell>` }),
};

// === Estados interactivos ===
export const WithNotifications: Story = {
  name: '🔔 Con Notificaciones',
  parameters: {
    docs: {
      description: {
        story: 'AppShell con notificaciones activas. Badge dinámico en campana.',
      },
    },
  },
  decorators: [
    applicationConfig({
      providers: [
        { 
          provide: UiStore, 
          useValue: {
            ...new MockUiStore(),
            unreadNotificationsCount: () => 3,
            initializeTheme: () => {}
          }
        },
      ],
    }),
  ],
  render: () => ({ template: `<app-shell></app-shell>` }),
};

export const NoNotifications: Story = {
  name: '🔕 Sin Notificaciones',
  parameters: {
    docs: {
      description: {
        story: 'AppShell sin notificaciones pendientes. No badge visible.',
      },
    },
  },
  decorators: [
    applicationConfig({
      providers: [
        { 
          provide: UiStore, 
          useValue: {
            ...new MockUiStore(),
            unreadNotificationsCount: () => 0,
            initializeTheme: () => {}
          }
        },
      ],
    }),
  ],
  render: () => ({ template: `<app-shell></app-shell>` }),
};

// === Usuario y datos ===
export const DifferentUser: Story = {
  name: '👤 Usuario Diferente',
  decorators: [
    applicationConfig({
      providers: [
        { 
          provide: AuthV5Service, 
          useValue: { 
            isAuthenticated: () => true, 
            currentSchool: () => ({ name: 'Escuela Premium' }),
            currentSchoolIdSignal: () => signal(2),
            user: () => ({ name: 'María García', role: 'Gerente', email: 'maria@premium.com' })
          }
        },
      ],
    }),
  ],
  render: () => ({ template: `<app-shell></app-shell>` }),
};

// === Estados responsive (simulado) ===
export const MobileView: Story = {
  name: '📱 Vista Mobile',
  parameters: {
    viewport: { defaultViewport: 'mobile1' },
    docs: {
      description: {
        story: 'AppShell en resolución móvil. Sidebar oculto por defecto, navbar adaptado.',
      },
    },
  },
  render: () => ({ template: `<app-shell></app-shell>` }),
};

// === Combinaciones complejas ===
export const AllInteractionsOpen: Story = {
  name: '🎭 Todas las Interacciones',
  parameters: {
    docs: {
      description: {
        story: 'Story para testing manual: tema oscuro, colapsado, notificaciones, entorno staging.',
      },
    },
  },
  decorators: [
    applicationConfig({
      providers: [
        { provide: EnvironmentService, useValue: { isProduction: () => false, envName: () => 'staging' } },
        { 
          provide: UiStore, 
          useValue: {
            ...new MockUiStore(),
            theme: () => 'dark',
            sidebarCollapsed: () => true,
            unreadNotificationsCount: () => 5,
            initializeTheme: () => {}
          }
        },
      ],
    }),
  ],
  render: () => {
    localStorage.setItem('theme', 'dark');
    localStorage.setItem('sidebar-collapsed', 'true');
    document.body.setAttribute('data-theme', 'dark');
    return { template: `<app-shell></app-shell>` };
  },
};
