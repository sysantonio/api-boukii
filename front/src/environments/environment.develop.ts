// Development server environment
export const environment = {
  name: 'Develop',
  envName: 'staging',
  production: false,
  development: false,
  staging: true,
  testing: false,

  // API Configuration
  apiUrl: 'https://api-dev.boukii.app',
  apiVersion: 'v5',
  apiTimeout: 30000,

  // App Configuration
  appName: 'Boukii Admin V5',
  appVersion: '5.0.0',
  appTitle: '🧪 Boukii V5 - Develop',
  appDescription: 'Boukii Admin Panel V5 - Development Server Environment',

  // Feature Flags
  enableDebugMode: true,
  enableAdvancedLogging: true,
  enablePerformanceMonitoring: true,
  enableHotReload: false,
  enableSourceMaps: true,
  enableMockData: false,
  enableStorybook: true,
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
    level: 'info',
    enableConsoleLogging: true,
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
    tokenKey: 'boukii_v5_dev_token',
    userKey: 'boukii_v5_dev_user',
    schoolKey: 'boukii_v5_dev_school',
    seasonKey: 'boukii_v5_dev_season',
    prefix: 'boukii_v5_dev_'
  },

  // External Services
  services: {
    analytics: {
      enabled: true,
      trackingId: 'GA-DEV-XXXXXXX'
    },
    errorReporting: {
      enabled: true,
      dsn: 'https://dev-sentry-dsn@sentry.io/project'
    }
  }
};
