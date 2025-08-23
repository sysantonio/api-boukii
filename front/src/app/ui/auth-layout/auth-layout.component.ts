import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { ThemeToggleComponent } from '@ui/theme-toggle/theme-toggle.component';
import { LanguageSelectorComponent } from '@shared/components/language-selector/language-selector.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { UiStore } from '@core/stores/ui.store';

@Component({
  selector: 'app-auth-layout',
  standalone: true,
  imports: [
    CommonModule,
    RouterOutlet,
    ThemeToggleComponent,
    LanguageSelectorComponent,
    TranslatePipe,
  ],
  template: `
    <div class="auth-layout">
      <!-- Auth Header -->
      <header class="auth-header">
        <div class="header-content">
          <div class="app-logo">
            <span class="logo-text">bouk<span class="logo-accent">ii</span></span>
          </div>

          <div class="header-actions">
            <app-language-selector />
            <app-theme-toggle />
          </div>
        </div>
      </header>

      <!-- Auth Content -->
      <main class="auth-main" role="main">
        <div class="auth-container">
          <router-outlet />
        </div>
      </main>

      <!-- Background Elements -->
      <div class="auth-background">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
      </div>
    </div>
  `,
  styles: [
    `
      .auth-layout {
        position: relative;
        min-height: 100vh;
        background: var(--gradient-primary);
        overflow: hidden;
      }

      /* === HEADER === */
      .auth-header {
        position: relative;
        z-index: 2;
        padding: var(--space-6) var(--space-8);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: var(--glass-backdrop);
        border-bottom: var(--glass-border);
      }

      .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: var(--content-max-width);
        margin: 0 auto;
      }

      .app-logo {
        display: flex;
        align-items: center;
      }

      .logo-text {
        font-size: 2rem;
        font-weight: var(--font-weight-bold);
        color: var(--color-white);
        font-family: var(--font-family-sans);
        letter-spacing: -0.025em;
      }

      .logo-accent {
        color: var(--color-turquoise-400);
      }

      .header-actions {
        display: flex;
        align-items: center;
        gap: var(--space-4);
      }

      /* === MAIN CONTENT === */
      .auth-main {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - var(--navbar-height));
        padding: var(--space-16) var(--space-8);
      }

      .auth-container {
        width: 100%;
        max-width: 480px;
        padding: var(--space-10);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--radius-3xl);
        box-shadow: var(--shadow-glass);
        border: var(--glass-border);
      }

      /* === BACKGROUND ELEMENTS === */
      .auth-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
        pointer-events: none;
      }

      .gradient-orb {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
      }

      .orb-1 {
        width: 300px;
        height: 300px;
        top: 10%;
        left: -5%;
        background: radial-gradient(circle, rgba(0, 212, 170, 0.15) 0%, transparent 70%);
        animation-delay: 0s;
      }

      .orb-2 {
        width: 200px;
        height: 200px;
        top: 50%;
        right: -5%;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
        animation-delay: 2s;
      }

      .orb-3 {
        width: 400px;
        height: 400px;
        bottom: -10%;
        left: 20%;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        animation-delay: 4s;
      }

      @keyframes float {
        0%, 100% {
          transform: translateY(0px) rotate(0deg);
        }
        25% {
          transform: translateY(-20px) rotate(90deg);
        }
        50% {
          transform: translateY(0px) rotate(180deg);
        }
        75% {
          transform: translateY(20px) rotate(270deg);
        }
      }

      /* === RESPONSIVE === */
      @media (max-width: 768px) {
        .auth-header {
          padding: var(--space-4);
        }

        .header-content {
          gap: var(--space-4);
        }

        .logo-text {
          font-size: 1.5rem;
        }

        .header-actions {
          gap: var(--space-3);
        }

        .auth-main {
          padding: var(--space-8) var(--space-4);
        }

        .auth-container {
          padding: var(--space-8);
          border-radius: var(--radius-2xl);
        }

        .orb-1, .orb-2, .orb-3 {
          transform: scale(0.7);
        }
      }

      @media (max-width: 480px) {
        .auth-header {
          padding: var(--space-3);
        }

        .logo-text {
          font-size: 1.25rem;
        }

        .header-actions {
          gap: var(--space-2);
        }

        .auth-main {
          padding: var(--space-6) var(--space-3);
        }

        .auth-container {
          padding: var(--space-6);
          max-width: 100%;
        }

        .orb-1, .orb-2, .orb-3 {
          transform: scale(0.5);
        }
      }

      /* === ACCESSIBILITY === */
      @media (prefers-reduced-motion: reduce) {
        .gradient-orb {
          animation: none;
        }
      }
    `,
  ],
})
export class AuthLayoutComponent implements OnInit {
  protected readonly ui = inject(UiStore);

  ngOnInit(): void {
    // Initialize theme system
    this.ui.initTheme();
  }
}