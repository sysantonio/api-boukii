import { ErrorHandler, Injectable, inject, NgZone } from '@angular/core';
import { Router } from '@angular/router';
import { LoggingService } from './logging.service';
import { ToastService } from './toast.service';
import { AuthStore } from '../stores/auth.store';
import { ErrorSeverity, TechnicalError } from '../models/error.models';
import { ApiService } from './api.service';
/**
 * Global Error Handler
 * Catches all unhandled errors in the Angular application
 * Features:
 * - Structured error logging
 * - User-friendly error notifications
 * - Error reporting to external services
 * - Development vs production error handling
 * - Error context enrichment
 */
@Injectable({ providedIn: 'root' })
export class GlobalErrorHandlerService implements ErrorHandler {
  private readonly logger = inject(LoggingService);
  private readonly toastService = inject(ToastService);
  private readonly authStore = inject(AuthStore);
  private readonly router = inject(Router);
  private readonly ngZone = inject(NgZone);
  private readonly api = inject(ApiService);

  handleError(error: unknown): void {
    console.error('🚨 Global Error Handler:', error);

    // Run outside Angular zone to prevent change detection errors
    this.ngZone.run(() => {
      this.processError(error);
    });
  }

  private processError(error: unknown): void {
    const processedError = this.categorizeError(error);
    const userMessage = this.getUserFriendlyMessage(processedError);
    const shouldShowToUser = this.shouldShowErrorToUser(processedError);

    // Log the error with full context
    this.logger.logError(
      processedError.title,
      processedError,
      {
        url: window.location.href,
        method: 'GLOBAL_ERROR',
        severity: this.getErrorSeverity(processedError),
      },
      userMessage
    );

    // Show user notification if appropriate
    if (shouldShowToUser) {
      this.showErrorToUser(processedError, userMessage);
    }

    // Handle critical errors
    if (this.isCriticalError(processedError)) {
      this.handleCriticalError(processedError);
    }

    // Report to external service in production
    if (this.isProduction()) {
      this.reportToExternalService(processedError);
    }
  }

  private categorizeError(error: unknown): TechnicalError {
    // JavaScript Error
    if (error instanceof Error) {
      return {
        type: 'javascript_error',
        title: error.name || 'JavaScript Error',
        detail: error.message || 'An unexpected error occurred',
        code: this.getErrorCode(error),
        stackTrace: error.stack,
        originalError: error,
      };
    }

    // Promise rejection
    if (this.isPromiseRejection(error)) {
      return {
        type: 'promise_rejection',
        title: 'Unhandled Promise Rejection',
        detail: this.extractPromiseRejectionMessage(error),
        code: 'PROMISE_REJECTION',
        originalError: error,
      };
    }

    // HTTP Error that wasn't caught by interceptor
    if (this.isHttpError(error)) {
      const httpError = error as { status: number; message?: string };
      return {
        type: 'http_error',
        title: `HTTP ${httpError.status} Error`,
        detail: httpError.message || 'Network request failed',
        code: `HTTP_${httpError.status}`,
        originalError: error,
      };
    }

    // Angular-specific errors
    if (this.isAngularError(error)) {
      return {
        type: 'angular_error',
        title: 'Angular Framework Error',
        detail: this.extractAngularErrorMessage(error),
        code: 'ANGULAR_ERROR',
        originalError: error,
      };
    }

    // Generic unknown error
    return {
      type: 'unknown_error',
      title: 'Unknown Error',
      detail: typeof error === 'string' ? error : 'An unexpected error occurred',
      code: 'UNKNOWN_ERROR',
      originalError: error,
    };
  }

  private getUserFriendlyMessage(error: TechnicalError): string {
    switch (error.type) {
      case 'javascript_error':
        if (error.code === 'CHUNK_LOAD_ERROR') {
          return 'The application needs to be refreshed. Please reload the page.';
        }
        if (error.code === 'NETWORK_ERROR') {
          return 'Network connection error. Please check your internet connection.';
        }
        return 'Something went wrong. Please try refreshing the page.';

      case 'promise_rejection':
        return 'An operation failed unexpectedly. Please try again.';

      case 'http_error':
        return 'Failed to communicate with the server. Please try again.';

      case 'angular_error':
        return 'The application encountered an error. Please refresh the page.';

      default:
        return 'An unexpected error occurred. Please try refreshing the page.';
    }
  }

  private shouldShowErrorToUser(error: TechnicalError): boolean {
    // Don't show certain internal errors to users
    const internalErrors = [
      'ResizeObserver loop limit exceeded',
      'Non-Error promise rejection captured',
      'Script error.',
    ];

    return !internalErrors.some((msg) => error.detail.toLowerCase().includes(msg.toLowerCase()));
  }

