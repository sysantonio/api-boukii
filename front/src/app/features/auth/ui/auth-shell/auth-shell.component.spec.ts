import { expect } from '@jest/globals';
jest.mock('../../../../core/services/translation.service', () => ({ TranslationService: class {} }));
import { TestBed } from '@angular/core/testing';
import { AuthShellComponent } from './auth-shell.component';
import { TranslationService } from '../../../../core/services/translation.service';

class MockTranslationService {
  instant(k: string) { return k; }
}

describe('AuthShellComponent', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [AuthShellComponent],
      providers: [{ provide: TranslationService, useClass: MockTranslationService }]
    });
  });

  it('renders inputs and projects auth form', () => {
    const fixture = TestBed.createComponent(AuthShellComponent);
    const component = fixture.componentInstance;
    component.brandLine = 'Brand';
    component.title = 'Title';
    component.subtitle = 'Subtitle';
    component.features = [
      { icon: 'a', title: 'A', subtitle: 'a' },
      { icon: 'b', title: 'B', subtitle: 'b' },
      { icon: 'c', title: 'C', subtitle: 'c' }
    ];

    const form = document.createElement('form');
    form.setAttribute('auth-form', '');
    form.setAttribute('data-testid', 'proj');
    fixture.nativeElement.appendChild(form);

    fixture.detectChanges();

    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('h1')?.textContent).toContain('Title');
    expect(compiled.querySelectorAll('.features li').length).toBe(3);
    expect(compiled.querySelector('[data-testid="proj"]')).toBeTruthy();
  });
});
