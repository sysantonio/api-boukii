import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-dashboard-page',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="dashboard-page">
      <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome to Boukii Admin V5</p>
      </div>

      <div class="dashboard-grid">
        <div class="dashboard-card">
          <div class="card-header">
            <h3 class="card-title">Quick Stats</h3>
            <svg viewBox="0 0 24 24" class="card-icon">
              <line x1="12" y1="2" x2="12" y2="6" />
              <line x1="12" y1="18" x2="12" y2="22" />
              <line x1="4.93" y1="4.93" x2="7.76" y2="7.76" />
              <line x1="16.24" y1="16.24" x2="19.07" y2="19.07" />
              <line x1="2" y1="12" x2="6" y2="12" />
              <line x1="18" y1="12" x2="22" y2="12" />
              <line x1="4.93" y1="19.07" x2="7.76" y2="16.24" />
              <line x1="16.24" y1="7.76" x2="19.07" y2="4.93" />
            </svg>
          </div>
          <div class="card-content">
            <div class="stat-item">
              <div class="stat-label">Active Bookings</div>
              <div class="stat-value">247</div>
            </div>
            <div class="stat-item">
              <div class="stat-label">Total Clients</div>
              <div class="stat-value">1,024</div>
            </div>
            <div class="stat-item">
              <div class="stat-label">Active Courses</div>
              <div class="stat-value">15</div>
            </div>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
            <svg viewBox="0 0 24 24" class="card-icon">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12,6 12,12 16,14" />
            </svg>
          </div>
          <div class="card-content">
            <div class="activity-list">
              <div class="activity-item">
                <div class="activity-icon">
                  <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </div>
                <div class="activity-content">
                  <div class="activity-title">New client registered</div>
                  <div class="activity-time">2 minutes ago</div>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon">
                  <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                  </svg>
                </div>
                <div class="activity-content">
                  <div class="activity-title">Booking confirmed</div>
                  <div class="activity-time">5 minutes ago</div>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon">
                  <svg viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                  </svg>
                </div>
                <div class="activity-content">
                  <div class="activity-title">Course updated</div>
                  <div class="activity-time">15 minutes ago</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="dashboard-card large">
          <div class="card-header">
            <h3 class="card-title">AppShell Layout Demo</h3>
            <svg viewBox="0 0 24 24" class="card-icon">
              <path
                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
              />
            </svg>
          </div>
          <div class="card-content">
            <p class="demo-text">
              This dashboard demonstrates the AppShell layout with responsive sidebar, header with
              theme toggle, and proper navigation structure. The theme system supports light/dark
              modes with smooth transitions.
            </p>
            <div class="demo-buttons">
              <button class="demo-btn primary">Primary Button</button>
              <button class="demo-btn secondary">Secondary Button</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [
    `
      .dashboard-page {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
      }

      .page-header {
        margin-bottom: var(--space-8);
      }

      .page-title {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        color: var(--color-text-primary);
        margin-bottom: var(--space-2);
      }

      .page-subtitle {
        font-size: var(--font-size-lg);
        color: var(--color-text-secondary);
        margin: 0;
      }

      .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
      }

      .dashboard-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-xl);
        padding: var(--space-6);
        box-shadow: var(--shadow-sm);
        transition: box-shadow var(--duration-fast) var(--ease-out);
      }

      .dashboard-card:hover {
        box-shadow: var(--shadow-md);
      }

      .dashboard-card.large {
        grid-column: 1 / -1;
      }

      .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--space-4);
      }

      .card-title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--color-text-primary);
        margin: 0;
      }

      .card-icon {
        width: 1.25rem;
        height: 1.25rem;
        stroke: var(--color-text-tertiary);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
      }

      .card-content {
        color: var(--color-text-secondary);
      }

      /* Stats */
      .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--space-3) 0;
        border-bottom: 1px solid var(--color-border-subtle);
      }

      .stat-item:last-child {
        border-bottom: none;
      }

      .stat-label {
        font-size: var(--font-size-sm);
        color: var(--color-text-secondary);
      }

      .stat-value {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-bold);
        color: var(--color-primary);
      }

      /* Activity */
      .activity-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
      }

      .activity-item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
      }

      .activity-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        background: var(--color-surface-elevated);
        border-radius: var(--radius-full);
        flex-shrink: 0;
      }

      .activity-icon svg {
        width: 1rem;
        height: 1rem;
        stroke: var(--color-text-tertiary);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
      }

      .activity-content {
        flex: 1;
      }

      .activity-title {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--color-text-primary);
      }

      .activity-time {
        font-size: var(--font-size-xs);
        color: var(--color-text-tertiary);
      }

      /* Demo content */
      .demo-text {
        font-size: var(--font-size-base);
        line-height: var(--line-height-relaxed);
        margin-bottom: var(--space-6);
      }

      .demo-buttons {
        display: flex;
        gap: var(--space-3);
        flex-wrap: wrap;
      }

      .demo-btn {
        padding: var(--space-3) var(--space-6);
        border: none;
        border-radius: var(--radius-lg);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        cursor: pointer;
        transition: all var(--duration-fast) var(--ease-out);
      }

      .demo-btn.primary {
        background: var(--color-primary);
        color: var(--color-text-on-primary);
      }

      .demo-btn.primary:hover {
        background: var(--color-primary-hover);
      }

      .demo-btn.secondary {
        background: var(--color-surface-elevated);
        color: var(--color-text-primary);
        border: 1px solid var(--color-border);
      }

      .demo-btn.secondary:hover {
        background: var(--color-surface-secondary);
        border-color: var(--color-border-strong);
      }

      /* Responsive */
      @media (max-width: 768px) {
        .dashboard-grid {
          grid-template-columns: 1fr;
          gap: var(--space-4);
        }

        .dashboard-card {
          padding: var(--space-4);
        }

        .page-title {
          font-size: var(--font-size-2xl);
        }
      }
    `,
  ],
})
export class DashboardPageComponent {}
