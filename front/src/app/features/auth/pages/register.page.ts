import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AuthShellComponent } from '../ui/auth-shell/auth-shell.component';
import { TextFieldComponent } from '../../../ui/atoms/text-field.component';

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
    TranslatePipe,
    AuthShellComponent,
    TextFieldComponent
  ],
  template: `
    <bk-auth-shell
      [titleKey]="'auth.hero.join'"
      [subtitleKey]="'auth.register.subtitle'"
      [features]="features">

      <div class="card-header">
        <h1 class="card-title">{{ 'auth.register.title' | translate }}</h1>
        <p class="card-subtitle">{{ 'auth.register.subtitle' | translate }}</p>
      </div>

      <form [formGroup]="registerForm" (ngSubmit)="onSubmit()" novalidate>
        <label class="field">
          <span>{{ 'auth.name.label' | translate }}</span>
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
          <span>{{ 'auth.common.email' | translate }}</span>
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
          <span>{{ 'auth.common.password' | translate }}</span>
          <div class="password-wrapper">
            <input 
              [type]="showPassword() ? 'text' : 'password'" 
              formControlName="password" 
              autocomplete="new-password"
              [class.is-invalid]="isFieldInvalid('password')" />
            <button 
              type="button" 
              class="password-toggle"
              [attr.aria-label]="(showPassword() ? 'auth.common.hidePassword' : 'auth.common.showPassword') | translate"
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
          <span>{{ 'auth.register.confirmPassword' | translate }}</span>
          <div class="password-wrapper">
            <input 
              [type]="showConfirmPassword() ? 'text' : 'password'" 
              formControlName="confirmPassword" 
              autocomplete="new-password"
              [class.is-invalid]="isFieldInvalid('confirmPassword') || hasPasswordMismatch()" />
            <button 
              type="button" 
              class="password-toggle"
              [attr.aria-label]="(showConfirmPassword() ? 'auth.common.hidePassword' : 'auth.common.showPassword') | translate"
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
              {{ 'common.loading' | translate }}
            } @else {
              {{ 'auth.common.signup' | translate }}
            }
          </button>
          <div class="links">
            <span>{{ 'auth.common.haveAccount' | translate }}</span>
            <a routerLink="/auth/login">{{ 'auth.common.signin' | translate }}</a>
          </div>
        </div>
      </form>
    </bk-auth-shell>
  `,
  styleUrls: ['./register.page.scss']
})
export class RegisterPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(false);
  readonly showPassword = signal(false);
  readonly showConfirmPassword = signal(false);
  readonly statusMessage = signal('');

  readonly features = [
    { title: 'auth.hero.feature1', subtitle: 'auth.hero.feature1Desc' },
    { title: 'auth.hero.feature2', subtitle: 'auth.hero.feature2Desc' },
    { title: 'auth.hero.feature3', subtitle: 'auth.hero.feature3Desc' }
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
        return this.translation.get(`auth.errors.required${field.charAt(0).toUpperCase() + field.slice(1)}`);
      }
      if (control.errors?.['email']) {
        return this.translation.get('auth.errors.invalidEmail');
      }
      if (control.errors?.['minlength']) {
        if (field === 'password') {
          return this.translation.get('auth.errors.requiredPassword');
        }
        return this.translation.get(`auth.${field}.minLength`);
      }
    }
    return '';
  }

  getPasswordMatchError(): string {
    if (this.registerForm.errors?.['passwordMismatch'] &&
        this.registerForm.get('confirmPassword')?.touched) {
      return this.translation.get('auth.errors.passwordsNoMatch');
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
    this.statusMessage.set(this.translation.get('auth.register.processing'));

    const { name, email, password } = this.registerForm.value;

    this.authV5.register({ name, email, password }).subscribe({
      next: (response) => {
        this.isLoading.set(false);

        if (response.success) {
          this.statusMessage.set(this.translation.get('auth.register.success'));
          this.toast.success(this.translation.get('auth.register.success'));

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
