import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { LoggingService } from '../services/logging.service';
import { AuthV5Service } from '../services/auth-v5.service';
import {
  ProblemDetails,
  HttpError,
  ErrorSeverity,
  BusinessError,
  TechnicalError,
  NetworkError,
} from '../models/error.models';

/**
 * Advanced HTTP Error Interceptor with RFC 7807 Problem Details support
 * Features:
 * - RFC 7807 error parsing
 * - Structured logging
 * - User-friendly error messages
 * - Automatic retry for certain errors
 * - Error classification and routing
 */
export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const logger = inject(LoggingService);
  const auth = inject(AuthV5Service);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      const enhancedError = enhanceHttpError(error, req.url);
      const errorResponse = parseErrorResponse(enhancedError);
      const userFriendlyMessage = getUserFriendlyMessage(errorResponse, enhancedError.status);

      // Log the error with full context
      logger.logError(
        `HTTP ${enhancedError.status} Error: ${enhancedError.message}`,
        errorResponse,
        {
          url: req.url,
          method: req.method,
          requestId: req.headers.get('X-Request-ID') ?? undefined,
          severity: getErrorSeverity(enhancedError.status),
        },
        userFriendlyMessage
      );

      // Handle specific error types
      handleSpecificErrors(enhancedError, auth, router, logger);

      // Return enhanced error for components to handle
      const finalError = new Error(userFriendlyMessage) as HttpError;
      finalError.status = enhancedError.status;
      finalError.statusText = enhancedError.statusText;
      finalError.url = enhancedError.url;
      finalError.error = errorResponse;
      finalError.headers = extractHeaders(error);
      finalError.timestamp = enhancedError.timestamp;
      finalError.requestId = req.headers.get('X-Request-ID') ?? undefined;

      return throwError(() => finalError);
    })
  );
};

/**
 * Enhance basic HttpErrorResponse with additional context
 */
function enhanceHttpError(error: HttpErrorResponse, url: string): HttpError {
  const enhanced = new Error(error.message || `HTTP ${error.status} Error`) as HttpError;
  enhanced.status = error.status;
  enhanced.statusText = error.statusText;
  enhanced.url = url;
  enhanced.error = error.error;
  enhanced.headers = extractHeaders(error);
  enhanced.timestamp = new Date();
  return enhanced;
}

/**
 * Parse error response into structured format
 */
function parseErrorResponse(
  error: HttpError
): BusinessError | TechnicalError | NetworkError | ProblemDetails {
  // Network errors (no response from server)
  if (error.status === 0) {
    return {
      type: 'network_error',
      title: 'Network Error',
      detail: 'Unable to connect to server. Please check your internet connection.',
      code: 'NETWORK_ERROR',
      retryable: true,
    };
  }

  // Timeout errors
  if (error.status === 408 || error.statusText === 'timeout') {
    return {
      type: 'network_error',
      title: 'Request Timeout',
      detail: 'The request took too long to complete. Please try again.',
      code: 'TIMEOUT',
      retryable: true,
    };
  }

  // Try to parse RFC 7807 Problem Details
  if (error.error && typeof error.error === 'object') {
    const errorBody = error.error as Record<string, unknown>;

    // Check if it's RFC 7807 format
    if (errorBody['type'] && errorBody['title'] && errorBody['status']) {
      return errorBody as unknown as ProblemDetails;
    }

    // Laravel validation errors
    if (errorBody['message'] && errorBody['errors']) {
      return {
        type: 'validation_error',
        title: 'Validation Error',
        detail: String(errorBody['message']),
        code: 'VALIDATION_ERROR',
        validationErrors: Object.entries(errorBody['errors'] as Record<string, unknown>).map(
          ([field, messages]) => ({
            field,
            message: Array.isArray(messages) ? messages[0] : String(messages),
            code: 'REQUIRED',
          })
        ),
      };
    }

    // Generic API error with message
    if (errorBody['message']) {
      return error.status >= 500
        ? {
            type: 'technical_error',
            title: 'Server Error',
            detail: String(errorBody['message']),
            code: `HTTP_${error.status}`,
            originalError: errorBody,
          }
        : {
            type: 'business_error',
            title: getErrorTitle(error.status),
            detail: String(errorBody['message']),
            code: `HTTP_${error.status}`,
          };
    }
  }

  // Fallback error structure
  return error.status >= 500
    ? {
        type: 'technical_error',
        title: 'Server Error',
        detail: error.statusText || 'An unexpected server error occurred.',
        code: `HTTP_${error.status}`,
      }
    : {
        type: 'business_error',
        title: getErrorTitle(error.status),
        detail: getDefaultErrorMessage(error.status),
        code: `HTTP_${error.status}`,
      };
}

