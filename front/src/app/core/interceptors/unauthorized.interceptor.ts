import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthStore } from '../stores/auth.store';
import { catchError, throwError } from 'rxjs';

/**
 * HTTP Interceptor that handles 401 Unauthorized responses
 * by signing out the user and redirecting to login
 */
export const unauthorizedInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthStore);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      // Handle 401 Unauthorized responses
      if (error.status === 401) {
        // Only sign out if user was previously authenticated
        // to avoid infinite loops during login attempts
        if (auth.isAuthenticated()) {
          auth.signOut();

          // Redirect to login page
          router.navigate(['/auth/login'], {
            queryParams: {
              returnUrl: router.url !== '/auth/login' ? router.url : null,
              message: 'Your session has expired. Please login again.',
            },
          });
        }
      }

      // Handle 403 Forbidden responses
      if (error.status === 403) {
        // Could redirect to access denied page or show toast
        console.warn('Access denied:', error.message);

        // Optionally redirect to access denied page
        // router.navigate(['/access-denied']);
      }

      // Handle network errors or server unavailable
      if (error.status === 0 || error.status >= 500) {
        console.error('Network or server error:', error);

        // Could show a global error message or retry mechanism
        // For now, just log the error
      }

      return throwError(() => error);
    })
  );
};
