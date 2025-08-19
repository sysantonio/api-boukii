import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatIconModule } from '@angular/material/icon';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';

// Custom validator for password confirmation
function passwordMatchValidator(control: AbstractControl): { [key: string]: boolean } | null {
  const password = control.get('password');
  const confirmPassword = control.get('confirmPassword');
  
  if (!password || !confirmPassword) {
    return null;
  }
  
  return password.value !== confirmPassword.value ? { passwordMismatch: true } : null;
}

@Component({
  selector: 'app-register-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatProgressSpinnerModule,
    MatIconModule,
    TranslatePipe
  ],
  template: `
    <div class="register-page">
      <div class="register-container">
        <!-- Left Side - Welcome -->
        <div class="welcome-side">
          <div class="welcome-content">
            <div class="brand-header">
              <div class="logo-container">
                <span class="logo-text">bouk<span class="logo-accent">ii</span></span>
                <span class="logo-version">V5</span>
              </div>
              <h1 class="welcome-title">{{ 'auth.register.welcome.title' | translate }}</h1>
              <p class="welcome-subtitle">{{ 'auth.register.welcome.subtitle' | translate }}</p>
            </div>
            
            <div class="features-showcase">
              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>dashboard</mat-icon>
                </div>
                <div class="feature-text">
                  <h3>{{ 'auth.register.features.management.title' | translate }}</h3>
                  <p>{{ 'auth.register.features.management.description' | translate }}</p>
                </div>
              </div>

              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>schedule</mat-icon>
                </div>
                <div class="feature-text">
                  <h3>{{ 'auth.register.features.seasons.title' | translate }}</h3>
                  <p>{{ 'auth.register.features.seasons.description' | translate }}</p>
                </div>
              </div>

              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>trending_up</mat-icon>
                </div>
                <div class="feature-text">
                  <h3>{{ 'auth.register.features.professional.title' | translate }}</h3>
                  <p>{{ 'auth.register.features.professional.description' | translate }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="form-side">
          <div class="form-content">
            <div class="form-header">
              <h2 class="form-title">{{ 'auth.register.title' | translate }}</h2>
              <p class="form-subtitle">{{ 'auth.register.subtitle' | translate }}</p>
            </div>

            <form [formGroup]="registerForm" (ngSubmit)="onSubmit()" class="auth-form" data-cy="register-form">
              <div class="form-group">
                <label class="form-label">{{ 'auth.name.label' | translate }}</label>
                <div class="input-wrapper">
                  <div class="input-icon">
                    <mat-icon>person</mat-icon>
                  </div>
                  <input 
                    type="text" 
                    formControlName="name"
                    [placeholder]="'auth.name.placeholder' | translate"
                    autocomplete="name"
                    class="form-input"
                    data-cy="name-input">
                </div>
                @if (registerForm.get('name')?.invalid && registerForm.get('name')?.touched) {
                  <div class="form-error">
                    @if (registerForm.get('name')?.errors?.['required']) {
                      {{ 'auth.name.required' | translate }}
                    }
                    @if (registerForm.get('name')?.errors?.['minlength']) {
                      {{ 'auth.name.minLength' | translate }}
                    }
                  </div>
                }
              </div>

              <div class="form-group">
                <label class="form-label">{{ 'auth.email.label' | translate }}</label>
                <div class="input-wrapper">
                  <div class="input-icon">
                    <mat-icon>email</mat-icon>
                  </div>
                  <input 
                    type="email" 
                    formControlName="email"
                    [placeholder]="'auth.email.placeholder' | translate"
                    autocomplete="email"
                    class="form-input"
                    data-cy="email-input">
                </div>
                @if (registerForm.get('email')?.invalid && registerForm.get('email')?.touched) {
                  <div class="form-error">
                    @if (registerForm.get('email')?.errors?.['required']) {
                      {{ 'auth.email.required' | translate }}
                    }
                    @if (registerForm.get('email')?.errors?.['email']) {
                      {{ 'auth.email.invalid' | translate }}
                    }
                  </div>
                }
              </div>

              <div class="form-group">
                <label class="form-label">{{ 'auth.password.label' | translate }}</label>
                <div class="input-wrapper">
                  <div class="input-icon">
                    <mat-icon>lock</mat-icon>
                  </div>
                  <input 
                    [type]="hidePassword() ? 'password' : 'text'" 
                    formControlName="password"
                    [placeholder]="'auth.password.placeholder' | translate"
                    autocomplete="new-password"
                    class="form-input"
                    data-cy="password-input">
                  <button 
                    type="button" 
                    class="password-toggle"
                    (click)="hidePassword.set(!hidePassword())"
                    data-cy="password-toggle">
                    <mat-icon>{{ hidePassword() ? 'visibility' : 'visibility_off' }}</mat-icon>
                  </button>
                </div>
                @if (registerForm.get('password')?.invalid && registerForm.get('password')?.touched) {
                  <div class="form-error">
                    @if (registerForm.get('password')?.errors?.['required']) {
                      {{ 'auth.password.required' | translate }}
                    }
                    @if (registerForm.get('password')?.errors?.['minlength']) {
                      {{ 'auth.password.minLength' | translate }}
                    }
                  </div>
                }
              </div>

              <div class="form-group">
                <label class="form-label">{{ 'auth.confirmPassword.label' | translate }}</label>
                <div class="input-wrapper">
                  <div class="input-icon">
                    <mat-icon>lock</mat-icon>
                  </div>
                  <input 
                    [type]="hideConfirmPassword() ? 'password' : 'text'" 
                    formControlName="confirmPassword"
                    [placeholder]="'auth.confirmPassword.placeholder' | translate"
                    autocomplete="new-password"
                    class="form-input"
                    data-cy="confirm-password-input">
                  <button 
                    type="button" 
                    class="password-toggle"
                    (click)="hideConfirmPassword.set(!hideConfirmPassword())">
                    <mat-icon>{{ hideConfirmPassword() ? 'visibility' : 'visibility_off' }}</mat-icon>
                  </button>
                </div>
                @if (registerForm.get('confirmPassword')?.invalid && registerForm.get('confirmPassword')?.touched) {
                  <div class="form-error">
                    @if (registerForm.get('confirmPassword')?.errors?.['required']) {
                      {{ 'auth.confirmPassword.required' | translate }}
                    }
                  </div>
                }
                @if (registerForm.errors?.['passwordMismatch'] && registerForm.get('confirmPassword')?.touched) {
                  <div class="form-error">
                    {{ 'auth.confirmPassword.mismatch' | translate }}
                  </div>
                }
              </div>

              <button 
                type="submit" 
                class="submit-button"
                [class.loading]="isLoading()"
                [disabled]="registerForm.invalid || isLoading()"
                data-cy="submit-button">
                @if (isLoading()) {
                  <div class="loading-spinner"></div>
                  {{ 'common.loading' | translate }}
                } @else {
                  {{ 'auth.register.button' | translate }}
                }
              </button>

              <div class="form-footer">
                <p class="form-link">
                  {{ 'auth.alreadyHaveAccount' | translate }}
                  <a routerLink="/auth/login" class="link">{{ 'auth.signIn' | translate }}</a>
                </p>
              </div>
            </form>
          </div>
        </div>

        <!-- Background Effects -->
        <div class="auth-background">
          <div class="gradient-orb orb-1"></div>
          <div class="gradient-orb orb-2"></div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .register-page {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: var(--gradient-primary);
      overflow: hidden;
      z-index: 1000;
    }

    .register-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      width: 100%;
      height: 100%;
    }

    /* === WELCOME SIDE === */
    .welcome-side {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--space-8);
      background: var(--gradient-primary);
      position: relative;
      overflow: hidden;
    }

    .welcome-side::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
      background-size: 40px 40px;
      animation: float 30s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(15px, -10px) rotate(1deg); }
      50% { transform: translate(0, 10px) rotate(0deg); }
      75% { transform: translate(-10px, -5px) rotate(-1deg); }
    }

    .welcome-content {
      position: relative;
      z-index: 2;
      max-width: 600px;
      width: 100%;
    }

    /* === BRAND HEADER === */
    .brand-header {
      margin-bottom: var(--space-8);
      text-align: center;
    }

    .logo-container {
      display: flex;
      align-items: baseline;
      justify-content: center;
      gap: var(--space-3);
      margin-bottom: var(--space-6);
    }

    .logo-text {
      font-size: 4rem;
      font-weight: var(--font-weight-bold);
      color: var(--color-white);
      font-family: var(--font-family-sans);
      letter-spacing: -0.02em;
      text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .logo-accent {
      color: var(--color-turquoise-400);
    }

    .logo-version {
      font-size: 1.5rem;
      font-weight: var(--font-weight-bold);
      color: var(--color-turquoise-400);
      background: rgba(34, 211, 238, 0.15);
      padding: var(--space-2) var(--space-4);
      border-radius: var(--radius-full);
      border: 2px solid rgba(34, 211, 238, 0.3);
      backdrop-filter: blur(10px);
    }

    .welcome-title {
      font-size: 2.5rem;
      font-weight: var(--font-weight-bold);
      color: var(--color-white);
      margin: 0 0 var(--space-3) 0;
      line-height: 1.1;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .welcome-subtitle {
      font-size: 1.25rem;
      color: rgba(255, 255, 255, 0.85);
      margin: 0;
      line-height: 1.4;
      font-weight: var(--font-weight-medium);
    }

    /* === FEATURES SHOWCASE === */
    .features-showcase {
      display: flex;
      flex-direction: column;
      gap: var(--space-4);
    }

    .feature-card {
      display: flex;
      align-items: center;
      gap: var(--space-4);
      padding: var(--space-4);
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px);
      border-radius: var(--radius-xl);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all var(--duration-normal) var(--ease-out);
    }

    .feature-card:hover {
      background: rgba(255, 255, 255, 0.12);
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .feature-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      background: rgba(34, 211, 238, 0.2);
      border-radius: var(--radius-lg);
      flex-shrink: 0;
    }

    .feature-icon mat-icon {
      color: var(--color-turquoise-400);
      font-size: 1.5rem;
      width: 1.5rem;
      height: 1.5rem;
    }

    .feature-text h3 {
      font-size: 1.125rem;
      font-weight: var(--font-weight-bold);
      color: var(--color-white);
      margin: 0 0 var(--space-1) 0;
    }

    .feature-text p {
      font-size: 0.875rem;
      color: rgba(255, 255, 255, 0.8);
      margin: 0;
      line-height: 1.4;
    }

    /* === FORM SIDE === */
    .form-side {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--space-8);
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .form-side::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background:
        radial-gradient(circle at 20% 80%, rgba(0, 212, 170, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(139, 92, 246, 0.02) 0%, transparent 50%);
      pointer-events: none;
    }

    .form-content {
      width: 100%;
      max-width: 450px;
      position: relative;
      z-index: 2;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
      border-radius: var(--radius-2xl);
      padding: var(--space-8);
      box-shadow:
        0 10px 25px rgba(0, 0, 0, 0.08),
        0 4px 10px rgba(0, 0, 0, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.8);
    }

    .form-header {
      text-align: center;
      margin-bottom: var(--space-8);
    }

    .form-title {
      font-size: 2rem;
      font-weight: var(--font-weight-bold);
      color: var(--color-text-primary);
      margin: 0 0 var(--space-2) 0;
      line-height: 1.2;
    }

    .form-subtitle {
      font-size: 1.125rem;
      color: var(--color-text-secondary);
      margin: 0;
      font-weight: var(--font-weight-medium);
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: var(--space-6);
    }

    /* === FORM GROUPS === */
    .form-group {
      display: flex;
      flex-direction: column;
      gap: var(--space-3);
    }

    .form-label {
      font-size: 1rem;
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
      margin: 0;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: var(--space-4);
      z-index: 2;
      color: var(--color-text-muted);
      display: flex;
      align-items: center;
    }

    .input-icon mat-icon {
      font-size: 1.25rem;
      width: 1.25rem;
      height: 1.25rem;
    }

    .form-input {
      width: 100%;
      padding: var(--space-4) var(--space-10) var(--space-4) var(--space-12);
      border: 2px solid #e2e8f0;
      border-radius: var(--radius-lg);
      font-size: 1rem;
      background: #ffffff;
      color: var(--color-text-primary);
      transition: all var(--duration-fast) var(--ease-out);
      outline: none;
      height: 50px;
      box-sizing: border-box;
    }

    .form-input::placeholder {
      color: #94a3b8;
      font-size: 0.875rem;
    }

    .form-input:hover {
      border-color: #cbd5e1;
    }

    .form-input:focus {
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.15);
      transform: translateY(-1px);
    }

    .form-input:invalid {
      border-color: #ef4444;
    }

    .form-input.ng-invalid.ng-touched {
      border-color: #ef4444;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    .password-toggle {
      position: absolute;
      right: var(--space-4);
      background: none;
      border: none;
      color: var(--color-text-muted);
      cursor: pointer;
      padding: var(--space-1);
      border-radius: var(--radius-sm);
      transition: color var(--duration-fast) var(--ease-out);
      display: flex;
      align-items: center;
      z-index: 2;
    }

    .password-toggle:hover {
      color: var(--color-text-primary);
    }

    .password-toggle mat-icon {
      font-size: 1.25rem;
      width: 1.25rem;
      height: 1.25rem;
    }

    .form-error {
      font-size: var(--font-size-sm);
      color: var(--color-error);
      margin-top: var(--space-1);
    }

    /* === SUBMIT BUTTON === */
    .submit-button {
      width: 100%;
      height: 50px;
      padding: var(--space-4) var(--space-6);
      background: var(--gradient-primary);
      color: var(--color-white);
      border: none;
      border-radius: var(--radius-xl);
      font-size: 1rem;
      font-weight: var(--font-weight-bold);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-2);
      transition: all var(--duration-fast) var(--ease-out);
      box-shadow:
        0 4px 14px 0 rgba(0, 212, 170, 0.39),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
      margin-top: var(--space-4);
      position: relative;
      overflow: hidden;
    }

    .submit-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.6s ease;
    }

    .submit-button:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow:
        0 8px 20px 0 rgba(0, 212, 170, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    .submit-button:hover:not(:disabled)::before {
      left: 100%;
    }

    .submit-button:active:not(:disabled) {
      transform: translateY(-1px);
    }

    .submit-button:disabled {
      background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
      cursor: not-allowed;
      transform: none;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      opacity: 0.6;
    }

    .submit-button:disabled::before {
      display: none;
    }

    .loading-spinner {
      width: 20px;
      height: 20px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top: 2px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .form-footer {
      text-align: center;
      margin-top: var(--space-4);
    }

    .form-link {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      margin: 0;
    }

    .link {
      color: var(--color-primary);
      text-decoration: none;
      font-weight: var(--font-weight-medium);
      transition: color var(--duration-fast) var(--ease-out);
    }

    .link:hover {
      color: var(--color-primary-hover);
      text-decoration: underline;
    }

    /* === RESPONSIVE === */
    @media (max-width: 1200px) {
      .register-container {
        grid-template-columns: 1.2fr 1fr;
      }

      .welcome-side {
        padding: var(--space-12);
      }

      .form-side {
        padding: var(--space-12);
      }

      .logo-text {
        font-size: 4rem;
      }

      .welcome-title {
        font-size: 2.5rem;
      }
    }

    @media (max-width: 768px) {
      .register-container {
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr;
      }

      .welcome-side {
        padding: var(--space-8) var(--space-6);
      }

      .brand-header {
        margin-bottom: var(--space-8);
      }

      .logo-text {
        font-size: 3.5rem;
      }

      .welcome-title {
        font-size: 2rem;
      }

      .welcome-subtitle {
        font-size: 1.125rem;
      }

      .features-showcase {
        gap: var(--space-4);
      }

      .feature-card {
        padding: var(--space-4);
        gap: var(--space-4);
      }

      .feature-icon {
        width: 50px;
        height: 50px;
      }

      .feature-icon mat-icon {
        font-size: 1.5rem;
        width: 1.5rem;
        height: 1.5rem;
      }

      .form-side {
        padding: var(--space-8) var(--space-6);
      }

      .form-header {
        margin-bottom: var(--space-8);
      }

      .form-title {
        font-size: 2rem;
      }

      .form-subtitle {
        font-size: 1.125rem;
      }
    }

    @media (max-width: 480px) {
      .welcome-side {
        padding: var(--space-6) var(--space-4);
      }

      .logo-text {
        font-size: 2.5rem;
      }

      .logo-version {
        font-size: 1.25rem;
      }

      .welcome-title {
        font-size: 1.75rem;
      }

      .welcome-subtitle {
        font-size: 1rem;
      }

      .features-showcase {
        display: none;
      }

      .form-side {
        padding: var(--space-6) var(--space-4);
      }

      .form-content {
        max-width: 100%;
      }

      .form-input {
        height: 60px;
        padding: var(--space-4) var(--space-10) var(--space-4) var(--space-12);
        font-size: 1rem;
      }

      .input-icon {
        left: var(--space-4);
      }

      .input-icon mat-icon {
        font-size: 1.25rem;
        width: 1.25rem;
        height: 1.25rem;
      }

      .submit-button {
        height: 60px;
        font-size: 1.125rem;
      }

      .form-label {
        font-size: 1rem;
      }
    }

    /* === ACCESSIBILITY === */
    @media (prefers-reduced-motion: reduce) {
      .submit-button,
      .form-input,
      .link {
        transition: none;
      }
    }

    .form-input:focus-visible,
    .submit-button:focus-visible,
    .link:focus-visible {
      outline: 2px solid var(--color-primary-focus);
      outline-offset: 2px;
    }
  `]
})
export class RegisterPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(false);
  readonly hidePassword = signal(true);
  readonly hideConfirmPassword = signal(true);

  registerForm!: FormGroup;

  ngOnInit(): void {
    this.initializeForm();
    
    // Redirect if already authenticated
    if (this.authV5.isAuthenticated()) {
      this.router.navigate(['/dashboard']);
    }
  }

  private initializeForm(): void {
    this.registerForm = this.fb.group({
      name: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', [Validators.required]]
    }, { validators: passwordMatchValidator });
  }

  onSubmit(): void {
    if (this.registerForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading.set(true);
    const { name, email, password } = this.registerForm.value;

    this.authV5.register({ name, email, password }).subscribe({
      next: (response) => {
        this.isLoading.set(false);
        console.log('✅ Registration successful:', response);
        
        this.toast.success(this.translation.get('auth.register.success'));
        
        // AuthV5Service handles navigation internally
      },
      error: (error) => {
        this.isLoading.set(false);
        console.error('❌ Registration error:', error);
        
        const errorMessage = this.getLocalizedErrorMessage(error);
        this.toast.error(errorMessage);
      }
    });
  }

  private markFormGroupTouched(): void {
    Object.keys(this.registerForm.controls).forEach(key => {
      const control = this.registerForm.get(key);
      control?.markAsTouched();
    });
  }

  private getLocalizedErrorMessage(error: any): string {
    // Try to get specific API error message
    if (error.code) {
      switch (error.code) {
        case 'email_already_exists':
          return this.translation.get('errors.api.emailAlreadyExists');
        case 'registration_failed':
          return this.translation.get('errors.api.registrationFailed');
        default:
          break;
      }
    }

    // Try to get HTTP status based errors
    if (error.status) {
      switch (error.status) {
        case 422:
          return this.translation.get('errors.validation');
        case 500:
          return this.translation.get('errors.serverError');
        case 0:
          return this.translation.get('errors.network');
        default:
          break;
      }
    }

    // Fallback to generic error message or provided message
    return error.userMessage || 
           error.detail || 
           error.message ||
           this.translation.get('auth.register.error');
  }
}