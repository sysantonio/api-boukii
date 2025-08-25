import { Component, inject, signal, computed, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { TranslatePipe } from '../../../../../shared/pipes/translate.pipe';
import { SchoolPermissionsService } from '../../services/school-permissions.service';
import { User } from '../../../users/types/user.types';
import { School } from '../../../../../core/services/context.service';

interface PermissionHistoryEntry {
  id: number;
  action: 'assigned' | 'removed' | 'modified';
  roles: string[];
  permissions: string[];
  schoolId: number;
  schoolName: string;
  changedBy: string;
  changedAt: string;
  reason?: string;
}

interface PermissionHistoryModal {
  userId: number;
  userName: string;
  schoolId?: number;
  schoolName?: string;
}

@Component({
  selector: 'app-permission-history-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, TranslatePipe],
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <div class="modal-overlay" 
         [attr.data-testid]="'permission-history-modal'"
         (click)="onOverlayClick($event)">
      <div class="modal-container" (click)="$event.stopPropagation()">
        <!-- Header -->
        <div class="modal-header">
          <div class="header-content">
            <h2 class="modal-title">
              {{ 'permissions.history.title' | translate }}
            </h2>
            <div class="user-info">
              <span class="user-name">{{ config()?.userName }}</span>
              @if (config()?.schoolName) {
                <span class="school-context">· {{ config()?.schoolName }}</span>
              }
            </div>
          </div>
          <button
            type="button"
            class="close-button"
            [attr.data-testid]="'close-history-modal'"
            (click)="onClose()"
            [attr.aria-label]="'common.close' | translate">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
              <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="modal-content">
          <!-- Loading State -->
          @if (isLoading()) {
            <div class="loading-container">
              <div class="loading-skeleton">
                @for (item of [1,2,3,4,5]; track item) {
                  <div class="history-item-skeleton">
                    <div class="skeleton-header">
                      <div class="skeleton-action"></div>
                      <div class="skeleton-date"></div>
                    </div>
                    <div class="skeleton-details">
                      <div class="skeleton-line"></div>
                      <div class="skeleton-line short"></div>
                    </div>
                  </div>
                }
              </div>
            </div>
          }

          <!-- Error State -->
          @if (error()) {
            <div class="error-container">
              <div class="error-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
              </div>
              <h3>{{ 'permissions.history.error.title' | translate }}</h3>
              <p>{{ error() }}</p>
              <button
                type="button"
                class="retry-button"
                [attr.data-testid]="'retry-history-load'"
                (click)="loadHistory()">
                {{ 'common.retry' | translate }}
              </button>
            </div>
          }

          <!-- Empty State -->
          @if (!isLoading() && !error() && historyEntries().length === 0) {
            <div class="empty-container">
              <div class="empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                </svg>
              </div>
              <h3>{{ 'permissions.history.empty.title' | translate }}</h3>
              <p>{{ 'permissions.history.empty.message' | translate }}</p>
            </div>
          }

          <!-- History List -->
          @if (!isLoading() && !error() && historyEntries().length > 0) {
            <div class="history-container">
              <!-- Filters -->
              <div class="history-filters">
                <div class="filter-group">
                  <label class="filter-label">
                    {{ 'permissions.history.filters.action' | translate }}
                  </label>
                  <select
                    class="filter-select"
                    [attr.data-testid]="'filter-action'"
                    [(ngModel)]="actionFilter"
                    (ngModelChange)="onFilterChange()">
                    <option value="">{{ 'permissions.history.filters.allActions' | translate }}</option>
                    <option value="assigned">{{ 'permissions.history.actions.assigned' | translate }}</option>
                    <option value="removed">{{ 'permissions.history.actions.removed' | translate }}</option>
                    <option value="modified">{{ 'permissions.history.actions.modified' | translate }}</option>
                  </select>
                </div>

                <div class="filter-group">
                  <label class="filter-label">
                    {{ 'permissions.history.filters.dateRange' | translate }}
                  </label>
                  <select
                    class="filter-select"
                    [attr.data-testid]="'filter-date-range'"
                    [(ngModel)]="dateRangeFilter"
                    (ngModelChange)="onFilterChange()">
                    <option value="">{{ 'permissions.history.filters.allTime' | translate }}</option>
                    <option value="today">{{ 'permissions.history.filters.today' | translate }}</option>
                    <option value="week">{{ 'permissions.history.filters.thisWeek' | translate }}</option>
                    <option value="month">{{ 'permissions.history.filters.thisMonth' | translate }}</option>
                    <option value="quarter">{{ 'permissions.history.filters.thisQuarter' | translate }}</option>
                  </select>
                </div>
              </div>

              <!-- History Timeline -->
              <div class="history-timeline" [attr.data-testid]="'history-timeline'">
                @for (entry of filteredHistory(); track entry.id) {
                  <div class="history-entry" [attr.data-testid]="'history-entry-' + entry.id">
                    <div class="entry-indicator" [class]="getActionClass(entry.action)">
                      <div class="indicator-icon">
                        @switch (entry.action) {
                          @case ('assigned') {
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                              <path d="M8 2a.5.5 0 01.5.5v5h5a.5.5 0 010 1h-5v5a.5.5 0 01-1 0v-5h-5a.5.5 0 010-1h5v-5A.5.5 0 018 2z"/>
                            </svg>
                          }
                          @case ('removed') {
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                              <path d="M2.5 7.5a.5.5 0 000 1h11a.5.5 0 000-1h-11z"/>
                            </svg>
                          }
                          @case ('modified') {
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                              <path d="M12.146.146a.5.5 0 01.708 0l3 3a.5.5 0 010 .708L8.5 11.207l-3 1a.5.5 0 01-.65-.65l1-3L12.146.146zM11.207 2L2 11.207V13.5a.5.5 0 00.5.5h2.293L13 5.793 11.207 2z"/>
                            </svg>
                          }
                        }
                      </div>
                    </div>

                    <div class="entry-content">
                      <div class="entry-header">
                        <div class="action-info">
                          <span class="action-label" [class]="getActionClass(entry.action)">
                            {{ ('permissions.history.actions.' + entry.action) | translate }}
                          </span>
                          <span class="school-name">{{ entry.schoolName }}</span>
                        </div>
                        <div class="timestamp-info">
                          <span class="changed-by">{{ entry.changedBy }}</span>
                          <time class="timestamp" [attr.datetime]="entry.changedAt">
                            {{ formatTimestamp(entry.changedAt) }}
                          </time>
                        </div>
                      </div>

                      <div class="entry-details">
                        @if (entry.roles.length > 0) {
                          <div class="roles-section">
                            <span class="section-label">{{ 'permissions.history.details.roles' | translate }}:</span>
                            <div class="roles-list">
                              @for (role of entry.roles; track role) {
                                <span class="role-badge">{{ role }}</span>
                              }
                            </div>
                          </div>
                        }

                        @if (entry.permissions.length > 0) {
                          <div class="permissions-section">
                            <span class="section-label">{{ 'permissions.history.details.permissions' | translate }}:</span>
                            <div class="permissions-list">
                              @for (permission of entry.permissions; track permission) {
                                <span class="permission-badge">{{ permission }}</span>
                              }
                            </div>
                          </div>
                        }

                        @if (entry.reason) {
                          <div class="reason-section">
                            <span class="section-label">{{ 'permissions.history.details.reason' | translate }}:</span>
                            <p class="reason-text">{{ entry.reason }}</p>
                          </div>
                        }
                      </div>
                    </div>
                  </div>
                }
              </div>

              <!-- Load More -->
              @if (hasMoreEntries()) {
                <div class="load-more-container">
                  <button
                    type="button"
                    class="load-more-button"
                    [attr.data-testid]="'load-more-history'"
                    [disabled]="isLoadingMore()"
                    (click)="loadMoreHistory()">
                    @if (isLoadingMore()) {
                      <span class="loading-spinner"></span>
                    }
                    {{ 'permissions.history.loadMore' | translate }}
                  </button>
                </div>
              }
            </div>
          }
        </div>
      </div>
    </div>
  `,
  styles: [`
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: var(--overlay-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      padding: var(--spacing-lg);
    }

    .modal-container {
      background: var(--surface-primary);
      border-radius: var(--border-radius-lg);
      box-shadow: var(--shadow-xl);
      width: 100%;
      max-width: 800px;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .modal-header {
      padding: var(--spacing-xl);
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: var(--spacing-lg);
    }

    .header-content {
      flex: 1;
      min-width: 0;
    }

    .modal-title {
      font-size: var(--font-size-xl);
      font-weight: var(--font-weight-semibold);
      color: var(--text-primary);
      margin: 0 0 var(--spacing-sm) 0;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: var(--spacing-xs);
      color: var(--text-secondary);
      font-size: var(--font-size-sm);
    }

    .user-name {
      font-weight: var(--font-weight-medium);
      color: var(--text-primary);
    }

    .school-context {
      color: var(--text-tertiary);
    }

    .close-button {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border: none;
      border-radius: var(--border-radius-md);
      background: transparent;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all var(--transition-default);
    }

    .close-button:hover {
      background: var(--surface-tertiary);
      color: var(--text-primary);
    }

    .close-button:focus-visible {
      outline: 2px solid var(--color-primary);
      outline-offset: 2px;
    }

    .modal-content {
      flex: 1;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    /* Loading State */
    .loading-container {
      padding: var(--spacing-xl);
      flex: 1;
    }

    .history-item-skeleton {
      padding: var(--spacing-lg);
      border-bottom: 1px solid var(--border-color);
    }

    .skeleton-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--spacing-md);
    }

    .skeleton-action {
      width: 80px;
      height: 20px;
      background: var(--skeleton-bg);
      border-radius: var(--border-radius-sm);
      animation: var(--skeleton-animation);
    }

    .skeleton-date {
      width: 120px;
      height: 16px;
      background: var(--skeleton-bg);
      border-radius: var(--border-radius-sm);
      animation: var(--skeleton-animation);
    }

    .skeleton-details {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-sm);
    }

    .skeleton-line {
      height: 16px;
      background: var(--skeleton-bg);
      border-radius: var(--border-radius-sm);
      animation: var(--skeleton-animation);
    }

    .skeleton-line.short {
      width: 60%;
    }

    /* Error State */
    .error-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: var(--spacing-xl);
      flex: 1;
    }

    .error-icon {
      width: 48px;
      height: 48px;
      color: var(--color-error);
      margin-bottom: var(--spacing-lg);
    }

    .error-container h3 {
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--text-primary);
      margin: 0 0 var(--spacing-md) 0;
    }

    .error-container p {
      color: var(--text-secondary);
      margin: 0 0 var(--spacing-xl) 0;
      max-width: 400px;
    }

    .retry-button {
      padding: var(--spacing-md) var(--spacing-xl);
      background: var(--color-primary);
      color: var(--color-on-primary);
      border: none;
      border-radius: var(--border-radius-md);
      font-weight: var(--font-weight-medium);
      cursor: pointer;
      transition: all var(--transition-default);
    }

    .retry-button:hover {
      background: var(--color-primary-hover);
    }

    .retry-button:focus-visible {
      outline: 2px solid var(--color-primary);
      outline-offset: 2px;
    }

    /* Empty State */
    .empty-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: var(--spacing-xl);
      flex: 1;
    }

    .empty-icon {
      width: 48px;
      height: 48px;
      color: var(--text-tertiary);
      margin-bottom: var(--spacing-lg);
    }

    .empty-container h3 {
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--text-primary);
      margin: 0 0 var(--spacing-md) 0;
    }

    .empty-container p {
      color: var(--text-secondary);
      margin: 0;
      max-width: 400px;
    }

    /* History Content */
    .history-container {
      flex: 1;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .history-filters {
      padding: var(--spacing-lg) var(--spacing-xl);
      border-bottom: 1px solid var(--border-color);
      display: flex;
      gap: var(--spacing-lg);
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-xs);
      min-width: 160px;
    }

    .filter-label {
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      color: var(--text-secondary);
    }

    .filter-select {
      padding: var(--spacing-sm) var(--spacing-md);
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius-md);
      background: var(--surface-primary);
      color: var(--text-primary);
      font-size: var(--font-size-sm);
      cursor: pointer;
      transition: all var(--transition-default);
    }

    .filter-select:hover {
      border-color: var(--border-color-hover);
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px var(--color-primary-alpha);
    }

    /* History Timeline */
    .history-timeline {
      flex: 1;
      overflow-y: auto;
      padding: 0;
    }

    .history-entry {
      display: flex;
      gap: var(--spacing-lg);
      padding: var(--spacing-lg) var(--spacing-xl);
      border-bottom: 1px solid var(--border-color);
      position: relative;
    }

    .history-entry:last-child {
      border-bottom: none;
    }

    .history-entry::before {
      content: '';
      position: absolute;
      left: calc(var(--spacing-xl) + 12px);
      top: calc(var(--spacing-lg) + 28px);
      bottom: 0;
      width: 2px;
      background: var(--border-color);
    }

    .history-entry:last-child::before {
      display: none;
    }

    .entry-indicator {
      position: relative;
      z-index: 1;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .entry-indicator.assigned {
      background: var(--color-success-bg);
      color: var(--color-success);
    }

    .entry-indicator.removed {
      background: var(--color-error-bg);
      color: var(--color-error);
    }

    .entry-indicator.modified {
      background: var(--color-warning-bg);
      color: var(--color-warning);
    }

    .indicator-icon {
      width: 16px;
      height: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .entry-content {
      flex: 1;
      min-width: 0;
    }

    .entry-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: var(--spacing-md);
      margin-bottom: var(--spacing-md);
    }

    .action-info {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      flex-wrap: wrap;
    }

    .action-label {
      padding: var(--spacing-xs) var(--spacing-sm);
      border-radius: var(--border-radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .action-label.assigned {
      background: var(--color-success-bg);
      color: var(--color-success);
    }

    .action-label.removed {
      background: var(--color-error-bg);
      color: var(--color-error);
    }

    .action-label.modified {
      background: var(--color-warning-bg);
      color: var(--color-warning);
    }

    .school-name {
      font-weight: var(--font-weight-medium);
      color: var(--text-primary);
    }

    .timestamp-info {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: var(--spacing-xs);
      text-align: right;
      flex-shrink: 0;
    }

    .changed-by {
      font-size: var(--font-size-sm);
      color: var(--text-secondary);
    }

    .timestamp {
      font-size: var(--font-size-xs);
      color: var(--text-tertiary);
    }

    .entry-details {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-md);
    }

    .roles-section,
    .permissions-section,
    .reason-section {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-sm);
    }

    .section-label {
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      color: var(--text-secondary);
    }

    .roles-list,
    .permissions-list {
      display: flex;
      flex-wrap: wrap;
      gap: var(--spacing-xs);
    }

    .role-badge,
    .permission-badge {
      padding: var(--spacing-xs) var(--spacing-sm);
      background: var(--surface-secondary);
      color: var(--text-primary);
      border-radius: var(--border-radius-sm);
      font-size: var(--font-size-xs);
      font-weight: var(--font-weight-medium);
    }

    .reason-text {
      margin: 0;
      color: var(--text-secondary);
      font-size: var(--font-size-sm);
      line-height: 1.5;
      font-style: italic;
    }

    /* Load More */
    .load-more-container {
      padding: var(--spacing-lg) var(--spacing-xl);
      display: flex;
      justify-content: center;
      border-top: 1px solid var(--border-color);
    }

    .load-more-button {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--spacing-sm);
      padding: var(--spacing-md) var(--spacing-xl);
      background: var(--surface-secondary);
      color: var(--text-primary);
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius-md);
      font-weight: var(--font-weight-medium);
      cursor: pointer;
      transition: all var(--transition-default);
    }

    .load-more-button:hover:not(:disabled) {
      background: var(--surface-tertiary);
      border-color: var(--border-color-hover);
    }

    .load-more-button:focus-visible {
      outline: 2px solid var(--color-primary);
      outline-offset: 2px;
    }

    .load-more-button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .loading-spinner {
      width: 16px;
      height: 16px;
      border: 2px solid var(--text-tertiary);
      border-top: 2px solid var(--text-primary);
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    /* Responsive Design */
    @media (max-width: 640px) {
      .modal-overlay {
        padding: var(--spacing-md);
        align-items: flex-end;
      }

      .modal-container {
        max-height: 85vh;
      }

      .modal-header {
        padding: var(--spacing-lg);
      }

      .header-content {
        min-width: 0;
      }

      .modal-title {
        font-size: var(--font-size-lg);
      }

      .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-xs);
      }

      .history-filters {
        padding: var(--spacing-md);
        flex-direction: column;
        gap: var(--spacing-md);
      }

      .filter-group {
        min-width: auto;
      }

      .history-entry {
        padding: var(--spacing-md);
        gap: var(--spacing-md);
      }

      .history-entry::before {
        left: calc(var(--spacing-md) + 12px);
        top: calc(var(--spacing-md) + 28px);
      }

      .entry-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
      }

      .timestamp-info {
        align-items: flex-start;
        text-align: left;
      }

      .action-info {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  `]
})
export class PermissionHistoryModalComponent implements OnInit {
  private readonly permissionsService = inject(SchoolPermissionsService);

  // Component state
  readonly config = signal<PermissionHistoryModal | null>(null);
  readonly historyEntries = signal<PermissionHistoryEntry[]>([]);
  readonly isLoading = signal(false);
  readonly error = signal<string | null>(null);
  readonly isLoadingMore = signal(false);
  readonly hasMoreEntries = signal(false);

  // Filters
  actionFilter = '';
  dateRangeFilter = '';

  // Pagination
  private currentPage = 1;
  private readonly pageSize = 20;

  // Computed
  readonly filteredHistory = computed(() => {
    let entries = this.historyEntries();
    
    if (this.actionFilter) {
      entries = entries.filter(entry => entry.action === this.actionFilter);
    }
    
    if (this.dateRangeFilter) {
      const now = new Date();
      const filterDate = this.getFilterDate(now, this.dateRangeFilter);
      entries = entries.filter(entry => new Date(entry.changedAt) >= filterDate);
    }
    
    return entries;
  });

  ngOnInit(): void {
    this.loadHistory();
  }

  setConfig(config: PermissionHistoryModal): void {
    this.config.set(config);
    this.resetState();
    this.loadHistory();
  }

  async loadHistory(): Promise<void> {
    const config = this.config();
    if (!config) return;

    this.isLoading.set(true);
    this.error.set(null);
    this.currentPage = 1;

    try {
      const response = await this.permissionsService
        .getUserPermissionHistory(config.userId, config.schoolId)
        .toPromise();
      
      this.historyEntries.set(response?.changes || []);
      this.hasMoreEntries.set((response?.changes?.length || 0) >= this.pageSize);
    } catch (err) {
      console.error('Error loading permission history:', err);
      this.error.set('Failed to load permission history. Please try again.');
    } finally {
      this.isLoading.set(false);
    }
  }

  async loadMoreHistory(): Promise<void> {
    const config = this.config();
    if (!config || this.isLoadingMore()) return;

    this.isLoadingMore.set(true);
    this.currentPage++;

    try {
      const response = await this.permissionsService
        .getUserPermissionHistory(config.userId, config.schoolId)
        .toPromise();
      
      const newEntries = response?.changes || [];
      this.historyEntries.update(current => [...current, ...newEntries]);
      this.hasMoreEntries.set(newEntries.length >= this.pageSize);
    } catch (err) {
      console.error('Error loading more history:', err);
      this.currentPage--; // Revert page increment
    } finally {
      this.isLoadingMore.set(false);
    }
  }

  onFilterChange(): void {
    // Filters are applied reactively through computed signal
    // This method can be used for additional side effects if needed
  }

  onClose(): void {
    // Emit close event or handle through parent component
    this.resetState();
  }

  onOverlayClick(event: Event): void {
    if (event.target === event.currentTarget) {
      this.onClose();
    }
  }

  getActionClass(action: string): string {
    return action; // Returns: 'assigned', 'removed', or 'modified'
  }

  formatTimestamp(timestamp: string): string {
    try {
      const date = new Date(timestamp);
      const now = new Date();
      const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60);
      
      if (diffInHours < 24) {
        return date.toLocaleTimeString([], { 
          hour: '2-digit', 
          minute: '2-digit' 
        });
      } else if (diffInHours < 24 * 7) {
        return date.toLocaleDateString([], { 
          weekday: 'short',
          hour: '2-digit', 
          minute: '2-digit' 
        });
      } else {
        return date.toLocaleDateString([], { 
          month: 'short',
          day: 'numeric',
          hour: '2-digit', 
          minute: '2-digit' 
        });
      }
    } catch {
      return timestamp;
    }
  }

  private getFilterDate(now: Date, range: string): Date {
    switch (range) {
      case 'today':
        return new Date(now.getFullYear(), now.getMonth(), now.getDate());
      case 'week':
        const weekStart = new Date(now);
        weekStart.setDate(now.getDate() - now.getDay());
        weekStart.setHours(0, 0, 0, 0);
        return weekStart;
      case 'month':
        return new Date(now.getFullYear(), now.getMonth(), 1);
      case 'quarter':
        const quarterStart = Math.floor(now.getMonth() / 3) * 3;
        return new Date(now.getFullYear(), quarterStart, 1);
      default:
        return new Date(0); // Beginning of time
    }
  }

  private resetState(): void {
    this.historyEntries.set([]);
    this.isLoading.set(false);
    this.error.set(null);
    this.isLoadingMore.set(false);
    this.hasMoreEntries.set(false);
    this.actionFilter = '';
    this.dateRangeFilter = '';
    this.currentPage = 1;
  }
}