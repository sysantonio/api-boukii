import { Injectable, signal, computed } from '@angular/core';

export type Theme = 'light' | 'dark';

// Helper to get stored sidebar state with fallback
function getStoredSidebarState(): boolean {
  if (typeof localStorage === 'undefined') return false;
  const stored = localStorage.getItem('sidebarCollapsed');
  return stored === 'true';
}

@Injectable({ providedIn: 'root' })
export class UiStore {
  // Private signals
  private readonly _sidebarCollapsed = signal(getStoredSidebarState());
  private readonly _theme = signal<Theme>('light');

  // Public readonly signals
  readonly sidebarCollapsed = this._sidebarCollapsed.asReadonly();
  readonly theme = this._theme.asReadonly();

  // Computed signals
  readonly effectiveTheme = computed(() => this._theme());
  readonly isDark = computed(() => this._theme() === 'dark');
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
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = theme;
    }
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('theme', theme);
    }
  }

  toggleTheme(): void {
    const next = this._theme() === 'light' ? 'dark' : 'light';
    this.setTheme(next);
  }

  initTheme(): void {
    const v =
      (typeof localStorage !== 'undefined' && (localStorage.getItem('theme') as Theme)) ||
      'light';
    this._theme.set(v);
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = v;
    }
  }
}
