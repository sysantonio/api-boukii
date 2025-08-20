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

class MockTranslationService {
  private langSignal = signal<SupportedLanguage>('es');
  currentLanguage = computed(() => this.langSignal());
  setLanguage(lang: SupportedLanguage) { this.langSignal.set(lang); }
  get(key: string) { return key; }
  instant(key: string) { return key; }
}

class MockAuthV5Service {
  user = computed(() => ({ name: 'Usuario Demo' }));
  isAuthenticated() { return true; }
  currentSchool() { return { id: 1, name: 'Escuela Demo' }; }
  currentSchoolIdSignal() { return signal(1); }
}

class MockUiStore {
  private themeSignal = signal<'light' | 'dark'>('light');
  private collapsedSignal = signal(false);
  theme = computed(() => this.themeSignal());
  isDark = computed(() => this.themeSignal() === 'dark');
  sidebarCollapsed = computed(() => this.collapsedSignal());
  initializeTheme() { document.body.dataset.theme = this.themeSignal(); }
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
        { provide: AuthV5Service, useClass: MockAuthV5Service },
        { provide: TranslationService, useClass: MockTranslationService },
        { provide: EnvironmentService, useValue: { envName: () => 'production', isProduction: () => true } },
      ],
    }),
  ],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type Story = StoryObj<AppShellComponent>;

function setup(theme: 'light' | 'dark', collapsed: boolean) {
  localStorage.setItem('theme', theme);
  localStorage.setItem('sidebarCollapsed', JSON.stringify(collapsed));
  return { template: `<app-shell></app-shell>` };
}

export const LightExpanded: Story = {
  render: () => setup('light', false),
};

export const LightCollapsed: Story = {
  render: () => setup('light', true),
};

export const DarkCollapsed: Story = {
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

export const StagingBadge: Story = {
  decorators: [
    applicationConfig({
      providers: [
        { provide: EnvironmentService, useValue: { envName: () => 'staging', isProduction: () => false } },
      ],
    }),
  ],
  render: () => setup('light', false),
};

