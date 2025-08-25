import { Injectable, inject } from '@angular/core';
import { Router } from '@angular/router';
import { TranslationService } from './translation.service';
import { ToastService } from './toast.service';
import { LoggingService } from './logging.service';

export interface AuthError {
  type: 'network' | 'validation' | 'unauthorized' | 'forbidden' | 'server' | 'unknown';
  code: string;
  message: string;
  originalError?: any;
  details?: Record<string, any>;
}

@Injectable({
  providedIn: 'root'
})
export class AuthErrorHandlerService {
  private readonly router = inject(Router);
  private readonly translation = inject(TranslationService);
  private readonly toast = inject(ToastService);
  private readonly logger = inject(LoggingService);

  /**
   * Handle authentication-specific errors with proper user feedback
   */
  handleAuthError(error: any, context: string = 'auth'): AuthError {
    const authError = this.parseError(error);
    
    this.logger.logError(`Auth Error in ${context}`, authError.originalError || authError, {
      context,
      errorType: authError.type,
      code: authError.code
    });

    // Show user-friendly toast based on error type
    this.showErrorToast(authError, context);

    // Handle specific error types with navigation
    this.handleErrorNavigation(authError, context);

    return authError;
  }

  /**
   * Parse various error formats into standardized AuthError
   */
  private parseError(error: any): AuthError {
    // Network errors (no connection)
    if (!error.status || error.status === 0) {
      return {
        type: 'network',
        code: 'NETWORK_ERROR',
        message: this.translation.get('auth.errors.networkError'),
        originalError: error
      };
    }

    // HTTP status-based errors
    switch (error.status) {
      case 400:
        return this.parseBadRequestError(error);
      
      case 401:
        return {
          type: 'unauthorized',
          code: 'UNAUTHORIZED',
          message: this.getUnauthorizedMessage(error),
          originalError: error
        };
      
      case 403:
        return {
          type: 'forbidden',
          code: 'FORBIDDEN',
          message: this.translation.get('auth.errors.forbidden'),
          originalError: error
        };
      
      case 422:
        return this.parseValidationError(error);
      
      case 429:
        return {
          type: 'server',
          code: 'RATE_LIMITED',
          message: this.translation.get('auth.errors.rateLimited'),
          originalError: error
        };
      
      case 500:
      case 502:
      case 503:
      case 504:
        return {
          type: 'server',
          code: `SERVER_ERROR_${error.status}`,
          message: this.translation.get('auth.errors.serverError'),
          originalError: error
        };
      
      default:
        return {
          type: 'unknown',
          code: `UNKNOWN_${error.status || 'ERROR'}`,
          message: this.translation.get('auth.errors.unknownError'),
          originalError: error
        };
    }
  }

  /**
   * Parse 400 Bad Request errors
   */
  private parseBadRequestError(error: any): AuthError {
    const errorBody = error.error || {};
    
    if (errorBody.message) {
      return {
        type: 'validation',
        code: 'BAD_REQUEST',
        message: errorBody.message,
        originalError: error
      };
    }
    
    return {
      type: 'validation',
      code: 'BAD_REQUEST',
      message: this.translation.get('auth.errors.invalidRequest'),
      originalError: error
    };
  }

  /**
   * Parse 401 Unauthorized errors with context
   */
  private getUnauthorizedMessage(error: any): string {
    const errorBody = error.error || {};
    
    // Specific unauthorized scenarios
    if (errorBody.code === 'INVALID_CREDENTIALS') {
      return this.translation.get('auth.errors.invalidCredentials');
    }
    
    if (errorBody.code === 'SESSION_EXPIRED') {
      return this.translation.get('auth.errors.sessionExpired');
    }
    
    if (errorBody.code === 'INVALID_TOKEN') {
      return this.translation.get('auth.errors.invalidToken');
    }
    
    // Generic unauthorized
    return this.translation.get('auth.errors.unauthorized');
  }

