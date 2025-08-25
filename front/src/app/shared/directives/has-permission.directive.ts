import { Directive, Input, OnInit, OnDestroy, TemplateRef, ViewContainerRef, inject } from '@angular/core';
import { Subject, takeUntil } from 'rxjs';
import { PermissionsService } from '@core/services/permissions.service';
import { AuthV5Service } from '@core/services/auth-v5.service';

/**
 * Structural directive to conditionally show/hide elements based on permissions
 * 
 * Usage examples:
 * 
 * Single permission:
 * <div *hasPermission="'clients.view'">Content for users with clients.view permission</div>
 * 
 * Multiple permissions (any):
 * <div *hasPermission="['clients.view', 'clients.manage']">Content for users with any of these permissions</div>
 * 
 * Multiple permissions (all):
 * <div *hasPermission="['clients.view', 'clients.edit']; requireAll: true">Content for users with all permissions</div>
 * 
 * With roles:
 * <div *hasPermission="[]; roles: ['admin', 'manager']">Content for admins or managers</div>
 * 
 * Inverse logic:
 * <div *hasPermission="'admin.panel'; else: true">Content for users WITHOUT admin.panel permission</div>
 */
@Directive({
  selector: '[hasPermission]',
  standalone: true
})
export class HasPermissionDirective implements OnInit, OnDestroy {
  private readonly permissionsService = inject(PermissionsService);
  private readonly authService = inject(AuthV5Service);
  private readonly templateRef = inject(TemplateRef<unknown>);
  private readonly viewContainer = inject(ViewContainerRef);
  private readonly destroy$ = new Subject<void>();

  private hasView = false;
  private lastPermissions: string[] = [];
  private lastRoles: string[] = [];

  @Input() set hasPermission(permissions: string | string[]) {
    this.lastPermissions = Array.isArray(permissions) ? permissions : [permissions];
    this.updateView();
  }

  @Input() hasPermissionRoles: string[] = [];
  @Input() hasPermissionRequireAll = false;
  @Input() hasPermissionElse = false; // If true, shows content when user DOESN'T have permission

  ngOnInit(): void {
    // Subscribe to auth state changes to update view when permissions change
    this.authService.isAuthenticated
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.updateView();
      });

    this.permissionsService.currentPermissions
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.updateView();
      });

    this.permissionsService.currentRoles
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.updateView();
      });

    // Initial view update
    this.updateView();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private updateView(): void {
    const shouldShow = this.checkPermissions();
    
    if (shouldShow && !this.hasView) {
      this.viewContainer.createEmbeddedView(this.templateRef);
      this.hasView = true;
    } else if (!shouldShow && this.hasView) {
      this.viewContainer.clear();
      this.hasView = false;
    }
  }

  private checkPermissions(): boolean {
    // If user is not authenticated, don't show anything
    if (!this.authService.isAuthenticated()) {
      return false;
    }

    let hasPermission = false;

    // Check permissions if any are specified
    if (this.lastPermissions.length > 0) {
      hasPermission = this.hasPermissionRequireAll 
        ? this.permissionsService.hasAllPermissions(this.lastPermissions)
        : this.permissionsService.hasAnyPermission(this.lastPermissions);
    } else {
      // If no permissions specified, default to true (will check roles)
      hasPermission = true;
    }

    // Check roles if any are specified
    if (this.hasPermissionRoles.length > 0) {
      const hasRole = this.hasPermissionRequireAll
        ? this.hasPermissionRoles.every(role => this.permissionsService.hasRole(role))
        : this.permissionsService.hasAnyRole(this.hasPermissionRoles);
      
      // If both permissions and roles are specified, both must pass
      if (this.lastPermissions.length > 0) {
        hasPermission = hasPermission && hasRole;
      } else {
        hasPermission = hasRole;
      }
    }

    // Apply inverse logic if specified
    return this.hasPermissionElse ? !hasPermission : hasPermission;
  }
}

/**
 * Convenience directive for role-based visibility
 * 
 * Usage:
 * <div *hasRole="'admin'">Admin only content</div>
 * <div *hasRole="['admin', 'manager']">Admin or manager content</div>
 */
@Directive({
  selector: '[hasRole]',
  standalone: true
})
export class HasRoleDirective implements OnInit, OnDestroy {
  private readonly permissionsService = inject(PermissionsService);
  private readonly authService = inject(AuthV5Service);
  private readonly templateRef = inject(TemplateRef<unknown>);
  private readonly viewContainer = inject(ViewContainerRef);
  private readonly destroy$ = new Subject<void>();

  private hasView = false;
  private roles: string[] = [];

  @Input() set hasRole(roles: string | string[]) {
    this.roles = Array.isArray(roles) ? roles : [roles];
    this.updateView();
  }

  @Input() hasRoleRequireAll = false;

  ngOnInit(): void {
    // Subscribe to auth state changes
    this.authService.isAuthenticated
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.updateView();
      });

    this.permissionsService.currentRoles
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.updateView();
      });

    this.updateView();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private updateView(): void {
    const shouldShow = this.checkRole();
    
    if (shouldShow && !this.hasView) {
      this.viewContainer.createEmbeddedView(this.templateRef);
      this.hasView = true;
    } else if (!shouldShow && this.hasView) {
      this.viewContainer.clear();
      this.hasView = false;
    }
  }

  private checkRole(): boolean {
    if (!this.authService.isAuthenticated() || this.roles.length === 0) {
      return false;
    }

    return this.hasRoleRequireAll
      ? this.roles.every(role => this.permissionsService.hasRole(role))
      : this.permissionsService.hasAnyRole(this.roles);
  }
}