/**
 * Runtime Environment Configuration Models
 * Supports dynamic configuration loading and feature flags
 */

/**
 * Environment types
 */
export type EnvironmentType = 'development' | 'staging' | 'production' | 'test';

/**
 * Log levels for different environments
 */
export type LogLevel = 'debug' | 'info' | 'warn' | 'error' | 'none';

/**
 * API endpoint configuration
 */
export interface ApiConfig {
  baseUrl: string;
  timeout: number;
  retryAttempts: number;
  retryDelay: number;
  version: string;
  endpoints: {
    auth: string;
    users: string;
    dashboard: string;
    schools: string;
    seasons: string;
    clients: string;
    courses: string;
    bookings: string;
    monitors: string;
  };
}

/**
 * Authentication configuration
 */
export interface AuthConfig {
  tokenKey: string;
  refreshTokenKey: string;
  tokenExpiry: number;
  refreshThreshold: number;
  loginRedirect: string;
  logoutRedirect: string;
  sessionTimeout: number;
  rememberMeEnabled: boolean;
}

/**
 * Application features that can be toggled
 */
export interface FeatureFlags {
  // Core features
  darkTheme: boolean;
  multiLanguage: boolean;
  notifications: boolean;
  analytics: boolean;

  // Dashboard features
  dashboardWidgets: boolean;
  realtimeUpdates: boolean;
  exportData: boolean;
  importData: boolean;

  // Admin features
  userManagement: boolean;
  systemSettings: boolean;
  auditLogs: boolean;
  backupRestore: boolean;

  // Experimental features
  experimentalUI: boolean;
  betaFeatures: boolean;
  debugMode: boolean;
  performanceMonitoring: boolean;

  // External integrations
  paymentGateway: boolean;
  emailService: boolean;
  smsService: boolean;
  socialLogin: boolean;
}

/**
 * Monitoring and analytics configuration
 */
export interface MonitoringConfig {
  enabled: boolean;
  errorReporting: boolean;
  performanceTracking: boolean;
  userAnalytics: boolean;
  logLevel: LogLevel;
  maxLogEntries: number;
  retentionDays: number;
  endpoint?: string;
  apiKey?: string;
}

/**
 * Cache configuration for different types of data
 */
export interface CacheConfig {
  enabled: boolean;
  strategies: {
    api: 'memory' | 'localStorage' | 'sessionStorage' | 'indexedDB';
    translations: 'memory' | 'localStorage' | 'sessionStorage';
    user: 'memory' | 'localStorage' | 'sessionStorage';
    dashboard: 'memory' | 'sessionStorage';
  };
  ttl: {
    default: number;
    user: number;
    translations: number;
    dashboard: number;
    api: number;
  };
  maxSize: {
    memory: number;
    localStorage: number;
    sessionStorage: number;
  };
}

/**
 * Security configuration
 */
export interface SecurityConfig {
  enableCSP: boolean;
  enableHSTS: boolean;
  enableXSSProtection: boolean;
  corsOrigins: string[];
  trustedDomains: string[];
  encryptLocalStorage: boolean;
  sessionSecurity: 'strict' | 'standard' | 'relaxed';
  passwordPolicy: {
    minLength: number;
    requireUppercase: boolean;
    requireLowercase: boolean;
    requireNumbers: boolean;
    requireSymbols: boolean;
  };
}

/**
 * Complete runtime environment configuration
 */
export interface RuntimeEnvironment {
  // Basic environment info
  name: string;
  type: EnvironmentType;
  version: string;
  buildDate: string;
  description: string;

  // Core configurations
  production: boolean;
  api: ApiConfig;
  auth: AuthConfig;
  features: FeatureFlags;

  // Advanced configurations
  monitoring: MonitoringConfig;
  cache: CacheConfig;
  security: SecurityConfig;

  // Custom app-specific settings
  app: {
    title: string;
    logo: string;
    supportEmail: string;
    supportPhone: string;
    companyName: string;
    privacyPolicyUrl: string;
    termsOfServiceUrl: string;
    maxFileUploadSize: number;
    allowedFileTypes: string[];
    defaultLanguage: string;
    availableLanguages: string[];
    timezone: string;
    dateFormat: string;
    timeFormat: string;
    currency: string;
  };

  // Third-party integrations
  integrations: {
    firebase?: {
      apiKey: string;
      authDomain: string;
      projectId: string;
      storageBucket: string;
      messagingSenderId: string;
      appId: string;
    };
    google?: {
      analytics?: string;
      maps?: string;
    };
    stripe?: {
      publishableKey: string;
    };
    sentry?: {
      dsn: string;
    };
  };
}

/**
 * Environment loading state
 */
export interface EnvironmentLoadingState {
  loading: boolean;
  loaded: boolean;
  error: string | null;
  lastUpdated: Date | null;
  source: 'static' | 'remote' | 'cache' | 'fallback';
}

/**
 * Configuration override for specific features
 */
export interface ConfigurationOverride {
  key: string;
  value: unknown;
  priority: number;
  source: string;
  expiresAt?: Date;
  conditions?: {
    userRoles?: string[];
    features?: string[];
    environment?: EnvironmentType[];
  };
}

/**
 * Runtime configuration change event
 */
export interface ConfigurationChangeEvent {
  type: 'feature_flag' | 'api_config' | 'auth_config' | 'full_reload';
  key: string;
  oldValue: unknown;
  newValue: unknown;
  source: string;
  timestamp: Date;
}
