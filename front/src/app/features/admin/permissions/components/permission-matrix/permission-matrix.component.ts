import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { debounceTime, distinctUntilChanged, startWith, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';
import { TranslatePipe } from '../../../../../shared/pipes/translate.pipe';
import { SchoolPermissionsService } from '../../services/school-permissions.service';
import { UsersService } from '../../../users/services/users.service';
import { RolesService } from '../../../roles/services/roles.service';
import { 
  UserPermissionMatrix, 
  PermissionAssignmentFilters,
  School,
  SchoolPermission 
} from '../../types/school-permissions.types';
import { Role } from '../../../roles/types/role.types';
import { UserListItem } from '../../../users/types/user.types';

@Component({
  selector: 'app-permission-matrix',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe],
  template: `
    <div class="permission-matrix-container" data-testid="permission-matrix">
      <!-- Filters -->
      <div class="matrix-filters" data-testid="matrix-filters">
        <form [formGroup]="filtersForm" class="filters-form">
          <div class="filter-group">
            <label for="search">{{ 'permissions.matrix.filters.search' | translate }}</label>
            <input 
              id="search"
              type="text" 
              formControlName="search" 
              [placeholder]="'permissions.matrix.filters.searchPlaceholder' | translate"
              class="form-input"
            />
          </div>
          
          <div class="filter-group">
            <label for="school">{{ 'permissions.matrix.filters.school' | translate }}</label>
            <select id="school" formControlName="schoolId" class="form-select">
              <option value="">{{ 'permissions.matrix.filters.allSchools' | translate }}</option>
              <option *ngFor="let school of schools()" [value]="school.id">
                {{ school.name }}
              </option>
            </select>
          </div>
          
          <div class="filter-group">
            <label for="role">{{ 'permissions.matrix.filters.role' | translate }}</label>
            <select id="role" formControlName="role" class="form-select">
              <option value="">{{ 'permissions.matrix.filters.allRoles' | translate }}</option>
              <option *ngFor="let role of roles()" [value]="role.name">
                {{ role.description }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label for="status">{{ 'permissions.matrix.filters.status' | translate }}</label>
            <select id="status" formControlName="status" class="form-select">
              <option value="">{{ 'permissions.matrix.filters.allStatuses' | translate }}</option>
              <option value="active">{{ 'permissions.status.active' | translate }}</option>
              <option value="inactive">{{ 'permissions.status.inactive' | translate }}</option>
              <option value="expired">{{ 'permissions.status.expired' | translate }}</option>
            </select>
          </div>

          <div class="filter-actions">
            <button type="button" (click)="exportMatrix()" class="btn btn-outline">
              <i class="icon-download"></i>
              {{ 'permissions.matrix.export' | translate }}
            </button>
            <button type="button" (click)="openBulkAssignment()" class="btn btn-primary">
              <i class="icon-users"></i>
              {{ 'permissions.matrix.bulkAssign' | translate }}
            </button>
          </div>
        </form>
      </div>

      <!-- Matrix Table -->
      <div class="matrix-table-container" *ngIf="!loading() && userMatrix().length > 0">
        <table class="permission-matrix-table" data-testid="matrix-table">
          <thead>
            <tr>
              <th class="user-column">{{ 'permissions.matrix.table.user' | translate }}</th>
              <th *ngFor="let school of schools()" class="school-column">
                <div class="school-header">
                  <span class="school-name">{{ school.name }}</span>
                  <span class="school-code">{{ school.code }}</span>
                </div>
              </th>
              <th class="actions-column">{{ 'permissions.matrix.table.actions' | translate }}</th>
            </tr>
          </thead>
          <tbody>
            <tr *ngFor="let userPermission of userMatrix(); trackBy: trackByUserId" class="matrix-row">
              <td class="user-cell">
                <div class="user-info">
                  <div class="user-avatar">{{ getInitials(userPermission.userName) }}</div>
                  <div class="user-details">
                    <div class="user-name">{{ userPermission.userName }}</div>
                    <div class="user-email">{{ userPermission.userEmail }}</div>
                  </div>
                </div>
              </td>
              
              <td *ngFor="let school of schools(); trackBy: trackBySchoolId" class="permission-cell">
                <div class="school-permissions" [attr.data-testid]="'permissions-' + userPermission.userId + '-' + school.id">
                  <ng-container *ngIf="getSchoolPermission(userPermission, school.id); let schoolPerm">
                    <div class="roles-section" *ngIf="schoolPerm.roles.length > 0">
                      <span *ngFor="let role of schoolPerm.roles" class="role-badge" [class]="'role-' + role">
                        {{ role }}
                      </span>
                    </div>
                    <div class="status-indicator" [class]="schoolPerm.isActive ? 'status-active' : 'status-inactive'">
                      <i [class]="schoolPerm.isActive ? 'icon-check-circle' : 'icon-x-circle'"></i>
                    </div>
                    <button 
                      class="edit-permissions-btn"
                      (click)="editUserSchoolPermissions(userPermission.userId, school.id)"
                      [attr.data-testid]="'edit-permissions-' + userPermission.userId + '-' + school.id"
                    >
                      <i class="icon-edit"></i>
                    </button>
                  </ng-container>
                  <ng-container *ngIf="!getSchoolPermission(userPermission, school.id)">
                    <button 
                      class="add-permissions-btn"
                      (click)="addUserSchoolPermissions(userPermission.userId, school.id)"
                      [attr.data-testid]="'add-permissions-' + userPermission.userId + '-' + school.id"
                    >
                      <i class="icon-plus"></i>
                      {{ 'permissions.matrix.addPermissions' | translate }}
                    </button>
                  </ng-container>
                </div>
              </td>

              <td class="actions-cell">
                <div class="user-actions">
                  <button 
                    class="btn btn-sm btn-outline"
                    (click)="viewUserPermissions(userPermission.userId)"
                    [attr.data-testid]="'view-user-permissions-' + userPermission.userId"
                  >
                    {{ 'permissions.matrix.viewAll' | translate }}
                  </button>
                  <button 
                    class="btn btn-sm btn-outline"
                    (click)="viewPermissionHistory(userPermission.userId)"
                    [attr.data-testid]="'view-permission-history-' + userPermission.userId"
                  >
                    {{ 'permissions.matrix.history' | translate }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Loading State -->
      <div *ngIf="loading()" class="loading-state" data-testid="matrix-loading">
        <div class="matrix-skeleton">
          <div *ngFor="let _ of skeletonRows" class="skeleton-row">
            <div class="skeleton-user"></div>
            <div *ngFor="let _ of schools()" class="skeleton-permission"></div>
            <div class="skeleton-actions"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div *ngIf="!loading() && userMatrix().length === 0" class="empty-state">
        <div class="empty-content">
          <i class="icon-shield-off"></i>
          <h3>{{ 'permissions.matrix.empty.title' | translate }}</h3>
          <p>{{ 'permissions.matrix.empty.message' | translate }}</p>
        </div>
      </div>

      <!-- Pagination -->
      <div *ngIf="userMatrix().length > 0" class="matrix-pagination">
        <div class="pagination-info">
          {{ 'pagination.showing' | translate }} 
          {{ (currentPage() - 1) * perPage() + 1 }} 
          {{ 'pagination.to' | translate }} 
          {{ Math.min(currentPage() * perPage(), totalUsers()) }} 
          {{ 'pagination.of' | translate }} 
          {{ totalUsers() }} 
          {{ 'permissions.matrix.users' | translate }}
        </div>
        
        <div class="pagination-controls">
          <button 
            class="btn btn-outline btn-sm"
            [disabled]="currentPage() === 1"
            (click)="goToPage(currentPage() - 1)"
          >
            {{ 'pagination.previous' | translate }}
          </button>
          
          <span class="page-indicator">
            {{ 'pagination.page' | translate }} {{ currentPage() }} {{ 'pagination.of' | translate }} {{ totalPages() }}
          </span>
          
          <button 
            class="btn btn-outline btn-sm"
            [disabled]="currentPage() === totalPages()"
            (click)="goToPage(currentPage() + 1)"
          >
            {{ 'pagination.next' | translate }}
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .permission-matrix-container {
      padding: var(--space-lg);
    }

    .matrix-filters {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      padding: var(--space-md);
      margin-bottom: var(--space-lg);
    }

    .filters-form {
      display: grid;
      grid-template-columns: 1fr 200px 200px 150px auto;
      gap: var(--space-md);
      align-items: end;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: var(--space-xs);
    }

    .filter-group label {
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      color: var(--color-text-secondary);
    }

    .form-input, .form-select {
      padding: var(--space-sm);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-sm);
      background: var(--color-surface);
      color: var(--color-text-primary);
    }

    .filter-actions {
      display: flex;
      gap: var(--space-sm);
    }

    .matrix-table-container {
      overflow-x: auto;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      background: var(--color-surface);
    }

    .permission-matrix-table {
      width: 100%;
      min-width: 800px;
      border-collapse: collapse;
    }

    .permission-matrix-table th {
      background: var(--color-surface-secondary);
      padding: var(--space-md);
      text-align: left;
      font-weight: var(--font-weight-semibold);
      border-bottom: 2px solid var(--color-border);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .user-column {
      width: 250px;
      position: sticky;
      left: 0;
      background: var(--color-surface-secondary);
      z-index: 11;
    }

    .school-column {
      width: 200px;
      text-align: center;
    }

    .school-header {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .school-name {
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .school-code {
      font-size: var(--font-size-xs);
      color: var(--color-text-secondary);
      font-family: var(--font-family-mono);
    }

    .actions-column {
      width: 200px;
    }

    .matrix-row {
      border-bottom: 1px solid var(--color-border-light);
      transition: background-color 0.2s ease;
    }

    .matrix-row:hover {
      background: var(--color-surface-hover);
    }

    .user-cell {
      padding: var(--space-md);
      position: sticky;
      left: 0;
      background: var(--color-surface);
      border-right: 1px solid var(--color-border);
    }

    .matrix-row:hover .user-cell {
      background: var(--color-surface-hover);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
    }

    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--color-primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-semibold);
      flex-shrink: 0;
    }

    .user-details {
      min-width: 0;
    }

    .user-name {
      font-weight: var(--font-weight-medium);
      color: var(--color-text-primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .user-email {
      font-size: var(--font-size-xs);
      color: var(--color-text-secondary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .permission-cell {
      padding: var(--space-sm);
      text-align: center;
      vertical-align: middle;
      border-right: 1px solid var(--color-border-light);
    }

    .school-permissions {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--space-xs);
      position: relative;
    }

    .roles-section {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-xs);
      justify-content: center;
    }

    .role-badge {
      padding: 2px var(--space-xs);
      border-radius: var(--radius-xs);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
    }

    .role-admin {
      background: var(--color-danger-light);
      color: var(--color-danger);
    }

    .role-manager {
      background: var(--color-warning-light);
      color: var(--color-warning);
    }

    .role-staff {
      background: var(--color-info-light);
      color: var(--color-info);
    }

    .status-indicator {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      border-radius: 50%;
    }

    .status-active {
      background: var(--color-success-light);
      color: var(--color-success);
    }

    .status-inactive {
      background: var(--color-warning-light);
      color: var(--color-warning);
    }

    .edit-permissions-btn, .add-permissions-btn {
      background: none;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-sm);
      padding: var(--space-xs);
      color: var(--color-text-secondary);
      cursor: pointer;
      font-size: var(--font-size-xs);
      display: flex;
      align-items: center;
      gap: var(--space-xs);
      transition: all 0.2s ease;
    }

    .edit-permissions-btn:hover, .add-permissions-btn:hover {
      border-color: var(--color-primary);
      color: var(--color-primary);
      background: var(--color-primary-light);
    }

    .actions-cell {
      padding: var(--space-sm);
    }

    .user-actions {
      display: flex;
      flex-direction: column;
      gap: var(--space-xs);
    }

    .btn {
      padding: var(--space-xs) var(--space-sm);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: var(--space-xs);
      transition: all 0.2s ease;
      text-align: center;
      justify-content: center;
    }

    .btn-primary {
      background: var(--color-primary);
      color: white;
    }

    .btn-outline {
      background: transparent;
      color: var(--color-text-secondary);
      border: 1px solid var(--color-border);
    }

    .btn-outline:hover {
      background: var(--color-surface-hover);
      color: var(--color-text-primary);
    }

    .btn-sm {
      padding: var(--space-xs) var(--space-sm);
      font-size: var(--font-size-xs);
    }

    .loading-state {
      padding: var(--space-xl);
    }

    .matrix-skeleton {
      display: flex;
      flex-direction: column;
      gap: var(--space-md);
    }

    .skeleton-row {
      display: flex;
      gap: var(--space-md);
      align-items: center;
    }

    .skeleton-user, .skeleton-permission, .skeleton-actions {
      background: linear-gradient(90deg, var(--color-surface-secondary) 25%, var(--color-surface-hover) 50%, var(--color-surface-secondary) 75%);
      background-size: 200% 100%;
      border-radius: var(--radius-sm);
      animation: shimmer 1.5s infinite;
    }

    .skeleton-user {
      width: 250px;
      height: 50px;
    }

    .skeleton-permission {
      width: 200px;
      height: 40px;
    }

    .skeleton-actions {
      width: 100px;
      height: 30px;
    }

    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    .empty-state {
      padding: var(--space-xl);
      text-align: center;
    }

    .empty-content {
      color: var(--color-text-secondary);
    }

    .empty-content i {
      font-size: 48px;
      margin-bottom: var(--space-md);
      opacity: 0.5;
    }

    .matrix-pagination {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: var(--space-lg);
      padding-top: var(--space-md);
      border-top: 1px solid var(--color-border);
    }

    .pagination-info {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
    }

    .pagination-controls {
      display: flex;
      align-items: center;
      gap: var(--space-md);
    }

    @media (max-width: 1200px) {
      .filters-form {
        grid-template-columns: 1fr;
        gap: var(--space-sm);
      }

      .filter-actions {
        justify-content: flex-start;
      }
    }

    @media (max-width: 768px) {
      .permission-matrix-container {
        padding: var(--space-md);
      }

      .user-column {
        width: 200px;
      }

      .school-column {
        width: 150px;
      }

      .actions-column {
        width: 120px;
      }

      .user-actions {
        flex-direction: row;
      }

      .matrix-pagination {
        flex-direction: column;
        gap: var(--space-sm);
        text-align: center;
      }
    }
  `]
})
export class PermissionMatrixComponent implements OnInit {
  private readonly permissionsService = inject(SchoolPermissionsService);
  private readonly usersService = inject(UsersService);
  private readonly rolesService = inject(RolesService);
  private readonly fb = inject(FormBuilder);

