import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AdminNavComponent } from '../../../shared/components/admin-nav/admin-nav.component';
import { RolesService } from './services/roles.service';
import { Role } from './types/role.types';
import { catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-roles-list-page',
  standalone: true,
  imports: [CommonModule, TranslatePipe, AdminNavComponent],
  template: `
    <div class="page" data-testid="roles-page">
      <!-- Admin Navigation -->
      <app-admin-nav></app-admin-nav>
      
      <!-- Page Header -->
      <div class="page-header" data-testid="page-header">
        <div class="page-header-content">
          <div class="page-title">
            <h1>{{ 'roles.title' | translate }}</h1>
            <p class="page-subtitle">{{ 'roles.subtitle' | translate }}</p>
          </div>
        </div>
      </div>

      <!-- Roles Grid -->
      <div class="roles-grid" *ngIf="!loading() && roles().length > 0">
        <div 
          *ngFor="let role of roles(); trackBy: trackByRoleId" 
          class="role-card"
          [attr.data-testid]="'role-card-' + role.id"
        >
          <div class="role-header">
            <h3 class="role-name">{{ role.description }}</h3>
            <span class="role-identifier">{{ role.name }}</span>
          </div>
          
          <div class="role-body">
            <div class="permissions-section">
              <h4>{{ 'roles.permissions' | translate }} ({{ role.permissions.length }})</h4>
              <div class="permissions-list" *ngIf="role.permissions.length <= 8; else manyPermissions">
                <span 
                  *ngFor="let permission of role.permissions" 
                  class="permission-chip"
                >
                  {{ permission }}
                </span>
              </div>
              <ng-template #manyPermissions>
                <div class="permissions-list">
                  <span 
                    *ngFor="let permission of role.permissions.slice(0, 6)" 
                    class="permission-chip"
                  >
                    {{ permission }}
                  </span>
                  <span class="permission-chip more-indicator">
                    +{{ role.permissions.length - 6 }} {{ 'common.more' | translate }}
                  </span>
                </div>
              </ng-template>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div *ngIf="loading()" class="loading-state">
        <div class="roles-grid">
          <div *ngFor="let _ of skeletonItems" class="role-card skeleton">
            <div class="skeleton-header"></div>
            <div class="skeleton-content"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div *ngIf="!loading() && roles().length === 0" class="empty-state">
        <div class="empty-state-content">
          <i class="icon-shield"></i>
          <h3>{{ 'roles.empty.title' | translate }}</h3>
          <p>{{ 'roles.empty.message' | translate }}</p>
        </div>
      </div>

      <!-- Error State -->
      <div *ngIf="error()" class="error-state">
        <div class="error-content">
          <i class="icon-alert-circle"></i>
          <h3>{{ 'common.error' | translate }}</h3>
          <p>{{ error() }}</p>
          <button class="btn btn-primary" (click)="retry()">
            {{ 'common.retry' | translate }}
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .page {
      padding: var(--space-lg);
      max-width: 1200px;
      margin: 0 auto;
    }

    .page-header {
      margin-bottom: var(--space-lg);
      border-bottom: 1px solid var(--color-border);
      padding-bottom: var(--space-md);
    }

    .page-header-content {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: var(--space-md);
    }

    .page-title h1 {
      font-size: var(--font-size-xl);
      font-weight: var(--font-weight-bold);
      color: var(--color-text-primary);
      margin: 0 0 var(--space-xs) 0;
    }

    .page-subtitle {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      margin: 0;
    }

    .roles-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: var(--space-lg);
    }

    .role-card {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      transition: all 0.2s ease;
    }

    .role-card:hover {
      box-shadow: var(--shadow-md);
      border-color: var(--color-primary-light);
    }

    .role-card.skeleton {
      overflow: hidden;
    }

    .role-header {
      padding: var(--space-md);
      border-bottom: 1px solid var(--color-border);
    }

    .role-name {
      margin: 0 0 var(--space-xs) 0;
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .role-identifier {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      font-family: var(--font-family-mono);
      background: var(--color-surface-secondary);
      padding: 2px var(--space-xs);
      border-radius: var(--radius-xs);
    }

    .role-body {
      padding: var(--space-md);
    }

    .permissions-section h4 {
      margin: 0 0 var(--space-sm) 0;
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-secondary);
    }

    .permissions-list {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-xs);
    }

    .permission-chip {
      padding: 2px var(--space-xs);
      background: var(--color-primary-light);
      color: var(--color-primary);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
      border-radius: var(--radius-xs);
      font-family: var(--font-family-mono);
    }

    .permission-chip.more-indicator {
      background: var(--color-surface-secondary);
      color: var(--color-text-secondary);
      font-family: var(--font-family-base);
    }

    .loading-state,
    .empty-state,
    .error-state {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 300px;
    }

    .empty-state-content,
    .error-content {
      text-align: center;
      color: var(--color-text-secondary);
    }

    .empty-state-content i,
    .error-content i {
      font-size: 48px;
      margin-bottom: var(--space-md);
      opacity: 0.5;
    }

    .empty-state-content h3,
    .error-content h3 {
      margin: 0 0 var(--space-sm) 0;
      color: var(--color-text-primary);
    }

    .skeleton-header,
    .skeleton-content {
      background: linear-gradient(90deg, var(--color-surface-secondary) 25%, var(--color-surface-hover) 50%, var(--color-surface-secondary) 75%);
      background-size: 200% 100%;
      border-radius: var(--radius-sm);
      animation: shimmer 1.5s infinite;
    }

    .skeleton-header {
      height: 60px;
      margin: var(--space-md);
      margin-bottom: 0;
    }

    .skeleton-content {
      height: 80px;
      margin: var(--space-md);
    }

    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    .btn {
      padding: var(--space-sm) var(--space-md);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: var(--space-xs);
      transition: all 0.2s ease;
    }

    .btn-primary {
      background: var(--color-primary);
      color: white;
    }

    .btn-primary:hover:not(:disabled) {
      background: var(--color-primary-dark);
    }

    @media (max-width: 768px) {
      .page {
        padding: var(--space-md);
      }

      .roles-grid {
        grid-template-columns: 1fr;
      }
    }
  `]
})
export class RolesListPageComponent implements OnInit {
  private readonly rolesService = inject(RolesService);

  // Reactive state using signals
  roles = signal<Role[]>([]);
  loading = signal(true);
  error = signal<string | null>(null);

  // UI state
  skeletonItems = Array.from({ length: 6 }, (_, i) => i);

  ngOnInit(): void {
    this.loadRoles();
  }

  private loadRoles(): void {
    this.loading.set(true);
    this.error.set(null);

    this.rolesService.getRoles().pipe(
      catchError(error => {
        this.error.set('Failed to load roles');
        return of({ data: [] as Role[] });
      }),
      finalize(() => this.loading.set(false))
    ).subscribe(response => {
      this.roles.set(response.data);
    });
  }

  retry(): void {
    this.loadRoles();
  }

  trackByRoleId(index: number, role: Role): number {
    return role.id;
  }
}