  private showErrorToUser(error: TechnicalError, userMessage: string): void {
    const isChunkLoadError = error.code === 'CHUNK_LOAD_ERROR';

    this.toastService.error(userMessage, {
      title: error.title,
      duration: 0, // Don't auto-dismiss errors
      actions: isChunkLoadError
        ? [
            {
              label: 'Reload Page',
              action: () => window.location.reload(),
              style: 'primary',
            },
          ]
        : [
            {
              label: 'Report Issue',
              action: () => this.openErrorReportDialog(error),
              style: 'secondary',
            },
          ],
    });
  }

  private isCriticalError(error: TechnicalError): boolean {
    const criticalPatterns = [
      'CHUNK_LOAD_ERROR',
      'MODULE_NOT_FOUND',
      'SECURITY_ERROR',
      'Cannot resolve all parameters',
    ];

    return criticalPatterns.some(
      (pattern) => error.code.includes(pattern) || error.detail.includes(pattern)
    );
  }

  private handleCriticalError(error: TechnicalError): void {
    // For critical errors, we might need to:
    // 1. Clear local storage
    // 2. Sign out user
    // 3. Redirect to safe page

    if (error.code === 'CHUNK_LOAD_ERROR') {
      // Clear service worker cache and reload
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then((registrations) => {
          registrations.forEach((registration) => registration.unregister());
          window.location.reload();
        });
      } else {
        window.location.reload();
      }
      return;
    }

    if (error.code === 'SECURITY_ERROR') {
      // Security error - sign out user and redirect
      this.authStore.signOut();
      this.router.navigate(['/auth/login'], {
        queryParams: { error: 'security_error' },
      });
      return;
    }

    // For other critical errors, show a more prominent error page
    this.router.navigate(['/error'], {
      queryParams: {
        type: error.type,
        code: error.code,
      },
    });
  }

  private getErrorSeverity(error: TechnicalError): ErrorSeverity {
    if (this.isCriticalError(error)) {
      return ErrorSeverity.CRITICAL;
    }

    if (error.type === 'http_error' || error.type === 'promise_rejection') {
      return ErrorSeverity.HIGH;
    }

    return ErrorSeverity.MEDIUM;
  }

  private getErrorCode(error: Error): string {
    // Common error patterns
    if (error.message.includes('Loading chunk')) {
      return 'CHUNK_LOAD_ERROR';
    }

    if (error.message.includes('Failed to fetch')) {
      return 'NETWORK_ERROR';
    }

    if (error.message.includes('Cannot resolve all parameters')) {
      return 'DEPENDENCY_INJECTION_ERROR';
    }

    return error.name || 'GENERIC_ERROR';
  }

  private isPromiseRejection(error: unknown): boolean {
    return (
      typeof error === 'object' && error !== null && 'promise' in error && 'rejection' in error
    );
  }

  private isHttpError(error: unknown): boolean {
    return typeof error === 'object' && error !== null && 'status' in error && 'url' in error;
  }

  private isAngularError(error: unknown): boolean {
    return typeof error === 'object' && error !== null && 'ngDebugContext' in error;
  }

  private extractPromiseRejectionMessage(error: unknown): string {
    const rejectionError = error as { rejection?: unknown };
    if (rejectionError.rejection instanceof Error) {
      return rejectionError.rejection.message;
    }
    return String(rejectionError.rejection || 'Promise rejected');
  }

  private extractAngularErrorMessage(error: unknown): string {
    const angularError = error as { message?: string };
    return angularError.message || 'Angular framework error';
  }

  private isProduction(): boolean {
    return !['localhost', '127.0.0.1'].includes(window.location.hostname);
  }

  private async reportToExternalService(error: TechnicalError): Promise<void> {
    try {
      // In a real app, send to error reporting service like Sentry, Bugsnag, etc.
      await this.api.post('/errors', JSON.stringify({
          error,
          timestamp: new Date().toISOString(),
          userAgent: navigator.userAgent,
          url: window.location.href,
          userId: this.authStore.user()?.id,
        })
      )
    } catch (reportingError) {
      // Don't throw errors from error reporting
      console.warn('Failed to report error to external service:', reportingError);
    }
  }

  private openErrorReportDialog(error: TechnicalError): void {
    // In a real app, open a dialog to report the issue
    const reportData = {
      error: error.detail,
      code: error.code,
      url: window.location.href,
      timestamp: new Date().toISOString(),
    };

    // For now, copy error details to clipboard
    navigator.clipboard
      ?.writeText(JSON.stringify(reportData, null, 2))
      .then(() => {
        this.toastService.success('Error details copied to clipboard');
      })
      .catch(() => {
        console.log('Error Report:', reportData);
        this.toastService.info('Error details logged to console');
      });
  }
}
