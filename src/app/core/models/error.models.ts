/**
 * RFC 7807 Problem Details for HTTP APIs
 * https://tools.ietf.org/html/rfc7807
 */
export interface ProblemDetails {
  type: string;
  title: string;
  status: number;
  detail: string;
  instance?: string;
  code?: string;
  errors?: Record<string, string[]>;
  requestId?: string;
  timestamp?: string;
}

/**
 * Enhanced HTTP Error with additional context
 */
export interface HttpError extends Error {
  status: number;
  statusText: string;
  url?: string;
  error?: ProblemDetails | unknown;
  headers?: Record<string, string>;
  timestamp: Date;
  requestId?: string;
}

/**
 * Error severity levels for logging and display
 */
export enum ErrorSeverity {
  LOW = 'low',
  MEDIUM = 'medium',
  HIGH = 'high',
  CRITICAL = 'critical',
}

/**
 * Error context for enhanced logging and debugging
 */
export interface ErrorContext {
  url: string;
  method: string;
  userId?: string | number;
  sessionId?: string;
  userAgent?: string;
  timestamp: Date;
  requestId: string;
  severity: ErrorSeverity;
  // Extended context properties
  language?: string;
  key?: string;
  environment?: string;
  error?: string;
  source?: string;
  version?: string;
  type?: string;
  feature?: string;
  value?: unknown;
  endpoints?: string[];
  endpoint?: string;
  name?: string;
  rule?: string;
  hostname?: string;
  currentVersion?: string;
  errorReporting?: boolean;
  googleAnalytics?: boolean;
  dsn?: string;
  trackingId?: string;
  [key: string]: unknown; // Allow additional properties
}

/**
 * Validation error structure for form errors
 */
export interface ValidationError {
  field: string;
  message: string;
  code?: string;
  value?: unknown;
}

/**
 * Business logic error (4xx errors)
 */
export interface BusinessError {
  type: string;
  title: string;
  detail: string;
  code: string;
  validationErrors?: ValidationError[];
}

/**
 * Technical error (5xx errors)
 */
export interface TechnicalError {
  type: string;
  title: string;
  detail: string;
  code: string;
  stackTrace?: string;
  originalError?: unknown;
}

/**
 * Network/connectivity error
 */
export interface NetworkError {
  type: 'network_error';
  title: string;
  detail: string;
  code: 'NETWORK_ERROR' | 'TIMEOUT' | 'CONNECTION_REFUSED';
  retryable: boolean;
}

/**
 * Unified error response type
 */
export type ErrorResponse = BusinessError | TechnicalError | NetworkError | ProblemDetails;

/**
 * Error logging payload
 */
export interface ErrorLogEntry {
  id: string;
  timestamp: Date;
  level: 'error' | 'warn' | 'info';
  message: string;
  context: ErrorContext;
  error: ErrorResponse;
  handled: boolean;
  userFriendlyMessage?: string;
}
