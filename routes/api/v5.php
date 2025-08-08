<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\V5\AuthV5Controller;
use App\Http\Controllers\API\V5\SeasonV5Controller;
use App\V5\Modules\Dashboard\Controllers\DashboardV5Controller;

/*
|--------------------------------------------------------------------------
| API V5 Routes
|--------------------------------------------------------------------------
|
| Rutas para la versión 5 del API de Boukii
| Estas rutas utilizan autenticación basada en tokens Sanctum
| con contexto de escuela y temporada
|
*/

// Grupo de rutas públicas (sin autenticación)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthV5Controller::class, 'login'])->name('v5.auth.login');
    Route::post('initial-login', [AuthV5Controller::class, 'initialLogin'])->name('v5.auth.initial-login');
    Route::post('check-user', [AuthV5Controller::class, 'checkUser'])->name('v5.auth.check-user');
});

// Debug endpoint outside middleware to check token without school context requirement
Route::middleware(['auth:api_v5'])->post('/debug-raw-token', function(\Illuminate\Http\Request $request) {
    try {
        $user = auth()->guard('api_v5')->user();
        $token = $user ? $user->currentAccessToken() : null;
        
        return response()->json([
            'success' => true,
            'message' => 'Raw token debug (no middleware)',
            'data' => [
                'authenticated' => !!$user,
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'token_id' => $token->id ?? null,
                'token_name' => $token->name ?? null,
                'token_context_data' => $token->context_data ?? null,
                'token_expires_at' => $token->expires_at ?? null,
                'has_school_context' => isset($token->context_data['school_id']) ?? false,
                'school_id_in_context' => $token->context_data['school_id'] ?? null
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Debug failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Grupo de rutas autenticadas
Route::middleware(['auth:api_v5'])->group(function () {
    
    // Rutas de autenticación
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthV5Controller::class, 'logout'])->name('v5.auth.logout');
        Route::get('me', [AuthV5Controller::class, 'me'])->name('v5.auth.me');
        Route::get('debug-token', function() {
            $user = Auth::guard('api_v5')->user(); 
            $token = $user->currentAccessToken();
            return response()->json([
                'user_id' => $user->id,
                'token_id' => $token->id,
                'school_id' => $token->school_id,
                'season_id' => $token->season_id,
                'context_data' => $token->context_data,
                'token_name' => $token->name
            ]);
        })->name('v5.auth.debug-token');
        Route::post('select-school', [AuthV5Controller::class, 'selectSchool'])->name('v5.auth.select-school');
        Route::post('select-season', [AuthV5Controller::class, 'selectSeason'])->name('v5.auth.select-season');
    });
    
    // Rutas que requieren contexto de escuela
    Route::middleware(['school.context.v5'])->group(function () {
        
        // Season management routes - ONLY require school context, NOT season context
        // (because you manage seasons, you don't need to be IN a season)
        Route::prefix('seasons')->name('seasons.')->group(function () {
            Route::get('/', [SeasonV5Controller::class, 'index'])->name('index');
            Route::post('/', [SeasonV5Controller::class, 'store'])->name('store');
            Route::get('/current', [SeasonV5Controller::class, 'current'])->name('current');
            Route::get('/{season}', [SeasonV5Controller::class, 'show'])->name('show');
            Route::put('/{season}', [SeasonV5Controller::class, 'update'])->name('update');
            Route::patch('/{season}', [SeasonV5Controller::class, 'update'])->name('patch');
            Route::delete('/{season}', [SeasonV5Controller::class, 'destroy'])->name('destroy');
            Route::post('/{season}/close', [SeasonV5Controller::class, 'close'])->name('close');
        });
        
        // Debug endpoint to test token and context
        Route::post('/debug-token', function(\Illuminate\Http\Request $request) {
            try {
                $user = auth()->guard('api_v5')->user();
                $token = $user ? $user->currentAccessToken() : null;
                $schoolId = $request->get('context_school_id');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Token debug info',
                    'data' => [
                        'user_id' => $user->id ?? null,
                        'user_email' => $user->email ?? null,
                        'token_id' => $token->id ?? null,
                        'token_name' => $token->name ?? null,
                        'token_context_data' => $token->context_data ?? null,
                        'school_id_from_context' => $schoolId,
                        'request_all' => $request->all(),
                        'middleware_applied' => $request->has('context_school_id')
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debug failed: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('debug-token');
        
        // Debug endpoint to test school context middleware specifically
        Route::post('/debug-school-context', function(\Illuminate\Http\Request $request) {
            try {
                $user = auth()->guard('api_v5')->user();
                $token = $user ? $user->currentAccessToken() : null;
                
                // Manually run the same logic as SchoolContextMiddleware
                $schoolIdFromToken = null;
                if ($token && isset($token->context_data['school_id'])) {
                    $schoolIdFromToken = (int) $token->context_data['school_id'];
                }
                
                $schoolIdFromRequest = $request->header('X-School-ID') 
                    ?? $request->query('school_id') 
                    ?? $request->input('school_id');
                
                return response()->json([
                    'success' => true,
                    'message' => 'School context debug',
                    'data' => [
                        'user_id' => $user->id ?? null,
                        'token_context_data' => $token->context_data ?? null,
                        'school_id_from_token' => $schoolIdFromToken,
                        'school_id_from_request' => $schoolIdFromRequest,
                        'context_school_id_in_request' => $request->get('context_school_id'),
                        'all_request_data' => $request->all(),
                        'middleware_should_set' => $schoolIdFromToken ?: $schoolIdFromRequest
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debug failed: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('debug-school-context');
        
        // Future routes that only require school context
        // Route::apiResource('users', UserV5Controller::class);
        
        // Rutas que requieren contexto de escuela y temporada
        Route::middleware(['season.permission'])->group(function () {
            
            // Dashboard/Welcome routes
            Route::prefix('dashboard')->group(function () {
                Route::get('stats', [DashboardV5Controller::class, 'stats'])->name('v5.dashboard.stats');
                Route::get('recent-activity', [DashboardV5Controller::class, 'recentActivity'])->name('v5.dashboard.recent-activity');
                Route::get('alerts', [DashboardV5Controller::class, 'alerts'])->name('v5.dashboard.alerts');
            });
            
            // Alias para compatibilidad con frontend (welcome -> dashboard)
            Route::prefix('welcome')->group(function () {
                Route::get('stats', [DashboardV5Controller::class, 'stats'])->name('v5.welcome.stats');
                Route::get('recent-activity', [DashboardV5Controller::class, 'recentActivity'])->name('v5.welcome.recent-activity');
                Route::get('alerts', [DashboardV5Controller::class, 'alerts'])->name('v5.welcome.alerts');
            });
            
            // Aquí se agregarían más rutas que requieren contexto completo
            // Route::apiResource('courses', CourseV5Controller::class);
            // Route::apiResource('bookings', BookingV5Controller::class);
            // Route::apiResource('equipment', EquipmentV5Controller::class);
            
        });
        
    });
    
});