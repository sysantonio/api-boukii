import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AuthShellComponent } from '@features/auth/ui/auth-shell/auth-shell.component';
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
    <auth-shell
      [brandLine]="t('auth.brandLine')"
      [title]="t(pageTitleKey)"
      [subtitle]="t(pageSubtitleKey)"
      [features]="features"
    >
      <ng-container auth-form>
        <form role="form" data-testid="auth-form" [formGroup]="loginForm" (ngSubmit)="onSubmit()" novalidate>
          <label class="field">
            <span>{{ 'auth.common.email' | translate }}</span>
            <input
              type="email"
              formControlName="email"
              autocomplete="username"
              [class.is-invalid]="isFieldInvalid('email')"
              [attr.aria-invalid]="isFieldInvalid('email') ? 'true' : 'false'"
              [attr.aria-describedby]="isFieldInvalid('email') ? 'email-error' : null" />
            <p class="field-error" [id]="'email-error'" role="alert" *ngIf="isFieldInvalid('email')">
              {{ getFieldError('email') }}
            </p>
          </label>

          <label class="field">
            <span>{{ 'auth.common.password' | translate }}</span>
            <div class="password-wrapper">
              <input
                [type]="showPassword() ? 'text' : 'password'"
                formControlName="password"
                autocomplete="current-password"
                [class.is-invalid]="isFieldInvalid('password')"
                [attr.aria-invalid]="isFieldInvalid('password') ? 'true' : 'false'"
                [attr.aria-describedby]="isFieldInvalid('password') ? 'password-error' : null" />
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
            <p class="field-error" [id]="'password-error'" role="alert" *ngIf="isFieldInvalid('password')">
              {{ getFieldError('password') }}
            </p>
          </label>

          @if (statusMessage()) {
            <div class="status-message" role="status" aria-live="polite">
              {{ statusMessage() }}
            </div>
          }

            <div class="card-actions">
              <button
                class="btn btn--primary"
                type="submit"
                [disabled]="loginForm.invalid || isSubmitting()"
                [attr.aria-busy]="isSubmitting() ? 'true' : null">
                @if (isSubmitting()) {
                  <div class="loading-spinner"></div>
                  {{ 'common.loading' | translate }}
                } @else {
                  {{ 'auth.login.cta' | translate }}
                }
              </button>
            </div>
        </form>
      </ng-container>
    </auth-shell>
  `,
  styleUrls: ['./login.page.scss']
})
export class LoginPage implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translationService = inject(TranslationService);
  private readonly toast = inject(ToastService);

  t = (k: string) => this.translationService.instant(k);
  pageTitleKey = 'auth.login.title';
  pageSubtitleKey = 'auth.login.subtitle';

  // Reactive state
  readonly isSubmitting = signal(false);
  readonly showPassword = signal(false);
  readonly statusMessage = signal('');

  readonly features = [
    {
      icon: 'i-rows',
      title: this.t('auth.features.suite.title'),
      subtitle: this.t('auth.features.suite.subtitle')
    },
    {
      icon: 'i-season',
      title: this.t('auth.features.multiseason.title'),
      subtitle: this.t('auth.features.multiseason.subtitle')
    },
    {
      icon: 'i-shield',
      title: this.t('auth.features.pro.title'),
      subtitle: this.t('auth.features.pro.subtitle')
    }
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
    return !!(control?.invalid && (control?.dirty || control?.touched));
  }

  getFieldError(field: string): string {
    const control = this.loginForm.get(field);
    if (control?.invalid && (control?.dirty || control?.touched)) {
      if (control.errors?.['required']) {
        return this.translationService.get(`auth.errors.required${field.charAt(0).toUpperCase() + field.slice(1)}`);
      }
      if (control.errors?.['email']) {
        return this.translationService.get('auth.errors.invalidEmail');
      }
      if (control.errors?.['minlength']) {
        return this.translationService.get('auth.errors.requiredPassword');
      }
    }
    return '';
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isSubmitting.set(true);
    this.statusMessage.set(this.translationService.get('auth.login.processing'));
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
    this.statusMessage.set(this.translationService.get('auth.login.selectingSchool'));

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

    this.isSubmitting.set(false);
    this.router.navigate(['/select-school']);
  }

  private handleSingleSeason(season: any, schoolId: number, accessToken: string): void {
    this.statusMessage.set(this.translationService.get('auth.login.selectingSeason'));

    this.authV5.selectSeason(season.id, schoolId, accessToken).subscribe({
      next: (_response) => {
        this.isSubmitting.set(false);
        this.statusMessage.set(this.translationService.get('auth.login.success'));

        this.toast.success(this.translationService.get('auth.login.success'));
        this.router.navigate(['/dashboard']);
      },
      error: (error) => {
        this.handleLoginError(`Season selection failed: ${error.message}`);
      }
    });
  }

  private handleLoginError(message: string): void {
    this.isSubmitting.set(false);
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
