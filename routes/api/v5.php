<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V5\AuthController;
use App\Http\Controllers\Api\V5\SeasonController;
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
    Route::post('login', [AuthController::class, 'login'])->name('v5.auth.login');
    Route::post('initial-login', [AuthController::class, 'initialLogin'])->name('v5.auth.initial-login');
    Route::post('check-user', [AuthController::class, 'checkUser'])->name('v5.auth.check-user');
});

// Debug endpoint outside middleware to check token without school context requirement
Route::middleware(['auth:sanctum'])->post('/debug-raw-token', function(\Illuminate\Http\Request $request) {
    try {
        $user = auth()->guard('sanctum')->user();
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
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Rutas de autenticación
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('v5.auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('v5.auth.me');
        Route::get('debug-token', function() {
            $user = Auth::guard('sanctum')->user();
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
        Route::post('select-school', [AuthController::class, 'selectSchool'])->name('v5.auth.select-school');
        Route::post('select-season', [AuthController::class, 'selectSeason'])->name('v5.auth.select-season');
    });
    
    // ✅ MOVED: Rutas de temporadas FUERA del grupo context.middleware
    // Rutas de temporadas - TODAS solo requieren contexto de escuela (no temporada)
    // No tiene sentido requerir temporada para gestionar temporadas
    Route::middleware(['school.context.middleware', 'role.permission.middleware:seasons.manage'])->prefix('seasons')->name('seasons.')->group(function () {
            // Listado y creación
            Route::get('/', [SeasonController::class, 'index'])->name('index');
            Route::post('/', [SeasonController::class, 'store'])->name('store');
            
            // Temporada actual (puede no existir, por eso no necesita season context)
            Route::get('/current', [SeasonController::class, 'current'])->name('current');
            
            // Operaciones específicas de temporada
            Route::get('/{season}', [SeasonController::class, 'show'])->name('show');
            Route::put('/{season}', [SeasonController::class, 'update'])->name('update');
            Route::patch('/{season}', [SeasonController::class, 'update'])->name('patch');
            Route::delete('/{season}', [SeasonController::class, 'destroy'])->name('destroy');
            
            // Acciones de estado para temporadas
            Route::post('/{season}/activate', [SeasonController::class, 'activate'])->name('activate');
            Route::post('/{season}/deactivate', [SeasonController::class, 'deactivate'])->name('deactivate');
            Route::post('/{season}/close', [SeasonController::class, 'close'])->name('close');
            Route::post('/{season}/reopen', [SeasonController::class, 'reopen'])->name('reopen');
        });

    // Rutas que requieren contexto completo (escuela y temporada)
    Route::middleware(['context.middleware'])->group(function () {

        // Debug endpoint to test token and context
        Route::post('/debug-token', function(\Illuminate\Http\Request $request) {
            try {
                $user = auth()->guard('sanctum')->user();
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

        Route::middleware(['role.permission.middleware:season.analytics'])->group(function () {
            // Dashboard/Welcome routes
            Route::prefix('dashboard')->group(function () {
                Route::get('stats', [DashboardV5Controller::class, 'stats'])->name('v5.dashboard.stats');
                Route::get('revenue', [DashboardV5Controller::class, 'revenue'])->name('v5.dashboard.revenue');
                Route::get('bookings', [DashboardV5Controller::class, 'bookings'])->name('v5.dashboard.bookings');
                Route::get('recent-activity', [DashboardV5Controller::class, 'recentActivity'])->name('v5.dashboard.recent-activity');
                Route::get('alerts', [DashboardV5Controller::class, 'alerts'])->name('v5.dashboard.alerts');
                Route::post('alerts/{alertId}/dismiss', [DashboardV5Controller::class, 'dismissAlert'])->name('v5.dashboard.alerts.dismiss');
                Route::get('daily-sessions', [DashboardV5Controller::class, 'dailySessions'])->name('v5.dashboard.daily-sessions');
                Route::get('today-reservations', [DashboardV5Controller::class, 'todayReservations'])->name('v5.dashboard.today-reservations');
            });

            // Alias para compatibilidad con frontend (welcome -> dashboard)
            Route::prefix('welcome')->group(function () {
                Route::get('stats', [DashboardV5Controller::class, 'stats'])->name('v5.welcome.stats');
                Route::get('revenue', [DashboardV5Controller::class, 'revenue'])->name('v5.welcome.revenue');
                Route::get('bookings', [DashboardV5Controller::class, 'bookings'])->name('v5.welcome.bookings');
                Route::get('recent-activity', [DashboardV5Controller::class, 'recentActivity'])->name('v5.welcome.recent-activity');
                Route::get('alerts', [DashboardV5Controller::class, 'alerts'])->name('v5.welcome.alerts');
                Route::post('alerts/{alertId}/dismiss', [DashboardV5Controller::class, 'dismissAlert'])->name('v5.welcome.alerts.dismiss');
                Route::get('daily-sessions', [DashboardV5Controller::class, 'dailySessions'])->name('v5.welcome.daily-sessions');
                Route::get('today-reservations', [DashboardV5Controller::class, 'todayReservations'])->name('v5.welcome.today-reservations');
            });

            // Aquí se agregarían más rutas que requieren contexto completo
            // Route::apiResource('courses', CourseV5Controller::class);
            // Route::apiResource('bookings', BookingV5Controller::class);
            // Route::apiResource('equipment', EquipmentV5Controller::class);
        });

    });

});
