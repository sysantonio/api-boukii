import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom, timeout, retry, catchError } from 'rxjs';
import { RuntimeEnvironment } from '../models/environment.models';
import { LoggingService } from './logging.service';
import { ErrorSeverity } from '../models/error.models';

/**
 * Configuration Loader Service
 * Handles loading configuration from multiple sources with fallback strategy
 */
@Injectable({ providedIn: 'root' })
export class ConfigLoaderService {
  private readonly http = inject(HttpClient);
  private readonly logger = inject(LoggingService);

  private readonly CONFIG_ENDPOINTS = [
    '/assets/config/runtime-config.json',
    '/config/runtime-config.json',
    '/api/config/runtime',
  ];

  private readonly LOAD_TIMEOUT = 10000; // 10 seconds
  private readonly MAX_RETRIES = 2;

  /**
   * Load configuration with fallback strategy
   */
  async loadConfiguration(): Promise<RuntimeEnvironment> {
    this.logger.logInfo('Starting configuration load', {
      url: 'config-loader',
      method: 'loadConfiguration',
      severity: ErrorSeverity.LOW,
      endpoints: this.CONFIG_ENDPOINTS,
    });

    // Try each endpoint in order
    for (let i = 0; i < this.CONFIG_ENDPOINTS.length; i++) {
      const endpoint = this.CONFIG_ENDPOINTS[i];

      try {
        const config = await this.loadFromEndpoint(endpoint);

        this.logger.logInfo('Configuration loaded successfully', {
          url: 'config-loader',
          method: 'loadConfiguration',
          severity: ErrorSeverity.LOW,
          endpoint,
          version: config.version,
          source: 'remote',
        });

        return this.validateAndEnrichConfig(config);
      } catch (error) {
        this.logger.logWarning(`Failed to load from ${endpoint}`, {
          url: 'config-loader',
          method: 'loadConfiguration',
          severity: ErrorSeverity.MEDIUM,
          endpoint,
          error: error instanceof Error ? error.message : 'Unknown error',
          attemptsRemaining: this.CONFIG_ENDPOINTS.length - i - 1,
        });

        // If this was the last endpoint, throw the error
        if (i === this.CONFIG_ENDPOINTS.length - 1) {
          throw new Error(
            `Failed to load configuration from all endpoints: ${error instanceof Error ? error.message : 'Unknown error'}`
          );
        }
      }
    }

    throw new Error('No configuration endpoints available');
  }

  /**
   * Load configuration from a specific endpoint
   */
  private async loadFromEndpoint(endpoint: string): Promise<RuntimeEnvironment> {
    try {
      const config = await firstValueFrom(
        this.http.get<RuntimeEnvironment>(endpoint).pipe(
          timeout(this.LOAD_TIMEOUT),
          retry(this.MAX_RETRIES),
          catchError((error) => {
            throw new Error(`HTTP request failed: ${error.message || 'Unknown error'}`);
          })
        )
      );

      if (!config || typeof config !== 'object') {
        throw new Error('Invalid configuration format');
      }

      return config;
    } catch (error) {
      throw new Error(
        `Failed to load from ${endpoint}: ${error instanceof Error ? error.message : 'Unknown error'}`
      );
    }
  }

  /**
   * Validate and enrich configuration
   */
  private validateAndEnrichConfig(config: RuntimeEnvironment): RuntimeEnvironment {
    // Basic validation
    if (!config.name || !config.type || !config.version) {
      throw new Error('Configuration missing required fields: name, type, version');
    }

    if (!config.api?.baseUrl) {
      throw new Error('Configuration missing API configuration');
    }

    // Enrich with runtime information
    const enrichedConfig: RuntimeEnvironment = {
      ...config,
      buildDate: config.buildDate || new Date().toISOString(),
    };

    // Validate environment-specific settings
    this.validateEnvironmentConfig(enrichedConfig);

    this.logger.logInfo('Configuration validated and enriched', {
      url: 'config-loader',
      method: 'validateAndEnrichConfig',
      severity: ErrorSeverity.LOW,
      name: enrichedConfig.name,
      type: enrichedConfig.type,
      version: enrichedConfig.version,
    });

    return enrichedConfig;
  }

