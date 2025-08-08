<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V5\Auth\AuthController;
use App\Http\Controllers\Api\V5\Dashboard\DashboardController;
use App\Http\Controllers\Api\V5\Seasons\SeasonController;
use App\V5\Modules\School\Controllers\SchoolV5Controller;
use App\V5\Modules\HealthCheck\Controllers\HealthCheckController;

/*
|--------------------------------------------------------------------------
| API V5 Routes - Unified Structure
|--------------------------------------------------------------------------
|
| RESTful API routes for Boukii V5 system
| Following clean architecture principles with proper middleware layering:
|
| 1. Public routes (no auth)
| 2. Authenticated routes (auth:sanctum)
| 3. School context routes (school.context.v5)
| 4. Season + School context routes (season.permission)
|
*/

// ============================================================================
// GROUP: API V5 BASE - All routes prefixed with /api/v5/
// ============================================================================
Route::middleware(['api'])->prefix('v5')->group(function () {
    
    // ========================================================================
    // PUBLIC ROUTES (no authentication required)
    // ========================================================================
    Route::prefix('auth')->group(function () {
        Route::post('check-user', [AuthController::class, 'checkUser'])->name('v5.auth.check-user');
        Route::post('login', [AuthController::class, 'login'])->name('v5.auth.login');
        Route::post('initial-login', [AuthController::class, 'initialLogin'])->name('v5.auth.initial-login');
    });
    
    // Health check (public)
    Route::get('health-check', [HealthCheckController::class, 'index'])->name('v5.health-check');
    
    // Debug endpoints (to be removed in production)
    Route::post('debug-raw-token', function(\Illuminate\Http\Request $request) {
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
    })->name('v5.debug.raw-token');

    // ========================================================================
    // AUTHENTICATED ROUTES (require auth:sanctum)
    // ========================================================================
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Auth routes for authenticated users
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('v5.auth.logout');
            Route::get('me', [AuthController::class, 'me'])->name('v5.auth.me');
            Route::post('select-school', [AuthController::class, 'selectSchool'])->name('v5.auth.select-school');
            Route::get('permissions', [AuthController::class, 'permissions'])->name('v5.auth.permissions');
        });
        
        // ====================================================================
        // SCHOOL CONTEXT ROUTES (require school.context.v5 middleware)
        // Routes that need school context but NOT necessarily season context
        // ====================================================================
        Route::middleware(['school.context.v5'])->group(function () {
            
            // Schools (manage own school settings)
            Route::prefix('schools')->name('v5.schools.')->group(function () {
                Route::get('current', [SchoolV5Controller::class, 'current'])->name('current');
                Route::put('current', [SchoolV5Controller::class, 'update'])->name('update');
                Route::get('settings', [SchoolV5Controller::class, 'settings'])->name('settings');
                Route::put('settings', [SchoolV5Controller::class, 'updateSettings'])->name('update-settings');
            });
            
            // Seasons (manage seasons for this school - NO season context needed)
            Route::prefix('seasons')->name('v5.seasons.')->group(function () {
                Route::get('/', [SeasonController::class, 'index'])->name('index');
                Route::post('/', [SeasonController::class, 'store'])->name('store');
                Route::get('current', [SeasonController::class, 'current'])->name('current');
                Route::get('{season}', [SeasonController::class, 'show'])->name('show');
                Route::put('{season}', [SeasonController::class, 'update'])->name('update');
                Route::patch('{season}', [SeasonController::class, 'update'])->name('patch');
                Route::delete('{season}', [SeasonController::class, 'destroy'])->name('destroy');
                Route::post('{season}/close', [SeasonController::class, 'close'])->name('close');
            });
            
            // Season selection (requires school context)
            Route::prefix('auth')->group(function () {
                Route::post('select-season', [AuthController::class, 'selectSeason'])->name('v5.auth.select-season');
                Route::post('switch-season', [AuthController::class, 'switchSeason'])->name('v5.auth.switch-season');
            });
            
            // Debug endpoints for school context
            Route::prefix('debug')->group(function () {
                Route::post('token', function(\Illuminate\Http\Request $request) {
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
                })->name('v5.debug.token');
                
                Route::post('school-context', function(\Illuminate\Http\Request $request) {
                    try {
                        $user = auth()->guard('api_v5')->user();
                        $token = $user ? $user->currentAccessToken() : null;
                        
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
                })->name('v5.debug.school-context');
            });
            
            // ================================================================
            // SEASON PERMISSION ROUTES (require season.permission middleware)
            // Routes that need BOTH school AND season context
            // ================================================================
            Route::middleware(['season.permission'])->group(function () {
                
                // ============================================================
                // DASHBOARD - Main overview and analytics
                // ============================================================
                Route::prefix('dashboard')->name('v5.dashboard.')->group(function () {
                    Route::get('stats', [DashboardController::class, 'stats'])->name('stats');
                    Route::get('recent-activity', [DashboardController::class, 'recentActivity'])->name('recent-activity');
                    Route::get('alerts', [DashboardController::class, 'alerts'])->name('alerts');
                    Route::delete('alerts/{alert}', [DashboardController::class, 'dismissAlert'])->name('dismiss-alert');
                    Route::get('daily-sessions', [DashboardController::class, 'dailySessions'])->name('daily-sessions');
                    Route::get('today-reservations', [DashboardController::class, 'todayReservations'])->name('today-reservations');
                });
                
                // ============================================================
                // CORE BUSINESS RESOURCES (full CRUD with season context)
                // ============================================================
                
                // TODO: Implement these controllers following the same pattern
                // Route::apiResource('bookings', BookingController::class)->names('v5.bookings');
                // Route::apiResource('clients', ClientController::class)->names('v5.clients'); 
                // Route::apiResource('monitors', MonitorController::class)->names('v5.monitors');
                // Route::apiResource('courses', CourseController::class)->names('v5.courses');
                // Route::apiResource('equipment', EquipmentController::class)->names('v5.equipment');
                
                // ============================================================
                // SPECIALIZED ENDPOINTS FOR BUSINESS LOGIC
                // ============================================================
                
                // Booking specific routes
                // Route::prefix('bookings')->name('v5.bookings.')->group(function () {
                //     Route::patch('{booking}/status', [BookingController::class, 'updateStatus'])->name('update-status');
                //     Route::post('{booking}/check-in', [BookingController::class, 'checkIn'])->name('check-in');
                //     Route::post('{booking}/complete', [BookingController::class, 'complete'])->name('complete');
                //     Route::post('{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
                //     Route::post('{booking}/payment', [BookingController::class, 'recordPayment'])->name('record-payment');
                //     Route::post('{booking}/refund', [BookingController::class, 'processRefund'])->name('process-refund');
                // });
                
                // Client specific routes  
                // Route::prefix('clients')->name('v5.clients.')->group(function () {
                //     Route::get('{client}/bookings', [ClientController::class, 'bookings'])->name('bookings');
                //     Route::get('{client}/history', [ClientController::class, 'history'])->name('history');
                //     Route::patch('{client}/status', [ClientController::class, 'updateStatus'])->name('update-status');
                // });
                
                // Course specific routes
                // Route::prefix('courses')->name('v5.courses.')->group(function () {
                //     Route::get('{course}/availability', [CourseController::class, 'availability'])->name('availability');
                //     Route::get('{course}/bookings', [CourseController::class, 'bookings'])->name('bookings');
                //     Route::post('{course}/duplicate', [CourseController::class, 'duplicate'])->name('duplicate');
                // });
                
                // Monitor specific routes
                // Route::prefix('monitors')->name('v5.monitors.')->group(function () {
                //     Route::get('{monitor}/schedule', [MonitorController::class, 'schedule'])->name('schedule');
                //     Route::get('{monitor}/performance', [MonitorController::class, 'performance'])->name('performance');
                //     Route::patch('{monitor}/availability', [MonitorController::class, 'updateAvailability'])->name('update-availability');
                // });
                
                // ============================================================
                // ANALYTICS & REPORTING
                // ============================================================
                // Route::prefix('analytics')->name('v5.analytics.')->group(function () {
                //     Route::get('overview', [AnalyticsController::class, 'overview'])->name('overview');
                //     Route::get('revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
                //     Route::get('bookings', [AnalyticsController::class, 'bookings'])->name('bookings');
                //     Route::get('clients', [AnalyticsController::class, 'clients'])->name('clients');
                //     Route::get('monitors', [AnalyticsController::class, 'monitors'])->name('monitors');
                //     Route::get('courses', [AnalyticsController::class, 'courses'])->name('courses');
                // });
                
                // ============================================================
                // OPERATIONS & PLANNING
                // ============================================================
                // Route::prefix('operations')->name('v5.operations.')->group(function () {
                //     Route::get('capacity-planning', [OperationsController::class, 'capacityPlanning'])->name('capacity-planning');
                //     Route::get('resource-allocation', [OperationsController::class, 'resourceAllocation'])->name('resource-allocation');
                //     Route::get('schedule-conflicts', [OperationsController::class, 'scheduleConflicts'])->name('schedule-conflicts');
                // });
                
                // ============================================================
                // COMMUNICATION & NOTIFICATIONS
                // ============================================================  
                // Route::prefix('communications')->name('v5.communications.')->group(function () {
                //     Route::get('/', [CommunicationController::class, 'index'])->name('index');
                //     Route::post('send-bulk', [CommunicationController::class, 'sendBulk'])->name('send-bulk');
                //     Route::get('templates', [CommunicationController::class, 'templates'])->name('templates');
                //     Route::post('templates', [CommunicationController::class, 'createTemplate'])->name('create-template');
                // });
                
            });
        });
    });
});

// ============================================================================
// LEGACY COMPATIBILITY ROUTES (to be deprecated)
// ============================================================================
Route::middleware(['api'])->prefix('v5')->group(function () {
    Route::middleware(['auth:sanctum', 'school.context.v5', 'season.permission'])->group(function () {
        // Alias for backward compatibility with frontend - DEPRECATED
        Route::prefix('welcome')->name('v5.welcome.')->group(function () {
            Route::get('stats', [DashboardController::class, 'stats'])->name('stats');
            Route::get('recent-activity', [DashboardController::class, 'recentActivity'])->name('recent-activity'); 
            Route::get('alerts', [DashboardController::class, 'alerts'])->name('alerts');
        });
    });
});