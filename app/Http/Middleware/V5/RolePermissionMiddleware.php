<?php

namespace App\Http\Middleware\V5;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\School;
use App\Models\Season;

/**
 * Role and Permission Middleware for V5 System
 * 
 * Handles granular permissions at three levels:
 * 1. Global/System level (superadmin)
 * 2. School level (school admin, manager, staff)
 * 3. Season level (season admin, manager, viewer)
 * 
 * Permission Flow:
 * - Global permissions override school permissions  
 * - School permissions override season permissions
 * - Season permissions are the most granular
 */
class RolePermissionMiddleware
{
    // ============================================================================
    // PERMISSION CONSTANTS
    // ============================================================================
    
    // Global permissions
    const GLOBAL_ADMIN = 'global.admin';
    const GLOBAL_SUPPORT = 'global.support';
    
    // School-level permissions
    const SCHOOL_ADMIN = 'school.admin';
    const SCHOOL_MANAGER = 'school.manager';
    const SCHOOL_STAFF = 'school.staff';
    const SCHOOL_VIEW = 'school.view';
    const SCHOOL_SETTINGS = 'school.settings';
    const SCHOOL_USERS = 'school.users';
    const SCHOOL_BILLING = 'school.billing';
    
    // Season-level permissions
    const SEASON_ADMIN = 'season.admin';
    const SEASON_MANAGER = 'season.manager';
    const SEASON_VIEW = 'season.view';
    const SEASON_BOOKINGS = 'season.bookings';
    const SEASON_CLIENTS = 'season.clients';
    const SEASON_MONITORS = 'season.monitors';
    const SEASON_COURSES = 'season.courses';
    const SEASON_ANALYTICS = 'season.analytics';
    const SEASON_EQUIPMENT = 'season.equipment';
    
    // Resource-specific permissions
    const BOOKING_CREATE = 'booking.create';
    const BOOKING_READ = 'booking.read';
    const BOOKING_UPDATE = 'booking.update';
    const BOOKING_DELETE = 'booking.delete';
    const BOOKING_PAYMENT = 'booking.payment';
    
    const CLIENT_CREATE = 'client.create';
    const CLIENT_READ = 'client.read';
    const CLIENT_UPDATE = 'client.update';
    const CLIENT_DELETE = 'client.delete';
    const CLIENT_EXPORT = 'client.export';
    
    const MONITOR_CREATE = 'monitor.create';
    const MONITOR_READ = 'monitor.read';
    const MONITOR_UPDATE = 'monitor.update';
    const MONITOR_DELETE = 'monitor.delete';
    const MONITOR_SCHEDULE = 'monitor.schedule';
    
