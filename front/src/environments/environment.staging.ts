// Staging environment
export const environment = {
  name: 'Staging',
  production: false,
  development: false,
  staging: true,
  testing: false,

  // API Configuration
  apiUrl: 'https://api-staging.boukii.app',
  apiVersion: 'v5',
  apiTimeout: 30000,

  // App Configuration
  appName: 'Boukii Admin V5',
  appVersion: '5.0.0',
  appTitle: '🚀 Boukii V5 - Staging',
  appDescription: 'Boukii Admin Panel V5 - Staging Environment',

  // Feature Flags
  enableDebugMode: false,
  enableAdvancedLogging: true,
  enablePerformanceMonitoring: true,
  enableHotReload: false,
  enableSourceMaps: false,
  enableMockData: false,
  enableStorybook: false,
  enableE2ETests: true,

  // UI Configuration
  theme: {
    defaultTheme: 'light',
    allowThemeSwitch: true,
    enableAnimations: true,
    enableTransitions: true
  },

  // Logging Configuration
  logging: {
    level: 'warn',
    enableConsoleLogging: false,
    enableRemoteLogging: true,
    enableErrorReporting: true
  },

  // Security Configuration
  security: {
    enableCSRF: true,
    enableCSP: true,
    enableSecureHeaders: true,
    sessionTimeout: 3600000, // 1 hour
    tokenRefreshThreshold: 300000 // 5 minutes
  },

  // Storage Configuration
  storage: {
    tokenKey: 'boukii_v5_staging_token',
    userKey: 'boukii_v5_staging_user',
    schoolKey: 'boukii_v5_staging_school',
    seasonKey: 'boukii_v5_staging_season',
    prefix: 'boukii_v5_staging_'
  },

  // External Services
  services: {
    analytics: {
      enabled: true,
      trackingId: 'GA-STAGING-XXXXXXX'
    },
    errorReporting: {
      enabled: true,
      dsn: 'https://staging-sentry-dsn@sentry.io/project'
    }
  }
};
