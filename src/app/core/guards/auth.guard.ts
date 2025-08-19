import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthStore } from '../stores/auth.store';

/**
 * Route guard that protects authenticated routes
 * Redirects to login page if user is not authenticated
 */
export const authGuard: CanActivateFn = (route, state) => {
  const auth = inject(AuthStore);
  const router = inject(Router);

  // Check if user is authenticated
  if (auth.isAuthenticated()) {
    return true;
  }

  // If not authenticated, redirect to login with return URL
  router.navigate(['/auth/login'], {
    queryParams: {
      returnUrl: state.url,
    },
  });

  return false;
};

/**
 * Route guard for guest-only pages (like login, register)
 * Redirects authenticated users to dashboard
 */
export const guestGuard: CanActivateFn = () => {
  const auth = inject(AuthStore);
  const router = inject(Router);

  // If authenticated, redirect to dashboard
  if (auth.isAuthenticated()) {
    router.navigate(['/dashboard']);
    return false;
  }

  return true;
};

/**
 * Role-based route guard factory
 * Creates a guard that checks for specific roles
 */
export function roleGuard(allowedRoles: string[]): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);

    // First check authentication
    if (!auth.isAuthenticated()) {
      router.navigate(['/auth/login']);
      return false;
    }

    // Check if user has any of the required roles
    const userRoles = auth.userRoles();
    const hasRequiredRole = allowedRoles.some((role) => userRoles.includes(role));

    if (!hasRequiredRole) {
      // Could redirect to access denied page
      router.navigate(['/access-denied']);
      return false;
    }

    return true;
  };
}

/**
 * Permission-based route guard factory
 * Creates a guard that checks for specific permissions
 */
export function permissionGuard(requiredPermission: string): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);

    // First check authentication
    if (!auth.isAuthenticated()) {
      router.navigate(['/auth/login']);
      return false;
    }

    // Check permission
    if (!auth.hasPermission(requiredPermission)) {
      router.navigate(['/access-denied']);
      return false;
    }

    return true;
  };
}
