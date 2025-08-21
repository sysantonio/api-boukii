import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RouterTestingModule } from '@angular/router/testing';
import { APP_BASE_HREF } from '@angular/common';

import { LoginPage } from './login.page';
import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';

describe('LoginPage', () => {
  let component: LoginPage;
  let fixture: ComponentFixture<LoginPage>;
  let _mockAuth: jest.Mocked<AuthV5Service>;
  let mockTranslation: jest.Mocked<TranslationService>;

  beforeEach(async () => {
    const authSpy: Partial<AuthV5Service> = {
      isAuthenticated: (() => false) as any,
      checkUser: jest.fn()
    };
    const translationSpy: Partial<TranslationService> = {
      get: jest.fn((key: string) => key),
      currentLanguage: jest.fn().mockReturnValue('en')
    };
    const routerSpy = { navigate: jest.fn() };
    const toastSpy = { success: jest.fn(), error: jest.fn() };

    await TestBed.configureTestingModule({
      imports: [LoginPage, RouterTestingModule.withRoutes([])],
      providers: [
        { provide: AuthV5Service, useValue: authSpy },
        { provide: TranslationService, useValue: translationSpy },
        { provide: Router, useValue: routerSpy },
        { provide: ToastService, useValue: toastSpy },
        { provide: APP_BASE_HREF, useValue: '/' }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(LoginPage);
    component = fixture.componentInstance;
    mockAuth = TestBed.inject(AuthV5Service) as jest.Mocked<AuthV5Service>;
    mockTranslation = TestBed.inject(TranslationService) as jest.Mocked<TranslationService>;
    fixture.detectChanges();
  });

  it('should show required field errors', () => {
    component.loginForm.get('email')?.markAsTouched();
    component.loginForm.get('password')?.markAsTouched();

    const emailError = component.getFieldError('email');
    const passwordError = component.getFieldError('password');

    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.requiredEmail');
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.requiredPassword');
    expect(emailError).toBe('auth.errors.requiredEmail');
    expect(passwordError).toBe('auth.errors.requiredPassword');
  });

  it('should validate email format', () => {
    component.loginForm.patchValue({ email: 'invalid' });
    component.loginForm.get('email')?.markAsTouched();

    const error = component.getFieldError('email');

    expect(mockTranslation.get).toHaveBeenCalledWith('auth.errors.invalidEmail');
    expect(error).toBe('auth.errors.invalidEmail');
  });

  it('should enable submit button only when form valid', () => {
    const button: HTMLButtonElement = fixture.nativeElement.querySelector('button[type="submit"]');
    expect(button.disabled).toBe(true);

    component.loginForm.patchValue({ email: 'test@example.com', password: 'abcdef' });
    fixture.detectChanges();

    expect(button.disabled).toBe(false);
  });

  it('should use translation keys for rendering', () => {
    mockTranslation.get.mockClear();
    fixture.detectChanges();
    expect(mockTranslation.get).toHaveBeenCalledWith('auth.common.signin');
  });
});

