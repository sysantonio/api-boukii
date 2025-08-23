import { expect } from '@jest/globals';
import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RegisterPage } from './register.page';
import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';

class MockTranslationService {
  instant(k: string) { return k; }
  currentLanguage() { return 'en'; }
  get(k: string) { return k; }
}

describe('RegisterPage', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RegisterPage],
      providers: [
        { provide: TranslationService, useClass: MockTranslationService },
         { provide: AuthV5Service, useValue: { isAuthenticated: () => false } },
        { provide: Router, useValue: {} },
        { provide: ToastService, useValue: {} }
      ]
    }).compileComponents();
  });

  it('renders translated title and form', () => {
    const fixture = TestBed.createComponent(RegisterPage);
    fixture.detectChanges();
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('h1')?.textContent).toContain('auth.register.title');
    expect(compiled.querySelector('form')).toBeTruthy();
  });
});
