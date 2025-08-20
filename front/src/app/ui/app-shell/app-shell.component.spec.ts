import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { screen } from '@testing-library/dom';
import userEvent from '@testing-library/user-event';

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

describe('AppShellComponent interactions', () => {
  beforeEach(async () => {
    localStorage.clear();
    await TestBed.configureTestingModule({
      imports: [AppShellComponent],
      providers: [
        provideRouter([]),
        { provide: UiStore, useValue: { initializeTheme: () => {} } },
        { provide: AuthStore, useValue: { loadMe: () => {} } },
        { provide: LoadingStore, useValue: { isLoading: () => false, longestRunningRequest: () => null } },
        { provide: AuthV5Service, useValue: { isAuthenticated: () => true, currentSchool: () => ({}), currentSchoolIdSignal: () => 1, user: () => ({ name: 'Test User' }) } },
        { provide: TranslationService, useClass: MockTranslationService },
        { provide: EnvironmentService, useValue: { isProduction: () => true, envName: () => 'production' } },
      ],
    }).compileComponents();
  });

  function renderComponent() {
    const fixture = TestBed.createComponent(AppShellComponent);
    fixture.detectChanges();
    document.body.appendChild(fixture.nativeElement);
    return fixture;
  }

  it('toggleSidebar() persists in localStorage', async () => {
    const fixture = renderComponent();
    const user = userEvent.setup();
    const button = screen.getByLabelText('Collapse sidebar');
    await user.click(button);
    expect(localStorage.getItem('sidebar-collapsed')).toBe('true');
    fixture.nativeElement.remove();
  });

  it('toggleTheme() changes data-theme and persists', async () => {
    const fixture = renderComponent();
    const user = userEvent.setup();
    const themeButton = screen.getByTitle('Toggle theme');
    expect(document.documentElement.getAttribute('data-theme')).toBe('light');
    await user.click(themeButton);
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark');
    expect(localStorage.getItem('theme')).toBe('dark');
    fixture.nativeElement.remove();
  });
});