    const COURSE_CREATE = 'course.create';
    const COURSE_READ = 'course.read';
    const COURSE_UPDATE = 'course.update';
    const COURSE_DELETE = 'course.delete';
    const COURSE_PRICING = 'course.pricing';

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        try {
            $user = Auth::guard('api_v5')->user();
            
            if (!$user) {
                return $this->unauthorizedResponse('Authentication required');
            }

            // Get context from request (set by previous middlewares)
            $schoolId = $request->get('context_school_id');
            $seasonId = $this->getSeasonIdFromRequest($request);
            
            Log::info('Permission check started', [
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'season_id' => $seasonId,
                'required_permissions' => $permissions,
                'route' => $request->route()?->getName()
            ]);

            // Check permissions for each required permission
            foreach ($permissions as $permission) {
                if (!$this->userHasPermission($user, $permission, $schoolId, $seasonId)) {
                    Log::warning('Permission denied', [
                        'user_id' => $user->id,
                        'permission' => $permission,
                        'school_id' => $schoolId,
                        'season_id' => $seasonId
                    ]);
                    
                    return $this->forbiddenResponse("Insufficient permissions: {$permission}");
                }
            }

            // Add user permissions to request for controllers to use
            $request->merge([
                'user_permissions' => $this->getUserPermissions($user, $schoolId, $seasonId)
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Permission middleware error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Permission check failed', 500);
        }
    }

    // ============================================================================
    // PERMISSION CHECKING METHODS
    // ============================================================================

    /**
     * Check if user has specific permission
     */
    private function userHasPermission(User $user, string $permission, ?int $schoolId = null, ?int $seasonId = null): bool
    {
        // 1. Check global permissions first (override everything)
        if ($this->userHasGlobalPermission($user, $permission)) {
            return true;
        }

        // 2. Check school-level permissions
        if ($schoolId && $this->userHasSchoolPermission($user, $permission, $schoolId)) {
            return true;
        }

        // 3. Check season-level permissions (most granular)
        if ($schoolId && $seasonId && $this->userHasSeasonPermission($user, $permission, $schoolId, $seasonId)) {
            return true;
        }

        // 4. Check if permission can be inherited from role-based permissions
        return $this->userHasInheritedPermission($user, $permission, $schoolId, $seasonId);
    }

    /**
     * Check global permissions
     */
    private function userHasGlobalPermission(User $user, string $permission): bool
    {
        // Check if user is superadmin
        if ($user->hasRole('superadmin')) {
            return true; // Superadmin has all permissions
        }

        // Check specific global permissions
        $globalPermissions = [
            self::GLOBAL_ADMIN => ['superadmin'],
            self::GLOBAL_SUPPORT => ['superadmin', 'support']
        ];

        if (isset($globalPermissions[$permission])) {
            return $user->hasAnyRole($globalPermissions[$permission]);
        }

        return false;
    }

    /**
     * Check school-level permissions
     */
    private function userHasSchoolPermission(User $user, string $permission, int $schoolId): bool
    {
        // Get user's role in this school
        $schoolUser = DB::table('school_users')
            ->where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->first();

        if (!$schoolUser) {
            // Check if user is owner of the school
            $school = School::find($schoolId);
            if ($school && $school->owner_id === $user->id) {
                return true; // Owner has all school permissions
            }
            return false;
        }

        // Define role-based permissions for schools
        $schoolPermissions = [
            'admin' => [
                self::SCHOOL_ADMIN,
                self::SCHOOL_MANAGER,
                self::SCHOOL_STAFF,
                self::SCHOOL_VIEW,
                self::SCHOOL_SETTINGS,
                self::SCHOOL_USERS,
                self::SCHOOL_BILLING,
                // Admin also gets all season permissions
                self::SEASON_ADMIN,
                self::SEASON_MANAGER,
                self::SEASON_VIEW,
                self::SEASON_BOOKINGS,
                self::SEASON_CLIENTS,
                self::SEASON_MONITORS,
                self::SEASON_COURSES,
                self::SEASON_ANALYTICS,
                self::SEASON_EQUIPMENT
            ],
            'manager' => [
                self::SCHOOL_MANAGER,
                self::SCHOOL_STAFF,
                self::SCHOOL_VIEW,
                self::SEASON_MANAGER,
                self::SEASON_VIEW,
                self::SEASON_BOOKINGS,
                self::SEASON_CLIENTS,
                self::SEASON_MONITORS,
                self::SEASON_COURSES,
                self::SEASON_ANALYTICS
            ],
            'staff' => [
                self::SCHOOL_STAFF,
                self::SCHOOL_VIEW,
                self::SEASON_VIEW,
                self::SEASON_BOOKINGS,
                self::SEASON_CLIENTS,
                self::SEASON_MONITORS
            ]
        ];

        $userRole = $schoolUser->role ?? 'staff';
        
        return in_array($permission, $schoolPermissions[$userRole] ?? []);
    }

    /**
     * Check season-level permissions
     */
    private function userHasSeasonPermission(User $user, string $permission, int $schoolId, int $seasonId): bool
    {
        // Get user's specific permissions for this season
        $seasonRole = DB::table('user_season_roles')
            ->where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->where('season_id', $seasonId)
            ->where('is_active', true)
            ->first();

        if (!$seasonRole) {
            return false;
        }

        // Define season-specific role permissions
        $seasonPermissions = [
            'admin' => [
                self::SEASON_ADMIN,
                self::SEASON_MANAGER,
                self::SEASON_VIEW,
                self::SEASON_BOOKINGS,
                self::SEASON_CLIENTS,
                self::SEASON_MONITORS,
                self::SEASON_COURSES,
                self::SEASON_ANALYTICS,
                self::SEASON_EQUIPMENT,
                // All CRUD permissions
                self::BOOKING_CREATE, self::BOOKING_READ, self::BOOKING_UPDATE, self::BOOKING_DELETE, self::BOOKING_PAYMENT,
                self::CLIENT_CREATE, self::CLIENT_READ, self::CLIENT_UPDATE, self::CLIENT_DELETE, self::CLIENT_EXPORT,
                self::MONITOR_CREATE, self::MONITOR_READ, self::MONITOR_UPDATE, self::MONITOR_DELETE, self::MONITOR_SCHEDULE,
                self::COURSE_CREATE, self::COURSE_READ, self::COURSE_UPDATE, self::COURSE_DELETE, self::COURSE_PRICING
            ],
            'manager' => [
                self::SEASON_MANAGER,
                self::SEASON_VIEW,
                self::SEASON_BOOKINGS,
                self::SEASON_CLIENTS,
                self::SEASON_MONITORS,
                self::SEASON_COURSES,
                self::SEASON_ANALYTICS,
                // Most CRUD permissions except delete
                self::BOOKING_CREATE, self::BOOKING_READ, self::BOOKING_UPDATE, self::BOOKING_PAYMENT,
                self::CLIENT_CREATE, self::CLIENT_READ, self::CLIENT_UPDATE, self::CLIENT_EXPORT,
                self::MONITOR_CREATE, self::MONITOR_READ, self::MONITOR_UPDATE, self::MONITOR_SCHEDULE,
                self::COURSE_CREATE, self::COURSE_READ, self::COURSE_UPDATE, self::COURSE_PRICING
            ],
            'viewer' => [
                self::SEASON_VIEW,
                // Read-only permissions
                self::BOOKING_READ,
                self::CLIENT_READ,
                self::MONITOR_READ,
                self::COURSE_READ
            ]
        ];

        $rolePermissions = $seasonRole->role ?? 'viewer';
        
        return in_array($permission, $seasonPermissions[$rolePermissions] ?? []);
    }

    /**
     * Check inherited permissions based on user's roles
     */
    private function userHasInheritedPermission(User $user, string $permission, ?int $schoolId = null, ?int $seasonId = null): bool
    {
        // Define inheritance rules - some permissions are automatically granted based on context
        
        // If user has school admin, they get most season permissions
        if ($schoolId && $this->userHasSchoolPermission($user, self::SCHOOL_ADMIN, $schoolId)) {
            $inheritedFromSchoolAdmin = [
                self::SEASON_VIEW,
                self::SEASON_BOOKINGS,
                self::SEASON_CLIENTS,
                self::SEASON_MONITORS,
                self::SEASON_COURSES,
                self::SEASON_ANALYTICS
            ];
            
            if (in_array($permission, $inheritedFromSchoolAdmin)) {
                return true;
            }
        }

        // If user has season manager, they get read permissions for all resources
        if ($schoolId && $seasonId && $this->userHasSeasonPermission($user, self::SEASON_MANAGER, $schoolId, $seasonId)) {
            $inheritedFromSeasonManager = [
                self::BOOKING_READ,
                self::CLIENT_READ,
                self::MONITOR_READ,
                self::COURSE_READ
            ];
            
            if (in_array($permission, $inheritedFromSeasonManager)) {
                return true;
            }
        }

        return false;
    }

    // ============================================================================
    // UTILITY METHODS
    // ============================================================================

    /**
     * Get season ID from request
     */
    private function getSeasonIdFromRequest(Request $request): ?int
    {
        // Context middleware injects the validated season id
        $seasonId = $request->get('context_season_id');
        if ($seasonId) {
            return (int) $seasonId;
        }

        // Fallbacks for legacy headers/params
        $seasonId = $request->header('X-Season-ID');
        if ($seasonId) {
            return (int) $seasonId;
        }

        $seasonId = $request->get('season_id');
        if ($seasonId) {
            return (int) $seasonId;
        }

        $user = auth()->user();
        if ($user && $user->currentAccessToken()) {
            $contextData = $user->currentAccessToken()->context_data;
            if (isset($contextData['season_id'])) {
                return (int) $contextData['season_id'];
            }
        }

        return null;
    }

    /**
     * Get all permissions for user in current context
     */
    private function getUserPermissions(User $user, ?int $schoolId = null, ?int $seasonId = null): array
    {
        $permissions = [];

        // Collect all permission constants
        $allPermissions = [
            // Global
            self::GLOBAL_ADMIN, self::GLOBAL_SUPPORT,
            // School
            self::SCHOOL_ADMIN, self::SCHOOL_MANAGER, self::SCHOOL_STAFF, self::SCHOOL_VIEW, 
            self::SCHOOL_SETTINGS, self::SCHOOL_USERS, self::SCHOOL_BILLING,
            // Season
            self::SEASON_ADMIN, self::SEASON_MANAGER, self::SEASON_VIEW, self::SEASON_BOOKINGS, 
            self::SEASON_CLIENTS, self::SEASON_MONITORS, self::SEASON_COURSES, self::SEASON_ANALYTICS, self::SEASON_EQUIPMENT,
            // Resources
            self::BOOKING_CREATE, self::BOOKING_READ, self::BOOKING_UPDATE, self::BOOKING_DELETE, self::BOOKING_PAYMENT,
            self::CLIENT_CREATE, self::CLIENT_READ, self::CLIENT_UPDATE, self::CLIENT_DELETE, self::CLIENT_EXPORT,
            self::MONITOR_CREATE, self::MONITOR_READ, self::MONITOR_UPDATE, self::MONITOR_DELETE, self::MONITOR_SCHEDULE,
            self::COURSE_CREATE, self::COURSE_READ, self::COURSE_UPDATE, self::COURSE_DELETE, self::COURSE_PRICING
        ];

        // Check each permission
        foreach ($allPermissions as $permission) {
            if ($this->userHasPermission($user, $permission, $schoolId, $seasonId)) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }

    // ============================================================================
    // RESPONSE HELPERS
    // ============================================================================

    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED'
        ], 401);
    }

    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'FORBIDDEN'
        ], 403);
    }

    private function errorResponse(string $message, int $status = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'PERMISSION_ERROR'
        ], $status);
    }
}