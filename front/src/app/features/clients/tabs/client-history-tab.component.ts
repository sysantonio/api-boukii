import { Component, Input, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ClientDetail, BookingHistoryItem } from '../client-detail.page';

interface BookingSummary {
  totalBookings: number;
  completedBookings: number;
  cancelledBookings: number;
  upcomingBookings: number;
  totalRevenue: number;
}

interface CoursesSummary {
  activeCourses: number;
  completedCourses: number;
  totalHours: number;
}

@Component({
  selector: 'app-client-history-tab',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  template: `
    <div class="client-history-tab">
      <div class="tab-header">
        <div>
          <h2>{{ 'clients.tabs.historial' | translate }}</h2>
          <p class="tab-description">{{ 'clients.tabs.historialDescription' | translate }}</p>
        </div>
      </div>

      @if (bookingHistory().length === 0) {
        <div class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
            </svg>
          </div>
          <h3>{{ 'clients.history.empty.title' | translate }}</h3>
          <p>{{ 'clients.history.empty.message' | translate }}</p>
        </div>
      }

      @if (bookingHistory().length > 0) {
        <!-- Summary Cards -->
        <div class="summary-section">
          <h3 class="section-title">{{ 'clients.history.summary' | translate }}</h3>
          
          <div class="summary-grid">
            <!-- Bookings Summary -->
            <div class="summary-card">
              <div class="card-header">
                <div class="card-icon bookings">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                  </svg>
                </div>
                <h4>{{ 'clients.history.bookings' | translate }}</h4>
              </div>
              <div class="card-content">
                <div class="stat-item">
                  <span class="stat-value">{{ bookingSummary().totalBookings }}</span>
                  <span class="stat-label">{{ 'clients.history.totalBookings' | translate }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value completed">{{ bookingSummary().completedBookings }}</span>
                  <span class="stat-label">{{ 'clients.history.completed' | translate }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value cancelled">{{ bookingSummary().cancelledBookings }}</span>
                  <span class="stat-label">{{ 'clients.history.cancelled' | translate }}</span>
                </div>
              </div>
            </div>

            <!-- Courses Summary -->
            <div class="summary-card">
              <div class="card-header">
                <div class="card-icon courses">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                  </svg>
                </div>
                <h4>{{ 'clients.history.courses' | translate }}</h4>
              </div>
              <div class="card-content">
                <div class="stat-item">
                  <span class="stat-value">{{ coursesSummary().activeCourses }}</span>
                  <span class="stat-label">{{ 'clients.history.active' | translate }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ coursesSummary().completedCourses }}</span>
                  <span class="stat-label">{{ 'clients.history.completed' | translate }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ coursesSummary().totalHours }}h</span>
                  <span class="stat-label">{{ 'clients.history.totalHours' | translate }}</span>
                </div>
              </div>
            </div>

            <!-- Revenue Summary -->
            <div class="summary-card">
              <div class="card-header">
                <div class="card-icon revenue">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                  </svg>
                </div>
                <h4>{{ 'clients.history.revenue' | translate }}</h4>
              </div>
              <div class="card-content">
                <div class="stat-item featured">
                  <span class="stat-value">{{ formatCurrency(bookingSummary().totalRevenue) }}</span>
                  <span class="stat-label">{{ 'clients.history.totalRevenue' | translate }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
          <h3 class="section-title">{{ 'clients.history.recentActivity' | translate }}</h3>
          
          <div class="timeline">
            @for (item of recentHistory(); track item.id) {
              <div class="timeline-item" [attr.data-type]="item.type">
                <div class="timeline-marker" [class]="getMarkerClass(item.type, item.status)">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    @if (item.type === 'booking') {
                      <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                    } @else {
                      <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    }
                  </svg>
                </div>
                
                <div class="timeline-content">
                  <div class="timeline-header">
                    <h4 class="timeline-title">{{ item.title }}</h4>
                    <div class="timeline-meta">
                      <span class="timeline-date">{{ formatDate(item.date) }}</span>
                      <span class="timeline-status" [class]="getStatusClass(item.status)">
                        {{ getStatusLabel(item.status) | translate }}
                      </span>
                    </div>
                  </div>
                  
                  @if (item.description) {
                    <p class="timeline-description">{{ item.description }}</p>
                  }
                  
                  <div class="timeline-details">
                    @if (item.service) {
                      <div class="detail-item">
                        <span class="detail-label">{{ 'clients.history.service' | translate }}:</span>
                        <span class="detail-value">{{ item.service }}</span>
                      </div>
                    }
                    
                    @if (item.instructor) {
                      <div class="detail-item">
                        <span class="detail-label">{{ 'clients.history.instructor' | translate }}:</span>
                        <span class="detail-value">{{ item.instructor }}</span>
                      </div>
                    }
                    
                    @if (item.amount) {
                      <div class="detail-item">
                        <span class="detail-label">{{ 'clients.history.amount' | translate }}:</span>
                        <span class="detail-value amount">{{ formatCurrency(item.amount) }}</span>
                      </div>
                    }
                  </div>
                </div>
              </div>
            }
          </div>
          
          @if (bookingHistory().length > recentHistory().length) {
            <div class="load-more">
              <button type="button" class="btn btn--outline" (click)="loadMoreHistory()">
                {{ 'clients.history.loadMore' | translate }} ({{ bookingHistory().length - recentHistory().length }})
              </button>
            </div>
          }
        </div>
      }
    </div>
  `,
  styleUrls: ['./client-history-tab.component.scss']
})
export class ClientHistoryTabComponent implements OnInit {
  @Input() client!: ClientDetail;

