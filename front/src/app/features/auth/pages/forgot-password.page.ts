import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AuthShellComponent } from '@features/auth/ui/auth-shell/auth-shell.component';

@Component({
  selector: 'app-forgot-password-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    TranslatePipe,
    AuthShellComponent,
  ],
  styleUrls: ['./forgot-password.page.scss'],
  template: `
    <auth-shell
      [brandLine]="t('auth.brandLine')"
      [title]="t(pageTitleKey)"
      [subtitle]="t(pageSubtitleKey)"
      [features]="features"
    >
      <ng-container auth-form>
        <!-- Form State -->
        @if (!emailSent()) {
          <form [formGroup]="form" (ngSubmit)="submit()" novalidate>
            <label class="field">
              <span>{{ 'auth.common.email' | translate }}</span>
              <input
                type="email"
                formControlName="email"
                autocomplete="email"
                [class.is-invalid]="isFieldInvalid('email')"
              />
              <small class="field-error" *ngIf="isFieldInvalid('email')">
                {{ getFieldError('email') }}
              </small>
            </label>

            @if (statusMessage() && isLoading()) {
              <div class="status-message" role="status" aria-live="polite">
                {{ statusMessage() }}
              </div>
            }

            <div class="card-actions">
              <button class="btn btn--primary" type="submit" [disabled]="form.invalid || isLoading()">
                @if (isLoading()) {
                  <div class="loading-spinner"></div>
                  {{ 'common.loading' | translate }}
                } @else {
                  {{ 'auth.forgotPassword.cta' | translate }}
                }
              </button>
            </div>
          </form>
        }

        <!-- Success State -->
        @if (emailSent()) {
          <div class="success-state">
            <div class="success-icon">
              <i class="i-mail"></i>
            </div>

            <div class="success-content">
              <h3 class="success-title">{{ 'auth.forgotPassword.successTitle' | translate }}</h3>
              <p class="success-message">{{ statusMessage() }}</p>
              <p class="help-text">{{ 'auth.forgotPassword.checkSpam' | translate }}</p>
            </div>

            <div class="card-actions">
              <button class="btn btn--primary" type="button" (click)="sendAnother()">
                {{ 'auth.forgotPassword.sendAnother' | translate }}
              </button>
            </div>
          </div>
        }
      </ng-container>
    </auth-shell>
  `,
})
export class ForgotPasswordPage implements OnInit {
  // DI
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translationService = inject(TranslationService);
  private readonly toast = inject(ToastService);

  t = (k: string) => this.translationService.instant(k);
  pageTitleKey = 'auth.forgotPassword.title';
  pageSubtitleKey = 'auth.forgotPassword.subtitle';

  // Señales reactivas
  readonly isLoading = signal(false);
  readonly emailSent = signal(false);
  readonly statusMessage = signal('');

  // Formulario (usamos 'form' porque así lo referencia la plantilla)
  form!: FormGroup;

  // Features for AuthShell
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

  ngOnInit(): void {
    this.form = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
    });

    // Si ya está autenticado, fuera de aquí
    if (this.authV5.isAuthenticated?.()) {
      this.router.navigate(['/dashboard']);
    }
  }

  // === Template API (coincide con el HTML) ===
  submit(): void {
    if (this.form.invalid || this.isLoading()) {
      this.form.markAllAsTouched();
      return;
    }

    this.isLoading.set(true);
    const email: string = this.form.value.email;

    // Mensaje optimista inmediato (patrón seguro)
    const sentMsg = this.t('auth.forgotPassword.emailSent');
    const processingMsg = this.t('auth.forgotPassword.processing');
    this.statusMessage.set(processingMsg);

    // Soporta tanto Promise como Observable; si tu servicio devuelve Observable, .subscribe sigue funcionando
    const maybe$ = this.authV5.forgotPassword?.(email);
    if (!maybe$) {
      // Fallback (si el servicio aún no está implementado)
      this.finishAsSent(sentMsg);
      return;
    }

    // Si es Observable
    if (typeof (maybe$ as any).subscribe === 'function') {
      (maybe$ as any).subscribe({
        next: () => this.finishAsSent(sentMsg),
        error: () => this.finishAsSent(sentMsg),
      });
      return;
    }

    // Si es Promise
    Promise.resolve(maybe$ as any)
      .then(() => this.finishAsSent(sentMsg))
      .catch(() => this.finishAsSent(sentMsg));
  }

  isFieldInvalid(field: string): boolean {
    const c = this.form.get(field);
    return !!(c && c.invalid && (c.dirty || c.touched));
  }

  getFieldError(field: string): string {
    const c = this.form.get(field);
    if (!c || !c.errors) return '';
    if (c.errors['required']) return this.t('auth.errors.requiredEmail');
    if (c.errors['email']) return this.t('auth.errors.invalidEmail');
    return this.t('auth.errors.fieldInvalid');
  }

  sendAnother(): void {
    this.emailSent.set(false);
    this.statusMessage.set('');
    this.form.reset();
  }

  // === Helpers ===
  private finishAsSent(message: string) {
    this.isLoading.set(false);
    this.emailSent.set(true);
    this.statusMessage.set(message);
    // Mensaje de toast “silencioso” y genérico
    this.toast.success?.(message);
  }
}
