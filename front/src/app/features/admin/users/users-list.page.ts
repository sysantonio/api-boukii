import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { debounceTime, distinctUntilChanged, startWith, switchMap, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { TranslatePipe } from '../../../shared/pipes/translate.pipe';
import { AdminNavComponent } from '../../../shared/components/admin-nav/admin-nav.component';
import { UsersService } from './services/users.service';
import { RolesService } from '../roles/services/roles.service';
import { UserListItem, UsersFilters } from './types/user.types';
import { Role } from '../roles/types/role.types';

@Component({
  selector: 'app-users-list-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe, AdminNavComponent],
  template: `
    <div class="page" data-testid="users-page">
      <!-- Admin Navigation -->
      <app-admin-nav></app-admin-nav>
      
      <!-- Page Header -->
      <div class="page-header" data-testid="page-header">
        <div class="page-header-content">
          <div class="page-title">
            <h1>{{ 'users.title' | translate }}</h1>
            <p class="page-subtitle">{{ 'users.list.subtitle' | translate }}</p>
          </div>
          <div class="page-actions">
            <button class="btn btn-primary" (click)="createUser()" data-testid="create-user-btn">
              <i class="icon-plus"></i>
              {{ 'users.list.createUser' | translate }}
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-section" data-testid="users-filters">
        <form [formGroup]="filtersForm" class="filters-form">
          <div class="filter-group">
            <label for="filter-text">{{ 'users.list.filters.search' | translate }}</label>
            <input 
              id="filter-text"
              type="text" 
              formControlName="search" 
              [placeholder]="'users.list.filters.searchPlaceholder' | translate"
              data-testid="filter-text"
              class="form-input"
            />
          </div>
          
          <div class="filter-group">
            <label for="filter-role">{{ 'users.list.filters.role' | translate }}</label>
            <select 
              id="filter-role"
              formControlName="role" 
              data-testid="filter-role"
              class="form-select"
            >
              <option value="">{{ 'users.list.filters.allRoles' | translate }}</option>
              <option *ngFor="let role of roles()" [value]="role.name">
                {{ role.description }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label for="filter-status">{{ 'users.list.filters.status' | translate }}</label>
            <select 
              id="filter-status"
              formControlName="status" 
              data-testid="filter-status"
              class="form-select"
            >
              <option value="">{{ 'users.list.filters.allStatuses' | translate }}</option>
              <option value="active">{{ 'users.status.active' | translate }}</option>
              <option value="inactive">{{ 'users.status.inactive' | translate }}</option>
              <option value="pending">{{ 'users.status.pending' | translate }}</option>
            </select>
          </div>
        </form>
      </div>

      <!-- Users Table -->
      <div class="table-section">
        <table class="data-table" data-testid="users-table">
          <thead>
            <tr>
              <th>{{ 'users.list.table.name' | translate }}</th>
              <th>{{ 'users.list.table.email' | translate }}</th>
              <th>{{ 'users.list.table.roles' | translate }}</th>
              <th>{{ 'users.list.table.status' | translate }}</th>
              <th>{{ 'users.list.table.createdAt' | translate }}</th>
              <th>{{ 'users.list.table.actions' | translate }}</th>
            </tr>
          </thead>
          <tbody *ngIf="!loading() && users().length > 0">
            <tr 
              *ngFor="let user of users()" 
              (click)="viewUser(user.id)"
              class="table-row clickable"
              [attr.data-testid]="'user-row-' + user.id"
            >
              <td>
                <div class="user-info">
                  <div class="user-avatar">
                    {{ getInitials(user.name) }}
                  </div>
                  <span class="user-name">{{ user.name }}</span>
                </div>
              </td>
              <td>{{ user.email }}</td>
              <td>
                <div class="roles-badges">
                  <span 
                    *ngFor="let role of user.roles" 
                    class="badge badge-role"
                    [class]="'badge-' + role"
                  >
                    {{ role }}
                  </span>
                </div>
              </td>
              <td>
                <span 
                  class="status-badge"
                  [class]="'status-' + user.status"
                  [attr.data-testid]="'user-status-' + user.id"
                >
                  {{ 'users.status.' + user.status | translate }}
                </span>
              </td>
              <td>{{ formatDate(user.createdAt) }}</td>
              <td>
                <div class="actions">
                  <button 
                    (click)="viewUser(user.id); $event.stopPropagation()" 
                    class="btn btn-sm btn-outline"
                    [attr.data-testid]="'view-user-' + user.id"
                  >
                    {{ 'common.view' | translate }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          
          <!-- Loading State -->
          <tbody *ngIf="loading()">
            <tr *ngFor="let _ of skeletonRows()" class="skeleton-row">
              <td colspan="6">
                <div class="skeleton-content"></div>
              </td>
            </tr>
          </tbody>
          
          <!-- Empty State -->
          <tbody *ngIf="!loading() && users().length === 0">
            <tr>
              <td colspan="6" class="empty-state">
                <div class="empty-state-content">
                  <i class="icon-users"></i>
                  <h3>{{ 'users.list.empty.title' | translate }}</h3>
                  <p>{{ 'users.list.empty.message' | translate }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-section" *ngIf="!loading() && totalPages() > 1" data-testid="users-pagination">
        <div class="pagination-info">
          <span>
            {{ 'pagination.showing' | translate }} 
            {{ (currentPage() - 1) * perPage() + 1 }} - 
            {{ Math.min(currentPage() * perPage(), totalUsers()) }} 
            {{ 'pagination.of' | translate }} 
            {{ totalUsers() }}
          </span>
        </div>
        <div class="pagination-controls">
          <button 
            (click)="goToPage(currentPage() - 1)" 
            [disabled]="currentPage() === 1"
            class="btn btn-sm btn-outline"
            data-testid="prev-page"
          >
            {{ 'pagination.previous' | translate }}
          </button>
          
          <span class="page-indicator">
            {{ 'pagination.page' | translate }} {{ currentPage() }} {{ 'pagination.of' | translate }} {{ totalPages() }}
          </span>
          
          <button 
            (click)="goToPage(currentPage() + 1)" 
            [disabled]="currentPage() === totalPages()"
            class="btn btn-sm btn-outline"
            data-testid="next-page"
          >
            {{ 'pagination.next' | translate }}
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
      justify-content: between;
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

    .filters-section {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      padding: var(--space-md);
      margin-bottom: var(--space-lg);
    }

    .filters-form {
      display: grid;
      grid-template-columns: 1fr 200px 200px;
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

    .form-input,
    .form-select {
      padding: var(--space-sm);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-base);
      background: var(--color-surface);
      color: var(--color-text-primary);
    }

    .form-input:focus,
    .form-select:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px var(--color-primary-alpha);
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      background: var(--color-surface);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }

    .data-table th {
      background: var(--color-surface-secondary);
      padding: var(--space-md);
      text-align: left;
      font-weight: var(--font-weight-semibold);
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      border-bottom: 1px solid var(--color-border);
    }

    .data-table td {
      padding: var(--space-md);
      border-bottom: 1px solid var(--color-border-light);
      vertical-align: middle;
    }

    .table-row.clickable {
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .table-row.clickable:hover {
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
    }

    .user-name {
      font-weight: var(--font-weight-medium);
    }

    .roles-badges {
      display: flex;
      gap: var(--space-xs);
      flex-wrap: wrap;
    }

    .badge {
      padding: var(--space-xs) var(--space-sm);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
    }

    .badge-admin {
      background: var(--color-danger-light);
      color: var(--color-danger);
    }

    .badge-manager {
      background: var(--color-warning-light);
      color: var(--color-warning);
    }

    .badge-staff {
      background: var(--color-info-light);
      color: var(--color-info);
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

    .btn-sm {
      padding: var(--space-xs) var(--space-sm);
      font-size: var(--font-size-xs);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .skeleton-row td {
      padding: var(--space-md);
    }

    .skeleton-content {
      height: 20px;
      background: linear-gradient(90deg, var(--color-surface-secondary) 25%, var(--color-surface-hover) 50%, var(--color-surface-secondary) 75%);
      background-size: 200% 100%;
      border-radius: var(--radius-sm);
      animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    .empty-state {
      text-align: center;
      padding: var(--space-xl) var(--space-lg);
    }

    .empty-state-content {
      color: var(--color-text-secondary);
    }

    .empty-state-content i {
      font-size: 48px;
      margin-bottom: var(--space-md);
      opacity: 0.5;
    }

    .empty-state-content h3 {
      margin: 0 0 var(--space-sm) 0;
      color: var(--color-text-primary);
    }

    .pagination-section {
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

    .page-indicator {
      font-size: var(--font-size-sm);
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

      .filters-form {
        grid-template-columns: 1fr;
      }

      .pagination-section {
        flex-direction: column;
        gap: var(--space-sm);
        text-align: center;
      }
    }
  `]
})
export class UsersListPageComponent implements OnInit {
  private readonly usersService = inject(UsersService);
  private readonly rolesService = inject(RolesService);
  private readonly router = inject(Router);
  private readonly fb = inject(FormBuilder);

