import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-dashboard-page',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  template: `
    <div class="page">
      <div class="page-header">
        <h1>{{ 'dashboard.title' | translate }}</h1>
        <div class="subtitle">{{ 'dashboard.welcome' | translate }}</div>
      </div>

      <div class="grid grid--two">
        <div class="card">
          <h3>{{ 'dashboard.stats.title' | translate }}</h3>
          <div class="stack">
            <div>
              <div class="label">{{ 'dashboard.stats.bookings' | translate }}</div>
              <div class="kpi">247</div>
              <span class="chip chip--green">+12%</span>
            </div>
            <div>
              <div class="label">{{ 'dashboard.stats.clients' | translate }}</div>
              <div class="kpi">1,024</div>
              <span class="chip chip--blue">+8%</span>
            </div>
            <div>
              <div class="label">{{ 'dashboard.stats.courses' | translate }}</div>
              <div class="kpi">15</div>
              <span class="chip chip--yellow">{{ 'dashboard.stats.active' | translate }}</span>
            </div>
            <div>
              <div class="label">{{ 'dashboard.stats.revenue' | translate }}</div>
              <div class="kpi">€12,450</div>
              <span class="chip chip--green">{{ 'dashboard.stats.thisMonth' | translate }}</span>
            </div>
          </div>
        </div>

        <div class="card">
          <h3>{{ 'dashboard.activity.title' | translate }}</h3>
          <div class="stack">
            <div class="activity-item">
              <div class="activity-content">
                <div class="activity-title">{{ 'dashboard.activity.newClient' | translate }}</div>
                <div class="activity-subtitle">María García se registró</div>
                <div class="activity-time">2 minutos</div>
              </div>
              <span class="chip chip--green">{{ 'dashboard.activity.completed' | translate }}</span>
            </div>
            <div class="activity-item">
              <div class="activity-content">
                <div class="activity-title">{{ 'dashboard.activity.bookingConfirmed' | translate }}</div>
                <div class="activity-subtitle">Clase de surf - Playa Norte</div>
                <div class="activity-time">5 minutos</div>
              </div>
              <span class="chip chip--yellow">{{ 'dashboard.activity.confirmed' | translate }}</span>
            </div>
            <div class="activity-item">
              <div class="activity-content">
                <div class="activity-title">{{ 'dashboard.activity.courseUpdated' | translate }}</div>
                <div class="activity-subtitle">Actualizado horario de Windsurf</div>
                <div class="activity-time">15 minutos</div>
              </div>
              <span class="chip chip--blue">{{ 'dashboard.activity.updated' | translate }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:24px">
        <h3>{{ 'dashboard.actions.title' | translate }}</h3>
        <p class="subtitle">{{ 'dashboard.actions.description' | translate }}</p>
        <div class="row gap">
          <button class="btn btn--primary">{{ 'dashboard.actions.newBooking' | translate }}</button>
          <button class="btn">{{ 'dashboard.actions.addClient' | translate }}</button>
          <button class="btn">{{ 'dashboard.actions.manageCourses' | translate }}</button>
          <button class="btn">{{ 'dashboard.actions.viewReports' | translate }}</button>
        </div>
      </div>

      <div class="grid grid--auto" style="margin-top:24px">
        <div class="card card--tight">
          <span class="chip chip--green">99.9%</span>
          <strong>{{ 'dashboard.status.operational' | translate }}</strong>
        </div>
        <div class="card card--tight">
          <span class="chip chip--yellow">3</span>
          <strong>{{ 'dashboard.status.pending' | translate }}</strong>
        </div>
        <div class="card card--tight">
          <span class="chip chip--blue">V5.1.0</span>
          <strong>{{ 'dashboard.status.available' | translate }}</strong>
        </div>
      </div>
    </div>
  `,
  styles: [
    `
      .label {
        font-size: var(--fs-12);
        color: var(--text-2);
        margin-bottom: 4px;
      }

      .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
      }

      .activity-content {
        flex: 1;
      }

      .activity-title {
        font-size: var(--fs-14);
        font-weight: 600;
        color: var(--text-1);
        margin-bottom: 2px;
      }

      .activity-subtitle {
        font-size: var(--fs-12);
        color: var(--text-2);
        margin-bottom: 4px;
      }

      .activity-time {
        font-size: var(--fs-12);
        color: var(--muted);
      }

      .card--tight {
        display: flex;
        align-items: center;
        gap: 8px;
      }

      h3 {
        font-size: var(--fs-18);
        font-weight: 600;
        color: var(--text-1);
        margin: 0 0 16px 0;
      }
    `,
  ],
})
export class DashboardPageComponent {}