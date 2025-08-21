import { Component, OnInit, OnDestroy, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { Subject, takeUntil, debounceTime, distinctUntilChanged, catchError, of } from 'rxjs';

import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { SchoolService, GetSchoolsParams } from '@core/services/school.service';
import { ContextService, School } from '@core/services/context.service';
import { TranslationService } from '@core/services/translation.service';

interface PaginationState {
  current: number;
  total: number;
  perPage: number;
  from: number;
  to: number;
  totalResults: number;
}

@Component({
  selector: 'app-select-school',
  standalone: true,
  imports: [CommonModule, FormsModule, TranslatePipe],
  template: `
    <div class="select-school-page">
      <!-- Breadcrumb -->
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <span class="breadcrumb-text">{{ 'schools.selectSchool.breadcrumb' | translate }}</span>
      </nav>

      <!-- Page Header -->
      <header class="page-header">
        <h1 class="page-title">{{ 'schools.selectSchool.title' | translate }}</h1>
        <p class="page-subtitle">{{ 'schools.selectSchool.subtitle' | translate }}</p>
      </header>

      <!-- Search Bar -->
      <div class="search-section">
        <div class="search-input-wrapper">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" [attr.stroke-width]="'2'">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          <input
            type="text"
            class="search-input"
            [placeholder]="'schools.selectSchool.searchPlaceholder' | translate"
            [(ngModel)]="searchQuery"
            (input)="onSearchInput()"
            [attr.aria-label]="'schools.selectSchool.searchPlaceholder' | translate"
          />
          @if (isSearching()) {
            <div class="search-spinner">
              <div class="spinner"></div>
            </div>
          }
        </div>
      </div>

      <!-- Content Area -->
      <main class="content-area" role="main">
        @if (isLoading() && !isSearching()) {
          <!-- Loading State -->
          <div class="loading-state" [attr.aria-label]="'schools.selectSchool.loadingSchools' | translate">
            <div class="loading-spinner">
              <div class="spinner large"></div>
            </div>
            <p class="loading-text">{{ 'schools.selectSchool.loadingSchools' | translate }}</p>
          </div>
        } @else if (hasError()) {
          <!-- Error State -->
          <div class="error-state" role="alert">
            <svg class="error-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" [attr.stroke-width]="'2'">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <h2 class="error-title">{{ 'schools.selectSchool.errorLoading' | translate }}</h2>
            <p class="error-message">{{ errorMessage() || ('common.error' | translate) }}</p>
            <button class="retry-button" (click)="loadSchools()" [disabled]="isLoading()">
              {{ 'common.retry' | translate }}
            </button>
          </div>
        } @else if (schools().length === 0) {
          <!-- Empty State -->
          <div class="empty-state">
            <svg class="empty-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" [attr.stroke-width]="'2'">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
            </svg>
            <h2 class="empty-title">{{ 'schools.selectSchool.noSchools' | translate }}</h2>
            <p class="empty-message">
              @if (searchQuery.trim()) {
                {{ 'common.noResults' | translate }}
              } @else {
                {{ 'schools.selectSchool.noSchoolsMessage' | translate }}
              }
            </p>
          </div>
        } @else {
          <!-- Schools Grid -->
          <div class="schools-grid">
            @for (school of schools(); track school.id) {
              <article class="school-card" [class.selecting]="isSelecting() === school.id">
                <div class="school-header">
                  <h3 class="school-name">{{ school.name }}</h3>
                  @if (school.slug) {
                    <span class="school-slug">{{ school.slug }}</span>
                  }
                </div>

                <div class="school-status">
                  <span 
                    class="status-badge" 
                    [class.active]="school.active"
                    [class.inactive]="!school.active"
                  >
                    {{ school.active ? ('schools.status.active' | translate) : ('schools.status.inactive' | translate) }}
                  </span>
                </div>

                <div class="school-actions">
                  <button
                    class="select-school-button"
                    [class.primary]="school.active"
                    [class.secondary]="!school.active"
                    [disabled]="isSelecting() !== null || !school.active"
                    (click)="selectSchool(school)"
                  >
                    @if (isSelecting() === school.id) {
                      <div class="button-spinner">
                        <div class="spinner small"></div>
                      </div>
                    }
                    <span [class.hidden]="isSelecting() === school.id">
                      {{ 'schools.selectSchool.useThisSchool' | translate }}
                    </span>
                  </button>
                </div>
              </article>
            }
          </div>

          <!-- Pagination -->
          @if (pagination().total > 1) {
            <nav class="pagination" aria-label="Schools pagination">
              <div class="pagination-info">
                <span class="pagination-text">
                  {{ 'schools.pagination.showing' | translate }}
                  <strong>{{ pagination().from }}</strong>
                  {{ 'schools.pagination.to' | translate }}
                  <strong>{{ pagination().to }}</strong>
                  {{ 'schools.pagination.of' | translate }}
                  <strong>{{ pagination().totalResults }}</strong>
                  {{ 'schools.pagination.results' | translate }}
                </span>
              </div>

              <div class="pagination-controls">
                <button
                  class="pagination-button"
                  [disabled]="pagination().current === 1 || isLoading()"
                  (click)="goToPage(pagination().current - 1)"
                  [attr.aria-label]="'schools.pagination.previous' | translate"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" [attr.stroke-width]="'2'">
                    <polyline points="15,18 9,12 15,6"></polyline>
                  </svg>
                  {{ 'schools.pagination.previous' | translate }}
                </button>

                <div class="page-numbers">
                  @for (page of getPageNumbers(); track page) {
                    @if (page === '...') {
                      <span class="page-ellipsis">...</span>
                    } @else {
                      <button
                        class="page-button"
                        [class.active]="page === pagination().current"
                        [disabled]="isLoading()"
                        (click)="onPageClick(page)"
                        [attr.aria-label]="getPageAriaLabel(page)"
                        [attr.aria-current]="page === pagination().current ? 'page' : null"
                      >
                        {{ page }}
                      </button>
                    }
                  }
                </div>

                <button
                  class="pagination-button"
                  [disabled]="pagination().current === pagination().total || isLoading()"
                  (click)="goToPage(pagination().current + 1)"
                  [attr.aria-label]="'schools.pagination.next' | translate"
                >
                  {{ 'schools.pagination.next' | translate }}
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" [attr.stroke-width]="'2'">
                    <polyline points="9,18 15,12 9,6"></polyline>
                  </svg>
                </button>
              </div>
            </nav>
          }
        }
      </main>
    </div>
  `,
  styleUrl: './select-school.styles.scss'
})
export class SelectSchoolPageComponent implements OnInit, OnDestroy {
  private readonly destroy$ = new Subject<void>();
  private readonly schoolService = inject(SchoolService);
  private readonly contextService = inject(ContextService);
  private readonly translationService = inject(TranslationService);
  private readonly router = inject(Router);

  // Component state
  private readonly _isLoading = signal(true);
  private readonly _isSearching = signal(false);
  private readonly _isSelecting = signal<number | null>(null);
  private readonly _hasError = signal(false);
  private readonly _errorMessage = signal<string | null>(null);
  private readonly _schools = signal<School[]>([]);
  private readonly _pagination = signal<PaginationState>({
    current: 1,
    total: 1,
    perPage: 20,
    from: 0,
    to: 0,
    totalResults: 0
  });

  // Search state
  searchQuery = '';
  private searchSubject = new Subject<string>();

  // Public computed signals
  readonly isLoading = computed(() => this._isLoading());
  readonly isSearching = computed(() => this._isSearching());
  readonly isSelecting = computed(() => this._isSelecting());
  readonly hasError = computed(() => this._hasError());
  readonly errorMessage = computed(() => this._errorMessage());
  readonly schools = computed(() => this._schools());
  readonly pagination = computed(() => this._pagination());

  ngOnInit(): void {
    this.setupSearch();
    this.loadSchools();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private setupSearch(): void {
    this.searchSubject
      .pipe(
        debounceTime(300),
        distinctUntilChanged(),
        takeUntil(this.destroy$)
      )
      .subscribe(() => {
        this.loadSchools(1);
      });
  }

  onSearchInput(): void {
    this._isSearching.set(true);
    this.searchSubject.next(this.searchQuery);
  }

  loadSchools(page: number = 1): void {
    this._isLoading.set(true);
    this._hasError.set(false);
    this._errorMessage.set(null);

    const params: GetSchoolsParams = {
      page,
      perPage: 20,
      search: this.searchQuery.trim(),
      active: true,
      orderBy: 'name',
      orderDirection: 'asc'
    };

    this.schoolService.getMySchools(params)
      .pipe(
        takeUntil(this.destroy$),
        catchError((error) => {
          console.error('Error loading schools:', error);
          this._hasError.set(true);
          this._errorMessage.set(error.message || 'Unknown error');
          return of(null);
        })
      )
      .subscribe((response) => {
        this._isLoading.set(false);
        this._isSearching.set(false);

        if (response) {
          this._schools.set(response.data);
          this._pagination.set({
            current: response.meta.page,
            total: response.meta.lastPage,
            perPage: response.meta.perPage,
            from: response.meta.from,
            to: response.meta.to,
            totalResults: response.meta.total
          });
        }
      });
  }

  async selectSchool(school: School): Promise<void> {
    if (!school.active || this._isSelecting() !== null) {
      return;
    }

    this._isSelecting.set(school.id);

    try {
      await this.contextService.setSchool(school.id);
      
      // Navigate to season selection
      this.router.navigate(['/select-season']);
    } catch (error) {
      console.error('Error selecting school:', error);
      
      // Show error to user
      this._errorMessage.set(
        this.translationService.get('schools.selectSchool.errorSelecting')
      );
      this._hasError.set(true);
      this._isSelecting.set(null);
    }
  }

  goToPage(page: number): void {
    if (page === this.pagination().current || page < 1 || page > this.pagination().total) {
      return;
    }

    this.loadSchools(page);
  }

  getPageNumbers(): Array<number | '...'> {
    const current = this.pagination().current;
    const total = this.pagination().total;
    const pages: Array<number | '...'> = [];

    if (total <= 7) {
      // Show all pages if 7 or fewer
      for (let i = 1; i <= total; i++) {
        pages.push(i);
      }
    } else {
      // Always show first page
      pages.push(1);

      if (current <= 4) {
        // Show 1, 2, 3, 4, 5, ..., last
        for (let i = 2; i <= 5; i++) {
          pages.push(i);
        }
        pages.push('...');
        pages.push(total);
      } else if (current >= total - 3) {
        // Show 1, ..., last-4, last-3, last-2, last-1, last
        pages.push('...');
        for (let i = total - 4; i <= total; i++) {
          pages.push(i);
        }
      } else {
        // Show 1, ..., current-1, current, current+1, ..., last
        pages.push('...');
        for (let i = current - 1; i <= current + 1; i++) {
          pages.push(i);
        }
        pages.push('...');
        pages.push(total);
      }
    }

    return pages;
  }

  onPageClick(page: number | '...'): void {
    if (typeof page === 'number') {
      this.goToPage(page);
    }
  }

  getPageAriaLabel(page: number | '...'): string {
    if (typeof page === 'number') {
      const pageText = this.translationService.get('schools.pagination.page');
      return `${pageText} ${page}`;
    }
    return '';
  }
}