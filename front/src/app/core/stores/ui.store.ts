import { Injectable, signal, computed } from '@angular/core';

export type Theme = 'light' | 'dark' | 'system';

// Helper to get system theme preference
function getSystemTheme(): 'light' | 'dark' {
  if (typeof window === 'undefined') return 'light';
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

// Helper to get stored theme with fallback
function getStoredTheme(): Theme {
  if (typeof localStorage === 'undefined') return 'system';
  return (localStorage.getItem('theme') as Theme) ?? 'system';
}

// Helper to get stored sidebar state with fallback
function getStoredSidebarState(): boolean {
  if (typeof localStorage === 'undefined') return false;
  const stored = localStorage.getItem('sidebarCollapsed');
  return stored === 'true';
}

// Helper to get effective theme (resolve 'system' to actual theme)
function getEffectiveTheme(theme: Theme): 'light' | 'dark' {
  return theme === 'system' ? getSystemTheme() : theme;
}

@Injectable({ providedIn: 'root' })
export class UiStore {
  // Private signals
  private readonly _sidebarCollapsed = signal(getStoredSidebarState());
  private readonly _theme = signal<Theme>(getStoredTheme());

  // Public readonly signals
  readonly sidebarCollapsed = this._sidebarCollapsed.asReadonly();
  readonly theme = this._theme.asReadonly();

  // Computed signals
  readonly effectiveTheme = computed(() => getEffectiveTheme(this._theme()));
  readonly isDark = computed(() => getEffectiveTheme(this._theme()) === 'dark');
  readonly sidebarIcon = computed(() => (this._sidebarCollapsed() ? 'panel-right' : 'panel-left'));

  // Methods
  toggleSidebar(): void {
    const newState = !this._sidebarCollapsed();
    this._sidebarCollapsed.set(newState);
    // Persist sidebar state
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('sidebarCollapsed', String(newState));
    }
  }

  setSidebarCollapsed(collapsed: boolean): void {
    this._sidebarCollapsed.set(collapsed);
    // Persist sidebar state
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('sidebarCollapsed', String(collapsed));
    }
  }

  setTheme(theme: Theme): void {
    this._theme.set(theme);

    // Persist theme preference
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('theme', theme);
    }

    // Apply theme to document
    if (typeof document !== 'undefined') {
      const effectiveTheme = getEffectiveTheme(theme);
      document.documentElement.dataset['theme'] = effectiveTheme;
    }
  }

  toggleTheme(): void {
    const currentTheme = this._theme();
    const nextTheme: Theme =
      currentTheme === 'light' ? 'dark' : currentTheme === 'dark' ? 'system' : 'light';
    this.setTheme(nextTheme);
  }

  // Initialize theme on app start
  initializeTheme(): void {
    const theme = this._theme();
    if (typeof document !== 'undefined') {
      const effectiveTheme = getEffectiveTheme(theme);
      document.documentElement.dataset['theme'] = effectiveTheme;

      // Listen for system theme changes if using system preference
      if (theme === 'system') {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const updateSystemTheme = () => {
          if (this._theme() === 'system') {
            document.documentElement.dataset['theme'] = mediaQuery.matches ? 'dark' : 'light';
          }
        };

        mediaQuery.addEventListener('change', updateSystemTheme);
      }
    }
  }
}