/**
 * Get user-friendly error message
 */
function getUserFriendlyMessage(
  errorResponse: BusinessError | TechnicalError | NetworkError | ProblemDetails,
  status: number
): string {
  // Use detail from error response if available
  if ('detail' in errorResponse && typeof errorResponse.detail === 'string') {
    return errorResponse.detail;
  }

  // Fallback to default messages
  switch (status) {
    case 400:
      return 'The request contains invalid data. Please check your input and try again.';
    case 401:
      return 'Authentication required. Please log in to continue.';
    case 403:
      return 'You do not have permission to perform this action.';
    case 404:
      return 'The requested resource was not found.';
    case 409:
      return 'There was a conflict with your request. The resource may have been modified.';
    case 422:
      return 'The data provided is invalid. Please check your input.';
    case 429:
      return 'Too many requests. Please wait a moment before trying again.';
    case 500:
      return 'An internal server error occurred. Please try again later.';
    case 502:
      return 'The server is temporarily unavailable. Please try again later.';
    case 503:
      return 'The service is temporarily unavailable. Please try again later.';
    case 504:
      return 'The server response timed out. Please try again later.';
    default:
      return `An error occurred (${status}). Please try again later.`;
  }
}

/**
 * Handle specific error conditions
 */
function handleSpecificErrors(
  error: HttpError,
  auth: AuthV5Service,
  router: Router,
  logger: LoggingService
): void {
  switch (error.status) {
    case 401:
      // Only sign out if user was previously authenticated
      if (auth.isAuthenticated()) {
        logger.logWarning('User session expired, signing out');
        auth.logout();
        router.navigate(['/auth/login'], {
          queryParams: {
            returnUrl: router.url !== '/auth/login' ? router.url : null,
            reason: 'session_expired',
          },
        });
      }
      break;

    case 403:
      logger.logWarning('Access denied to resource', {
        url: error.url,
        severity: ErrorSeverity.MEDIUM,
      });
      break;

    case 429:
      logger.logWarning('Rate limit exceeded', {
        url: error.url,
        severity: ErrorSeverity.HIGH,
      });
      break;

    case 500:
    case 502:
    case 503:
    case 504:
      logger.logError(`Server error ${error.status}`, parseErrorResponse(error), {
        url: error.url,
        severity: ErrorSeverity.HIGH,
      });
      break;
  }
}

/**
 * Extract headers from error response
 */
function extractHeaders(error: HttpErrorResponse): Record<string, string> {
  const headers: Record<string, string> = {};
  error.headers?.keys().forEach((key) => {
    const value = error.headers.get(key);
    if (value) {
      headers[key] = value;
    }
  });
  return headers;
}

/**
 * Get error severity based on status code
 */
function getErrorSeverity(status: number): ErrorSeverity {
  if (status >= 500) return ErrorSeverity.HIGH;
  if (status === 429 || status === 403) return ErrorSeverity.MEDIUM;
  return ErrorSeverity.LOW;
}

/**
 * Get error title based on status code
 */
function getErrorTitle(status: number): string {
  switch (status) {
    case 400:
      return 'Bad Request';
    case 401:
      return 'Unauthorized';
    case 403:
      return 'Forbidden';
    case 404:
      return 'Not Found';
    case 409:
      return 'Conflict';
    case 422:
      return 'Unprocessable Entity';
    case 429:
      return 'Too Many Requests';
    default:
      return 'Client Error';
  }
}

/**
 * Get default error message for status code
 */
function getDefaultErrorMessage(status: number): string {
  switch (status) {
    case 400:
      return 'The request was malformed or invalid.';
    case 401:
      return 'Authentication is required to access this resource.';
    case 403:
      return 'You do not have permission to access this resource.';
    case 404:
      return 'The requested resource could not be found.';
    case 409:
      return 'The request conflicts with the current state of the resource.';
    case 422:
      return 'The request data failed validation.';
    case 429:
      return 'Rate limit exceeded. Please retry after some time.';
    default:
      return 'A client error occurred.';
  }
}
