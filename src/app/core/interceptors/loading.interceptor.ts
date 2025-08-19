import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { finalize } from 'rxjs';
import { LoadingStore } from '../stores/loading.store';

/**
 * Loading Interceptor
 * Automatically manages global loading state for HTTP requests
 * Features:
 * - Tracks individual requests
 * - Prevents loading flashing for fast requests
 * - Supports request categorization
 * - Provides detailed loading information
 */
export const loadingInterceptor: HttpInterceptorFn = (req, next) => {
  const loadingStore = inject(LoadingStore);

  // Skip loading for certain requests
  const skipLoadingUrls = ['/api/health', '/api/ping', '/api/logs'];

  const shouldSkipLoading =
    skipLoadingUrls.some((url) => req.url.includes(url)) || req.headers.has('X-Skip-Loading');

  if (shouldSkipLoading) {
    return next(req);
  }

  // Generate unique request ID
  const requestId = loadingStore.generateRequestId();

  // Get request description for better UX
  const description = getRequestDescription(req.url, req.method);

  // Start loading
  loadingStore.startLoading(requestId, req.url, req.method, description);

  return next(req).pipe(
    finalize(() => {
      // Stop loading when request completes (success or error)
      loadingStore.stopLoading(requestId);
    })
  );
};

/**
 * Get user-friendly description for common API endpoints
 */
function getRequestDescription(url: string, method: string): string | undefined {
  const cleanUrl = url.replace(/\/api\/v?\d*/, '');

  // Auth requests
  if (cleanUrl.includes('/auth/login')) return 'Signing in...';
  if (cleanUrl.includes('/auth/logout')) return 'Signing out...';
  if (cleanUrl.includes('/auth/me')) return 'Loading profile...';
  if (cleanUrl.includes('/auth/refresh')) return 'Refreshing session...';

  // Dashboard requests
  if (cleanUrl.includes('/dashboard')) return 'Loading dashboard...';
  if (cleanUrl.includes('/stats')) return 'Loading statistics...';

  // CRUD operations
  if (method === 'POST') {
    if (cleanUrl.includes('/users')) return 'Creating user...';
    if (cleanUrl.includes('/clients')) return 'Creating client...';
    if (cleanUrl.includes('/courses')) return 'Creating course...';
    if (cleanUrl.includes('/bookings')) return 'Creating booking...';
    return 'Creating...';
  }

  if (method === 'PUT' || method === 'PATCH') {
    if (cleanUrl.includes('/users')) return 'Updating user...';
    if (cleanUrl.includes('/clients')) return 'Updating client...';
    if (cleanUrl.includes('/courses')) return 'Updating course...';
    if (cleanUrl.includes('/bookings')) return 'Updating booking...';
    return 'Updating...';
  }

  if (method === 'DELETE') {
    if (cleanUrl.includes('/users')) return 'Deleting user...';
    if (cleanUrl.includes('/clients')) return 'Deleting client...';
    if (cleanUrl.includes('/courses')) return 'Deleting course...';
    if (cleanUrl.includes('/bookings')) return 'Deleting booking...';
    return 'Deleting...';
  }

  if (method === 'GET') {
    if (cleanUrl.includes('/users')) return 'Loading users...';
    if (cleanUrl.includes('/clients')) return 'Loading clients...';
    if (cleanUrl.includes('/courses')) return 'Loading courses...';
    if (cleanUrl.includes('/bookings')) return 'Loading bookings...';
    if (cleanUrl.includes('/monitors')) return 'Loading monitors...';
    return 'Loading...';
  }

  return undefined;
}

/**
 * Helper function to skip loading for specific requests
 * Usage: Add X-Skip-Loading header to requests that shouldn't show loading
 */
export function skipLoadingHeader() {
  return { 'X-Skip-Loading': 'true' };
}
