import { Component, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AdminNavComponent } from '../../../shared/components/admin-nav/admin-nav.component';
import { PermissionMatrixComponent } from './components/permission-matrix/permission-matrix.component';

@Component({
  selector: 'app-permissions-page',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    TranslatePipe,
    AdminNavComponent,
    PermissionMatrixComponent
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <div class="permissions-page">
      <!-- Admin Navigation -->
      <app-admin-nav [currentSection]="'permissions'"></app-admin-nav>
      
      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-content">
          <div class="breadcrumb">
            <a href="/admin" class="breadcrumb-link">
              {{ 'admin.title' | translate }}
            </a>
            <span class="breadcrumb-separator">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06L7.28 12.78a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z"/>
              </svg>
            </span>
            <span class="breadcrumb-current">
              {{ 'permissions.title' | translate }}
            </span>
          </div>
          
          <h1 class="page-title">
            {{ 'permissions.title' | translate }}
          </h1>
          
          <p class="page-description">
            {{ 'permissions.description' | translate }}
          </p>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="page-content">
        <app-permission-matrix></app-permission-matrix>
      </div>
    </div>
  `,
  styles: [`
    .permissions-page {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: var(--surface-background);
    }

    .page-header {
      background: var(--surface-primary);
      border-bottom: 1px solid var(--border-color);
      padding: var(--spacing-xl) var(--spacing-xl) var(--spacing-lg);
    }

    .page-header-content {
      max-width: 1200px;
      margin: 0 auto;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      margin-bottom: var(--spacing-md);
      font-size: var(--font-size-sm);
    }

    .breadcrumb-link {
      color: var(--text-secondary);
      text-decoration: none;
      transition: color var(--transition-default);
    }

    .breadcrumb-link:hover {
      color: var(--text-primary);
    }

    .breadcrumb-separator {
      color: var(--text-tertiary);
      display: flex;
      align-items: center;
    }

    .breadcrumb-current {
      color: var(--text-primary);
      font-weight: var(--font-weight-medium);
    }

    .page-title {
      font-size: var(--font-size-2xl);
      font-weight: var(--font-weight-bold);
      color: var(--text-primary);
      margin: 0 0 var(--spacing-sm) 0;
      line-height: 1.2;
    }

    .page-description {
      color: var(--text-secondary);
      margin: 0;
      max-width: 600px;
      line-height: 1.5;
    }

    .page-content {
      flex: 1;
      padding: var(--spacing-xl);
    }

    .page-content > * {
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .page-header {
        padding: var(--spacing-lg) var(--spacing-md) var(--spacing-md);
      }

      .page-title {
        font-size: var(--font-size-xl);
      }

      .page-content {
        padding: var(--spacing-lg) var(--spacing-md);
      }

      .breadcrumb {
        flex-wrap: wrap;
      }
    }

    @media (max-width: 480px) {
      .page-header {
        padding: var(--spacing-md);
      }

      .page-title {
        font-size: var(--font-size-lg);
      }

      .page-content {
        padding: var(--spacing-md);
      }
    }
  `]
})
export class PermissionsPageComponent {}