  /**
   * Validate environment-specific configuration
   */
  private validateEnvironmentConfig(config: RuntimeEnvironment): void {
    const validationRules: Array<{
      condition: boolean;
      message: string;
      severity: 'error' | 'warning';
    }> = [
      {
        condition: config.production && config.type !== 'production',
        message: 'Production flag set but environment type is not production',
        severity: 'warning',
      },
      {
        condition: !config.production && config.type === 'production',
        message: 'Environment type is production but production flag is false',
        severity: 'error',
      },
      {
        condition: config.api.timeout < 1000,
        message: 'API timeout is very low (< 1 second)',
        severity: 'warning',
      },
      {
        condition: config.api.timeout > 60000,
        message: 'API timeout is very high (> 1 minute)',
        severity: 'warning',
      },
      {
        condition: config.auth.tokenExpiry < 300000, // 5 minutes
        message: 'Token expiry is very short (< 5 minutes)',
        severity: 'warning',
      },
      {
        condition: config.production && config.features.debugMode,
        message: 'Debug mode enabled in production environment',
        severity: 'warning',
      },
      {
        condition: config.production && !config.monitoring.enabled,
        message: 'Monitoring disabled in production environment',
        severity: 'warning',
      },
      {
        condition: !config.security.enableCSP && config.production,
        message: 'CSP disabled in production environment',
        severity: 'warning',
      },
    ];

    for (const rule of validationRules) {
      if (rule.condition) {
        if (rule.severity === 'error') {
          throw new Error(`Configuration validation error: ${rule.message}`);
        } else {
          this.logger.logWarning(`Configuration validation warning: ${rule.message}`, {
            url: 'config-loader',
            method: 'validateEnvironmentConfig',
            severity: ErrorSeverity.MEDIUM,
            rule: rule.message,
          });
        }
      }
    }
  }

  /**
   * Create default configuration for emergency fallback
   */
  createFallbackConfiguration(): RuntimeEnvironment {
    const hostname = typeof window !== 'undefined' ? window.location.hostname : 'localhost';
    const isProduction = hostname.includes('boukii.app');
    const isDevelopment = hostname === 'localhost' || hostname.includes('127.0.0.1');

    const fallbackConfig: RuntimeEnvironment = {
      name: 'Boukii Admin V5 - Emergency Fallback',
      type: isProduction ? 'production' : isDevelopment ? 'development' : 'staging',
      version: '5.0.0-fallback',
      buildDate: new Date().toISOString(),
      description: 'Emergency fallback configuration',
      production: isProduction,

      api: {
        baseUrl: isProduction ? 'https://api.boukii.app' : 'http://localhost:8000',
        timeout: 30000,
        retryAttempts: 3,
        retryDelay: 1000,
        version: 'v5',
        endpoints: {
          auth: '/api/v5/auth',
          users: '/api/v5/users',
          dashboard: '/api/v5/dashboard',
          schools: '/api/v5/schools',
          seasons: '/api/v5/seasons',
          clients: '/api/v5/clients',
          courses: '/api/v5/courses',
          bookings: '/api/v5/bookings',
          monitors: '/api/v5/monitors',
        },
      },

      auth: {
        tokenKey: 'boukii_auth_token',
        refreshTokenKey: 'boukii_refresh_token',
        tokenExpiry: 3600000,
        refreshThreshold: 300000,
        loginRedirect: '/auth/login',
        logoutRedirect: '/auth/login',
        sessionTimeout: 28800000,
        rememberMeEnabled: true,
      },

      features: {
        darkTheme: true,
        multiLanguage: true,
        notifications: true,
        analytics: false,
        dashboardWidgets: true,
        realtimeUpdates: false,
        exportData: true,
        importData: false,
        userManagement: true,
        systemSettings: false,
        auditLogs: false,
        backupRestore: false,
        experimentalUI: false,
        betaFeatures: false,
        debugMode: isDevelopment,
        performanceMonitoring: false,
        paymentGateway: false,
        emailService: false,
        smsService: false,
        socialLogin: false,
      },

      monitoring: {
        enabled: false,
        errorReporting: false,
        performanceTracking: false,
        userAnalytics: false,
        logLevel: 'warn',
        maxLogEntries: 100,
        retentionDays: 7,
      },

      cache: {
        enabled: true,
        strategies: {
          api: 'memory',
          translations: 'localStorage',
          user: 'sessionStorage',
          dashboard: 'sessionStorage',
        },
        ttl: {
          default: 300000,
          user: 3600000,
          translations: 86400000,
          dashboard: 600000,
          api: 300000,
        },
        maxSize: {
          memory: 5242880, // 5MB
          localStorage: 2621440, // 2.5MB
          sessionStorage: 1310720, // 1.25MB
        },
      },

      security: {
        enableCSP: false,
        enableHSTS: false,
        enableXSSProtection: true,
        corsOrigins: ['*'],
        trustedDomains: [],
        encryptLocalStorage: false,
        sessionSecurity: 'standard',
        passwordPolicy: {
          minLength: 6,
          requireUppercase: false,
          requireLowercase: false,
          requireNumbers: false,
          requireSymbols: false,
        },
      },

      app: {
        title: 'Boukii Admin V5',
        logo: '/assets/images/logo.svg',
        supportEmail: 'support@boukii.app',
        supportPhone: '',
        companyName: 'Boukii Technologies',
        privacyPolicyUrl: '#',
        termsOfServiceUrl: '#',
        maxFileUploadSize: 5242880, // 5MB
        allowedFileTypes: ['jpg', 'jpeg', 'png', 'pdf'],
        defaultLanguage: 'en',
        availableLanguages: ['en', 'es'],
        timezone: 'UTC',
        dateFormat: 'dd/MM/yyyy',
        timeFormat: 'HH:mm',
        currency: 'EUR',
      },

      integrations: {},
    };

    this.logger.logWarning('Using emergency fallback configuration', {
      url: 'config-loader',
      method: 'createFallbackConfiguration',
      severity: ErrorSeverity.HIGH,
      hostname,
      environment: fallbackConfig.type,
    });

    return fallbackConfig;
  }

