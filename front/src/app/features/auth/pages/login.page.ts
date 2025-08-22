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
  selector: 'app-login-page',
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
      [title]="translation.get('auth.login.title')"
      [subtitle]="translation.get('auth.login.subtitle')"
      [brandLine]="translation.get('auth.brandLine')"
      [features]="features"
      [footerLinks]="footerLinks">

      <div class="card-header">
        <h1 class="card-title">{{ 'auth.login.title' | translate }}</h1>
        <p class="card-subtitle">{{ 'auth.login.subtitle' | translate }}</p>
      </div>

      <form [formGroup]="loginForm" (ngSubmit)="onSubmit()" novalidate>
        <label class="field">
          <span>{{ 'auth.common.email' | translate }}</span>
          <input 
            type="email" 
            formControlName="email" 
            autocomplete="username"
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
              autocomplete="current-password"
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

        @if (statusMessage()) {
          <div class="status-message" role="status" aria-live="polite">
            {{ statusMessage() }}
          </div>
        }

        <div class="card-actions">
          <button class="btn btn--primary" type="submit" [disabled]="loginForm.invalid || isLoading()">
            @if (isLoading()) {
              <div class="loading-spinner"></div>
              {{ 'common.loading' | translate }}
            } @else {
              {{ 'auth.login.cta' | translate }}
            }
          </button>
        </div>
      </form>
    </bk-auth-shell>
  `,
  styleUrls: ['./login.page.scss']
})
export class LoginPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(false);
  readonly showPassword = signal(false);
  readonly statusMessage = signal('');

  readonly features = [
    {
      icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/></svg>`,
      title: this.translation.get('auth.features.suite.title'),
      subtitle: this.translation.get('auth.features.suite.subtitle')
    },
    {
      icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>`,
      title: this.translation.get('auth.features.multiseason.title'),
      subtitle: this.translation.get('auth.features.multiseason.subtitle')
    },
    {
      icon: `<svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>`,
      title: this.translation.get('auth.features.pro.title'),
      subtitle: this.translation.get('auth.features.pro.subtitle')
    }
  ];

  readonly footerLinks = [
    { label: this.translation.get('auth.login.links.forgot'), routerLink: '/auth/forgot-password' },
    { label: this.translation.get('auth.login.links.register'), routerLink: '/auth/register' }
  ];

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

  onFieldChange(field: string, value: string): void {
    this.loginForm.patchValue({ [field]: value });
    this.loginForm.get(field)?.markAsTouched();
  }

  togglePassword(): void {
    this.showPassword.set(!this.showPassword());
  }

  isFieldInvalid(field: string): boolean {
    const control = this.loginForm.get(field);
    return !!(control?.invalid && control?.touched);
  }

  getFieldError(field: string): string {
    const control = this.loginForm.get(field);
    if (control?.invalid && control?.touched) {
      if (control.errors?.['required']) {
        return this.translation.get(`auth.errors.required${field.charAt(0).toUpperCase() + field.slice(1)}`);
      }
      if (control.errors?.['email']) {
        return this.translation.get('auth.errors.invalidEmail');
      }
      if (control.errors?.['minlength']) {
        return this.translation.get('auth.errors.requiredPassword');
      }
    }
    return '';
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading.set(true);
    this.statusMessage.set(this.translation.get('auth.login.processing'));
    const { email, password } = this.loginForm.value;

    // Step 1: Check user credentials
    this.authV5.checkUser({ email, password }).subscribe({
      next: (response) => {
        if (!response.success || !response.data) {
          this.handleLoginError('Invalid response from server');
          return;
        }

        const { schools, temp_token } = response.data;

        if (schools.length === 0) {
          this.handleLoginError('No schools available for this user');
        } else if (schools.length === 1) {
          this.handleSingleSchoolUser(schools[0], temp_token);
        } else if (schools.length > 1) {
          this.handleMultiSchoolUser(schools, temp_token);
        } else {
          this.handleLoginError('Invalid school data received');
        }
      },
      error: (error) => {
        this.handleLoginError(error?.message || 'Login failed');
      }
    });
  }

  private handleSingleSchoolUser(school: any, tempToken: string): void {
    this.statusMessage.set(this.translation.get('auth.login.selectingSchool'));

    this.authV5.selectSchool(school.id, tempToken).subscribe({
      next: (response) => {
        if (!response.success || !response.data) {
          this.handleLoginError('School selection failed');
          return;
        }

        const { available_seasons, access_token } = response.data;

        if (available_seasons && available_seasons.length > 0) {
          const firstSeason = available_seasons[0];
          this.handleSingleSeason(firstSeason, response.data.school.id, access_token);
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
    localStorage.setItem('boukii_temp_token', tempToken);
    localStorage.setItem('boukii_temp_schools', JSON.stringify(schools));

    this.isLoading.set(false);
    this.router.navigate(['/select-school']);
  }

  private handleSingleSeason(season: any, schoolId: number, accessToken: string): void {
    this.statusMessage.set(this.translation.get('auth.login.selectingSeason'));

    this.authV5.selectSeason(season.id, schoolId, accessToken).subscribe({
      next: (_response) => {
        this.isLoading.set(false);
        this.statusMessage.set(this.translation.get('auth.login.success'));

        this.toast.success(this.translation.get('auth.login.success'));
        this.router.navigate(['/dashboard']);
      },
      error: (error) => {
        this.handleLoginError(`Season selection failed: ${error.message}`);
      }
    });
  }

  private handleLoginError(message: string): void {
    this.isLoading.set(false);
    this.statusMessage.set(message);
    this.toast.error(message);
  }

  private markFormGroupTouched(): void {
    Object.keys(this.loginForm.controls).forEach(key => {
      const control = this.loginForm.get(key);
      control?.markAsTouched();
    });
  }
}
