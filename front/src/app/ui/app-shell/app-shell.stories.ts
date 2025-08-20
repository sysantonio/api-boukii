import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata, applicationConfig } from '@storybook/angular';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideRouter } from '@angular/router';

import { AppShellComponent } from './app-shell.component';
import { UiStore } from '@core/stores/ui.store';
import { AuthStore } from '@core/stores/auth.store';
import { LoadingStore } from '@core/stores/loading.store';
import { AuthV5Service } from '@core/services/auth-v5.service';
import { TranslationService, SupportedLanguage } from '@core/services/translation.service';
import { EnvironmentService } from '@core/services/environment.service';

class MockTranslationService {
  private lang: SupportedLanguage = 'es';

  currentLanguage(): SupportedLanguage {
    return this.lang;
  }

  setLanguage(lang: SupportedLanguage): void {
    this.lang = lang;
  }

  get(key: string): string {
    return key;
  }

  instant(key: string): string {
    return key;
  }
}

const meta: Meta<AppShellComponent> = {
  title: 'App/AppShell',
  component: AppShellComponent,
  decorators: [
    moduleMetadata({
      imports: [AppShellComponent],
    }),
    applicationConfig({
      providers: [
        provideAnimations(),
        provideRouter([]),
        { provide: UiStore, useValue: { initializeTheme: () => {} } },
        { provide: AuthStore, useValue: { loadMe: () => {} } },
        { provide: LoadingStore, useValue: { isLoading: () => false, longestRunningRequest: () => null } },
        { provide: AuthV5Service, useValue: { isAuthenticated: () => true, currentSchool: () => ({}), currentSchoolIdSignal: () => 1, user: () => ({ name: 'Test User' }) } },
        { provide: TranslationService, useClass: MockTranslationService },
        { provide: EnvironmentService, useValue: { isProduction: () => true, envName: () => 'production' } },
      ],
    }),
  ],
  parameters: {
    layout: 'fullscreen',
  },
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<AppShellComponent>;

export const Default: Story = {
  render: () => {
    localStorage.clear();
    return { template: `<app-shell></app-shell>` };
  },
};

export const Collapsed: Story = {
  render: () => {
    localStorage.setItem('sidebar-collapsed', 'true');
    return { template: `<app-shell></app-shell>` };
  },
};

export const Dark: Story = {
  render: () => {
    localStorage.setItem('theme', 'dark');
    return { template: `<app-shell></app-shell>` };
  },
};

export const NonProdBadge: Story = {
  decorators: [
    applicationConfig({
      providers: [
        { provide: EnvironmentService, useValue: { isProduction: () => false, envName: () => 'staging' } },
      ],
    }),
  ],
  render: () => ({ template: `<app-shell></app-shell>` }),
};
