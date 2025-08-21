import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RouterTestingModule } from '@angular/router/testing';
import { APP_BASE_HREF } from '@angular/common';

import { RegisterPage } from './register.page';
import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';

describe('RegisterPage', () => {
  let component: RegisterPage;
  let fixture: ComponentFixture<RegisterPage>;
  let mockAuth: jest.Mocked<AuthV5Service>;
  let mockTranslation: jest.Mocked<TranslationService>;

  beforeEach(async () => {
    const authSpy: Partial<AuthV5Service> = {
      isAuthenticated: (() => false) as any,
      register: jest.fn()
    };
    const translationSpy: Partial<TranslationService> = {
      get: jest.fn((key: string) => key),
      currentLanguage: jest.fn().mockReturnValue('en')
    };
    const routerSpy = { navigate: jest.fn() };
    const toastSpy = { success: jest.fn(), error: jest.fn() };

    await TestBed.configureTestingModule({
      imports: [RegisterPage, RouterTestingModule.withRoutes([])],
      providers: [
        { provide: AuthV5Service, useValue: authSpy },
        { provide: TranslationService, useValue: translationSpy },
        { provide: Router, useValue: routerSpy },
        { provide: ToastService, useValue: toastSpy },
        { provide: APP_BASE_HREF, useValue: '/' }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(RegisterPage);
    component = fixture.componentInstance;
    mockAuth = TestBed.inject(AuthV5Service) as jest.Mocked<AuthV5Service>;
    mockTranslation = TestBed.inject(TranslationService) as jest.Mocked<TranslationService>;
    fixture.detectChanges();
  });

  it('should show required field errors', () => {
    component.registerForm.get('email')?.markAsTouched();
    component.registerForm.get('password')?.markAsTouched();
    component.registerForm.get('confirmPassword')?.markAsTouched();

    const emailError = component.getFieldError('email');
    const passwordError = component.getFieldError('password');
    const confirmError = component.getFieldError('confirmPassword');

    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.requiredEmail');
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.requiredPassword');
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.requiredConfirmPassword');
    expect(emailError).toBe('auth.errors.requiredEmail');
    expect(passwordError).toBe('auth.errors.requiredPassword');
    expect(confirmError).toBe('auth.errors.requiredConfirmPassword');
  });

  it('should validate email format', () => {
    component.registerForm.patchValue({ email: 'invalid' });
    component.registerForm.get('email')?.markAsTouched();

    const error = component.getFieldError('email');

    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.invalidEmail');
    expect(error).toBe('auth.errors.invalidEmail');
  });

  it('should show password mismatch error', () => {
    component.registerForm.patchValue({ password: 'abc123', confirmPassword: 'xyz789' });
    component.registerForm.get('password')?.markAsTouched();
    component.registerForm.get('confirmPassword')?.markAsTouched();
    component.registerForm.updateValueAndValidity();

    const error = component.getPasswordMatchError();

    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.passwordsNoMatch');
    expect(error).toBe('auth.errors.passwordsNoMatch');
  });

  it('should enable submit button only when form valid', () => {
    const button: HTMLButtonElement = fixture.nativeElement.querySelector('button[type="submit"]');
    expect(button.disabled).toBe(true);

    component.registerForm.patchValue({
      name: 'Test',
      email: 'test@example.com',
      password: 'abcdef',
      confirmPassword: 'abcdef'
    });
    fixture.detectChanges();

    expect(button.disabled).toBe(false);
  });

  it('should use translation keys for rendering', () => {
    mockTranslation.get.mockClear();
    fixture.detectChanges();
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.common.signup');
  });
});