  readonly bookingHistory = signal<BookingHistoryItem[]>([]);
  readonly showingAll = signal(false);

  readonly bookingSummary = computed((): BookingSummary => {
    const history = this.bookingHistory();
    const totalBookings = history.filter(item => item.type === 'booking').length;
    const completedBookings = history.filter(item => item.type === 'booking' && item.status === 'completed').length;
    const cancelledBookings = history.filter(item => item.type === 'booking' && item.status === 'cancelled').length;
    const upcomingBookings = history.filter(item => item.type === 'booking' && item.status === 'confirmed').length;
    const totalRevenue = history
      .filter(item => item.type === 'booking' && item.status === 'completed')
      .reduce((sum, item) => sum + (item.amount || 0), 0);

    return {
      totalBookings,
      completedBookings,
      cancelledBookings,
      upcomingBookings,
      totalRevenue
    };
  });

  readonly coursesSummary = computed((): CoursesSummary => {
    const history = this.bookingHistory();
    const activeCourses = history.filter(item => item.type === 'course' && item.status === 'active').length;
    const completedCourses = history.filter(item => item.type === 'course' && item.status === 'completed').length;
    const totalHours = history
      .filter(item => item.type === 'course')
      .reduce((sum, item) => sum + (item.duration_hours || 0), 0);

    return {
      activeCourses,
      completedCourses,
      totalHours
    };
  });

  readonly recentHistory = computed(() => {
    const history = [...this.bookingHistory()].sort((a, b) => 
      new Date(b.date).getTime() - new Date(a.date).getTime()
    );
    
    return this.showingAll() ? history : history.slice(0, 10);
  });

  ngOnInit(): void {
    this.loadHistoryData();
  }

  private loadHistoryData(): void {
    // Mock data - replace with actual service call
    const mockHistory: BookingHistoryItem[] = [
      {
        id: 1,
        client_id: this.client.id,
        type: 'booking',
        status: 'completed',
        title: 'Clase de Tenis Individual',
        description: 'Clase práctica de 1 hora con trabajo en revés y volea',
        service: 'Tenis Individual',
        instructor: 'Carlos Martínez',
        date: '2024-03-15T10:00:00Z',
        amount: 45.00,
        duration_hours: 1
      },
      {
        id: 2,
        client_id: this.client.id,
        type: 'course',
        status: 'active',
        title: 'Curso de Natación - Nivel Intermedio',
        description: 'Curso de 8 semanas para mejorar técnica de brazada',
        service: 'Natación Grupal',
        instructor: 'Ana García',
        date: '2024-03-10T09:00:00Z',
        amount: 240.00,
        duration_hours: 16
      },
      {
        id: 3,
        client_id: this.client.id,
        type: 'booking',
        status: 'completed',
        title: 'Entrenamiento Personal',
        description: 'Sesión de entrenamiento funcional y cardio',
        service: 'Entrenamiento Personal',
        instructor: 'Miguel Ruiz',
        date: '2024-03-08T17:30:00Z',
        amount: 60.00,
        duration_hours: 1
      },
      {
        id: 4,
        client_id: this.client.id,
        type: 'booking',
        status: 'cancelled',
        title: 'Clase de Pádel',
        description: 'Cancelada por condiciones meteorológicas',
        service: 'Pádel',
        instructor: 'Luis Fernández',
        date: '2024-03-05T16:00:00Z',
        amount: 35.00,
        duration_hours: 1
      },
      {
        id: 5,
        client_id: this.client.id,
        type: 'course',
        status: 'completed',
        title: 'Curso de Iniciación al Golf',
        description: 'Curso completo de 6 semanas para principiantes',
        service: 'Golf Iniciación',
        instructor: 'Roberto Silva',
        date: '2024-02-28T11:00:00Z',
        amount: 180.00,
        duration_hours: 12
      },
      {
        id: 6,
        client_id: this.client.id,
        type: 'booking',
        status: 'completed',
        title: 'Fisioterapia Deportiva',
        description: 'Tratamiento de recuperación post-lesión',
        service: 'Fisioterapia',
        instructor: 'Dr. Patricia López',
        date: '2024-02-25T14:00:00Z',
        amount: 50.00,
        duration_hours: 0.75
      }
    ];
    
    this.bookingHistory.set(mockHistory);
  }

  loadMoreHistory(): void {
    this.showingAll.set(true);
  }

  getMarkerClass(type: string, status: string): string {
    const baseClass = `marker-${type}`;
    const statusClass = `marker-${status}`;
    return `${baseClass} ${statusClass}`;
  }

  getStatusClass(status: string): string {
    const statusClasses: Record<string, string> = {
      'completed': 'status-completed',
      'active': 'status-active',
      'confirmed': 'status-confirmed',
      'cancelled': 'status-cancelled',
      'pending': 'status-pending'
    };
    return statusClasses[status] || 'status-default';
  }

  getStatusLabel(status: string): string {
    const statusLabels: Record<string, string> = {
      'completed': 'clients.history.status.completed',
      'active': 'clients.history.status.active',
      'confirmed': 'clients.history.status.confirmed',
      'cancelled': 'clients.history.status.cancelled',
      'pending': 'clients.history.status.pending'
    };
    return statusLabels[status] || status;
  }

  formatDate(dateString: string): string {
    if (!dateString) return '';
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch {
      return dateString;
    }
  }

  formatCurrency(amount: number): string {
    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR'
    }).format(amount);
  }
}