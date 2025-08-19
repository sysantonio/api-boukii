import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

import { AuthV5Service } from '../../core/services/auth-v5.service';
import { TranslationService } from '../../core/services/translation.service';
import { ToastService } from '../../core/services/toast.service';
import { School } from '../../core/models/auth-v5.models';
import { TranslatePipe } from '../../shared/pipes/translate.pipe';

@Component({
  selector: 'app-school-selection-page',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  template: `
    <div class="school-selection-page">
      <div class="school-selection-container">
        <div class="school-selection-card">
          <div class="card-header">
            <h1 class="card-title">{{ 'schoolSelection.title' | translate }}</h1>
            <p class="card-subtitle">{{ 'schoolSelection.subtitle' | translate }}</p>
          </div>
          
          <div class="card-content">
            @if (isLoading()) {
              <div class="loading-state">
                <div class="spinner">🔄</div>
                <p>{{ 'schoolSelection.loading' | translate }}</p>
              </div>
            } @else if (schools().length === 0) {
              <div class="empty-state">
                <p>{{ 'schoolSelection.noSchools' | translate }}</p>
                <button (click)="logout()" class="logout-button">
                  {{ 'auth.signOut' | translate }}
                </button>
              </div>
            } @else {
              <div class="schools-list">
                @for (school of schools(); track school.id) {
                  <button 
                    (click)="selectSchool(school)"
                    class="school-item"
                    [disabled]="isSelecting()"
                    data-cy="school-item">
                    <div class="school-info">
                      <h3 class="school-name">{{ school.name }}</h3>
                      @if (school.description) {
                        <p class="school-description">{{ school.description }}</p>
                      }
                    </div>
                    <div class="school-arrow">→</div>
                  </button>
                }
              </div>
            }
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .school-selection-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 1rem;
    }

    .school-selection-container {
      width: 100%;
      max-width: 500px;
    }

    .school-selection-card {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .card-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .card-title {
      font-size: 1.75rem;
      font-weight: 600;
      margin: 0 0 0.5rem 0;
      color: #1f2937;
    }

    .card-subtitle {
      color: #6b7280;
      margin: 0;
    }

    .loading-state, .empty-state {
      text-align: center;
      padding: 2rem;
    }

    .spinner {
      font-size: 2rem;
      margin-bottom: 1rem;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .schools-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .school-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: white;
      cursor: pointer;
      transition: all 0.2s;
      text-align: left;
      width: 100%;
    }

    .school-item:hover:not(:disabled) {
      border-color: #3b82f6;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .school-item:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .school-info {
      flex: 1;
    }

    .school-name {
      font-size: 1.125rem;
      font-weight: 500;
      margin: 0 0 0.25rem 0;
      color: #1f2937;
    }

    .school-description {
      font-size: 0.875rem;
      color: #6b7280;
      margin: 0;
    }

    .school-arrow {
      font-size: 1.25rem;
      color: #9ca3af;
      margin-left: 1rem;
    }

    .logout-button {
      padding: 0.75rem 1.5rem;
      background: #ef4444;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .logout-button:hover {
      background: #dc2626;
    }
  `]
})
export class SchoolSelectionPage implements OnInit {
  private readonly router = inject(Router);
  private readonly authV5 = inject(AuthV5Service);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);

  // Reactive state
  readonly isLoading = signal(true);
  readonly isSelecting = signal(false);
  readonly schools = signal<School[]>([]);

  ngOnInit(): void {
    this.loadSchools();
  }

  private loadSchools(): void {
    this.isLoading.set(true);
    
    // First try to get temporary schools from login flow
    const tempSchoolsData = localStorage.getItem('boukii_temp_schools');
    if (tempSchoolsData) {
      try {
        const tempSchools = JSON.parse(tempSchoolsData);
        console.log('📚 Loading schools from temporary login data:', tempSchools);
        this.schools.set(tempSchools);
        this.isLoading.set(false);
        
        // Auto-select if only one school (shouldn't happen in this flow, but just in case)
        if (tempSchools.length === 1) {
          this.selectSchool(tempSchools[0]);
        }
        return;
      } catch (error) {
        console.error('❌ Error parsing temporary schools data:', error);
        localStorage.removeItem('boukii_temp_schools');
      }
    }
    
    // Fallback: get schools from auth service (for direct navigation to select-school)
    this.authV5.getUserSchools().subscribe({
      next: (response) => {
        this.isLoading.set(false);
        if (response.success && response.data) {
          this.schools.set(response.data);
          
          // Auto-select if only one school
          if (response.data.length === 1) {
            this.selectSchool(response.data[0]);
          }
        }
      },
      error: (error) => {
        this.isLoading.set(false);
        console.error('❌ Error loading schools:', error);
        const errorMessage = this.getLocalizedErrorMessage(error);
        this.toast.error(errorMessage);
      }
    });
  }

  selectSchool(school: School): void {
    if (this.isSelecting()) return;
    
    this.isSelecting.set(true);
    
    // Check if we're coming from login flow (temp token exists)
    const tempToken = localStorage.getItem('boukii_temp_token');
    
    if (tempToken) {
      // Use V5 API flow for school selection during login
      console.log('🏫 Selecting school via V5 API:', school.name);
      this.authV5.selectSchool(school.id, tempToken).subscribe({
        next: (response) => {
          // Clean up temporary data
          localStorage.removeItem('boukii_temp_token');
          localStorage.removeItem('boukii_temp_schools');
          
          if (!response.success || !response.data) {
            this.isSelecting.set(false);
            this.toast.error('School selection failed');
            return;
          }

          const { available_seasons, access_token, has_multiple_seasons } = response.data;

          if (!has_multiple_seasons || available_seasons.length === 1) {
            // Single season - complete login
            console.log('✅ Single season - completing V5 login');
            
            this.authV5.handleLoginSuccess({
              token: access_token,
              user: response.data.user,
              school: response.data.school,
              season: response.data.season
            });
            
            this.isSelecting.set(false);
            this.toast.success(this.translation.get('auth.login.success'));
            this.router.navigate(['/dashboard']);
          } else {
            // Multiple seasons - continue to season selection
            console.log('🗓️ Multiple seasons available, continuing to season selection');
            // TODO: Implement season selection flow
            this.isSelecting.set(false);
            this.toast.info('Season selection not yet implemented');
          }
        },
        error: (error) => {
          this.isSelecting.set(false);
          console.error('❌ V5 school selection error:', error);
          const errorMessage = this.getLocalizedErrorMessage(error);
          this.toast.error(errorMessage);
        }
      });
    } else {
      // Use local selection for already logged in users
      console.log('🏫 Selecting school locally:', school.name);
      this.authV5.setSelectedSchool(school.id).subscribe({
        next: () => {
          this.isSelecting.set(false);
          // AuthV5Service handles navigation to dashboard or season selection
        },
        error: (error) => {
          this.isSelecting.set(false);
          console.error('❌ Error selecting school:', error);
          const errorMessage = this.getLocalizedErrorMessage(error);
          this.toast.error(errorMessage);
        }
      });
    }
  }

  logout(): void {
    this.authV5.logout();
    this.router.navigate(['/auth/login']);
  }

  private getLocalizedErrorMessage(error: any): string {
    // Try to get specific API error message
    if (error.code) {
      switch (error.code) {
        case 'school_selection_failed':
          return this.translation.get('errors.api.schoolSelectionFailed');
        case 'access_denied':
          return this.translation.get('errors.forbidden');
        default:
          break;
      }
    }

    // Try to get HTTP status based errors
    if (error.status) {
      switch (error.status) {
        case 403:
          return this.translation.get('errors.forbidden');
        case 404:
          return this.translation.get('errors.notFound');
        case 500:
          return this.translation.get('errors.serverError');
        case 0:
          return this.translation.get('errors.network');
        default:
          break;
      }
    }

    // Fallback to generic error message or provided message
    return error.userMessage || 
           error.detail || 
           error.message ||
           this.translation.get('schoolSelection.error');
  }
}