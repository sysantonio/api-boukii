import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { screen } from '@testing-library/dom';
import userEvent from '@testing-library/user-event';
import { OverlayContainer } from '@angular/cdk/overlay';
import { ThemeService } from '@core/services/theme.service';

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
        { provide: AuthV5Service, useValue: { isAuthenticated: () => true, currentSchool: () => ({}), currentSchoolIdSignal: () => 1, user: () => ({ name: 'Test User' }), permissions: () => [] } },
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

  it('chevron toggles collapsed class, rotates and persists', async () => {
    const fixture = renderComponent();
    const user = userEvent.setup();
    const button = screen.getByLabelText('Colapsar/Expandir');
    const shell = fixture.nativeElement.querySelector('.app-shell');
    expect(shell.classList).not.toContain('app-sidebar--collapsed');
    await user.click(button);
    fixture.detectChanges();
    expect(shell.classList).toContain('app-sidebar--collapsed');
    expect(fixture.nativeElement.querySelector('.chev')?.classList).toContain('rot');
    expect(localStorage.getItem('sidebarCollapsed')).toBe('true');
    // Click again to expand and ensure button remains focusable
    await user.click(button);
    fixture.detectChanges();
    expect(shell.classList).not.toContain('app-sidebar--collapsed');
    fixture.nativeElement.remove();
  });

  it('setTheme() propagates data-theme to OverlayContainer', async () => {
    const fixture = renderComponent();
    const overlay = TestBed.inject(OverlayContainer);
    const themeService = TestBed.inject(ThemeService);
    themeService.setTheme('dark');
    await Promise.resolve();
    expect(overlay.getContainerElement().getAttribute('data-theme')).toBe('dark');
    fixture.nativeElement.remove();
  });
});