  // Reactive state using signals
  userMatrix = signal<UserPermissionMatrix[]>([]);
  schools = signal<School[]>([]);
  roles = signal<Role[]>([]);
  loading = signal(true);
  currentPage = signal(1);
  totalPages = signal(1);
  totalUsers = signal(0);
  perPage = signal(20);

  // UI state
  skeletonRows = Array.from({ length: 5 }, (_, i) => i);

  // Computed values
  Math = Math;

  // Form
  filtersForm: FormGroup;

  constructor() {
    this.filtersForm = this.fb.group({
      search: [''],
      schoolId: [''],
      role: [''],
      status: ['']
    });
  }

  ngOnInit(): void {
    this.loadInitialData();
    this.setupFilters();
  }

  private loadInitialData(): void {
    this.loading.set(true);
    // Load schools, roles, and initial matrix data
    // Implementation would load from services
    this.loadPermissionMatrix();
  }

  private setupFilters(): void {
    this.filtersForm.valueChanges.pipe(
      startWith(this.filtersForm.value),
      debounceTime(300),
      distinctUntilChanged((prev, curr) => JSON.stringify(prev) === JSON.stringify(curr))
    ).subscribe(() => {
      this.currentPage.set(1);
      this.loadPermissionMatrix();
    });
  }

  private loadPermissionMatrix(): void {
    this.loading.set(true);
    
    const filters: PermissionAssignmentFilters = {
      ...this.filtersForm.value,
      page: this.currentPage(),
      perPage: this.perPage()
    };

    this.permissionsService.getPermissionMatrix(filters).pipe(
      catchError(error => {
        console.error('Error loading permission matrix:', error);
        return of({ 
          data: [], 
          meta: { total: 0, page: 1, perPage: 20, lastPage: 1, totalUsers: 0, totalSchools: 0 } 
        });
      }),
      finalize(() => this.loading.set(false))
    ).subscribe(response => {
      this.userMatrix.set(response.data);
      this.totalUsers.set(response.meta.totalUsers);
      this.totalPages.set(response.meta.lastPage);
    });
  }

