import { Injectable, signal, computed } from '@angular/core';

export type Theme = 'light' | 'dark';


@Injectable({ providedIn: 'root' })
export class UiStore {
  // Signals
  private readonly _sidebarCollapsed = signal(getStoredSidebarState());

  // Public theme signal
  readonly theme = signal<Theme>('light');

  // Public readonly signals
  readonly sidebarCollapsed = this._sidebarCollapsed.asReadonly();

  // Computed signals
  readonly effectiveTheme = computed(() => this.theme());
  readonly isDark = computed(() => this.theme() === 'dark');
  readonly sidebarIcon = computed(() => (this._sidebarCollapsed() ? 'panel-right' : 'panel-left'));

  // Methods
  initFromStorage(): void {
    if (typeof localStorage === 'undefined') return;
    const stored = localStorage.getItem('sidebar-collapsed');
    this._sidebarCollapsed.set(stored === 'true');
  }

  toggleSidebar(): void {
    const newState = !this._sidebarCollapsed();
    this._sidebarCollapsed.set(newState);
    // Persist sidebar state
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('sidebar-collapsed', String(newState));
    }
  }

  setTheme(theme: Theme): void {
    this.theme.set(theme);
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = theme;
    }
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('theme', theme);
    }
  }

  toggleTheme(): void {
    this.theme.update((v) => (v === 'light' ? 'dark' : 'light'));
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = this.theme();
    }
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('theme', this.theme());
    }
  }

  initTheme(): void {
    const v =
      (typeof localStorage !== 'undefined' && (localStorage.getItem('theme') as Theme)) || 'light';
    this.theme.set(v);
    if (typeof document !== 'undefined') {
      document.body.dataset.theme = v;
    }
  }
}

function getStoredSidebarState(): boolean {
  if (typeof localStorage === 'undefined') return false;
  return localStorage.getItem('sidebar-collapsed') === 'true';
}
