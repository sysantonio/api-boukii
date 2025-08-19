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
  selector: 'app-forgot-password-page',
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
    <div class="forgot-password-page">
      <div class="forgot-password-container">
        <!-- Left Side - Welcome -->
        <div class="welcome-side">
          <div class="welcome-content">
            <div class="brand-header">
              <div class="logo-container">
                <span class="logo-text">bouk<span class="logo-accent">ii</span></span>
                <span class="logo-version">V5</span>
              </div>
              <h1 class="welcome-title">{{ 'auth.forgotPassword.welcome.title' | translate }}</h1>
              <p class="welcome-subtitle">{{ 'auth.forgotPassword.welcome.subtitle' | translate }}</p>
            </div>
            
            <div class="features-showcase">
              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>security</mat-icon>
                </div>
                <div class="feature-text">
                  <h3>{{ 'auth.forgotPassword.features.secure.title' | translate }}</h3>
                  <p>{{ 'auth.forgotPassword.features.secure.description' | translate }}</p>
                </div>
              </div>

              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>email</mat-icon>
                </div>
                <div class="feature-text">
                  <h3>{{ 'auth.forgotPassword.features.email.title' | translate }}</h3>
                  <p>{{ 'auth.forgotPassword.features.email.description' | translate }}</p>
                </div>
              </div>

              <div class="feature-card">
                <div class="feature-icon">
                  <mat-icon>refresh</mat-icon>
                </div>
                <div class="feature-text">
                  <h3>{{ 'auth.forgotPassword.features.quick.title' | translate }}</h3>
                  <p>{{ 'auth.forgotPassword.features.quick.description' | translate }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="form-side">
          <div class="form-content">
            @if (!isSubmitted()) {
              <!-- Password Reset Form -->
              <div class="form-header">
                <h2 class="form-title">{{ 'auth.forgotPassword.title' | translate }}</h2>
                <p class="form-subtitle">{{ 'auth.forgotPassword.subtitle' | translate }}</p>
              </div>

              <form [formGroup]="forgotPasswordForm" (ngSubmit)="onSubmit()" class="auth-form" data-cy="forgot-password-form">
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
                  @if (forgotPasswordForm.get('email')?.invalid && forgotPasswordForm.get('email')?.touched) {
                    <div class="form-error">
                      @if (forgotPasswordForm.get('email')?.errors?.['required']) {
                        {{ 'auth.email.required' | translate }}
                      }
                      @if (forgotPasswordForm.get('email')?.errors?.['email']) {
                        {{ 'auth.email.invalid' | translate }}
                      }
                    </div>
                  }
                </div>

                <button 
                  type="submit" 
                  class="submit-button"
                  [class.loading]="isLoading()"
                  [disabled]="forgotPasswordForm.invalid || isLoading()"
                  data-cy="submit-button">
                  @if (isLoading()) {
                    <div class="loading-spinner"></div>
                    {{ 'common.loading' | translate }}
                  } @else {
                    {{ 'auth.forgotPassword.button' | translate }}
                  }
                </button>

                <div class="form-footer">
                  <p class="form-link">
                    {{ 'auth.forgotPassword.rememberedPassword' | translate }}
                    <a routerLink="/auth/login" class="link">{{ 'auth.backToLogin' | translate }}</a>
                  </p>
                </div>
              </form>
            } @else {
              <!-- Success State -->
              <div class="success-state">
                <div class="success-icon">
                  <mat-icon>check_circle</mat-icon>
                </div>
                <h2 class="success-title">{{ 'auth.forgotPassword.successTitle' | translate }}</h2>
                <p class="success-message">
                  {{ 'auth.forgotPassword.successMessage' | translate }}
                  <strong>{{ submittedEmail() }}</strong>
                </p>
                <p class="success-note">{{ 'auth.forgotPassword.checkSpam' | translate }}</p>
                
                <div class="success-actions">
                  <button 
                    type="button"
                    (click)="resetForm()"
                    class="secondary-button">
                    {{ 'auth.forgotPassword.sendAnother' | translate }}
                  </button>
                  
                  <button 
                    type="button"
                    routerLink="/auth/login"
                    class="submit-button">
                    {{ 'auth.backToLogin' | translate }}
                  </button>
                </div>
              </div>
            }
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
    .forgot-password-page {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: var(--gradient-primary);
      overflow: hidden;
      z-index: 1000;
    }

    .forgot-password-container {
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
      text-align: center;
      max-width: 480px;
    }

    .brand-header {
      margin-bottom: var(--space-12);
    }

    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-3);
      margin-bottom: var(--space-6);
    }

    .logo-text {
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      letter-spacing: -0.02em;
    }

    .logo-accent {
      color: var(--color-turquoise-500);
    }

    .logo-version {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      backdrop-filter: blur(10px);
    }

    .welcome-title {
      font-size: 2rem;
      font-weight: 600;
      color: white;
      margin: 0 0 var(--space-4) 0;
      line-height: 1.2;
    }

    .welcome-subtitle {
      font-size: 1.125rem;
      color: rgba(255, 255, 255, 0.8);
      margin: 0;
      line-height: 1.5;
    }

    .features-showcase {
      display: flex;
      flex-direction: column;
      gap: var(--space-6);
      margin-top: var(--space-8);
    }

    .feature-card {
      display: flex;
      align-items: center;
      gap: var(--space-4);
      padding: var(--space-5);
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      backdrop-filter: blur(10px);
      transition: all 0.2s ease;
    }

    .feature-card:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.15);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .feature-icon {
      flex-shrink: 0;
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 10px;
      color: white;
    }

    .feature-text {
      text-align: left;
    }

    .feature-text h3 {
      font-size: 1rem;
      font-weight: 600;
      color: white;
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

    .form-content {
      width: 100%;
      max-width: 400px;
    }

    .form-header {
      text-align: center;
      margin-bottom: var(--space-8);
    }

    .form-title {
      font-size: 1.875rem;
      font-weight: 600;
      color: var(--color-gray-900);
      margin: 0 0 var(--space-2) 0;
      letter-spacing: -0.025em;
    }

    .form-subtitle {
      font-size: 1rem;
      color: var(--color-gray-600);
      margin: 0;
      line-height: 1.5;
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: var(--space-6);
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: var(--space-2);
    }

    .form-label {
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--color-gray-700);
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
      color: var(--color-gray-400);
      display: flex;
      align-items: center;
      justify-content: center;
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

    .form-input:focus {
      outline: none;
      border-color: var(--color-turquoise-500);
      box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.1);
    }

    .form-input::placeholder {
      color: var(--color-gray-400);
    }

    .form-error {
      font-size: 0.875rem;
      color: var(--color-red-600);
      margin-top: var(--space-1);
    }

    .submit-button {
      height: 48px;
      background: var(--gradient-primary);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-2);
    }

    .submit-button:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 212, 170, 0.4);
    }

    .submit-button:disabled {
      background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
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

    .secondary-button {
      height: 48px;
      background: transparent;
      color: var(--color-gray-700);
      border: 2px solid var(--color-gray-200);
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-2);
      margin-right: var(--space-3);
    }

    .secondary-button:hover {
      border-color: var(--color-gray-300);
      background: var(--color-gray-50);
    }

    .form-footer {
      text-align: center;
      margin-top: var(--space-4);
    }

    .form-link {
      font-size: 0.875rem;
      color: var(--color-gray-600);
      margin: 0;
    }

    .link {
      color: var(--color-turquoise-600);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s ease;
    }

    .link:hover {
      color: var(--color-turquoise-700);
      text-decoration: underline;
    }

    /* === SUCCESS STATE === */
    .success-state {
      text-align: center;
    }

    .success-icon {
      width: 80px;
      height: 80px;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto var(--space-6) auto;
    }

    .success-icon mat-icon {
      font-size: 2.5rem;
      width: 2.5rem;
      height: 2.5rem;
      color: white;
    }

    .success-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--color-gray-900);
      margin: 0 0 var(--space-4) 0;
    }

    .success-message {
      font-size: 1rem;
      color: var(--color-gray-600);
      margin: 0 0 var(--space-2) 0;
      line-height: 1.5;
    }

    .success-note {
      font-size: 0.875rem;
      color: var(--color-gray-500);
      margin: 0 0 var(--space-8) 0;
    }

    .success-actions {
      display: flex;
      gap: var(--space-3);
      justify-content: center;
    }

    /* === BACKGROUND === */
    .auth-background {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      pointer-events: none;
    }

    .gradient-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(40px);
      opacity: 0.3;
    }

    .orb-1 {
      width: 300px;
      height: 300px;
      background: var(--gradient-primary);
      top: -150px;
      left: -150px;
      animation: float-orb 20s ease-in-out infinite;
    }

    .orb-2 {
      width: 200px;
      height: 200px;
      background: var(--gradient-secondary);
      bottom: -100px;
      right: -100px;
      animation: float-orb 15s ease-in-out infinite reverse;
    }

    @keyframes float-orb {
      0%, 100% { transform: translate(0, 0) scale(1); }
      50% { transform: translate(20px, -20px) scale(1.1); }
    }

    /* === RESPONSIVE === */
    @media (max-width: 1200px) {
      .forgot-password-container {
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
      .forgot-password-container {
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

      .success-actions {
        flex-direction: column;
      }

      .secondary-button {
        margin-right: 0;
        margin-bottom: var(--space-3);
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

      .success-actions {
        flex-direction: column;
      }

      .secondary-button {
        width: 100%;
        margin-right: 0;
        margin-bottom: var(--space-3);
      }
    }

    .back-link {
      margin-bottom: 0.5rem;
    }

    .success-content {
      text-align: center;
    }

    .success-icon {
      display: flex;
      justify-content: center;
      margin-bottom: 1rem;
    }

    .large-icon {
      font-size: 4rem !important;
      width: 4rem !important;
      height: 4rem !important;
    }

    .success-message {
      font-size: 1rem;
      margin-bottom: 1rem;
      line-height: 1.5;
      color: rgba(0, 0, 0, 0.87);
    }

    .success-note {
      font-size: 0.875rem;
      color: rgba(0, 0, 0, 0.6);
      font-style: italic;
      margin-bottom: 2rem;
    }

    .success-actions {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .action-button {
      width: 100%;
      height: 44px;
    }

    mat-card-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    mat-card-title {
      font-size: 1.75rem;
      font-weight: 500;
      margin-bottom: 0.5rem;
    }

    mat-card-subtitle {
      color: rgba(0, 0, 0, 0.6);
      font-size: 1rem;
    }

    /* Dark theme support */
    @media (prefers-color-scheme: dark) {
      .forgot-password-page {
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
      }
    }

    /* Responsive design */
    @media (max-width: 480px) {
      .forgot-password-card {
        margin: 1rem;
        padding: 1.5rem;
      }
      
      .forgot-password-container {
        max-width: 100%;
      }

      .success-actions {
        flex-direction: column;
      }

      .action-button {
        width: 100%;
      }
    }

    @media (min-width: 480px) {
      .success-actions {
        flex-direction: row;
        justify-content: center;
      }

      .action-button {
        width: auto;
        min-width: 120px;
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
export class ForgotPasswordPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(false);
  readonly isSubmitted = signal(false);
  readonly submittedEmail = signal('');

  forgotPasswordForm!: FormGroup;

  ngOnInit(): void {
    this.initializeForm();
    
    // Redirect if already authenticated
    if (this.authV5.isAuthenticated()) {
      this.router.navigate(['/dashboard']);
    }
  }

  private initializeForm(): void {
    this.forgotPasswordForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  onSubmit(): void {
    if (this.forgotPasswordForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading.set(true);
    const { email } = this.forgotPasswordForm.value;

    this.authV5.requestPasswordReset({ email }).subscribe({
      next: (response) => {
        this.isLoading.set(false);
        console.log('✅ Password reset request successful:', response);
        
        this.isSubmitted.set(true);
        this.submittedEmail.set(email);
        
        this.toast.success(this.translation.get('auth.forgotPassword.success'));
      },
      error: (error) => {
        this.isLoading.set(false);
        console.error('❌ Password reset error:', error);
        
        const errorMessage = this.getLocalizedErrorMessage(error);
        this.toast.error(errorMessage);
      }
    });
  }

  resetForm(): void {
    this.isSubmitted.set(false);
    this.submittedEmail.set('');
    this.forgotPasswordForm.reset();
  }

  private markFormGroupTouched(): void {
    Object.keys(this.forgotPasswordForm.controls).forEach(key => {
      const control = this.forgotPasswordForm.get(key);
      control?.markAsTouched();
    });
  }

  private getLocalizedErrorMessage(error: any): string {
    // Try to get specific API error message
    if (error.code) {
      switch (error.code) {
        case 'email_not_found':
          return this.translation.get('errors.api.emailNotFound');
        case 'password_reset_failed':
          return this.translation.get('errors.api.passwordResetFailed');
        default:
          break;
      }
    }

    // Try to get HTTP status based errors
    if (error.status) {
      switch (error.status) {
        case 404:
          return this.translation.get('errors.api.emailNotFound');
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
           this.translation.get('auth.forgotPassword.error');
  }
}