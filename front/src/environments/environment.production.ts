// Production environment
export const environment = {
  name: 'Production',
  production: true,
  development: false,
  staging: false,
  testing: false,

  // API Configuration
  apiUrl: 'https://api.boukii.app',
  apiVersion: 'v5',
  apiTimeout: 30000,

  // App Configuration
  appName: 'Boukii Admin V5',
  appVersion: '5.0.0',
  appTitle: '🏢 Boukii V5 - Production',
  appDescription: 'Boukii Admin Panel V5 - Production Environment',

  // Feature Flags
  enableDebugMode: false,
  enableAdvancedLogging: false,
  enablePerformanceMonitoring: true,
  enableHotReload: false,
  enableSourceMaps: false,
  enableMockData: false,
  enableStorybook: false,
  enableE2ETests: false,

  // UI Configuration
  theme: {
    defaultTheme: 'light',
    allowThemeSwitch: true,
    enableAnimations: true,
    enableTransitions: true
  },

  // Logging Configuration
  logging: {
    level: 'error',
    enableConsoleLogging: false,
    enableRemoteLogging: true,
    enableErrorReporting: true
  },

  // Security Configuration
  security: {
    enableCSRF: true,
    enableCSP: true,
    enableSecureHeaders: true,
    sessionTimeout: 1800000, // 30 minutes
    tokenRefreshThreshold: 300000 // 5 minutes
  },

  // Storage Configuration
  storage: {
    tokenKey: 'boukii_v5_token',
    userKey: 'boukii_v5_user',
    schoolKey: 'boukii_v5_school',
    seasonKey: 'boukii_v5_season',
    prefix: 'boukii_v5_'
  },

  // External Services
  services: {
    analytics: {
      enabled: true,
      trackingId: 'GA-PROD-XXXXXXX'
    },
    errorReporting: {
      enabled: true,
      dsn: 'https://prod-sentry-dsn@sentry.io/project'
    }
  }
};
