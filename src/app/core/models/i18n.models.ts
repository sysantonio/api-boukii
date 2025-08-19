/**
 * Internationalization models and interfaces
 */

/**
 * Supported languages
 */
export type SupportedLanguage = 'en' | 'es';

/**
 * Language configuration
 */
export interface LanguageConfig {
  code: SupportedLanguage;
  name: string;
  nativeName: string;
  flag: string;
  direction: 'ltr' | 'rtl';
  dateFormat: string;
  timeFormat: string;
  currencyCode: string;
  currencySymbol: string;
  thousandsSeparator: string;
  decimalSeparator: string;
}

/**
 * Translation key structure for type safety
 */
export interface TranslationKeys {
  // Navigation
  nav: {
    dashboard: string;
    clients: string;
    courses: string;
    bookings: string;
    monitors: string;
    settings: string;
    logout: string;
    mainNavigation: string;
  };

  // Common actions
  actions: {
    save: string;
    cancel: string;
    delete: string;
    edit: string;
    create: string;
    update: string;
    search: string;
    filter: string;
    export: string;
    import: string;
    refresh: string;
    back: string;
    next: string;
    previous: string;
    submit: string;
    reset: string;
    confirm: string;
    close: string;
  };

  // Form labels
  form: {
    name: string;
    email: string;
    password: string;
    confirmPassword: string;
    phone: string;
    address: string;
    city: string;
    country: string;
    zipCode: string;
    birthDate: string;
    gender: string;
    description: string;
    notes: string;
    status: string;
    type: string;
    category: string;
    price: string;
    duration: string;
    capacity: string;
    startDate: string;
    endDate: string;
    time: string;
  };

  // Validation messages
  validation: {
    required: string;
    email: string;
    minLength: string;
    maxLength: string;
    min: string;
    max: string;
    pattern: string;
    passwordMismatch: string;
    phoneInvalid: string;
    dateInvalid: string;
    uniqueViolation: string;
  };

  // Error messages
  errors: {
    generic: string;
    network: string;
    unauthorized: string;
    forbidden: string;
    notFound: string;
    validation: string;
    server: string;
    timeout: string;
    chunkLoadError: string;
    sessionExpired: string;
  };

  // Success messages
  success: {
    saved: string;
    updated: string;
    deleted: string;
    created: string;
    sent: string;
    uploaded: string;
    imported: string;
    exported: string;
  };

  // Loading states
  loading: {
    generic: string;
    saving: string;
    loading: string;
    processing: string;
    uploading: string;
    deleting: string;
    signing_in: string;
    signing_out: string;
  };

  // Date and time
  date: {
    today: string;
    yesterday: string;
    tomorrow: string;
    thisWeek: string;
    nextWeek: string;
    thisMonth: string;
    nextMonth: string;
    days: string[];
    daysShort: string[];
    months: string[];
    monthsShort: string[];
  };

  // Numbers and formatting
  format: {
    currency: string;
    percentage: string;
    decimal: string;
    integer: string;
  };

  // Theme and UI
  theme: {
    light: string;
    dark: string;
    system: string;
    switchTo: string;
    options: string;
  };

  // Dashboard
  dashboard: {
    title: string;
    welcome: string;
    overview: string;
    statistics: string;
    recentActivity: string;
    quickActions: string;
  };

  // Authentication
  auth: {
    login: string;
    logout: string;
    register: string;
    forgotPassword: string;
    resetPassword: string;
    profile: string;
    signInTitle: string;
    signInSubtitle: string;
    rememberMe: string;
    noAccount: string;
    alreadyHaveAccount: string;
    signInButton: string;
    signOutConfirm: string;
  };

  // Feature Flags
  featureFlags: {
    title: string;
    resetDefaults: string;
    reloadConfig: string;
    categories: {
      core: {
        title: string;
        description: string;
      };
      dashboard: {
        title: string;
        description: string;
      };
      admin: {
        title: string;
        description: string;
      };
      experimental: {
        title: string;
        description: string;
      };
    };
    features: {
      darkTheme: { name: string; description: string };
      multiLanguage: { name: string; description: string };
      notifications: { name: string; description: string };
      analytics: { name: string; description: string };
      dashboardWidgets: { name: string; description: string };
      realtimeUpdates: { name: string; description: string };
      exportData: { name: string; description: string };
      importData: { name: string; description: string };
      userManagement: { name: string; description: string };
      systemSettings: { name: string; description: string };
      auditLogs: { name: string; description: string };
      backupRestore: { name: string; description: string };
      experimentalUI: { name: string; description: string };
      betaFeatures: { name: string; description: string };
      debugMode: { name: string; description: string };
      performanceMonitoring: { name: string; description: string };
    };
  };
}

/**
 * Translation file structure
 */
export type TranslationFile = TranslationKeys;

/**
 * Interpolation parameters for translations
 */
export interface TranslationParams {
  [key: string]: string | number | boolean | Date;
}

/**
 * Translation context for pluralization and formatting
 */
export interface TranslationContext {
  count?: number;
  gender?: 'male' | 'female' | 'neutral';
  context?: string;
}

/**
 * Language detection result
 */
export interface LanguageDetection {
  language: SupportedLanguage;
  confidence: number;
  source: 'browser' | 'stored' | 'default';
}

/**
 * Translation loading state
 */
export interface TranslationLoadingState {
  loading: boolean;
  loaded: boolean;
  error: string | null;
  lastUpdated: Date | null;
}
