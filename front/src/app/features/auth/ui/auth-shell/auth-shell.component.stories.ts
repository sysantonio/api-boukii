import type { Meta, StoryObj } from '@storybook/angular';
import { applicationConfig, moduleMetadata } from '@storybook/angular';
import { CommonModule } from '@angular/common';
import { provideRouter } from '@angular/router';

import { AuthShellComponent } from './auth-shell.component';
import { TranslationService } from '../../../../core/services/translation.service';

const mockTranslationService = {
  currentLanguage: () => 'es' as const,
  setLanguage: (lang: string) => console.log('Setting language to:', lang),
  get: (key: string) => {
    const translations: Record<string, string> = {
      'language.toggle': 'Cambiar idioma',
      'language.spanish': 'Español',
      'language.english': 'Inglés', 
      'language.french': 'Francés'
    };
    return translations[key] || key;
  },
  instant: (key: string) => {
    const translations: Record<string, string> = {
      'language.toggle': 'Cambiar idioma',
      'language.spanish': 'Español',
      'language.english': 'Inglés',
      'language.french': 'Francés'
    };
    return translations[key] || key;
  }
};

const mockFeatures = [
  {
    icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/></svg>`,
    title: 'Suite Completa',
    subtitle: 'Todo lo que necesitas para tu escuela deportiva'
  },
  {
    icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>`,
    title: 'Analytics Avanzados',
    subtitle: 'Métricas e informes en tiempo real'
  },
  {
    icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>`,
    title: 'Seguridad Total',
    subtitle: 'Protección de datos y acceso controlado'
  }
];

const meta: Meta<AuthShellComponent> = {
  title: 'Features/Auth/UI/AuthShell',
  component: AuthShellComponent,
  decorators: [
    applicationConfig({
      providers: [
        provideRouter([]),
      ],
    }),
    moduleMetadata({
      imports: [CommonModule],
      providers: [
        { provide: TranslationService, useValue: mockTranslationService }
      ],
    }),
  ],
  parameters: {
    layout: 'fullscreen',
    docs: {
      description: {
        component: 'Auth Shell component that provides a unified layout for login, register, and forgot password pages with feature showcase and language selection.'
      }
    }
  },
  argTypes: {
    title: {
      control: 'text',
      description: 'Main title displayed in the hero section'
    },
    subtitle: {
      control: 'text',
      description: 'Subtitle displayed below the title'
    },
    brandLine: {
      control: 'text',
      description: 'Brand line describing the service value proposition'
    },
    features: {
      control: 'object',
      description: 'Array of features to display with icons, titles, and descriptions'
    },
    footerLinks: {
      control: 'object',
      description: 'Array of footer links with labels and router links'
    }
  },
};

export default meta;
type Story = StoryObj<AuthShellComponent>;

// Login story
export const Login: Story = {
  args: {
    title: 'Bienvenido de vuelta',
    subtitle: 'Accede a tu panel de administración de Boukii V5',
    brandLine: 'Gestiona tu escuela deportiva de forma profesional',
    features: mockFeatures,
    footerLinks: [
      { label: '¿Olvidaste tu contraseña?', routerLink: '/auth/forgot-password' },
      { label: 'Crear cuenta', routerLink: '/auth/register' }
    ]
  },
  render: (args) => ({
    props: args,
    template: `
      <bk-auth-shell 
        [title]="title" 
        [subtitle]="subtitle" 
        [brandLine]="brandLine"
        [features]="features"
        [footerLinks]="footerLinks">
        
        <div class="card-header">
          <h1 class="card-title">Iniciar Sesión</h1>
          <p class="card-subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        <form>
          <label class="field">
            <span>Email</span>
            <input type="email" placeholder="usuario@example.com" />
          </label>

          <label class="field">
            <span>Contraseña</span>
            <div class="password-wrapper">
              <input type="password" placeholder="••••••••" />
            </div>
          </label>

          <div class="card-actions">
            <button class="btn btn--primary" type="submit">
              Iniciar Sesión
            </button>
          </div>
        </form>
      </bk-auth-shell>
    `,
  }),
};

// Register story
export const Register: Story = {
  args: {
    title: 'Únete a Boukii V5',
    subtitle: 'Gestiona tu escuela deportiva de manera profesional y moderna',
    brandLine: 'Gestiona tu escuela deportiva de forma profesional',
    features: mockFeatures,
    footerLinks: [
      { label: '¿Ya tienes cuenta?', routerLink: '/auth/login' }
    ]
  },
  render: (args) => ({
    props: args,
    template: `
      <bk-auth-shell 
        [title]="title" 
        [subtitle]="subtitle" 
        [brandLine]="brandLine"
        [features]="features"
        [footerLinks]="footerLinks">
        
        <div class="card-header">
          <h1 class="card-title">Crear cuenta</h1>
          <p class="card-subtitle">Únete a Boukii V5</p>
        </div>

        <form>
          <label class="field">
            <span>Nombre</span>
            <input type="text" placeholder="Tu nombre completo" />
          </label>

          <label class="field">
            <span>Email</span>
            <input type="email" placeholder="usuario@example.com" />
          </label>

          <label class="field">
            <span>Contraseña</span>
            <div class="password-wrapper">
              <input type="password" placeholder="••••••••" />
            </div>
          </label>

          <label class="field">
            <span>Confirmar Contraseña</span>
            <div class="password-wrapper">
              <input type="password" placeholder="••••••••" />
            </div>
          </label>

          <div class="card-actions">
            <button class="btn btn--primary" type="submit">
              Crear Cuenta
            </button>
          </div>
        </form>
      </bk-auth-shell>
    `,
  }),
};

// Forgot Password story
export const ForgotPassword: Story = {
  args: {
    title: 'Recupera Tu Acceso',
    subtitle: 'Te ayudamos a recuperar el acceso a tu cuenta de forma segura y rápida',
    brandLine: 'Gestiona tu escuela deportiva de forma profesional',
    features: [
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>`,
        title: 'Proceso Seguro',
        subtitle: 'Utilizamos encriptación de alta seguridad para proteger tu información'
      },
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>`,
        title: 'Envío Instantáneo',
        subtitle: 'Recibirás el enlace de recuperación en tu email en segundos'
      },
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M15 1H9v2h6V1zm-4 13h2V8h-2v6zm8.03-6.61l1.42-1.42c-.43-.51-.9-.99-1.41-1.41l-1.42 1.42A8.962 8.962 0 0012 4c-4.97 0-9 4.03-9 9s4.02 9 9 9a8.994 8.994 0 007.03-14.61z"/></svg>`,
        title: 'Acceso Rápido',
        subtitle: 'Recupera el acceso a tu cuenta en menos de 2 minutos'
      }
    ],
    footerLinks: [
      { label: 'Volver al login', routerLink: '/auth/login' }
    ]
  },
  render: (args) => ({
    props: args,
    template: `
      <bk-auth-shell 
        [title]="title" 
        [subtitle]="subtitle" 
        [brandLine]="brandLine"
        [features]="features"
        [footerLinks]="footerLinks">
        
        <div class="card-header">
          <h1 class="card-title">Recuperar contraseña</h1>
          <p class="card-subtitle">Te enviaremos un enlace de recuperación a tu email</p>
        </div>

        <form>
          <label class="field">
            <span>Email</span>
            <input type="email" placeholder="usuario@example.com" />
          </label>

          <div class="card-actions">
            <button class="btn btn--primary" type="submit">
              Enviar Enlace
            </button>
          </div>
        </form>
      </bk-auth-shell>
    `,
  }),
};

