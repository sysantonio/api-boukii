import { Injectable, inject, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, timer, of, throwError } from 'rxjs';
import { map, catchError, retry, shareReplay, switchMap } from 'rxjs/operators';

export interface FeatureFlags {
  // Core módulos V5
  useV5Dashboard: boolean;
  useV5Planificador: boolean;
  useV5Reservas: boolean;
  useV5Cursos: boolean;
  useV5Monitores: boolean;
  useV5Clientes: boolean;
  useV5Analytics: boolean;
  useV5Settings: boolean;
  
  // Features específicas
  useV5Calendar: boolean;
  useV5Payments: boolean;
  useV5Communications: boolean;
  useV5Renting: boolean;
  useV5Chat: boolean;
  
  // Configuraciones avanzadas
  enableBetaFeatures: boolean;
  enableDebugMode: boolean;
  maintenanceMode: boolean;
  
  // Integraciones
  enableMicrogate: boolean;
  enableWhatsApp: boolean;
  enableDeepL: boolean;
  enableAccuWeather: boolean;
}

interface FeatureFlagResponse {
  success: boolean;
  data: Partial<FeatureFlags>;
  metadata: {
    schoolId: number;
    updatedAt: string;
    version: string;
  };
}

interface FeatureFlagCache {
  flags: FeatureFlags;
  timestamp: number;
  schoolId: number;
}

@Injectable({
  providedIn: 'root'
})
export class FeatureFlagService {
  private readonly http = inject(HttpClient);
  
  // Cache configuration
  private readonly CACHE_TTL = 5 * 60 * 1000; // 5 minutos
  private readonly CACHE_KEY_PREFIX = 'boukii_feature_flags';
  
  // Default feature flags (conservative approach)
  private readonly DEFAULT_FLAGS: FeatureFlags = {
    useV5Dashboard: false,
    useV5Planificador: false,
    useV5Reservas: false,
    useV5Cursos: false,
    useV5Monitores: false,
    useV5Clientes: true, // Ya implementado completamente
    useV5Analytics: false,
    useV5Settings: false,
    useV5Calendar: false,
    useV5Payments: false,
    useV5Communications: false,
    useV5Renting: false,
    useV5Chat: false,
    enableBetaFeatures: false,
    enableDebugMode: false,
    maintenanceMode: false,
    enableMicrogate: false,
    enableWhatsApp: false,
    enableDeepL: false,
    enableAccuWeather: true
  };
  
  // Reactive state
  private readonly _flags = signal<FeatureFlags>(this.DEFAULT_FLAGS);
  private readonly _loading = signal<boolean>(false);
  private readonly _error = signal<string | null>(null);
  private readonly _lastUpdated = signal<Date | null>(null);
  
  // Public signals
  readonly flags = this._flags.asReadonly();
  readonly loading = this._loading.asReadonly();
  readonly error = this._error.asReadonly();
  readonly lastUpdated = this._lastUpdated.asReadonly();
  
  // Computed flags para uso fácil
  readonly canUseV5Dashboard = computed(() => this.flags().useV5Dashboard);
  readonly canUseV5Clientes = computed(() => this.flags().useV5Clientes);
  readonly canUseV5Planificador = computed(() => this.flags().useV5Planificador);
  readonly isMaintenanceMode = computed(() => this.flags().maintenanceMode);
  readonly isBetaMode = computed(() => this.flags().enableBetaFeatures);
  
  // Auto-refresh timer
  private refreshTimer$ = timer(0, this.CACHE_TTL).pipe(
    switchMap(() => this.refreshFlags())
  );
  
  constructor() {
    this.initializeFromCache();
    this.startAutoRefresh();
  }
  
  /**
   * Inicializa flags desde localStorage cache
   */
  private initializeFromCache(): void {
    try {
      const schoolId = this.getCurrentSchoolId();
      if (!schoolId) return;
      
      const cached = localStorage.getItem(`${this.CACHE_KEY_PREFIX}_${schoolId}`);
      if (!cached) return;
      
      const cacheData: FeatureFlagCache = JSON.parse(cached);
      
      // Verificar si cache es válido
      const isExpired = Date.now() - cacheData.timestamp > this.CACHE_TTL;
      const isCorrectSchool = cacheData.schoolId === schoolId;
      
      if (!isExpired && isCorrectSchool) {
        this._flags.set({ ...this.DEFAULT_FLAGS, ...cacheData.flags });
        this._lastUpdated.set(new Date(cacheData.timestamp));
        console.log('[FeatureFlags] Initialized from cache:', cacheData.flags);
      }
    } catch (error) {
      console.warn('[FeatureFlags] Error loading from cache:', error);
    }
  }
  
  /**
   * Inicia el auto-refresh de feature flags
   */
  private startAutoRefresh(): void {
    // Solo auto-refresh si hay schoolId
    const schoolId = this.getCurrentSchoolId();
    if (schoolId) {
      this.refreshTimer$.subscribe();
    }
  }
  
  /**
   * Obtiene el schoolId actual del contexto
   */
  private getCurrentSchoolId(): number | null {
    try {
      const context = localStorage.getItem('boukii_context');
      if (context) {
        const parsed = JSON.parse(context);
        return parsed.schoolId || null;
      }
    } catch (error) {
      console.warn('[FeatureFlags] Error getting school ID:', error);
    }
    return null;
  }
  
