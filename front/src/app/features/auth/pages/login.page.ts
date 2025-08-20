import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
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

@Component({
  selector: 'app-login-page',
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
    <div class="login-page">
      <div class="login-container">
        <!-- Left Side - Welcome -->
        <div class="welcome-side">
          <div class="welcome-content">
            <div class="brand-header">
              <div class="logo-container">
                <span class="logo-text">bouk<span class="logo-accent">ii</span></span>
                <span class="logo-version">V5</span>
              </div>
              <h1 class="welcome-title">{{ 'auth.login.welcome.title' | translate }}</h1>
              <p class="welcome-subtitle">{{ 'auth.login.welcome.subtitle' | translate }}</p>
            </div>

            <div class="features-showcase">
              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>dashboard</mat-icon>
                </div>
                <div class="feature-content">
                  <h3>{{ 'auth.login.features.dashboard.title' | translate }}</h3>
                  <p>{{ 'auth.login.features.dashboard.description' | translate }}</p>
                </div>
              </div>

              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>groups</mat-icon>
                </div>
                <div class="feature-content">
                  <h3>{{ 'auth.login.features.management.title' | translate }}</h3>
                  <p>{{ 'auth.login.features.management.description' | translate }}</p>
                </div>
              </div>

              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>analytics</mat-icon>
                </div>
                <div class="feature-content">
                  <h3>{{ 'auth.login.features.reports.title' | translate }}</h3>
                  <p>{{ 'auth.login.features.reports.description' | translate }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="form-side">
          <div class="form-content">
            <div class="form-header">
              <h2 class="form-title">{{ 'auth.login.title' | translate }}</h2>
              <p class="form-subtitle">{{ 'auth.login.subtitle' | translate }}</p>
            </div>

            <form [formGroup]="loginForm" (ngSubmit)="onSubmit()" class="login-form" data-cy="login-form">
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
                @if (loginForm.get('email')?.invalid && loginForm.get('email')?.touched) {
                  <div class="error-message">
                    @if (loginForm.get('email')?.errors?.['required']) {
                      {{ 'auth.email.required' | translate }}
                    }
                    @if (loginForm.get('email')?.errors?.['email']) {
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
                    autocomplete="current-password"
                    class="form-input"
                    data-cy="password-input">
                  <button
                    type="button"
                    class="password-toggle"
                    (click)="hidePassword.set(!hidePassword())"
                    [attr.aria-label]="'Show password'"
                    [attr.aria-pressed]="!hidePassword()">
                    <mat-icon>{{ hidePassword() ? 'visibility_off' : 'visibility' }}</mat-icon>
                  </button>
                </div>
                @if (loginForm.get('password')?.invalid && loginForm.get('password')?.touched) {
                  <div class="error-message">
                    @if (loginForm.get('password')?.errors?.['required']) {
                      {{ 'auth.password.required' | translate }}
                    }
                    @if (loginForm.get('password')?.errors?.['minlength']) {
                      {{ 'auth.password.minLength' | translate }}
                    }
                  </div>
                }
              </div>

              <button
                type="submit"
                [disabled]="loginForm.invalid || isLoading()"
                class="login-button"
                data-cy="login-button">
                @if (isLoading()) {
                  <mat-spinner diameter="20" color="accent"></mat-spinner>
                  <span>{{ 'common.loading' | translate }}</span>
                } @else {
                  {{ 'auth.login.button' | translate }}
                }
              </button>

              <div class="form-links">
                <a routerLink="/auth/forgot-password" class="forgot-link">
                  {{ 'auth.forgotPassword' | translate }}
                </a>

                <div class="register-prompt">
                  <span>{{ 'auth.noAccount' | translate }}</span>
                  <a routerLink="/auth/register" class="register-link">
                    {{ 'auth.createAccount' | translate }}
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .login-page {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: var(--gradient-primary);
      overflow: hidden;
      z-index: 1000;
    }

    .login-container {
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

      mat-icon {
        color: var(--color-turquoise-400);
        font-size: 1.5rem;
        width: 1.5rem;
        height: 1.5rem;
      }
    }

    .feature-content h3 {
      font-size: 1.125rem;
      font-weight: var(--font-weight-bold);
      color: var(--color-white);
      margin: 0 0 var(--space-1) 0;
    }

    .feature-content p {
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

    .login-form {
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

      mat-icon {
        font-size: 1.25rem;
        width: 1.25rem;
        height: 1.25rem;
      }
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

      &::placeholder {
        color: #94a3b8;
        font-size: 0.875rem;
      }

      &:hover {
        border-color: #cbd5e1;
      }

      &:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.15);
        transform: translateY(-1px);
      }

      &:invalid {
        border-color: #ef4444;
      }

      &.ng-invalid.ng-touched {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
      }
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

      &:hover {
        color: var(--color-text-primary);
      }

      mat-icon {
        font-size: 1.25rem;
        width: 1.25rem;
        height: 1.25rem;
      }
    }

    .error-message {
      font-size: var(--font-size-sm);
      color: var(--color-error);
      margin-top: var(--space-1);
    }

    /* === LOGIN BUTTON === */
    .login-button {
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

      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s ease;
      }

      &:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow:
          0 8px 20px 0 rgba(0, 212, 170, 0.5),
          inset 0 1px 0 rgba(255, 255, 255, 0.2);

        &::before {
          left: 100%;
        }
      }

      &:active:not(:disabled) {
        transform: translateY(-1px);
      }

      &:disabled {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        cursor: not-allowed;
        transform: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        opacity: 0.6;

        &::before {
          display: none;
        }
      }

      mat-spinner {
        width: 20px !important;
        height: 20px !important;
      }
    }

    /* === FORM LINKS === */
    .form-links {
      display: flex;
      flex-direction: column;
      gap: var(--space-4);
      align-items: center;
      margin-top: var(--space-6);
    }

    .forgot-link {
      color: var(--color-primary);
      text-decoration: none;
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      transition: color var(--duration-fast) var(--ease-out);

      &:hover {
        color: var(--color-primary-hover);
        text-decoration: underline;
      }
    }

    .register-prompt {
      display: flex;
      align-items: center;
      gap: var(--space-2);
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
    }

    .register-link {
      color: var(--color-primary);
      text-decoration: none;
      font-weight: var(--font-weight-semibold);
      transition: color var(--duration-fast) var(--ease-out);

      &:hover {
        color: var(--color-primary-hover);
        text-decoration: underline;
      }
    }

    /* === RESPONSIVE === */
    @media (max-width: 1200px) {
      .login-container {
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
      .login-container {
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

        mat-icon {
          font-size: 1.5rem;
          width: 1.5rem;
          height: 1.5rem;
        }
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

        mat-icon {
          font-size: 1.25rem;
          width: 1.25rem;
          height: 1.25rem;
        }
      }

      .login-button {
        height: 60px;
        font-size: 1.125rem;
      }

      .form-label {
        font-size: 1rem;
      }
    }

    /* === ACCESSIBILITY === */
    @media (prefers-reduced-motion: reduce) {
      .login-button,
      .form-input,
      .forgot-link,
      .register-link {
        transition: none;
      }
    }

    .form-input:focus-visible,
    .login-button:focus-visible,
    .forgot-link:focus-visible,
    .register-link:focus-visible {
      outline: 2px solid var(--color-primary-focus);
      outline-offset: 2px;
    }
  `]
})
export class LoginPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(false);
  readonly hidePassword = signal(true);

  loginForm!: FormGroup;

  ngOnInit(): void {
    this.initializeForm();

    // Redirect if already authenticated
    if (this.authV5.isAuthenticated()) {
      this.router.navigate(['/dashboard']);
    }
  }

  private initializeForm(): void {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading.set(true);
    const { email, password } = this.loginForm.value;

    // Step 1: Check user credentials
    this.authV5.checkUser({ email, password }).subscribe({
      next: (response) => {
        console.log('✅ User check successful:', response);

        if (!response.success || !response.data) {
          this.handleLoginError('Invalid response from server');
          return;
        }

        const { user: _user, schools, requires_school_selection, temp_token } = response.data;

        console.log('🔍 Login response analysis:', {
          schools_count: schools.length,
          requires_school_selection,
          school_names: schools.map((s: any) => s.name)
        });

        if (schools.length === 0) {
          this.handleLoginError('No schools available for this user');
        } else if (schools.length === 1) {
          // Single school user - always auto-select regardless of requires_school_selection
          console.log('👤 Single school user - auto-selecting');
          this.handleSingleSchoolUser(schools[0], temp_token);
        } else if (schools.length > 1) {
          // Multi-school user - show school selection
          console.log('👥 Multi-school user - showing selection');
          this.handleMultiSchoolUser(schools, temp_token);
        } else {
          this.handleLoginError('Invalid school data received');
        }
      },
      error: (error) => {
        this.handleLoginError(error?.message || 'Login failed');
        console.error('❌ Login error:', error);

        const errorMessage = this.getLocalizedErrorMessage(error);
        this.toast.error(errorMessage);
      }
    });
  }

  private handleSingleSchoolUser(school: any, tempToken: string): void {
    console.log('🏫 Single school user, auto-selecting school:', school.name);

    // Auto-select the single school
    this.authV5.selectSchool(school.id, tempToken).subscribe({
      next: (response) => {
        if (!response.success || !response.data) {
          this.handleLoginError('School selection failed');
          return;
        }

        const { available_seasons, access_token, has_multiple_seasons } = response.data;

        if (!has_multiple_seasons || available_seasons.length === 1) {
          // Single season - login is complete, store the access token
          console.log('✅ Single season - login complete after school selection');
          
          // Store the token and complete login
          this.authV5.handleLoginSuccess({
            token: access_token,
            user: response.data.user,
            schools: [response.data.school],
            school: response.data.school,
            season: response.data.season
          });
          
          this.isLoading.set(false);
          this.toast.success(this.translation.get('auth.login.success'));
          this.router.navigate(['/dashboard']);
        } else if (has_multiple_seasons && available_seasons.length > 1) {
          // Multiple seasons - user needs to choose
          this.showSeasonSelection(available_seasons, response.data.school.id, access_token);
        } else {
          this.handleLoginError('No seasons available for this school');
        }
      },
      error: (error) => {
        this.handleLoginError(`School selection failed: ${error.message}`);
      }
    });
  }

  private handleMultiSchoolUser(schools: any[], tempToken: string): void {
    console.log('🏫 Multi-school user, navigating to school selection');

    // Store temporary token and schools data for school selection page
    localStorage.setItem('boukii_temp_token', tempToken);
    localStorage.setItem('boukii_temp_schools', JSON.stringify(schools));
    
    this.isLoading.set(false);
    this.router.navigate(['/select-school']);
  }

  private handleSingleSeason(season: any, schoolId: number, tempToken: string): void {
    console.log('🗓️ Single season, auto-selecting:', season.name);

    // Auto-select the single season
    this.authV5.selectSeason(season.id, schoolId, tempToken).subscribe({
      next: (response) => {
        this.isLoading.set(false);
        console.log('✅ Complete V5 login successful:', response);

        this.toast.success(this.translation.get('auth.login.success'));
        this.router.navigate(['/dashboard']);
      },
      error: (error) => {
        this.handleLoginError(`Season selection failed: ${error.message}`);
      }
    });
  }

  private showSeasonSelection(seasons: any[], schoolId: number, tempToken: string): void {
    console.log('🗓️ Multiple seasons, showing selection UI');

    // For now, auto-select first season (later implement season selection UI)
    this.handleSingleSeason(seasons[0], schoolId, tempToken);
  }

  private handleLoginError(message: string): void {
    this.isLoading.set(false);
    console.error('❌ Login error:', message);
    this.toast.error(message);
  }

  private markFormGroupTouched(): void {
    Object.keys(this.loginForm.controls).forEach(key => {
      const control = this.loginForm.get(key);
      control?.markAsTouched();
    });
  }

  private getLocalizedErrorMessage(error: any): string {
    // Try to get specific API error message
    if (error.code) {
      switch (error.code) {
        case 'invalid_credentials':
          return this.translation.get('errors.api.loginFailed');
        case 'email_not_found':
          return this.translation.get('errors.api.emailNotFound');
        case 'account_locked':
          return this.translation.get('errors.api.accountLocked');
        default:
          break;
      }
    }

    // Try to get HTTP status based errors
    if (error.status) {
      switch (error.status) {
        case 401:
          return this.translation.get('errors.unauthorized');
        case 403:
          return this.translation.get('errors.forbidden');
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
           this.translation.get('auth.login.error');
  }
}