  // Reactive state using signals
  users = signal<UserListItem[]>([]);
  roles = signal<Role[]>([]);
  loading = signal(true);
  currentPage = signal(1);
  totalPages = signal(1);
  totalUsers = signal(0);
  perPage = signal(20);

  // Computed values
  skeletonRows = computed(() => Array.from({ length: 10 }, (_, i) => i));

  // Form
  filtersForm: FormGroup;

  // Expose Math to template
  Math = Math;

  constructor() {
    this.filtersForm = this.fb.group({
      search: [''],
      role: [''],
      status: ['']
    });
  }

  ngOnInit(): void {
    this.loadRoles();
    this.setupFilters();
    this.loadUsers();
  }

  private setupFilters(): void {
    // React to filter changes with debouncing
    this.filtersForm.valueChanges.pipe(
      startWith(this.filtersForm.value),
      debounceTime(300),
      distinctUntilChanged((prev, curr) => JSON.stringify(prev) === JSON.stringify(curr))
    ).subscribe(() => {
      this.currentPage.set(1); // Reset to first page when filters change
      this.loadUsers();
    });
  }

  private loadRoles(): void {
    this.rolesService.getRoles().pipe(
      catchError(error => {
        console.error('Error loading roles:', error);
        return of({ data: [] as Role[] });
      })
    ).subscribe(response => {
      this.roles.set(response.data);
    });
  }

  private loadUsers(): void {
    this.loading.set(true);
    
    const filters: UsersFilters = {
      ...this.filtersForm.value,
      page: this.currentPage(),
      perPage: this.perPage()
    };

    // Remove empty values
    Object.keys(filters).forEach(key => {
      if (!filters[key as keyof UsersFilters]) {
        delete filters[key as keyof UsersFilters];
      }
    });

    this.usersService.getUsers(filters).pipe(
      catchError(error => {
        console.error('Error loading users:', error);
        return of({ 
          data: [] as UserListItem[], 
          meta: { total: 0, page: 1, perPage: 20, lastPage: 1 } 
        });
      })
    ).subscribe(response => {
      this.users.set(response.data);
      this.totalUsers.set(response.meta.total);
      this.totalPages.set(response.meta.lastPage);
      this.loading.set(false);
    });
  }

  viewUser(userId: number): void {
    this.router.navigate(['/admin/users', userId]);
  }

  createUser(): void {
    // TODO: Navigate to create user page
    console.log('Create user clicked');
  }

  goToPage(page: number): void {
    if (page >= 1 && page <= this.totalPages()) {
      this.currentPage.set(page);
      this.loadUsers();
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

  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString();
  }
}