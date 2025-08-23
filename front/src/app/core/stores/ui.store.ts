import { Injectable, signal, computed } from '@angular/core';

export type Theme = 'light' | 'dark';

// Helper to get stored theme with fallback
function getStoredTheme(): Theme {
  if (typeof localStorage === 'undefined') return 'light';
  return (localStorage.getItem('theme') as Theme) ?? 'light';
}

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
  readonly theme = signal<Theme>(getStoredTheme());

  // Public readonly signals
  readonly sidebarCollapsed = this._sidebarCollapsed.asReadonly();
  readonly isDark = computed(() => this.theme() === 'dark');
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

  toggleTheme(): void {
    this.theme.update(v => (v === 'light' ? 'dark' : 'light'));
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = this.theme();
    }
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('theme', this.theme());
    }
  }

  initTheme(): void {
    const stored = getStoredTheme();
    this.theme.set(stored);
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = stored;
    }
  }
}
