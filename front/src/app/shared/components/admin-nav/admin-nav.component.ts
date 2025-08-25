import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { TranslatePipe } from '../../pipes/translate.pipe';

@Component({
  selector: 'app-admin-nav',
  standalone: true,
  imports: [CommonModule, RouterModule, TranslatePipe],
  template: `
    <nav class="admin-nav" data-testid="admin-nav">
      <div class="admin-nav-header">
        <h2>{{ 'admin.title' | translate }}</h2>
      </div>
      
      <ul class="admin-nav-links">
        <li>
          <a 
            routerLink="/admin/users" 
            routerLinkActive="active"
            class="nav-link"
            data-testid="users-nav-link"
          >
            <i class="icon-users"></i>
            {{ 'users.title' | translate }}
          </a>
        </li>
        
        <li>
          <a 
            routerLink="/admin/roles" 
            routerLinkActive="active"
            class="nav-link"
            data-testid="roles-nav-link"
          >
            <i class="icon-shield"></i>
            {{ 'roles.title' | translate }}
          </a>
        </li>
        
        <li>
          <a 
            routerLink="/admin/permissions" 
            routerLinkActive="active"
            class="nav-link"
            data-testid="permissions-nav-link"
          >
            <i class="icon-key"></i>
            {{ 'permissions.title' | translate }}
          </a>
        </li>
      </ul>
    </nav>
  `,
  styles: [`
    .admin-nav {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      padding: var(--space-md);
      margin-bottom: var(--space-lg);
    }

    .admin-nav-header {
      margin-bottom: var(--space-md);
      padding-bottom: var(--space-sm);
      border-bottom: 1px solid var(--color-border);
    }

    .admin-nav-header h2 {
      margin: 0;
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .admin-nav-links {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: var(--space-md);
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
      padding: var(--space-sm) var(--space-md);
      border-radius: var(--radius-sm);
      color: var(--color-text-secondary);
      text-decoration: none;
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }

    .nav-link:hover {
      color: var(--color-text-primary);
      background: var(--color-surface-hover);
    }

    .nav-link.active {
      color: var(--color-primary);
      background: var(--color-primary-light);
      border-color: var(--color-primary-light);
    }

    .nav-link i {
      font-size: var(--font-size-base);
    }

    @media (max-width: 768px) {
      .admin-nav-links {
        flex-direction: column;
        gap: var(--space-xs);
      }
    }
  `]
})
export class AdminNavComponent {
  @Input() currentSection?: string;
}