// Empty state (minimal)
export const EmptyState: Story = {
  args: {
    title: 'Título Básico',
    subtitle: 'Subtítulo opcional',
    brandLine: 'Línea de marca',
    features: undefined,
    footerLinks: undefined
  },
  render: (args) => ({
    props: args,
    template: `
      <bk-auth-shell 
        [title]="title" 
        [subtitle]="subtitle" 
        [brandLine]="brandLine"
        [features]="features"
        [footerLinks]="footerLinks">
        
        <div class="card-header">
          <h1 class="card-title">Contenido de Ejemplo</h1>
          <p class="card-subtitle">Sin características ni enlaces del footer</p>
        </div>

        <div style="padding: 24px; text-align: center; color: var(--text-2);">
          <p>Contenido básico del formulario</p>
        </div>
      </bk-auth-shell>
    `,
  }),
};

// With many features
export const WithManyFeatures: Story = {
  args: {
    title: 'Plataforma Completa',
    subtitle: 'Todas las herramientas que necesitas para gestionar tu escuela deportiva',
    brandLine: 'La solución más completa para escuelas deportivas modernas',
    features: [
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/></svg>`,
        title: 'Suite Completa',
        subtitle: 'Todo lo que necesitas en una sola plataforma'
      },
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>`,
        title: 'Analytics Avanzados',
        subtitle: 'Métricas detalladas de tu negocio'
      },
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>`,
        title: 'Seguridad Avanzada',
        subtitle: 'Protección máxima para tus datos'
      },
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>`,
        title: 'Planificación',
        subtitle: 'Organiza horarios y reservas fácilmente'
      },
      {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>`,
        title: 'Crecimiento',
        subtitle: 'Herramientas para hacer crecer tu negocio'
      }
    ],
    footerLinks: [
      { label: 'Términos y condiciones', routerLink: '/terms' },
      { label: 'Política de privacidad', routerLink: '/privacy' },
      { label: 'Soporte', routerLink: '/support' }
    ]
  },
  render: (args) => ({
    props: args,
    template: `
      <bk-auth-shell 
        [title]="title" 
        [subtitle]="subtitle" 
        [brandLine]="brandLine"
        [features]="features"
        [footerLinks]="footerLinks">
        
        <div class="card-header">
          <h1 class="card-title">Muchas Características</h1>
          <p class="card-subtitle">Ejemplo con múltiples características y enlaces</p>
        </div>

        <div style="padding: 24px; text-align: center; color: var(--text-2);">
          <p>Contenido del formulario con muchas características en el lateral</p>
        </div>
      </bk-auth-shell>
    `,
  }),
};