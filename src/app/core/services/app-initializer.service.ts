import { Injectable, inject } from '@angular/core';
import { EnvironmentService } from './environment.service';
import { ConfigLoaderService } from './config-loader.service';
import { LoggingService } from './logging.service';
import { ErrorSeverity } from '../models/error.models';

/**
 * Application Initializer Service
 * Handles pre-bootstrap initialization of configuration and services
 */
@Injectable({ providedIn: 'root' })
export class AppInitializerService {
  private readonly environmentService = inject(EnvironmentService);
  private readonly configLoader = inject(ConfigLoaderService);
  private readonly logger = inject(LoggingService);

  /**
   * Initialize application configuration
   * This runs before Angular bootstraps the application
   */
  async initializeApp(): Promise<void> {
    try {
      this.logger.logInfo('Application initialization started', {
        url: 'app-initializer',
        method: 'initializeApp',
        severity: ErrorSeverity.LOW,
        timestamp: new Date(),
      });

      // Wait for environment service to be ready
      // The environment service will automatically try to load configuration
      // We just need to wait for it to complete
      await this.waitForEnvironmentReady();

      // Additional initialization steps can be added here
      await this.initializeAdditionalServices();

      this.logger.logInfo('Application initialization completed', {
        url: 'app-initializer',
        method: 'initializeApp',
        severity: ErrorSeverity.LOW,
        environment: this.environmentService.environmentType(),
        version: this.environmentService.environment()?.version,
      });
    } catch (error) {
      this.logger.logError(
        'Application initialization failed',
        {
          type: 'initialization_error',
          title: 'App Initialization Error',
          detail: error instanceof Error ? error.message : 'Unknown initialization error',
          code: 'INIT_ERROR',
        },
        {
          url: 'app-initializer',
          method: 'initializeApp',
          severity: ErrorSeverity.CRITICAL,
        }
      );

      // Don't prevent app from starting, but log the error
      console.error('App initialization failed:', error);
    }
  }

  /**
   * Wait for environment service to be ready
   */
  private async waitForEnvironmentReady(): Promise<void> {
    return new Promise((resolve, reject) => {
      const maxWaitTime = 30000; // 30 seconds
      const checkInterval = 100; // 100ms
      let elapsed = 0;

      const checkReady = () => {
        const loadingState = this.environmentService.loadingState();

        if (loadingState.loaded) {
          resolve();
          return;
        }

        if (loadingState.error) {
          this.logger.logWarning('Environment loading failed, continuing with fallback', {
            url: 'app-initializer',
            method: 'waitForEnvironmentReady',
            severity: ErrorSeverity.MEDIUM,
            error: loadingState.error,
          });
          resolve(); // Continue even if loading failed (fallback config)
          return;
        }

        elapsed += checkInterval;
        if (elapsed >= maxWaitTime) {
          reject(new Error('Timeout waiting for environment configuration'));
          return;
        }

        setTimeout(checkReady, checkInterval);
      };

      checkReady();
    });
  }

  /**
   * Initialize additional services that depend on environment configuration
   */
  private async initializeAdditionalServices(): Promise<void> {
    const environment = this.environmentService.environment();
    if (!environment) {
      throw new Error('Environment not available for service initialization');
    }

    // Initialize services based on environment configuration
    if (environment.monitoring.enabled) {
      await this.initializeMonitoring();
    }

    if (environment.features.analytics) {
      await this.initializeAnalytics();
    }

    // Set up security configurations
    if (environment.security.enableCSP) {
      this.setupContentSecurityPolicy();
    }
  }