  getSchoolPermission(userPermission: UserPermissionMatrix, schoolId: number): SchoolPermission | null {
    return userPermission.schoolPermissions.find(sp => sp.school.id === schoolId) || null;
  }

  editUserSchoolPermissions(userId: number, schoolId: number): void {
    // Open modal or navigate to edit page
    console.log('Edit permissions for user', userId, 'in school', schoolId);
  }

  addUserSchoolPermissions(userId: number, schoolId: number): void {
    // Open modal or navigate to assign permissions page
    console.log('Add permissions for user', userId, 'in school', schoolId);
  }

  viewUserPermissions(userId: number): void {
    // Navigate to user permissions overview
    console.log('View all permissions for user', userId);
  }

  viewPermissionHistory(userId: number): void {
    // Open permission history modal
    console.log('View permission history for user', userId);
  }

  exportMatrix(): void {
    const filters: PermissionAssignmentFilters = this.filtersForm.value;
    this.permissionsService.exportPermissionMatrix(filters).subscribe(blob => {
      // Handle file download
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `permission-matrix-${new Date().toISOString().split('T')[0]}.xlsx`;
      a.click();
      window.URL.revokeObjectURL(url);
    });
  }

  openBulkAssignment(): void {
    // Open bulk assignment modal
    console.log('Open bulk assignment');
  }

  goToPage(page: number): void {
    if (page >= 1 && page <= this.totalPages()) {
      this.currentPage.set(page);
      this.loadPermissionMatrix();
    }
  }

  getInitials(name: string): string {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .substring(0, 2);
  }

  trackByUserId(index: number, item: UserPermissionMatrix): number {
    return item.userId;
  }

  trackBySchoolId(index: number, school: School): number {
    return school.id;
  }
}