import { UiStore } from './ui.store';

describe('UiStore theme persistence', () => {
  beforeEach(() => {
    localStorage.clear();
    delete (document.documentElement.dataset as any).theme;
  });

  it('setTheme persists to localStorage and dataset.theme', () => {
    const store = new UiStore();
    store.setTheme('dark');
    expect(localStorage.getItem('theme')).toBe('dark');
    expect((document.documentElement.dataset as any).theme).toBe('dark');
  });

  it('toggleTheme persists changes', () => {
    const store = new UiStore();
    store.setTheme('light');
    store.toggleTheme();
    expect(localStorage.getItem('theme')).toBe('dark');
    expect((document.documentElement.dataset as any).theme).toBe('dark');
  });

  it('initializeTheme applies stored theme', () => {
    localStorage.setItem('theme', 'dark');
    const store = new UiStore();
    store.initializeTheme();
    expect((document.documentElement.dataset as any).theme).toBe('dark');
  });
});
