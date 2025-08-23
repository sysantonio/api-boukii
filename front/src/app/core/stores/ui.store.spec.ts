import { UiStore } from './ui.store';

describe('UiStore theme persistence', () => {
  beforeEach(() => {
    localStorage.clear();
    delete (document.body.dataset as any).theme;
  });

  it('toggleTheme persists changes', () => {
    const store = new UiStore();
    store.toggleTheme();
    expect(localStorage.getItem('theme')).toBe('dark');
    expect((document.body.dataset as any).theme).toBe('dark');
  });

  it('initTheme applies stored theme', () => {
    localStorage.setItem('theme', 'dark');
    const store = new UiStore();
    store.initTheme();
    expect((document.body.dataset as any).theme).toBe('dark');
  });
});
