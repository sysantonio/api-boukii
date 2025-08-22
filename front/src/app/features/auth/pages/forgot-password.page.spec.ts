import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RouterTestingModule } from '@angular/router/testing';
import { APP_BASE_HREF } from '@angular/common';

import { ForgotPasswordPage } from './forgot-password.page';
import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';

describe('ForgotPasswordPage', () => {
  let component: ForgotPasswordPage;
  let fixture: ComponentFixture<ForgotPasswordPage>;
  let _mockAuth: jest.Mocked<AuthV5Service>;
  let mockTranslation: jest.Mocked<TranslationService>;

  beforeEach(async () => {
    const authSpy: Partial<AuthV5Service> = {
      isAuthenticated: (() => false) as any,
      forgotPassword: jest.fn()
    };
    const translationSpy: Partial<TranslationService> = {
      get: jest.fn((key: string) => key),
      currentLanguage: jest.fn().mockReturnValue('en')
    };
    const routerSpy = { navigate: jest.fn() };
    const toastSpy = { success: jest.fn(), error: jest.fn() };

    await TestBed.configureTestingModule({
      imports: [ForgotPasswordPage, RouterTestingModule.withRoutes([])],
      providers: [
        { provide: AuthV5Service, useValue: authSpy },
        { provide: TranslationService, useValue: translationSpy },
        { provide: Router, useValue: routerSpy },
        { provide: ToastService, useValue: toastSpy },
        { provide: APP_BASE_HREF, useValue: '/' }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(ForgotPasswordPage);
    component = fixture.componentInstance;
    mockAuth = TestBed.inject(AuthV5Service) as jest.Mocked<AuthV5Service>;
    mockTranslation = TestBed.inject(TranslationService) as jest.Mocked<TranslationService>;
    fixture.detectChanges();
  });

  it('should show required email error', () => {
    component.form.get('email')?.markAsTouched();
    const error = component.getFieldError('email');
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.requiredEmail');
    expect(error).toBe('auth.errors.requiredEmail');
  });

  it('should validate email format', () => {
    component.form.patchValue({ email: 'invalid' });
    component.form.get('email')?.markAsTouched();
    const error = component.getFieldError('email');
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.invalidEmail');
    expect(error).toBe('auth.errors.invalidEmail');
  });

  it('should enable submit button only when form valid', () => {
    const button: HTMLButtonElement = fixture.nativeElement.querySelector('button[type="submit"]');
    expect(button.disabled).toBe(true);

    component.form.patchValue({ email: 'test@example.com' });
    fixture.detectChanges();

    expect(button.disabled).toBe(false);
  });

  it('should use translation keys for rendering', () => {
    mockTranslation.get.mockClear();
    fixture.detectChanges();
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.forgotPassword.cta');
  });
});

