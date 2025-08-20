import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata, applicationConfig } from '@storybook/angular';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideRouter } from '@angular/router';
import { within, userEvent } from '@storybook/testing-library';

import { AppShellComponent } from './app-shell.component';
import { UiStore } from '@core/stores/ui.store';
import { AuthStore } from '@core/stores/auth.store';
import { LoadingStore } from '@core/stores/loading.store';
import { AuthV5Service } from '@core/services/auth-v5.service';
import { TranslationService, SupportedLanguage } from '@core/services/translation.service';
import { EnvironmentService } from '@core/services/environment.service';

class MockTranslationService {
  private lang: SupportedLanguage = 'es';
  currentLanguage(): SupportedLanguage { return this.lang; }
  setLanguage(lang: SupportedLanguage): void { this.lang = lang; }
  get(key: string): string { return key; }
  instant(key: string): string { return key; }
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
        { provide: UiStore, useValue: { initializeTheme: () => {} } },
        { provide: AuthStore, useValue: { loadMe: () => {} } },
        { provide: LoadingStore, useValue: { isLoading: () => false, longestRunningRequest: () => null } },
        { provide: AuthV5Service, useValue: { isAuthenticated: () => true, currentSchool: () => ({}), currentSchoolIdSignal: () => 1, user: () => ({ name: 'Test User' }), permissions: () => [] } },
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

