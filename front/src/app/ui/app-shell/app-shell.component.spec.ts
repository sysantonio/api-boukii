import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { screen } from '@testing-library/dom';
import userEvent from '@testing-library/user-event';
import { signal, computed } from '@angular/core';

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

class MockUiStore {
  sidebarCollapsed = signal(false);
  theme = signal<'light' | 'dark'>('light');
  isDark = computed(() => this.theme() === 'dark');

  initTheme(): void {
    (document.body.dataset as any).theme = this.theme();
  }
  toggleSidebar(): void {
    const newState = !this.sidebarCollapsed();
    this.sidebarCollapsed.set(newState);
    localStorage.setItem('sidebarCollapsed', String(newState));
  }
  toggleTheme(): void {
    const next = this.theme() === 'light' ? 'dark' : 'light';
    this.theme.set(next);
    localStorage.setItem('theme', next);
    (document.body.dataset as any).theme = next;
  }
}

describe('AppShellComponent interactions', () => {
  const logoutSpy = jest.fn();

  beforeEach(async () => {
    localStorage.clear();
    delete (document.body.dataset as any).theme;
    logoutSpy.mockReset();
    await TestBed.configureTestingModule({
      imports: [AppShellComponent],
      providers: [
        provideRouter([]),
        { provide: UiStore, useClass: MockUiStore },
        { provide: AuthStore, useValue: { loadMe: () => {} } },
        { provide: LoadingStore, useValue: { isLoading: () => false, longestRunningRequest: () => null } },
        {
          provide: AuthV5Service,
          useValue: {
            isAuthenticated: () => true,
            currentSchool: () => ({}),
            currentSchoolIdSignal: () => 1,
            user: () => ({ name: 'Test User' }),
            permissions: () => [],
            logout: logoutSpy,
          },
        },
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
    const button = screen.getByLabelText('sidebar.toggle');
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

  it('theme toggle persists selection and updates document dataset', async () => {
    const fixture = renderComponent();
    const user = userEvent.setup();
    const button = screen.getByTitle('theme.toggle');

    await user.click(button); // light -> dark
    fixture.detectChanges();
    expect(localStorage.getItem('theme')).toBe('dark');
    expect((document.body.dataset as any).theme).toBe('dark');

    await user.click(button); // dark -> light
    fixture.detectChanges();
    expect(localStorage.getItem('theme')).toBe('light');
    expect((document.body.dataset as any).theme).toBe('light');

    fixture.nativeElement.remove();
  });

  it('changes language via TranslationService and updates active option', async () => {
    const translation = TestBed.inject(TranslationService) as unknown as MockTranslationService;
    const setLangSpy = jest.spyOn(translation, 'setLanguage');
    const fixture = renderComponent();
    const user = userEvent.setup();

    const languageButton = screen.getByRole('button', { name: 'ES' });
    await user.click(languageButton);
    fixture.detectChanges();

    const enOption = screen.getByRole('menuitemradio', { name: 'language.en' });
    await user.click(enOption);
    fixture.detectChanges();

    expect(setLangSpy).toHaveBeenCalledWith('en');

    // reopen to check active state
    await user.click(languageButton);
    fixture.detectChanges();
    const enOptionActive = screen.getByRole('menuitemradio', { name: 'language.en' });
    const esOption = screen.getByRole('menuitemradio', { name: 'language.es' });
    expect(enOptionActive.classList).toContain('active');
    expect(enOptionActive.getAttribute('aria-checked')).toBe('true');
    expect(esOption.classList).not.toContain('active');
    fixture.nativeElement.remove();
  });

  it('shows notification badge or dot based on sidebar state', async () => {
    const fixture = renderComponent();
    const ui = TestBed.inject(UiStore) as unknown as MockUiStore;

    // sidebar expanded => number badge
    let badge = fixture.nativeElement.querySelector('.notification-badge');
    expect(badge?.textContent).toBe('2');
    expect(fixture.nativeElement.querySelector('.notification-dot')).toBeNull();

    // collapse sidebar => dot
    ui.toggleSidebar();
    fixture.detectChanges();
    expect(fixture.nativeElement.querySelector('.notification-badge')).toBeNull();
    expect(fixture.nativeElement.querySelector('.notification-dot')).not.toBeNull();

    fixture.nativeElement.remove();
  });

  it('invokes logout without confirmation dialog', async () => {
    const fixture = renderComponent();
    const user = userEvent.setup();

    // Open user dropdown
    const menuButton = screen.getByRole('button', { name: /Test User/ });
    await user.click(menuButton);
    fixture.detectChanges();

    const confirmSpy = jest.spyOn(window, 'confirm');

    const logoutButton = screen.getByRole('menuitem', { name: 'userMenu.logout' });
    await user.click(logoutButton);

    expect(confirmSpy).not.toHaveBeenCalled();
    expect(logoutSpy).toHaveBeenCalled();
    fixture.nativeElement.remove();
  });
});