  /**
   * Initialize monitoring services
   */
  private async initializeMonitoring(): Promise<void> {
    try {
      const environment = this.environmentService.environment()!;

      // Configure logging level
      this.logger.setLogLevel?.(environment.monitoring.logLevel);

      // Initialize error reporting if enabled
      if (environment.monitoring.errorReporting && environment.integrations.sentry?.dsn) {
        await this.initializeSentry(environment.integrations.sentry.dsn);
      }

      this.logger.logInfo('Monitoring services initialized', {
        url: 'app-initializer',
        method: 'initializeMonitoring',
        severity: ErrorSeverity.LOW,
        errorReporting: environment.monitoring.errorReporting,
        logLevel: environment.monitoring.logLevel,
      });
    } catch (error) {
      this.logger.logWarning('Failed to initialize monitoring', {
        url: 'app-initializer',
        method: 'initializeMonitoring',
        severity: ErrorSeverity.MEDIUM,
        error: error instanceof Error ? error.message : 'Unknown error',
      });
    }
  }

  /**
   * Initialize analytics services
   */
  private async initializeAnalytics(): Promise<void> {
    try {
      const environment = this.environmentService.environment()!;

      if (environment.integrations.google?.analytics) {
        await this.initializeGoogleAnalytics(environment.integrations.google.analytics);
      }

      this.logger.logInfo('Analytics services initialized', {
        url: 'app-initializer',
        method: 'initializeAnalytics',
        severity: ErrorSeverity.LOW,
        googleAnalytics: !!environment.integrations.google?.analytics,
      });
    } catch (error) {
      this.logger.logWarning('Failed to initialize analytics', {
        url: 'app-initializer',
        method: 'initializeAnalytics',
        severity: ErrorSeverity.MEDIUM,
        error: error instanceof Error ? error.message : 'Unknown error',
      });
    }
  }

  /**
   * Initialize Sentry error reporting
   */
  private async initializeSentry(dsn: string): Promise<void> {
    try {
      // In a real implementation, you would initialize Sentry here
      // For now, we'll just log that it would be initialized

      this.logger.logInfo('Sentry error reporting would be initialized', {
        url: 'app-initializer',
        method: 'initializeSentry',
        severity: ErrorSeverity.LOW,
        dsn: `${dsn.substring(0, 20)}...`, // Log partial DSN for security
      });

      // Real Sentry initialization would look like:
      // import * as Sentry from '@sentry/angular';
      // Sentry.init({ dsn });
    } catch (error) {
      this.logger.logWarning('Failed to initialize Sentry', {
        url: 'app-initializer',
        method: 'initializeSentry',
        severity: ErrorSeverity.MEDIUM,
        error: error instanceof Error ? error.message : 'Unknown error',
      });
    }
  }

  /**
   * Initialize Google Analytics
   */
  private async initializeGoogleAnalytics(trackingId: string): Promise<void> {
    try {
      // In a real implementation, you would initialize Google Analytics here
      // For now, we'll just log that it would be initialized

      this.logger.logInfo('Google Analytics would be initialized', {
        url: 'app-initializer',
        method: 'initializeGoogleAnalytics',
        severity: ErrorSeverity.LOW,
        trackingId,
      });

      // Real GA initialization would look like:
      // gtag('config', trackingId);
    } catch (error) {
      this.logger.logWarning('Failed to initialize Google Analytics', {
        url: 'app-initializer',
        method: 'initializeGoogleAnalytics',
        severity: ErrorSeverity.MEDIUM,
        error: error instanceof Error ? error.message : 'Unknown error',
      });
    }
  }

  /**
   * Set up Content Security Policy
   */
  private setupContentSecurityPolicy(): void {
    try {
      // In a real implementation, you would configure CSP headers
      // This is typically done at the server level, but can be set via meta tags

      this.logger.logInfo('Content Security Policy would be configured', {
        url: 'app-initializer',
        method: 'setupContentSecurityPolicy',
        severity: ErrorSeverity.LOW,
      });
    } catch (error) {
      this.logger.logWarning('Failed to setup CSP', {
        url: 'app-initializer',
        method: 'setupContentSecurityPolicy',
        severity: ErrorSeverity.MEDIUM,
        error: error instanceof Error ? error.message : 'Unknown error',
      });
    }
  }

  /**
   * Get initialization factory function for APP_INITIALIZER
   */
  static initializerFactory(appInitializer: AppInitializerService): () => Promise<void> {
    return () => appInitializer.initializeApp();
  }
}
