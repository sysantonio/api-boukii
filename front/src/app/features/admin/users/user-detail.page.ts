import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, FormArray } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { switchMap, catchError, finalize } from 'rxjs/operators';
import { of, forkJoin } from 'rxjs';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AdminNavComponent } from '../../../shared/components/admin-nav/admin-nav.component';
import { UsersService } from './services/users.service';
import { RolesService } from '../roles/services/roles.service';
import { UserDetail } from './types/user.types';
import { Role } from '../roles/types/role.types';

@Component({
  selector: 'app-user-detail-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe, AdminNavComponent],
  template: `
    <div class="page" data-testid="user-detail-page">
      <!-- Admin Navigation -->
      <app-admin-nav></app-admin-nav>
      
      <!-- Loading State -->
      <div *ngIf="loading()" class="loading-state">
        <div class="skeleton-header"></div>
        <div class="skeleton-content">
          <div class="skeleton-card"></div>
          <div class="skeleton-card"></div>
        </div>
      </div>

      <!-- Content -->
      <div *ngIf="!loading() && user()">
        <!-- Page Header -->
        <div class="page-header" data-testid="page-header">
          <div class="page-header-content">
            <div class="page-title">
              <div class="breadcrumb">
                <button (click)="goBack()" class="breadcrumb-link">
                  <i class="icon-arrow-left"></i>
                  {{ 'users.title' | translate }}
                </button>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ user()?.name }}</span>
              </div>
              <h1>{{ 'users.detail.title' | translate }}</h1>
            </div>
            <div class="page-actions">
              <button 
                class="btn btn-outline" 
                (click)="editUser()"
                data-testid="edit-user-btn"
              >
                <i class="icon-edit"></i>
                {{ 'common.edit' | translate }}
              </button>
            </div>
          </div>
        </div>

        <div class="content-grid">
          <!-- User Information Card -->
          <div class="card" data-testid="user-info-card">
            <div class="card-header">
              <h2>{{ 'users.detail.userInfo' | translate }}</h2>
            </div>
            <div class="card-body">
              <div class="user-profile">
                <div class="user-avatar-large">
                  {{ getInitials(user()?.name || '') }}
                </div>
                <div class="user-details">
                  <h3>{{ user()?.name }}</h3>
                  <p class="user-email">{{ user()?.email }}</p>
                  <div class="user-meta">
                    <div class="meta-item">
                      <span class="meta-label">{{ 'users.detail.status' | translate }}:</span>
                      <span 
                        class="status-badge"
                        [class]="'status-' + user()?.status"
                      >
                        {{ 'users.status.' + user()?.status | translate }}
                      </span>
                    </div>
                    <div class="meta-item" *ngIf="user()?.phone">
                      <span class="meta-label">{{ 'users.detail.phone' | translate }}:</span>
                      <span>{{ user()?.phone }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Permissions (if available) -->
              <div class="permissions-section" *ngIf="user()?.permissions && user()?.permissions?.length">
                <h4>{{ 'users.detail.permissions' | translate }}</h4>
                <div class="permissions-list">
                  <span 
                    *ngFor="let permission of user()?.permissions" 
                    class="permission-tag"
                  >
                    {{ permission }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Role Assignment Card -->
          <div class="card" data-testid="user-roles-section">
            <div class="card-header">
              <h2>{{ 'users.detail.rolesSection' | translate }}</h2>
              <p class="card-subtitle">{{ 'users.detail.rolesSubtitle' | translate }}</p>
            </div>
            <div class="card-body">
              <form [formGroup]="rolesForm" (ngSubmit)="saveRoles()">
                <div class="roles-list" *ngIf="roles().length > 0; else noRoles">
                  <div 
                    *ngFor="let role of roles(); trackBy: trackByRoleId" 
                    class="role-item"
                  >
                    <label class="role-checkbox" [attr.data-testid]="'role-checkbox-' + role.id">
                      <input 
                        type="checkbox" 
                        [value]="role.id"
                        [checked]="isRoleAssigned(role.name)"
                        (change)="toggleRole(role, $event)"
                      />
                      <div class="checkbox-custom"></div>
                      <div class="role-info">
                        <div class="role-name">{{ role.description }}</div>
                        <div class="role-description">
                          {{ 'roles.permissions' | translate }}: 
                          <span class="permissions-count">{{ role.permissions.length }}</span>
                        </div>
                        <div class="role-permissions" *ngIf="role.permissions.length <= 5">
                          <span 
                            *ngFor="let permission of role.permissions" 
                            class="permission-chip"
                          >
                            {{ permission }}
                          </span>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>

                <ng-template #noRoles>
                  <div class="empty-state">
                    <p>{{ 'roles.noRoles' | translate }}</p>
                  </div>
                </ng-template>

                <!-- Form Actions -->
                <div class="form-actions" *ngIf="roles().length > 0">
                  <button 
                    type="button" 
                    class="btn btn-outline"
                    (click)="resetRoles()"
                    [disabled]="saving()"
                  >
                    {{ 'common.reset' | translate }}
                  </button>
                  <button 
                    type="submit" 
                    class="btn btn-primary"
                    [disabled]="saving() || !rolesForm.dirty"
                    data-testid="save-roles-btn"
                  >
                    <span *ngIf="saving()" class="spinner"></span>
                    {{ saving() ? ('common.saving' | translate) : ('users.detail.saveRoles' | translate) }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div *ngIf="!loading() && error()" class="error-state">
        <div class="error-content">
          <i class="icon-alert-circle"></i>
          <h3>{{ 'common.error' | translate }}</h3>
          <p>{{ error() }}</p>
          <button class="btn btn-primary" (click)="retry()">
            {{ 'common.retry' | translate }}
          </button>
        </div>
      </div>

      <!-- Success Message -->
      <div 
        *ngIf="successMessage()" 
        class="success-message"
        data-testid="success-message"
      >
        <i class="icon-check-circle"></i>
        {{ successMessage() }}
      </div>

      <!-- Error Message -->
      <div 
        *ngIf="errorMessage()" 
        class="error-message"
        data-testid="error-message"
      >
        <i class="icon-alert-circle"></i>
        {{ errorMessage() }}
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
      margin: var(--space-sm) 0 0 0;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: var(--space-xs);
      margin-bottom: var(--space-sm);
    }

    .breadcrumb-link {
      background: none;
      border: none;
      color: var(--color-text-secondary);
      text-decoration: none;
      font-size: var(--font-size-sm);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: var(--space-xs);
      padding: var(--space-xs);
      border-radius: var(--radius-sm);
      transition: background-color 0.2s ease;
    }

    .breadcrumb-link:hover {
      background: var(--color-surface-hover);
      color: var(--color-text-primary);
    }

    .breadcrumb-separator {
      color: var(--color-text-tertiary);
      font-size: var(--font-size-sm);
    }

    .breadcrumb-current {
      color: var(--color-text-primary);
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
    }

    .content-grid {
      display: grid;
      grid-template-columns: 400px 1fr;
      gap: var(--space-lg);
    }

    .card {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
    }

    .card-header {
      padding: var(--space-md);
      border-bottom: 1px solid var(--color-border);
    }

    .card-header h2 {
      margin: 0 0 var(--space-xs) 0;
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .card-subtitle {
      margin: 0;
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
    }

    .card-body {
      padding: var(--space-md);
    }

    .user-profile {
      display: flex;
      gap: var(--space-md);
      margin-bottom: var(--space-lg);
    }

    .user-avatar-large {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--color-primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: var(--font-size-xl);
      font-weight: var(--font-weight-bold);
      flex-shrink: 0;
    }

    .user-details h3 {
      margin: 0 0 var(--space-xs) 0;
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .user-email {
      margin: 0 0 var(--space-md) 0;
      color: var(--color-text-secondary);
      font-size: var(--font-size-base);
    }

    .user-meta {
      display: flex;
      flex-direction: column;
      gap: var(--space-sm);
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: var(--space-xs);
    }

    .meta-label {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      font-weight: var(--font-weight-medium);
    }

    .status-badge {
      padding: var(--space-xs) var(--space-sm);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
    }

    .status-active {
      background: var(--color-success-light);
      color: var(--color-success);
    }

    .status-inactive {
      background: var(--color-warning-light);
      color: var(--color-warning);
    }

    .status-pending {
      background: var(--color-info-light);
      color: var(--color-info);
    }

    .permissions-section {
      margin-top: var(--space-lg);
      padding-top: var(--space-lg);
      border-top: 1px solid var(--color-border);
    }

    .permissions-section h4 {
      margin: 0 0 var(--space-md) 0;
      font-size: var(--font-size-base);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .permissions-list {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-xs);
    }

    .permission-tag {
      padding: var(--space-xs) var(--space-sm);
      background: var(--color-surface-secondary);
      color: var(--color-text-secondary);
      font-size: var(--font-size-xs);
      border-radius: var(--radius-sm);
      border: 1px solid var(--color-border);
    }

    .roles-list {
      display: flex;
      flex-direction: column;
      gap: var(--space-md);
      margin-bottom: var(--space-lg);
    }

    .role-item {
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      transition: border-color 0.2s ease;
    }

    .role-item:hover {
      border-color: var(--color-primary-light);
    }

    .role-checkbox {
      display: flex;
      align-items: flex-start;
      gap: var(--space-md);
      padding: var(--space-md);
      cursor: pointer;
      width: 100%;
    }

    .role-checkbox input[type="checkbox"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .checkbox-custom {
      width: 20px;
      height: 20px;
      border: 2px solid var(--color-border);
      border-radius: var(--radius-sm);
      background: var(--color-surface);
      transition: all 0.2s ease;
      position: relative;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .role-checkbox input[type="checkbox"]:checked + .checkbox-custom {
      background: var(--color-primary);
      border-color: var(--color-primary);
    }

    .role-checkbox input[type="checkbox"]:checked + .checkbox-custom::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 10px;
      height: 10px;
      background: white;
      mask: url('data:image/svg+xml,<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>') no-repeat center center;
    }

    .role-info {
      flex: 1;
    }

    .role-name {
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
      margin-bottom: var(--space-xs);
    }

    .role-description {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      margin-bottom: var(--space-sm);
    }

    .permissions-count {
      font-weight: var(--font-weight-medium);
      color: var(--color-primary);
    }

    .role-permissions {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-xs);
    }

    .permission-chip {
      padding: 2px var(--space-xs);
      background: var(--color-primary-light);
      color: var(--color-primary);
      font-size: var(--font-size-xs);
      border-radius: var(--radius-xs);
    }

    .form-actions {
      display: flex;
      gap: var(--space-sm);
      padding-top: var(--space-lg);
      border-top: 1px solid var(--color-border);
      justify-content: flex-end;
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

    .btn-outline {
      background: transparent;
      color: var(--color-text-primary);
      border: 1px solid var(--color-border);
    }

    .btn-outline:hover:not(:disabled) {
      background: var(--color-surface-hover);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .spinner {
      width: 16px;
      height: 16px;
      border: 2px solid transparent;
      border-top: 2px solid currentColor;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .loading-state,
    .error-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 400px;
      text-align: center;
      color: var(--color-text-secondary);
    }

    .skeleton-header,
    .skeleton-card {
      background: linear-gradient(90deg, var(--color-surface-secondary) 25%, var(--color-surface-hover) 50%, var(--color-surface-secondary) 75%);
      background-size: 200% 100%;
      border-radius: var(--radius-md);
      animation: shimmer 1.5s infinite;
    }

    .skeleton-header {
      height: 60px;
      margin-bottom: var(--space-lg);
    }

    .skeleton-content {
      display: grid;
      grid-template-columns: 400px 1fr;
      gap: var(--space-lg);
    }

    .skeleton-card {
      height: 300px;
    }

    .success-message,
    .error-message {
      position: fixed;
      top: var(--space-lg);
      right: var(--space-lg);
      padding: var(--space-md);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      gap: var(--space-sm);
      font-weight: var(--font-weight-medium);
      box-shadow: var(--shadow-lg);
      z-index: 1000;
      animation: slideInRight 0.3s ease;
    }

    .success-message {
      background: var(--color-success-light);
      color: var(--color-success);
      border: 1px solid var(--color-success);
    }

    .error-message {
      background: var(--color-danger-light);
      color: var(--color-danger);
      border: 1px solid var(--color-danger);
    }

    @keyframes slideInRight {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    .empty-state {
      text-align: center;
      padding: var(--space-xl);
      color: var(--color-text-secondary);
    }

    @media (max-width: 768px) {
      .page {
        padding: var(--space-md);
      }

      .page-header-content {
        flex-direction: column;
        align-items: stretch;
      }

      .content-grid {
        grid-template-columns: 1fr;
      }

      .user-profile {
        flex-direction: column;
        text-align: center;
      }

      .form-actions {
        flex-direction: column;
      }
    }
  `]
})
export class UserDetailPageComponent implements OnInit {
  private readonly usersService = inject(UsersService);
  private readonly rolesService = inject(RolesService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly fb = inject(FormBuilder);

  // Reactive state using signals
  user = signal<UserDetail | null>(null);
  roles = signal<Role[]>([]);
  loading = signal(true);
  saving = signal(false);
  error = signal<string | null>(null);
  successMessage = signal<string | null>(null);
  errorMessage = signal<string | null>(null);

  // Form
  rolesForm: FormGroup;
  private userId?: number;
  private originalUserRoles: string[] = [];

  constructor() {
    this.rolesForm = this.fb.group({
      selectedRoles: this.fb.array([])
    });
  }

  ngOnInit(): void {
    this.route.params.pipe(
      switchMap(params => {
        this.userId = +params['id'];
        this.loading.set(true);
        this.error.set(null);

        // Load user and roles in parallel
        return forkJoin({
          user: this.usersService.getUserById(this.userId),
          roles: this.rolesService.getRoles()
        });
      }),
      catchError(error => {
        this.error.set('Failed to load user details');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    ).subscribe(result => {
      if (result) {
        this.user.set(result.user);
        this.roles.set(result.roles.data);
        this.originalUserRoles = [...(result.user.roles || [])];
        this.initializeForm();
      }
    });
  }

  private initializeForm(): void {
    // The form will be managed by individual checkbox change handlers
    // This simplifies the reactive forms complexity for this use case
  }

  isRoleAssigned(roleName: string): boolean {
    return this.user()?.roles.includes(roleName) || false;
  }

  toggleRole(role: Role, event: Event): void {
    const checkbox = event.target as HTMLInputElement;
    const currentUser = this.user();
    
    if (!currentUser) return;

    const currentRoles = [...currentUser.roles];
    
    if (checkbox.checked && !currentRoles.includes(role.name)) {
      currentRoles.push(role.name);
    } else if (!checkbox.checked) {
      const index = currentRoles.indexOf(role.name);
      if (index > -1) {
        currentRoles.splice(index, 1);
      }
    }

    // Update user signal with new roles
    this.user.set({ ...currentUser, roles: currentRoles });
    
    // Mark form as dirty
    this.rolesForm.markAsDirty();
  }

  saveRoles(): void {
    const currentUser = this.user();
    if (!currentUser || !this.userId) return;

    this.saving.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    // Map role names to role IDs
    const selectedRoleIds = currentUser.roles
      .map(roleName => this.roles().find(role => role.name === roleName)?.id)
      .filter(id => id !== undefined) as number[];

    this.usersService.updateUserRoles(this.userId, selectedRoleIds).pipe(
      catchError(error => {
        this.errorMessage.set(error.message || 'Failed to update user roles');
        return of(null);
      }),
      finalize(() => this.saving.set(false))
    ).subscribe(result => {
      if (result !== null) {
        this.successMessage.set('User roles updated successfully');
        this.originalUserRoles = [...currentUser.roles];
        this.rolesForm.markAsPristine();
        
        // Clear success message after 3 seconds
        setTimeout(() => this.successMessage.set(null), 3000);
      }
    });
  }

  resetRoles(): void {
    const currentUser = this.user();
    if (!currentUser) return;

    // Reset to original roles
    this.user.set({ ...currentUser, roles: [...this.originalUserRoles] });
    this.rolesForm.markAsPristine();
  }

  editUser(): void {
    // TODO: Navigate to edit user page
    console.log('Edit user clicked');
  }

  goBack(): void {
    this.router.navigate(['/admin/users']);
  }

  retry(): void {
    this.ngOnInit();
  }

  getInitials(name: string): string {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .substring(0, 2);
  }

  trackByRoleId(index: number, role: Role): number {
    return role.id;
  }
}