  /**
   * Parse 422 Validation errors
   */
  private parseValidationError(error: any): AuthError {
    const errorBody = error.error || {};
    
    if (errorBody.errors) {
      // Laravel-style validation errors
      const validationDetails: Record<string, string[]> = errorBody.errors;
      const firstField = Object.keys(validationDetails)[0];
      const firstError = validationDetails[firstField]?.[0];
      
      return {
        type: 'validation',
        code: 'VALIDATION_ERROR',
        message: firstError || errorBody.message || this.translation.get('auth.errors.validationFailed'),
        originalError: error,
        details: validationDetails
      };
    }
    
    return {
      type: 'validation',
      code: 'VALIDATION_ERROR',
      message: errorBody.message || this.translation.get('auth.errors.validationFailed'),
      originalError: error
    };
  }

  /**
   * Show appropriate toast message based on error type
   */
  private showErrorToast(authError: AuthError, context: string): void {
    const duration = authError.type === 'network' ? 5000 : 4000;
    
    switch (authError.type) {
      case 'network':
        this.toast.error(authError.message, { 
          duration,
          actions: [{
            label: this.translation.get('common.retry'),
            action: () => window.location.reload()
          }]
        });
        break;
      
      case 'validation':
        this.toast.warning(authError.message, { duration });
        break;
      
      case 'unauthorized':
      case 'forbidden':
        this.toast.error(authError.message, { duration });
        break;
      
      case 'server':
        this.toast.error(authError.message, { 
          duration: authError.code.includes('500') ? 0 : 6000 // 0 means no auto-dismiss
        });
        break;
      
      default:
        this.toast.error(authError.message, { duration });
        break;
    }
  }

  /**
   * Handle navigation based on error type and context
   */
  private handleErrorNavigation(authError: AuthError, context: string): void {
    // Only redirect for certain error types
    if (authError.type === 'unauthorized' && context !== 'login') {
      // Don't redirect if we're already on login page
      if (this.router.url !== '/auth/login') {
        setTimeout(() => {
          this.router.navigate(['/auth/login'], {
            queryParams: { 
              returnUrl: this.router.url,
              reason: 'session_expired' 
            }
          });
        }, 2000);
      }
    }
    
    if (authError.type === 'forbidden' && context === 'school-selection') {
      // User doesn't have access to any schools
      setTimeout(() => {
        this.router.navigate(['/no-access'], {
          queryParams: { reason: 'no_school_access' }
        });
      }, 2000);
    }
  }

  /**
   * Handle specific authentication flow errors
   */
  handleLoginError(error: any): AuthError {
    return this.handleAuthError(error, 'login');
  }

  handleSchoolSelectionError(error: any): AuthError {
    return this.handleAuthError(error, 'school-selection');
  }

  handleSeasonSelectionError(error: any): AuthError {
    return this.handleAuthError(error, 'season-selection');
  }

  handleRegisterError(error: any): AuthError {
    return this.handleAuthError(error, 'register');
  }

  handleForgotPasswordError(error: any): AuthError {
    return this.handleAuthError(error, 'forgot-password');
  }

  /**
   * Create user-friendly error message for display
   */
  getDisplayMessage(authError: AuthError): string {
    return authError.message;
  }

  /**
   * Check if error is retryable
   */
  isRetryable(authError: AuthError): boolean {
    return ['network', 'server'].includes(authError.type) && 
           !authError.code.includes('403') && 
           !authError.code.includes('401');
  }

  /**
   * Get suggested action for error
   */
  getSuggestedAction(authError: AuthError): string | null {
    switch (authError.type) {
      case 'network':
        return this.translation.get('auth.errors.actions.checkConnection');
      
      case 'validation':
        return this.translation.get('auth.errors.actions.checkInput');
      
      case 'unauthorized':
        return this.translation.get('auth.errors.actions.tryLogin');
      
      case 'server':
        return this.translation.get('auth.errors.actions.tryLater');
      
      default:
        return null;
    }
  }
}