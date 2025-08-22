/**
 * Client-side feature flag routing
 * Determina si usar V5 o Legacy basado en feature flags de la escuela
 */
(function() {
    'use strict';
    
    // Cache para feature flags
    const CACHE_KEY = 'boukii_feature_flags';
    const CACHE_TTL = 5 * 60 * 1000; // 5 minutos
    
    // Feature flags por defecto (fallback)
    const DEFAULT_FLAGS = {
        useV5Dashboard: false,
        useV5Planificador: false,
        useV5Reservas: false,
        useV5Cursos: false,
        useV5Monitores: false,
        useV5Clientes: false,
        useV5Analytics: false,
        useV5Settings: false
    };
    
    /**
     * Obtiene school_id desde localStorage o URL
     */
    function getCurrentSchoolId() {
        // Primero intentar desde context almacenado
        const context = localStorage.getItem('boukii_context');
        if (context) {
            try {
                const parsed = JSON.parse(context);
                return parsed.schoolId;
            } catch (e) {
                console.warn('[FeatureFlags] Error parsing context:', e);
            }
        }
        
        // Fallback: extraer de URL params
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('school_id');
    }
    
    /**
     * Obtiene feature flags desde cache o API
     */
    async function getFeatureFlags(schoolId) {
        if (!schoolId) {
            console.warn('[FeatureFlags] No school ID found, using defaults');
            return DEFAULT_FLAGS;
        }
        
        // Check cache primero
        const cached = localStorage.getItem(`${CACHE_KEY}_${schoolId}`);
        if (cached) {
            try {
                const { data, timestamp } = JSON.parse(cached);
                if (Date.now() - timestamp < CACHE_TTL) {
                    console.log('[FeatureFlags] Using cached flags for school:', schoolId);
                    return { ...DEFAULT_FLAGS, ...data };
                }
            } catch (e) {
                console.warn('[FeatureFlags] Error parsing cached flags:', e);
            }
        }
        
        // Fetch desde API
        try {
            console.log('[FeatureFlags] Fetching flags for school:', schoolId);
            const response = await fetch(`/api/feature-flags?school_id=${schoolId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                timeout: 5000
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            const flags = result.data || {};
            
            // Cache result
            localStorage.setItem(`${CACHE_KEY}_${schoolId}`, JSON.stringify({
                data: flags,
                timestamp: Date.now()
            }));
            
            console.log('[FeatureFlags] Fetched flags:', flags);
            return { ...DEFAULT_FLAGS, ...flags };
            
        } catch (error) {
            console.error('[FeatureFlags] Error fetching flags:', error);
            return DEFAULT_FLAGS;
        }
    }
    
    /**
     * Determina la ruta basada en feature flags
     */
    function shouldUseLegacy(currentPath, flags) {
        const routeMapping = {
            '/dashboard': 'useV5Dashboard',
            '/planificador': 'useV5Planificador',
            '/reservas': 'useV5Reservas',
            '/cursos': 'useV5Cursos',
            '/monitores': 'useV5Monitores',
            '/clientes': 'useV5Clientes',
            '/analytics': 'useV5Analytics',
            '/settings': 'useV5Settings',
            '/ajustes': 'useV5Settings'
        };
        
        // Buscar coincidencia de ruta
        for (const [route, flag] of Object.entries(routeMapping)) {
            if (currentPath.startsWith(route)) {
                const useV5 = flags[flag] === true;
                console.log(`[FeatureFlags] Route ${route} -> V5: ${useV5} (flag: ${flag})`);
                return !useV5; // Retorna true si debe usar legacy
            }
        }
        
        // Por defecto, usar legacy para rutas no mapeadas
        console.log(`[FeatureFlags] Unmapped route ${currentPath} -> Legacy (default)`);
        return true;
    }
    
    /**
     * Redirige a legacy si es necesario
     */
    function redirectToLegacyIfNeeded(flags) {
        const currentPath = window.location.pathname;
        
        // Evitar loops de redirección
        if (currentPath.startsWith('/legacy/')) {
            console.log('[FeatureFlags] Already in legacy, skipping check');
            return;
        }
        
        // Skip para rutas de auth y API
        if (currentPath.startsWith('/auth/') || currentPath.startsWith('/api/')) {
            console.log('[FeatureFlags] Auth/API route, skipping check');
            return;
        }
        
        if (shouldUseLegacy(currentPath, flags)) {
            const legacyUrl = `/legacy${currentPath}${window.location.search}${window.location.hash}`;
            console.log(`[FeatureFlags] Redirecting to legacy: ${legacyUrl}`);
            window.location.href = legacyUrl;
        } else {
            console.log(`[FeatureFlags] Staying on V5 for path: ${currentPath}`);
        }
    }
    
    /**
     * Función principal
     */
    async function initializeFeatureFlags() {
        try {
            const schoolId = getCurrentSchoolId();
            const flags = await getFeatureFlags(schoolId);
            
            // Hacer flags disponibles globalmente
            window.BoukiiFeatureFlags = flags;
            
            // Aplicar routing condicional
            redirectToLegacyIfNeeded(flags);
            
            // Event para cuando cambien los flags
            window.dispatchEvent(new CustomEvent('featureFlagsLoaded', { 
                detail: { flags, schoolId } 
            }));
            
        } catch (error) {
            console.error('[FeatureFlags] Initialization failed:', error);
            // En caso de error, usar legacy como fallback
            window.location.href = `/legacy${window.location.pathname}${window.location.search}${window.location.hash}`;
        }
    }
    
    /**
     * Función para refrescar feature flags (útil para testing)
     */
    window.refreshFeatureFlags = function(schoolId) {
        if (schoolId) {
            localStorage.removeItem(`${CACHE_KEY}_${schoolId}`);
        }
        return initializeFeatureFlags();
    };
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeFeatureFlags);
    } else {
        initializeFeatureFlags();
    }
    
})();