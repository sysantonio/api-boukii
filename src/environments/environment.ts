// Default development environment
export const environment = {
  name: 'Development',
  production: false,
  development: true,
  staging: false,
  testing: false,

  // API Configuration
  apiUrl: 'http://api-boukii.test',
  apiVersion: 'v5',
  apiTimeout: 30000,

  // App Configuration
  appName: 'Boukii Admin V5',
  appVersion: '5.0.0',
  appTitle: '🔧 Boukii V5 - Development',
  appDescription: 'Boukii Admin Panel V5 - Development Environment',

  // Feature Flags
  enableDebugMode: true,
  enableAdvancedLogging: true,
  enablePerformanceMonitoring: true,
  enableHotReload: true,
  enableSourceMaps: true,
  enableMockData: true,
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
    level: 'debug',
    enableConsoleLogging: true,
    enableRemoteLogging: false,
    enableErrorReporting: false
  },

  // Security Configuration
  security: {
    enableCSRF: false,
    enableCSP: false,
    enableSecureHeaders: false,
    sessionTimeout: 7200000, // 2 hours
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
      enabled: false,
      trackingId: ''
    },
    errorReporting: {
      enabled: false,
      dsn: ''
    }
  }
};
