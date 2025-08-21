import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AuthShellComponent } from '../ui/auth-shell/auth-shell.component';
import { TextFieldComponent } from '../../../ui/atoms/text-field.component';

@Component({
  selector: 'app-forgot-password-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    TranslatePipe,
    AuthShellComponent,
    TextFieldComponent
  ],
  template: `
    <bk-auth-shell
      [titleKey]="'auth.forgotPassword.welcome.title'"
      [subtitleKey]="'auth.forgotPassword.subtitle'"
      [features]="features">

      <h2 id="forgotPasswordTitle" class="visually-hidden">{{ 'auth.forgotPassword.title' | translate }}</h2>

      @if (!emailSent()) {
        <!-- Reset Password Form -->
        <div class="card-header">
          <h1 class="card-title">{{ 'auth.forgotPassword.title' | translate }}</h1>
          <p class="card-subtitle">{{ 'auth.forgotPassword.subtitle' | translate }}</p>
        </div>

        <form [formGroup]="forgotPasswordForm" (ngSubmit)="onSubmit()" class="auth-form">
          <!-- Email -->
          <ui-text-field
            [label]="'auth.common.email' | translate"
            [placeholder]="'auth.common.email' | translate"
            [errorMessage]="getFieldError('email')"
              type="email"
              autocomplete="email"
              (valueChange)="onFieldChange('email', $event)">
            </ui-text-field>

            <button 
              type="submit" 
              class="btn btn--primary w-100" 
              [disabled]="forgotPasswordForm.invalid || isLoading()"
              [attr.aria-describedby]="statusMessage() ? 'status-message' : null">
              @if (isLoading()) {
                <div class="loading-spinner">
                  <div class="spinner"></div>
                </div>
                {{ 'common.loading' | translate }}
              } @else {
                {{ 'auth.forgotPassword.button' | translate }}
              }
            </button>

            <div class="links">
              <a routerLink="/auth/login">{{ 'auth.forgotPassword.rememberedPassword' | translate }}</a>
            </div>
        </form>

      } @else {
        <!-- Success State -->
        <div class="success-state" role="status" aria-live="polite">
          <div class="success-icon">
            <i class="i-mail" aria-hidden="true"></i>
          </div>

          <div class="card-header">
            <h1 class="card-title">{{ 'auth.forgotPassword.successTitle' | translate }}</h1>
            <p class="card-subtitle">{{ 'auth.forgotPassword.emailSent' | translate }}</p>
          </div>

          @if (submittedEmail()) {
            <div class="email-confirmation">
              <p class="email-sent-message">
                <strong>{{ submittedEmail() }}</strong>
              </p>
              <p class="help-text">{{ 'auth.forgotPassword.checkSpam' | translate }}</p>
            </div>
          }

          <div class="success-actions">
            <button
              type="button"
              class="btn btn--secondary w-100"
              (click)="resetForm()"
              [disabled]="isLoading()">
              {{ 'auth.forgotPassword.sendAnother' | translate }}
            </button>

            <div class="links">
              <a routerLink="/auth/login">{{ 'auth.forgotPassword.rememberedPassword' | translate }}</a>
            </div>
          </div>
        </div>
      }

      <div
        id="status-message"
        class="visually-hidden"
        role="status"
        aria-live="polite"
        [attr.aria-hidden]="!statusMessage()">
        {{ statusMessage() }}
      </div>
    </bk-auth-shell>
  `,
  styleUrls: ['./forgot-password.page.scss']
})
export class ForgotPasswordPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(false);
  readonly statusMessage = signal('');
  readonly emailSent = signal(false);
  readonly submittedEmail = signal('');

  readonly features = [
    { icon: 'i-grid', titleKey: 'auth.hero.feature1', descKey: 'auth.hero.feature1Desc' },
    { icon: 'i-clock', titleKey: 'auth.hero.feature2', descKey: 'auth.hero.feature2Desc' },
    { icon: 'i-trending-up', titleKey: 'auth.hero.feature3', descKey: 'auth.hero.feature3Desc' }
  ];

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

  onFieldChange(field: string, value: string): void {
    this.forgotPasswordForm.patchValue({ [field]: value });
    this.forgotPasswordForm.get(field)?.markAsTouched();
  }

  getFieldError(field: string): string {
    const control = this.forgotPasswordForm.get(field);
    if (control?.invalid && control?.touched) {
      if (control.errors?.['required']) {
        return this.translation.get('auth.errors.requiredEmail');
      }
      if (control.errors?.['email']) {
        return this.translation.get('auth.errors.invalidEmail');
      }
    }
    return '';
  }

  isFieldInvalid(field: string): boolean {
    const control = this.forgotPasswordForm.get(field);
    return !!(control?.invalid && control?.touched);
  }

  onSubmit(): void {
    if (this.forgotPasswordForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading.set(true);
    this.statusMessage.set(this.translation.get('auth.forgotPassword.processing'));
    
    const { email } = this.forgotPasswordForm.value;

    this.authV5.forgotPassword(email).subscribe({
      next: (response: any) => {
        this.isLoading.set(false);
        
        if (response.success) {
          this.emailSent.set(true);
          this.submittedEmail.set(email);
          this.statusMessage.set(this.translation.get('auth.forgotPassword.emailSent'));
          this.toast.success(this.translation.get('auth.forgotPassword.success'));
        } else {
          // Show generic success message for security
          this.emailSent.set(true);
          this.submittedEmail.set(email);
          this.statusMessage.set(this.translation.get('auth.forgotPassword.emailSent'));
        }
      },
      error: (_error: any) => {
        this.isLoading.set(false);
        
        // For security, always show success message
        // Don't reveal whether email exists or not
        this.emailSent.set(true);
        this.submittedEmail.set(email);
        this.statusMessage.set(this.translation.get('auth.forgotPassword.emailSent'));
      }
    });
  }

  resetForm(): void {
    this.emailSent.set(false);
    this.submittedEmail.set('');
    this.statusMessage.set('');
    this.forgotPasswordForm.reset();
  }

  private markFormGroupTouched(): void {
    Object.keys(this.forgotPasswordForm.controls).forEach(key => {
      const control = this.forgotPasswordForm.get(key);
      control?.markAsTouched();
    });
  }
}