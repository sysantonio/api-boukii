import { Injectable, signal, computed, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
import {
  SupportedLanguage,
  TranslationFile,
  TranslationParams,
  TranslationContext,
  LanguageDetection,
  TranslationLoadingState,
} from '../models/i18n.models';
import { ErrorSeverity } from '../models/error.models';
import {
  LANGUAGE_CONFIGS,
  DEFAULT_LANGUAGE,
  FALLBACK_LANGUAGE,
  LANGUAGE_STORAGE_KEY,
  detectBrowserLanguage,
  getLanguageConfig,
} from '../config/languages.config';
import { LoggingService } from './logging.service';

@Injectable({ providedIn: 'root' })
export class TranslationService {
  private readonly http = inject(HttpClient);
  private readonly logger = inject(LoggingService);

  // Private signals
  private readonly _currentLanguage = signal<SupportedLanguage>(DEFAULT_LANGUAGE);
  private readonly _translations = signal<Record<SupportedLanguage, TranslationFile | null>>({
    en: null,
    es: null,
  });
  private readonly _loadingState = signal<TranslationLoadingState>({
    loading: false,
    loaded: false,
    error: null,
    lastUpdated: null,
  });

  // Public readonly signals
  readonly currentLanguage = this._currentLanguage.asReadonly();
  readonly translations = this._translations.asReadonly();
  readonly loadingState = this._loadingState.asReadonly();

  // Computed signals
  readonly currentLanguageConfig = computed(() => getLanguageConfig(this._currentLanguage()));

  readonly isRTL = computed(() => this.currentLanguageConfig().direction === 'rtl');

  readonly availableLanguages = computed(() => Object.values(LANGUAGE_CONFIGS));

  readonly currentTranslations = computed(() => this._translations()[this._currentLanguage()]);

  readonly isLoading = computed(() => this._loadingState().loading);
  readonly hasError = computed(() => !!this._loadingState().error);

  // Translation cache for performance
  private readonly translationCache = new Map<string, string>();

  constructor() {
    this.initializeLanguage();
  }

  /**
   * Initialize translation service
   */
  private async initializeLanguage(): Promise<void> {
    const detectedLanguage = this.detectLanguage();
    await this.setLanguage(detectedLanguage.language);

    this.logger.logInfo('Translation service initialized', {
      url: 'translation-service',
      method: 'initializeLanguage',
      severity: ErrorSeverity.LOW,
      language: detectedLanguage.language,
    });
  }

  /**
   * Detect user's preferred language
   */
  private detectLanguage(): LanguageDetection {
    // Check stored preference first
    const storedLanguage = this.getStoredLanguage();
    if (storedLanguage) {
      return {
        language: storedLanguage,
        confidence: 1.0,
        source: 'stored',
      };
    }

    // Detect from browser
    const browserLanguage = detectBrowserLanguage();
    return {
      language: browserLanguage,
      confidence: 0.8,
      source: 'browser',
    };
  }

  /**
   * Set current language and load translations
   */
  async setLanguage(language: SupportedLanguage): Promise<void> {
    if (language === this._currentLanguage()) {
      return; // Already set
    }

    this._loadingState.set({
      loading: true,
      loaded: false,
      error: null,
      lastUpdated: null,
    });

    try {
      // Load translations for the language if not already loaded
      await this.loadTranslations(language);

      // Set as current language
      this._currentLanguage.set(language);

      // Store preference
      this.storeLanguage(language);

      // Update loading state
      this._loadingState.set({
        loading: false,
        loaded: true,
        error: null,
        lastUpdated: new Date(),
      });

      // Clear cache when language changes
      this.translationCache.clear();

      // Update document language attribute
      this.updateDocumentLanguage(language);

      this.logger.logInfo('Language changed successfully', {
        url: 'translation-service',
        method: 'setLanguage',
        severity: ErrorSeverity.LOW,
        language,
      });
    } catch (_error) {
      this._loadingState.set({
        loading: false,
        loaded: false,
        error: _error instanceof Error ? _error.message : 'Failed to load translations',
        lastUpdated: null,
      });

      this.logger.logError(
        'Failed to set language',
        {
          type: 'translation_error',
          title: 'Translation Load Error',
          detail: _error instanceof Error ? _error.message : 'Unknown error',
          code: 'TRANSLATION_LOAD_ERROR',
        },
        { language }
      );

      throw _error;
    }
  }

  /**
   * Load translations for a specific language
   */
  private async loadTranslations(language: SupportedLanguage): Promise<void> {
    const currentTranslations = this._translations();

    // Skip if already loaded
    if (currentTranslations[language]) {
      return;
    }

    try {
      const translations = await this.fetchTranslations(language);

      this._translations.set({
        ...currentTranslations,
        [language]: translations,
      });
    } catch (_error) {
      // If loading fails and it's not the fallback language, try fallback
      if (language !== FALLBACK_LANGUAGE) {
        this.logger.logWarning(`Failed to load ${language} translations, trying fallback`);
        await this.loadTranslations(FALLBACK_LANGUAGE);
      } else {
        throw _error;
      }
    }
  }

  /**
   * Fetch translations from the server or local files
   */
  private async fetchTranslations(language: SupportedLanguage): Promise<TranslationFile> {
    try {
      // In a real app, this would fetch from an API or translation service
      // For now, we'll load from local assets
      const response = await firstValueFrom(
        this.http.get<TranslationFile>(`/assets/i18n/${language}.json`)
      );
      return response;
    } catch (_error) {
      // Fallback to embedded translations if file doesn't exist
      this.logger.logWarning(`Could not load translation file for ${language}, using embedded`);
      return this.getEmbeddedTranslations(language);
    }
  }

  /**
   * Get embedded fallback translations
   */
  private getEmbeddedTranslations(language: SupportedLanguage): TranslationFile {
    // Basic embedded translations as fallback
    const baseTranslations: TranslationFile = {
      nav: {
        dashboard: language === 'es' ? 'Dashboard' : 'Dashboard',
        clients: language === 'es' ? 'Clientes' : 'Clients',
        courses: language === 'es' ? 'Cursos' : 'Courses',
        bookings: language === 'es' ? 'Reservas' : 'Bookings',
        monitors: language === 'es' ? 'Monitores' : 'Monitors',
        settings: language === 'es' ? 'Configuración' : 'Settings',
        logout: language === 'es' ? 'Cerrar sesión' : 'Logout',
        mainNavigation: language === 'es' ? 'Navegación principal' : 'Main navigation',
      },
      actions: {
        save: language === 'es' ? 'Guardar' : 'Save',
        cancel: language === 'es' ? 'Cancelar' : 'Cancel',
        delete: language === 'es' ? 'Eliminar' : 'Delete',
        edit: language === 'es' ? 'Editar' : 'Edit',
        create: language === 'es' ? 'Crear' : 'Create',
        update: language === 'es' ? 'Actualizar' : 'Update',
        search: language === 'es' ? 'Buscar' : 'Search',
        filter: language === 'es' ? 'Filtrar' : 'Filter',
        export: language === 'es' ? 'Exportar' : 'Export',
        import: language === 'es' ? 'Importar' : 'Import',
        refresh: language === 'es' ? 'Actualizar' : 'Refresh',
        back: language === 'es' ? 'Atrás' : 'Back',
        next: language === 'es' ? 'Siguiente' : 'Next',
        previous: language === 'es' ? 'Anterior' : 'Previous',
        submit: language === 'es' ? 'Enviar' : 'Submit',
        reset: language === 'es' ? 'Restablecer' : 'Reset',
        confirm: language === 'es' ? 'Confirmar' : 'Confirm',
        close: language === 'es' ? 'Cerrar' : 'Close',
      },
      form: {
        name: language === 'es' ? 'Nombre' : 'Name',
        email: language === 'es' ? 'Email' : 'Email',
        password: language === 'es' ? 'Contraseña' : 'Password',
        confirmPassword: language === 'es' ? 'Confirmar contraseña' : 'Confirm Password',
        phone: language === 'es' ? 'Teléfono' : 'Phone',
        address: language === 'es' ? 'Dirección' : 'Address',
        city: language === 'es' ? 'Ciudad' : 'City',
        country: language === 'es' ? 'País' : 'Country',
        zipCode: language === 'es' ? 'Código postal' : 'Zip Code',
        birthDate: language === 'es' ? 'Fecha de nacimiento' : 'Birth Date',
        gender: language === 'es' ? 'Género' : 'Gender',
        description: language === 'es' ? 'Descripción' : 'Description',
        notes: language === 'es' ? 'Notas' : 'Notes',
        status: language === 'es' ? 'Estado' : 'Status',
        type: language === 'es' ? 'Tipo' : 'Type',
        category: language === 'es' ? 'Categoría' : 'Category',
        price: language === 'es' ? 'Precio' : 'Price',
        duration: language === 'es' ? 'Duración' : 'Duration',
        capacity: language === 'es' ? 'Capacidad' : 'Capacity',
        startDate: language === 'es' ? 'Fecha de inicio' : 'Start Date',
        endDate: language === 'es' ? 'Fecha de fin' : 'End Date',
        time: language === 'es' ? 'Hora' : 'Time',
      },
      validation: {
        required: language === 'es' ? 'Este campo es obligatorio' : 'This field is required',
        email: language === 'es' ? 'Email no válido' : 'Invalid email',
        minLength: language === 'es' ? 'Mínimo {{min}} caracteres' : 'Minimum {{min}} characters',
        maxLength: language === 'es' ? 'Máximo {{max}} caracteres' : 'Maximum {{max}} characters',
        min: language === 'es' ? 'Valor mínimo: {{min}}' : 'Minimum value: {{min}}',
        max: language === 'es' ? 'Valor máximo: {{max}}' : 'Maximum value: {{max}}',
        pattern: language === 'es' ? 'Formato no válido' : 'Invalid format',
        passwordMismatch:
          language === 'es' ? 'Las contraseñas no coinciden' : 'Passwords do not match',
        phoneInvalid: language === 'es' ? 'Teléfono no válido' : 'Invalid phone number',
        dateInvalid: language === 'es' ? 'Fecha no válida' : 'Invalid date',
        uniqueViolation: language === 'es' ? 'Este valor ya existe' : 'This value already exists',
      },
      errors: {
        generic: language === 'es' ? 'Ha ocurrido un error' : 'An error occurred',
        network: language === 'es' ? 'Error de conexión' : 'Network error',
        unauthorized: language === 'es' ? 'No autorizado' : 'Unauthorized',
        forbidden: language === 'es' ? 'Acceso denegado' : 'Access forbidden',
        notFound: language === 'es' ? 'No encontrado' : 'Not found',
        validation: language === 'es' ? 'Error de validación' : 'Validation error',
        server: language === 'es' ? 'Error del servidor' : 'Server error',
        timeout: language === 'es' ? 'Tiempo agotado' : 'Request timeout',
        chunkLoadError:
          language === 'es' ? 'Error de carga. Recarga la página.' : 'Load error. Please refresh.',
        sessionExpired: language === 'es' ? 'Sesión expirada' : 'Session expired',
      },
      success: {
        saved: language === 'es' ? 'Guardado correctamente' : 'Saved successfully',
        updated: language === 'es' ? 'Actualizado correctamente' : 'Updated successfully',
        deleted: language === 'es' ? 'Eliminado correctamente' : 'Deleted successfully',
        created: language === 'es' ? 'Creado correctamente' : 'Created successfully',
        sent: language === 'es' ? 'Enviado correctamente' : 'Sent successfully',
        uploaded: language === 'es' ? 'Subido correctamente' : 'Uploaded successfully',
        imported: language === 'es' ? 'Importado correctamente' : 'Imported successfully',
        exported: language === 'es' ? 'Exportado correctamente' : 'Exported successfully',
      },
      loading: {
        generic: language === 'es' ? 'Cargando...' : 'Loading...',
        saving: language === 'es' ? 'Guardando...' : 'Saving...',
        loading: language === 'es' ? 'Cargando...' : 'Loading...',
        processing: language === 'es' ? 'Procesando...' : 'Processing...',
        uploading: language === 'es' ? 'Subiendo...' : 'Uploading...',
        deleting: language === 'es' ? 'Eliminando...' : 'Deleting...',
        signing_in: language === 'es' ? 'Iniciando sesión...' : 'Signing in...',
        signing_out: language === 'es' ? 'Cerrando sesión...' : 'Signing out...',
      },
      date: {
        today: language === 'es' ? 'Hoy' : 'Today',
        yesterday: language === 'es' ? 'Ayer' : 'Yesterday',
        tomorrow: language === 'es' ? 'Mañana' : 'Tomorrow',
        thisWeek: language === 'es' ? 'Esta semana' : 'This week',
        nextWeek: language === 'es' ? 'Próxima semana' : 'Next week',
        thisMonth: language === 'es' ? 'Este mes' : 'This month',
        nextMonth: language === 'es' ? 'Próximo mes' : 'Next month',
        days:
          language === 'es'
            ? ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
            : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        daysShort:
          language === 'es'
            ? ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
            : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        months:
          language === 'es'
            ? [
                'Enero',
                'Febrero',
                'Marzo',
                'Abril',
                'Mayo',
                'Junio',
                'Julio',
                'Agosto',
                'Septiembre',
                'Octubre',
                'Noviembre',
                'Diciembre',
              ]
            : [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
              ],
        monthsShort:
          language === 'es'
            ? ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
            : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      },
      format: {
        currency: language === 'es' ? '{{value}} €' : '${{value}}',
        percentage: language === 'es' ? '{{value}}%' : '{{value}}%',
        decimal: language === 'es' ? '{{value}}' : '{{value}}',
        integer: language === 'es' ? '{{value}}' : '{{value}}',
      },
      theme: {
        light: language === 'es' ? 'Claro' : 'Light',
        dark: language === 'es' ? 'Oscuro' : 'Dark',
        system: language === 'es' ? 'Sistema' : 'System',
        switchTo: language === 'es' ? 'Cambiar a tema {{theme}}' : 'Switch to {{theme}} theme',
        options: language === 'es' ? 'Opciones de tema' : 'Theme options',
      },
      dashboard: {
        title: language === 'es' ? 'Dashboard' : 'Dashboard',
        welcome: language === 'es' ? 'Bienvenido' : 'Welcome',
        overview: language === 'es' ? 'Resumen' : 'Overview',
        statistics: language === 'es' ? 'Estadísticas' : 'Statistics',
        recentActivity: language === 'es' ? 'Actividad reciente' : 'Recent Activity',
        quickActions: language === 'es' ? 'Acciones rápidas' : 'Quick Actions',
      },
      auth: {
        login: language === 'es' ? 'Iniciar sesión' : 'Sign In',
        logout: language === 'es' ? 'Cerrar sesión' : 'Sign Out',
        register: language === 'es' ? 'Registrarse' : 'Sign Up',
        forgotPassword: language === 'es' ? '¿Olvidaste tu contraseña?' : 'Forgot Password?',
        resetPassword: language === 'es' ? 'Restablecer contraseña' : 'Reset Password',
        profile: language === 'es' ? 'Perfil' : 'Profile',
        signInTitle: language === 'es' ? 'Iniciar sesión' : 'Sign In',
        signInSubtitle: language === 'es' ? 'Ingresa a tu cuenta' : 'Sign in to your account',
        rememberMe: language === 'es' ? 'Recordarme' : 'Remember me',
        noAccount: language === 'es' ? '¿No tienes cuenta?' : "Don't have an account?",
        alreadyHaveAccount: language === 'es' ? '¿Ya tienes cuenta?' : 'Already have an account?',
        signInButton: language === 'es' ? 'Iniciar sesión' : 'Sign In',
        signOutConfirm:
          language === 'es'
            ? '¿Estás seguro de cerrar sesión?'
            : 'Are you sure you want to sign out?',
      },
      featureFlags: {
        title: language === 'es' ? 'Feature Flags' : 'Feature Flags',
        resetDefaults: language === 'es' ? 'Restablecer por defecto' : 'Reset to Defaults',
        reloadConfig: language === 'es' ? 'Recargar configuración' : 'Reload Configuration',
        categories: {
          core: {
            title: language === 'es' ? 'Funciones Principales' : 'Core Features',
            description:
              language === 'es'
                ? 'Funcionalidades básicas de la aplicación'
                : 'Basic application functionality',
          },
          dashboard: {
            title: language === 'es' ? 'Dashboard' : 'Dashboard',
            description:
              language === 'es'
                ? 'Funciones del panel de control'
                : 'Dashboard and analytics features',
          },
          admin: {
            title: language === 'es' ? 'Administración' : 'Administration',
            description:
              language === 'es'
                ? 'Herramientas de administración del sistema'
                : 'System administration tools',
          },
          experimental: {
            title: language === 'es' ? 'Experimental' : 'Experimental',
            description:
              language === 'es'
                ? 'Funciones en desarrollo y depuración'
                : 'Development and debugging features',
          },
        },
        features: {
          darkTheme: {
            name: language === 'es' ? 'Tema Oscuro' : 'Dark Theme',
            description:
              language === 'es'
                ? 'Permite cambiar entre temas claro y oscuro'
                : 'Enable light/dark theme switching',
          },
          multiLanguage: {
            name: language === 'es' ? 'Multi-idioma' : 'Multi-Language',
            description:
              language === 'es'
                ? 'Soporte para múltiples idiomas'
                : 'Support for multiple languages',
          },
          notifications: {
            name: language === 'es' ? 'Notificaciones' : 'Notifications',
            description:
              language === 'es' ? 'Sistema de notificaciones push' : 'Push notification system',
          },
          analytics: {
            name: language === 'es' ? 'Analíticas' : 'Analytics',
            description:
              language === 'es' ? 'Seguimiento de uso y métricas' : 'Usage tracking and metrics',
          },
          dashboardWidgets: {
            name: language === 'es' ? 'Widgets Dashboard' : 'Dashboard Widgets',
            description:
              language === 'es'
                ? 'Widgets interactivos en el dashboard'
                : 'Interactive dashboard widgets',
          },
          realtimeUpdates: {
            name: language === 'es' ? 'Actualizaciones en Tiempo Real' : 'Realtime Updates',
            description:
              language === 'es' ? 'Actualizaciones automáticas de datos' : 'Automatic data updates',
          },
          exportData: {
            name: language === 'es' ? 'Exportar Datos' : 'Export Data',
            description:
              language === 'es' ? 'Capacidad de exportar datos' : 'Data export capabilities',
          },
          importData: {
            name: language === 'es' ? 'Importar Datos' : 'Import Data',
            description:
              language === 'es' ? 'Capacidad de importar datos' : 'Data import capabilities',
          },
          userManagement: {
            name: language === 'es' ? 'Gestión de Usuarios' : 'User Management',
            description:
              language === 'es'
                ? 'Administración de usuarios y permisos'
                : 'User and permission management',
          },
          systemSettings: {
            name: language === 'es' ? 'Configuración del Sistema' : 'System Settings',
            description:
              language === 'es'
                ? 'Configuración avanzada del sistema'
                : 'Advanced system configuration',
          },
          auditLogs: {
            name: language === 'es' ? 'Logs de Auditoría' : 'Audit Logs',
            description:
              language === 'es' ? 'Registro de actividades del sistema' : 'System activity logging',
          },
          backupRestore: {
            name: language === 'es' ? 'Backup y Restauración' : 'Backup & Restore',
            description:
              language === 'es'
                ? 'Funciones de respaldo y restauración'
                : 'Backup and restore functions',
          },
          experimentalUI: {
            name: language === 'es' ? 'UI Experimental' : 'Experimental UI',
            description:
              language === 'es'
                ? 'Interfaz de usuario experimental'
                : 'Experimental user interface',
          },
          betaFeatures: {
            name: language === 'es' ? 'Funciones Beta' : 'Beta Features',
            description: language === 'es' ? 'Funciones en fase beta' : 'Features in beta phase',
          },
          debugMode: {
            name: language === 'es' ? 'Modo Debug' : 'Debug Mode',
            description:
              language === 'es' ? 'Herramientas de depuración' : 'Development debugging tools',
          },
          performanceMonitoring: {
            name: language === 'es' ? 'Monitoreo de Rendimiento' : 'Performance Monitoring',
            description:
              language === 'es'
                ? 'Seguimiento del rendimiento de la app'
                : 'Application performance tracking',
          },
        },
      },
    };

    return baseTranslations;
  }

  /**
   * Get translation for a key
   */
  get(key: string, params?: TranslationParams, context?: TranslationContext): string {
    const cacheKey = this.getCacheKey(key, params, context);

    // Check cache first
    if (this.translationCache.has(cacheKey)) {
      return this.translationCache.get(cacheKey)!;
    }

    const translation = this.getTranslation(key, params, context);

    // Cache the result
    this.translationCache.set(cacheKey, translation);

    return translation;
  }

  /**
   * Get translation without caching (for dynamic content)
   */
  instant(key: string, params?: TranslationParams, context?: TranslationContext): string {
    return this.getTranslation(key, params, context);
  }

  /**
   * Internal method to get translation
   */
  private getTranslation(
    key: string,
    params?: TranslationParams,
    context?: TranslationContext
  ): string {
    const currentTranslations = this.currentTranslations();

    if (!currentTranslations) {
      this.logger.logWarning('No translations loaded', { key });
      return key; // Return key as fallback
    }

    // Navigate through the nested object structure
    const translation = this.getNestedProperty(currentTranslations, key);

    if (translation === undefined) {
      // Try fallback language if available
      const fallbackTranslations = this._translations()[FALLBACK_LANGUAGE];
      if (fallbackTranslations && this._currentLanguage() !== FALLBACK_LANGUAGE) {
        const fallbackTranslation = this.getNestedProperty(fallbackTranslations, key);
        if (fallbackTranslation !== undefined) {
          this.logger.logWarning('Using fallback translation', {
            key,
            language: FALLBACK_LANGUAGE,
          });
          return this.interpolate(fallbackTranslation, params, context);
        }
      }

      this.logger.logWarning('Translation key not found', {
        key,
        language: this._currentLanguage(),
      });
      return key; // Return key as fallback
    }

    return this.interpolate(translation, params, context);
  }

  /**
   * Get nested property from object using dot notation
   */
  private getNestedProperty(obj: unknown, path: string): string | undefined {
    return path
      .split('.')
      .reduce(
        (current, key) =>
          current && typeof current === 'object' && key in current
            ? (current as Record<string, unknown>)[key]
            : undefined,
        obj
      ) as string | undefined;
  }

  /**
   * Interpolate parameters into translation string
   */
  private interpolate(
    translation: string,
    params?: TranslationParams,
    context?: TranslationContext
  ): string {
    if (!params && !context) {
      return translation;
    }

    let result = translation;

    // Handle regular parameter interpolation
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        const placeholder = new RegExp(`{{\\s*${key}\\s*}}`, 'g');
        result = result.replace(placeholder, String(value));
      });
    }

    // Handle pluralization (basic implementation)
    if (context?.count !== undefined) {
      result = this.handlePluralization(result, context.count);
    }

    return result;
  }

  /**
   * Handle basic pluralization
   */
  private handlePluralization(translation: string, count: number): string {
    // Simple pluralization logic
    // In a more advanced implementation, you'd use proper pluralization rules
    const pluralMatch = translation.match(/\{(\w+),\s*plural,\s*(.+?)\}/);
    if (pluralMatch) {
      const [, , forms] = pluralMatch;

      // Basic English/Spanish pluralization
      if (count === 1) {
        const oneMatch = forms.match(/one\s*\{([^}]+)\}/);
        if (oneMatch) return oneMatch[1];
      } else {
        const otherMatch = forms.match(/other\s*\{([^}]+)\}/);
        if (otherMatch) return otherMatch[1];
      }
    }

    return translation;
  }

  /**
   * Generate cache key
   */
  private getCacheKey(
    key: string,
    params?: TranslationParams,
    context?: TranslationContext
  ): string {
    const language = this._currentLanguage();
    const paramsStr = params ? JSON.stringify(params) : '';
    const contextStr = context ? JSON.stringify(context) : '';
    return `${language}:${key}:${paramsStr}:${contextStr}`;
  }

  /**
   * Get stored language from localStorage
   */
  private getStoredLanguage(): SupportedLanguage | null {
    if (typeof localStorage === 'undefined') return null;

    const stored = localStorage.getItem(LANGUAGE_STORAGE_KEY);
    if (stored && stored in LANGUAGE_CONFIGS) {
      return stored as SupportedLanguage;
    }

    return null;
  }

  /**
   * Store language preference
   */
  private storeLanguage(language: SupportedLanguage): void {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(LANGUAGE_STORAGE_KEY, language);
    }
  }

  /**
   * Update document language attribute
   */
  private updateDocumentLanguage(language: SupportedLanguage): void {
    if (typeof document !== 'undefined') {
      document.documentElement.lang = language;
      document.documentElement.dir = getLanguageConfig(language).direction;
    }
  }

  /**
   * Force reload translations (useful for development)
   */
  async reloadTranslations(): Promise<void> {
    this.translationCache.clear();

    // Clear loaded translations
    this._translations.set({
      en: null,
      es: null,
    });

    // Reload current language
    await this.loadTranslations(this._currentLanguage());

    this.logger.logInfo('Translations reloaded', {
      language: this._currentLanguage(),
    });
  }

  /**
   * Get formatted date according to current language
   */
  formatDate(date: Date, format: 'short' | 'medium' | 'long' = 'medium'): string {
    const config = this.currentLanguageConfig();
    const locale = config.code === 'es' ? 'es-ES' : 'en-US';

    const optionsMap: Record<string, Intl.DateTimeFormatOptions> = {
      short: { dateStyle: 'short' },
      medium: { dateStyle: 'medium' },
      long: { dateStyle: 'long' },
    };
    const options = optionsMap[format] || optionsMap['medium'];

    return new Intl.DateTimeFormat(locale, options).format(date);
  }

  /**
   * Get formatted number according to current language
   */
  formatNumber(value: number, type: 'decimal' | 'currency' | 'percentage' = 'decimal'): string {
    const config = this.currentLanguageConfig();
    const locale = config.code === 'es' ? 'es-ES' : 'en-US';

    const optionsMap: Record<string, Intl.NumberFormatOptions> = {
      decimal: { style: 'decimal' },
      currency: {
        style: 'currency',
        currency: config.currencyCode,
      },
      percentage: { style: 'percent' },
    };
    const options = optionsMap[type] || optionsMap['decimal'];

    return new Intl.NumberFormat(locale, options).format(value);
  }
}