  /**
   * Validate configuration format
   */
  validateConfigurationFormat(config: unknown): config is RuntimeEnvironment {
    if (!config || typeof config !== 'object') {
      return false;
    }

    const requiredFields = ['name', 'type', 'version', 'api', 'auth', 'features'];
    for (const field of requiredFields) {
      if (!(field in config)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Check if configuration is compatible with current version
   */
  isConfigurationCompatible(config: RuntimeEnvironment): boolean {
    // Version compatibility check
    const currentVersion = '5.0.0';
    const configVersion = config.version;

    // For now, accept any 5.x.x version
    const versionRegex = /^5\.\d+\.\d+/;

    if (!versionRegex.test(configVersion)) {
      this.logger.logWarning('Configuration version may be incompatible', {
        url: 'config-loader',
        method: 'isConfigurationCompatible',
        severity: ErrorSeverity.MEDIUM,
        currentVersion,
        configVersion,
      });
      return false;
    }

    return true;
  }

  /**
   * Merge configuration with defaults
   */
  mergeWithDefaults(
    config: Partial<RuntimeEnvironment>,
    defaults: RuntimeEnvironment
  ): RuntimeEnvironment {
    const merged = this.deepMerge(defaults, config);

    this.logger.logInfo('Configuration merged with defaults', {
      url: 'config-loader',
      method: 'mergeWithDefaults',
      severity: ErrorSeverity.LOW,
    });

    return merged as RuntimeEnvironment;
  }

  /**
   * Deep merge two objects
   */
  private deepMerge(target: unknown, source: unknown): unknown {
    if (source === null || typeof source !== 'object') {
      return source;
    }

    if (target === null || typeof target !== 'object') {
      return source;
    }

    const result = { ...target } as Record<string, unknown>;

    for (const key in source as Record<string, unknown>) {
      const sourceValue = (source as Record<string, unknown>)[key];
      const targetValue = result[key];

      if (Array.isArray(sourceValue)) {
        result[key] = sourceValue;
      } else if (sourceValue && typeof sourceValue === 'object') {
        result[key] = this.deepMerge(targetValue, sourceValue);
      } else {
        result[key] = sourceValue;
      }
    }

    return result;
  }
}
