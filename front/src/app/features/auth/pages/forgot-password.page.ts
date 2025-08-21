import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { AuthV5Service } from '../../../core/services/auth-v5.service';
import { TranslationService } from '../../../core/services/translation.service';
import { ToastService } from '../../../core/services/toast.service';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AuthShellComponent } from '../ui/auth-shell/auth-shell.component';

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
    <bk-auth-shell
      [titleKey]="'auth.forgotPassword.welcome.title'"
      [subtitleKey]="'auth.forgotPassword.subtitle'"
      [features]="features">

      <div class="card-header">
        <h1 class="card-title">{{ 'auth.forgotPassword.title' | translate }}</h1>
        <p class="card-subtitle">{{ 'auth.forgotPassword.subtitle' | translate }}</p>
      </div>

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
                {{ 'auth.forgotPassword.button' | translate }}
              }
            </button>

            <div class="links">
              <a routerLink="/auth/login">{{ 'auth.forgotPassword.rememberedPassword' | translate }}</a>
            </div>
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
            <div class="links">
              <a routerLink="/auth/login">{{ 'auth.forgotPassword.rememberedPassword' | translate }}</a>
            </div>
          </div>
        </div>
      }
    </bk-auth-shell>
  `,
})
export class ForgotPasswordPage implements OnInit {
  // DI
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly t = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Señales reactivas
  readonly isLoading = signal(false);
  readonly emailSent = signal(false);
  readonly statusMessage = signal('');

  // Formulario (usamos 'form' porque así lo referencia la plantilla)
  form!: FormGroup;

  // Features for AuthShell
  readonly features = [
    { title: 'auth.hero.feature1', subtitle: 'auth.hero.feature1Desc' },
    { title: 'auth.hero.feature2', subtitle: 'auth.hero.feature2Desc' },
    { title: 'auth.hero.feature3', subtitle: 'auth.hero.feature3Desc' }
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
    const sentMsg = this.t.get('auth.forgotPassword.emailSent');
    const processingMsg = this.t.get('auth.forgotPassword.processing');
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
    if (c.errors['required']) return this.t.get('auth.errors.requiredEmail');
    if (c.errors['email']) return this.t.get('auth.errors.invalidEmail');
    return this.t.get('auth.errors.fieldInvalid');
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
