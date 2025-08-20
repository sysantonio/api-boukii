// Test environment for CI/CD and automated testing
export const environment = {
  name: 'Test',
  envName: 'test',
  production: false,
  development: false,
  staging: false,
  testing: true,

  // API Configuration
  apiUrl: 'http://api-test.boukii.test',
  apiVersion: 'v5',
  apiTimeout: 10000,

  // App Configuration
  appName: 'Boukii Admin V5',
  appVersion: '5.0.0',
  appTitle: '🧪 Boukii V5 - Test',
  appDescription: 'Boukii Admin Panel V5 - Test Environment',

  // Feature Flags
  enableDebugMode: true,
  enableAdvancedLogging: false,
  enablePerformanceMonitoring: false,
  enableHotReload: false,
  enableSourceMaps: false,
  enableMockData: true,
  enableStorybook: false,
  enableE2ETests: true,

  // UI Configuration
  theme: {
    defaultTheme: 'light',
    allowThemeSwitch: false,
    enableAnimations: false,
    enableTransitions: false
  },

  // Logging Configuration
  logging: {
    level: 'error',
    enableConsoleLogging: false,
    enableRemoteLogging: false,
    enableErrorReporting: false
  },

  // Security Configuration
  security: {
    enableCSRF: false,
    enableCSP: false,
    enableSecureHeaders: false,
    sessionTimeout: 3600000, // 1 hour
    tokenRefreshThreshold: 300000 // 5 minutes
  },

  // Storage Configuration
  storage: {
    tokenKey: 'boukii_v5_test_token',
    userKey: 'boukii_v5_test_user',
    schoolKey: 'boukii_v5_test_school',
    seasonKey: 'boukii_v5_test_season',
    prefix: 'boukii_v5_test_'
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
