import { inject } from '@angular/core';
import { CanActivateFn, CanMatchFn, Router, ActivatedRouteSnapshot } from '@angular/router';
import { PermissionsService } from '@core/services/permissions.service';
import { AuthV5Service } from '@core/services/auth-v5.service';
import { ContextService } from '@core/services/context.service';

/**
 * Generic permission guard factory
 */
export function createPermissionGuard(requiredPermissions: string[], requireAll = false): CanActivateFn {
  return () => {
    const permissions = inject(PermissionsService);
    const router = inject(Router);
    const auth = inject(AuthV5Service);
    const context = inject(ContextService);

    // Check authentication first
    if (!auth.isAuthenticated()) {
      router.navigate(['/auth/login']);
      return false;
    }

    // Check complete context
    if (!context.hasCompleteContext()) {
      // Redirect to appropriate selection page
      if (!context.hasSchoolSelected()) {
        router.navigate(['/select-school']);
      } else {
        router.navigate(['/select-season']);
      }
      return false;
    }

    // Check permissions
    const hasPermission = requireAll 
      ? permissions.hasAllPermissions(requiredPermissions)
      : permissions.hasAnyPermission(requiredPermissions);

    if (!hasPermission) {
      console.warn('Permission denied. Required:', requiredPermissions, 'User has:', permissions.currentPermissions());
      router.navigate(['/unauthorized']);
      return false;
    }

    return true;
  };
}

/**
 * Generic role guard factory
 */
export function createRoleGuard(requiredRoles: string[], requireAll = false): CanActivateFn {
  return () => {
    const permissions = inject(PermissionsService);
    const router = inject(Router);
    const auth = inject(AuthV5Service);
    const context = inject(ContextService);

    // Check authentication first
    if (!auth.isAuthenticated()) {
      router.navigate(['/auth/login']);
      return false;
    }

    // Check complete context
    if (!context.hasCompleteContext()) {
      // Redirect to appropriate selection page
      if (!context.hasSchoolSelected()) {
        router.navigate(['/select-school']);
      } else {
        router.navigate(['/select-season']);
      }
      return false;
    }

    // Check roles
    const hasRole = requireAll 
      ? requiredRoles.every(role => permissions.hasRole(role))
      : permissions.hasAnyRole(requiredRoles);

    if (!hasRole) {
      console.warn('Role access denied. Required:', requiredRoles, 'User roles:', permissions.currentRoles().map(r => r.slug));
      router.navigate(['/unauthorized']);
      return false;
    }

    return true;
  };
}

/**
 * Route data-based permission guard
 */
export const permissionGuard: CanActivateFn = (route: ActivatedRouteSnapshot) => {
  const permissions = inject(PermissionsService);
  const router = inject(Router);
  const auth = inject(AuthV5Service);
  const context = inject(ContextService);

  // Check authentication first
  if (!auth.isAuthenticated()) {
    router.navigate(['/auth/login']);
    return false;
  }

  // Check complete context
  if (!context.hasCompleteContext()) {
    if (!context.hasSchoolSelected()) {
      router.navigate(['/select-school']);
    } else {
      router.navigate(['/select-season']);
    }
    return false;
  }

  // Get required permissions from route data
  const requiredPermissions: string[] = route.data['permissions'] || [];
  const requiredRoles: string[] = route.data['roles'] || [];
  const requireAll: boolean = route.data['requireAll'] || false;

  // Check permissions if specified
  if (requiredPermissions.length > 0) {
    const hasPermission = requireAll 
      ? permissions.hasAllPermissions(requiredPermissions)
      : permissions.hasAnyPermission(requiredPermissions);

    if (!hasPermission) {
      console.warn('Route permission denied. Required:', requiredPermissions);
      router.navigate(['/unauthorized']);
      return false;
    }
  }

  // Check roles if specified
  if (requiredRoles.length > 0) {
    const hasRole = requireAll 
      ? requiredRoles.every(role => permissions.hasRole(role))
      : permissions.hasAnyRole(requiredRoles);

    if (!hasRole) {
      console.warn('Route role access denied. Required:', requiredRoles);
      router.navigate(['/unauthorized']);
      return false;
    }
  }

  return true;
};

// Predefined guards for common permissions
export const adminGuard = createRoleGuard([PermissionsService.ROLES.ADMIN, PermissionsService.ROLES.OWNER]);
export const managerGuard = createRoleGuard([
  PermissionsService.ROLES.ADMIN, 
  PermissionsService.ROLES.OWNER, 
  PermissionsService.ROLES.MANAGER
]);

// Module-specific guards
export const clientsGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.CLIENTS_VIEW,
  PermissionsService.PERMISSIONS.CLIENTS_MANAGE
]);

export const bookingsGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.BOOKINGS_VIEW,
  PermissionsService.PERMISSIONS.BOOKINGS_MANAGE
]);

export const coursesGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.COURSES_VIEW,
  PermissionsService.PERMISSIONS.COURSES_MANAGE
]);

export const monitorsGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.MONITORS_VIEW,
  PermissionsService.PERMISSIONS.MONITORS_MANAGE
]);

export const reportsGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.REPORTS_VIEW
]);

export const settingsGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.SETTINGS_VIEW,
  PermissionsService.PERMISSIONS.SETTINGS_EDIT
]);

export const usersManagementGuard = createPermissionGuard([
  PermissionsService.PERMISSIONS.USERS_MANAGE
]);

/**
 * School management guard - requires manage or administrate permissions
 */
export const schoolManagementGuard: CanActivateFn = () => {
  const permissions = inject(PermissionsService);
  const router = inject(Router);
  const auth = inject(AuthV5Service);
  const context = inject(ContextService);

  if (!auth.isAuthenticated()) {
    router.navigate(['/auth/login']);
    return false;
  }

  if (!context.hasCompleteContext()) {
    if (!context.hasSchoolSelected()) {
      router.navigate(['/select-school']);
    } else {
      router.navigate(['/select-season']);
    }
    return false;
  }

  if (!permissions.canManageSchool()) {
    console.warn('School management access denied');
    router.navigate(['/unauthorized']);
    return false;
  }

  return true;
};