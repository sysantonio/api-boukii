import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { AuthShellComponent } from '@features/auth/ui/auth-shell/auth-shell.component';

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
    AuthShellComponent
  ],
  template: `
    <auth-shell
      [brandLine]="t('auth.brandLine')"
      [title]="t(pageTitleKey)"
      [subtitle]="t(pageSubtitleKey)"
      [features]="features"
    >
      <ng-container auth-form>
        <form [formGroup]="registerForm" (ngSubmit)="onSubmit()" novalidate>
          <label class="field">
            <span>{{ t('auth.name.label') }}</span>
            <input
              type="text"
              formControlName="name"
              autocomplete="name"
              [class.is-invalid]="isFieldInvalid('name')" />
            <small class="field-error" *ngIf="isFieldInvalid('name')">
              {{ getFieldError('name') }}
            </small>
          </label>

          <label class="field">
            <span>{{ t('auth.common.email') }}</span>
            <input
              type="email"
              formControlName="email"
              autocomplete="email"
              [class.is-invalid]="isFieldInvalid('email')" />
            <small class="field-error" *ngIf="isFieldInvalid('email')">
              {{ getFieldError('email') }}
            </small>
          </label>

          <label class="field">
            <span>{{ t('auth.common.password') }}</span>
            <div class="password-wrapper">
              <input
                [type]="showPassword() ? 'text' : 'password'"
                formControlName="password"
                autocomplete="new-password"
                [class.is-invalid]="isFieldInvalid('password')" />
              <button
                type="button"
                class="password-toggle"
                [attr.aria-label]="t(showPassword() ? 'auth.common.hidePassword' : 'auth.common.showPassword')"
                [attr.aria-pressed]="showPassword()"
                (click)="togglePassword()">
                @if (showPassword()) {
                  <i class="i-eye-off"></i>
                } @else {
                  <i class="i-eye"></i>
                }
              </button>
            </div>
            <small class="field-error" *ngIf="isFieldInvalid('password')">
              {{ getFieldError('password') }}
            </small>
          </label>

          <label class="field">
            <span>{{ t('auth.register.confirmPassword') }}</span>
            <div class="password-wrapper">
              <input
                [type]="showConfirmPassword() ? 'text' : 'password'"
                formControlName="confirmPassword"
                autocomplete="new-password"
                [class.is-invalid]="isFieldInvalid('confirmPassword') || hasPasswordMismatch()" />
              <button
                type="button"
                class="password-toggle"
                [attr.aria-label]="t(showConfirmPassword() ? 'auth.common.hidePassword' : 'auth.common.showPassword')"
                [attr.aria-pressed]="showConfirmPassword()"
                (click)="toggleConfirmPassword()">
                @if (showConfirmPassword()) {
                  <i class="i-eye-off"></i>
                } @else {
                  <i class="i-eye"></i>
                }
              </button>
            </div>
            <small class="field-error" *ngIf="isFieldInvalid('confirmPassword') || hasPasswordMismatch()">
              {{ getFieldError('confirmPassword') || getPasswordMatchError() }}
            </small>
          </label>

          @if (statusMessage()) {
            <div class="status-message" role="status" aria-live="polite">
              {{ statusMessage() }}
            </div>
          }

          <div class="card-actions">
            <button class="btn btn--primary" type="submit" [disabled]="registerForm.invalid || isLoading()">
              @if (isLoading()) {
                <div class="loading-spinner"></div>
                {{ t('common.loading') }}
              } @else {
                {{ t('auth.register.cta') }}
              }
            </button>
            <nav class="auth-links">
              <a routerLink="/auth/login">{{ t('auth.register.links.login') }}</a>
            </nav>
          </div>
        </form>
      </ng-container>
    </auth-shell>
  `,
  styleUrls: ['./register.page.scss']
})
export class RegisterPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translationService = inject(TranslationService);
  private readonly toast = inject(ToastService);

  pageTitleKey = 'auth.register.title';
  pageSubtitleKey = 'auth.register.subtitle';

  t = (k: string) => this.translationService.instant(k);

  // Reactive state
  readonly isLoading = signal(false);
  readonly showPassword = signal(false);
  readonly showConfirmPassword = signal(false);
  readonly statusMessage = signal('');

  readonly features = [
    { icon: 'i-rows',   title: this.t('auth.features.suite.title'),       subtitle: this.t('auth.features.suite.subtitle') },
    { icon: 'i-season', title: this.t('auth.features.multiseason.title'), subtitle: this.t('auth.features.multiseason.subtitle') },
    { icon: 'i-shield', title: this.t('auth.features.pro.title'),         subtitle: this.t('auth.features.pro.subtitle') },
  ];

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
      name: ['', [Validators.required, Validators.minLength(2)]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      confirmPassword: ['', [Validators.required]]
    }, { validators: passwordMatchValidator });
  }

  onFieldChange(field: string, value: string): void {
    this.registerForm.patchValue({ [field]: value });
    this.registerForm.get(field)?.markAsTouched();

    // Re-validate form when password fields change
    if (field === 'password' || field === 'confirmPassword') {
      this.registerForm.updateValueAndValidity();
    }
  }

  togglePassword(): void {
    this.showPassword.set(!this.showPassword());
  }

  toggleConfirmPassword(): void {
    this.showConfirmPassword.set(!this.showConfirmPassword());
  }

  getFieldError(field: string): string {
    const control = this.registerForm.get(field);
    if (control?.invalid && control?.touched) {
      if (control.errors?.['required']) {
        return this.translationService.get(`auth.errors.required${field.charAt(0).toUpperCase() + field.slice(1)}`);
      }
      if (control.errors?.['email']) {
        return this.translationService.get('auth.errors.invalidEmail');
      }
      if (control.errors?.['minlength']) {
        if (field === 'password') {
          return this.translationService.get('auth.errors.requiredPassword');
        }
        return this.translationService.get(`auth.${field}.minLength`);
      }
    }
    return '';
  }

  getPasswordMatchError(): string {
    if (this.registerForm.errors?.['passwordMismatch'] &&
        this.registerForm.get('confirmPassword')?.touched) {
      return this.translationService.get('auth.errors.passwordsNoMatch');
    }
    return '';
  }

  hasPasswordMismatch(): boolean {
    return !!(this.registerForm.errors?.['passwordMismatch'] &&
              this.registerForm.get('confirmPassword')?.touched);
  }

  isFieldInvalid(field: string): boolean {
    const control = this.registerForm.get(field);
    return !!(control?.invalid && control?.touched);
  }

  onSubmit(): void {
    if (this.registerForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading.set(true);
    this.statusMessage.set(this.translationService.get('auth.register.processing'));

    const { name, email, password } = this.registerForm.value;

    this.authV5.register({ name, email, password }).subscribe({
      next: (response) => {
        this.isLoading.set(false);

        if (response.success) {
          this.statusMessage.set(this.translationService.get('auth.register.success'));
          this.toast.success(this.translationService.get('auth.register.success'));

          // Redirect to login page
          this.router.navigate(['/auth/login'], {
            queryParams: { email: email, registered: 'true' }
          });
        } else {
          this.handleRegisterError(response.message || 'Registration failed');
        }
      },
      error: (error) => {
        this.handleRegisterError(error?.message || 'Registration failed');
      }
    });
  }

  private handleRegisterError(message: string): void {
    this.isLoading.set(false);
    this.statusMessage.set(message);
    this.toast.error(message);
  }

  private markFormGroupTouched(): void {
    Object.keys(this.registerForm.controls).forEach(key => {
      const control = this.registerForm.get(key);
      control?.markAsTouched();
    });
    this.registerForm.updateValueAndValidity();
  }
}