  /**
   * Fetch feature flags desde API
   */
  fetchFlags(schoolId?: number): Observable<FeatureFlags> {
    const targetSchoolId = schoolId || this.getCurrentSchoolId();
    
    if (!targetSchoolId) {
      console.warn('[FeatureFlags] No school ID available, using defaults');
      return of(this.DEFAULT_FLAGS);
    }
    
    this._loading.set(true);
    this._error.set(null);
    
    return this.http.get<FeatureFlagResponse>(`/feature-flags`, {
      params: { school_id: targetSchoolId.toString() }
    }).pipe(
      retry(3),
      map(response => {
        const flags = { ...this.DEFAULT_FLAGS, ...response.data };
        
        // Cache the result
        this.cacheFlags(flags, targetSchoolId);
        
        // Update reactive state
        this._flags.set(flags);
        this._lastUpdated.set(new Date());
        this._loading.set(false);
        
        console.log('[FeatureFlags] Fetched flags for school', targetSchoolId, ':', flags);
        return flags;
      }),
      catchError(error => {
        console.error('[FeatureFlags] Error fetching flags:', error);
        this._error.set(error.message || 'Error loading feature flags');
        this._loading.set(false);
        
        // Return cached or default flags on error
        const cached = this.getCachedFlags(targetSchoolId);
        return of(cached || this.DEFAULT_FLAGS);
      }),
      shareReplay(1)
    );
  }
  
  /**
   * Refresh flags (usado por timer y manualmente)
   */
  refreshFlags(schoolId?: number): Observable<FeatureFlags> {
    return this.fetchFlags(schoolId);
  }
  
  /**
   * Cache flags en localStorage
   */
  private cacheFlags(flags: FeatureFlags, schoolId: number): void {
    try {
      const cacheData: FeatureFlagCache = {
        flags,
        timestamp: Date.now(),
        schoolId
      };
      
      localStorage.setItem(
        `${this.CACHE_KEY_PREFIX}_${schoolId}`,
        JSON.stringify(cacheData)
      );
    } catch (error) {
      console.warn('[FeatureFlags] Error caching flags:', error);
    }
  }
  
  /**
   * Obtiene flags desde cache
   */
  private getCachedFlags(schoolId: number): FeatureFlags | null {
    try {
      const cached = localStorage.getItem(`${this.CACHE_KEY_PREFIX}_${schoolId}`);
      if (cached) {
        const cacheData: FeatureFlagCache = JSON.parse(cached);
        const isExpired = Date.now() - cacheData.timestamp > this.CACHE_TTL;
        
        if (!isExpired && cacheData.schoolId === schoolId) {
          return { ...this.DEFAULT_FLAGS, ...cacheData.flags };
        }
      }
    } catch (error) {
      console.warn('[FeatureFlags] Error reading cache:', error);
    }
    return null;
  }
  
  /**
   * Verifica si una feature específica está habilitada
   */
  isEnabled(featureName: keyof FeatureFlags): boolean {
    return this.flags()[featureName] === true;
  }
  
  /**
   * Verifica múltiples features (AND logic)
   */
  areEnabled(...featureNames: (keyof FeatureFlags)[]): boolean {
    return featureNames.every(name => this.isEnabled(name));
  }
  
  /**
   * Verifica si alguna de las features está habilitada (OR logic)
   */
  isAnyEnabled(...featureNames: (keyof FeatureFlags)[]): boolean {
    return featureNames.some(name => this.isEnabled(name));
  }
  
  /**
   * Override temporal de flags (para testing)
   */
  setTemporaryOverride(overrides: Partial<FeatureFlags>): void {
    if (this.isEnabled('enableDebugMode')) {
      const currentFlags = this.flags();
      this._flags.set({ ...currentFlags, ...overrides });
      console.log('[FeatureFlags] Temporary override applied:', overrides);
    } else {
      console.warn('[FeatureFlags] Debug mode required for overrides');
    }
  }
  
  /**
   * Limpia cache para una escuela específica
   */
  clearCache(schoolId?: number): void {
    const targetSchoolId = schoolId || this.getCurrentSchoolId();
    if (targetSchoolId) {
      localStorage.removeItem(`${this.CACHE_KEY_PREFIX}_${targetSchoolId}`);
      console.log('[FeatureFlags] Cache cleared for school:', targetSchoolId);
    }
  }
  
  /**
   * Limpia todo el cache de feature flags
   */
  clearAllCache(): void {
    const keys = Object.keys(localStorage).filter(key => 
      key.startsWith(this.CACHE_KEY_PREFIX)
    );
    keys.forEach(key => localStorage.removeItem(key));
    console.log('[FeatureFlags] All cache cleared');
  }
  
  /**
   * Estado de debug para development
   */
  getDebugInfo() {
    return {
      currentFlags: this.flags(),
      schoolId: this.getCurrentSchoolId(),
      lastUpdated: this.lastUpdated(),
      loading: this.loading(),
      error: this.error(),
      cacheKeys: Object.keys(localStorage).filter(key => 
        key.startsWith(this.CACHE_KEY_PREFIX)
      )
    };
  }
}