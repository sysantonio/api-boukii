<?php

namespace App\Http\Controllers\API\V5;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V5\LoginV5Request;
use App\Http\Requests\API\V5\InitialLoginV5Request;
use App\Http\Requests\API\V5\CheckUserV5Request;
use App\Http\Requests\API\V5\SelectSchoolV5Request;
use App\Http\Requests\API\V5\SelectSeasonV5Request;
use App\Models\User;
use App\Models\School;
use App\Models\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="V5 Authentication",
 *     description="Authentication endpoints for Boukii V5 system"
 * )
 */
class AuthV5Controller extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v5/auth/login",
     *     summary="Login school administrator",
     *     tags={"V5 Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "school_id", "season_id"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@school.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="school_id", type="integer", example=1),
     *             @OA\Property(property="season_id", type="integer", example=5),
     *             @OA\Property(property="remember_me", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_at", type="string", format="datetime"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="role", type="string"),
     *                     @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *                 ),
     *                 @OA\Property(
     *                     property="school",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="season",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="is_active", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(property="error_code", type="string", example="INVALID_CREDENTIALS")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Access denied"),
     *             @OA\Property(property="error_code", type="string", example="ACCESS_DENIED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(LoginV5Request $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $schoolId = $request->input('school_id');
            $seasonId = $request->input('season_id');
            $rememberMe = $request->boolean('remember_me', false);

            // 1. Verificar credenciales del usuario
            $user = User::where('email', $credentials['email'])
                ->where('active', true)
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return $this->respondWithError(
                    'Invalid credentials',
                    401,
                    'INVALID_CREDENTIALS'
                );
            }

            // 2. Verificar que la escuela existe y está activa
            $school = School::where('id', $schoolId)
                ->where('active', true)
                ->first();

            if (!$school) {
                return $this->respondWithError(
                    'School not found or inactive',
                    403,
                    'SCHOOL_NOT_FOUND'
                );
            }

            // 3. Verificar que el usuario tiene acceso a esta escuela
            if (!$this->userHasAccessToSchool($user, $school)) {
                return $this->respondWithError(
                    'User does not have access to this school',
                    403,
                    'SCHOOL_ACCESS_DENIED'
                );
            }

            // 4. Verificar que la temporada existe y pertenece a la escuela
            $season = Season::where('id', $seasonId)
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->first();

            if (!$season) {
                return $this->respondWithError(
                    'Season not found or not associated with school',
                    403,
                    'SEASON_NOT_FOUND'
                );
            }

            // 5. Verificar permisos específicos del usuario para administrar la escuela
            if (!$this->userCanAdministerSchool($user, $school)) {
                return $this->respondWithError(
                    'Insufficient permissions to access this school',
                    403,
                    'INSUFFICIENT_PERMISSIONS'
                );
            }

            // 6. Revocar tokens existentes si no es "remember me"
            if (!$rememberMe) {
                $user->tokens()->delete();
            }

            // 7. Crear nuevo token con información de contexto
            $tokenName = "auth_v5_{$school->slug}_{$season->id}";
            $expiresAt = $rememberMe ? now()->addDays(30) : now()->addDays(1);

            $token = $user->createToken($tokenName, [], $expiresAt);

            // 8. Agregar información de contexto al token
            $token->accessToken->update([
                'school_id' => $school->id,
                'season_id' => $season->id,
                'context_data' => json_encode([
                    'school_id' => $school->id,
                    'school_slug' => $school->slug,
                    'season_id' => $season->id,
                    'season_name' => $season->name,
                    'login_at' => now()->toISOString(),
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip()
                ])
            ]);

            // 9. Registrar login exitoso
            $this->logSuccessfulLogin($user, $school, $season, $request);

            // 10. Preparar respuesta
            $response = [
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'role' => $this->getUserPrimaryRole($user),
                    'permissions' => $this->getUserPermissions($user, $school),
                    'avatar_url' => $user->avatar_url,
                    'last_login_at' => $user->last_login_at?->toISOString(),
                ],
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slug' => $school->slug,
                    'logo' => $school->logo,
                    'timezone' => $school->timezone ?? 'Europe/Madrid',
                    'currency' => $school->currency ?? 'EUR',
                ],
                'season' => [
                    'id' => $season->id,
                    'name' => $season->name,
                    'start_date' => $season->start_date,
                    'end_date' => $season->end_date,
                    'is_active' => $season->is_active,
                    'is_current' => $season->is_current ?? false,
                ]
            ];

            // 11. Actualizar último login del usuario
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            return $this->respondWithData($response, 'Login successful');

        } catch (\Exception $e) {
            \Log::error('V5 Login error', [
                'email' => $request->input('email'),
                'school_id' => $request->input('school_id'),
                'season_id' => $request->input('season_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                'An error occurred during login',
                500,
                'LOGIN_ERROR'
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v5/auth/logout",
     *     summary="Logout user",
     *     tags={"V5 Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout successful")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        $user = Auth::guard('api_v5')->user();

        if ($user) {
            // Revocar token actual
            $user->currentAccessToken()->delete();

            // Log logout
            \Log::info('V5 User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'logout_at' => now()->toISOString()
            ]);
        }

        return $this->respondWithData([], 'Logout successful');
    }

    /**
     * @OA\Get(
     *     path="/api/v5/auth/me",
     *     summary="Get current user info",
     *     tags={"V5 Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserContext")
     *         )
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        $user = Auth::guard('api_v5')->user();
        $token = $user->currentAccessToken();
        $contextData = json_decode($token->context_data ?? '{}', true);

        $school = School::find($token->school_id);
        $season = Season::find($token->season_id);

        $response = [
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $this->getUserPrimaryRole($user),
                'permissions' => $this->getUserPermissions($user, $school),
                'avatar_url' => $user->avatar_url,
            ],
            'school' => $school ? [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'logo' => $school->logo,
                'timezone' => $school->timezone ?? 'Europe/Madrid',
                'currency' => $school->currency ?? 'EUR',
            ] : null,
            'season' => $season ? [
                'id' => $season->id,
                'name' => $season->name,
                'start_date' => $season->start_date,
                'end_date' => $season->end_date,
                'is_active' => $season->is_active,
                'is_current' => $season->is_current ?? false,
            ] : null,
            'context' => $contextData,
            'token_expires_at' => $token->expires_at?->toISOString(),
        ];

        return $this->respondWithData($response);
    }

    /**
     * Verificar si el usuario tiene acceso a la escuela
     */
    private function userHasAccessToSchool(User $user, School $school): bool
    {
        // Superadmin tiene acceso a todas las escuelas
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Verificar relación directa user-school
        if ($user->schools()->where('schools.id', $school->id)->exists()) {
            return true;
        }

        // TODO: Verificar si es propietario de la escuela (campo owner_id no existe actualmente)
        // if ($school->owner_id === $user->id) {
        //     return true;
        // }

        return false;
    }

    /**
     * Verificar si el usuario puede administrar la escuela
     */
    private function userCanAdministerSchool(User $user, School $school): bool
    {
        // Superadmin puede administrar cualquier escuela
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Verificar rol de school_admin para esta escuela específica
        if ($user->hasRole('school_admin') && $this->userHasAccessToSchool($user, $school)) {
            return true;
        }

        // TODO: Verificar si es propietario (campo owner_id no existe actualmente)
        // if ($school->owner_id === $user->id) {
        //     return true;
        // }

        return false;
    }

    /**
     * Obtener el rol principal del usuario
     */
    private function getUserPrimaryRole(User $user): string
    {
        $roles = $user->getRoleNames();

        // Orden de prioridad de roles
        $rolePriority = ['superadmin', 'school_admin', 'monitor', 'client'];

        foreach ($rolePriority as $role) {
            if ($roles->contains($role)) {
                return $role;
            }
        }

        return 'client'; // rol por defecto
    }

    /**
     * Obtener permisos del usuario para la escuela
     */
    private function getUserPermissions(User $user, School $school): array
    {
        $permissions = [];

        if ($user->hasRole('superadmin')) {
            $permissions = [
                'manage_all_schools',
                'manage_users',
                'manage_seasons',
                'manage_courses',
                'manage_bookings',
                'manage_equipment',
                'view_analytics',
                'manage_settings'
            ];
        } elseif ($user->hasRole('school_admin')) {
            $permissions = [
                'manage_school_users',
                'manage_seasons',
                'manage_courses',
                'manage_bookings',
                'manage_equipment',
                'view_school_analytics',
                'manage_school_settings'
            ];
        } elseif ($user->hasRole('monitor')) {
            $permissions = [
                'view_courses',
                'manage_bookings',
                'checkout_equipment',
                'checkin_equipment'
            ];
        }

        return $permissions;
    }

    /**
     * Registrar login exitoso para auditoría
     */
    private function logSuccessfulLogin(User $user, School $school, Season $season, $request): void
    {
        \Log::info('V5 Login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'school_id' => $school->id,
            'school_name' => $school->name,
            'season_id' => $season->id,
            'season_name' => $season->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'login_at' => now()->toISOString()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v5/auth/check-user",
     *     summary="Check user credentials and get available schools",
     *     tags={"V5 Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@school.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User credentials valid - shows available schools",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User authenticated successfully"),
     *             @OA\Property(property="requires_school_selection", type="boolean", example=true),
     *             @OA\Property(property="available_schools", type="array", @OA\Items(ref="#/components/schemas/School")),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(property="error_code", type="string", example="INVALID_CREDENTIALS")
     *         )
     *     )
     * )
     */
    public function checkUser(CheckUserV5Request $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);

            // 1. Verificar credenciales del usuario
            $user = User::where('email', $credentials['email'])
                ->where('active', true)
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return $this->respondWithError(
                    'Invalid credentials',
                    401,
                    'INVALID_CREDENTIALS'
                );
            }

            // 2. Obtener escuelas disponibles del usuario
            $availableSchools = $this->getUserAvailableSchools($user);

            if ($availableSchools->isEmpty()) {
                return $this->respondWithError(
                    'User has no access to any schools',
                    403,
                    'NO_SCHOOLS_ACCESS'
                );
            }

            // 3. Si solo tiene una escuela, proceder con login automático
            if ($availableSchools->count() === 1) {
                $school = $availableSchools->first();

                // Proceder con initial login automático para esta escuela
                return $this->proceedWithSchoolLogin($user, $school, $request->boolean('remember_me', false), $request);
            }

            // 4. Si tiene múltiples escuelas, devolver lista para selección
            $schoolsData = $availableSchools->map(function($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slug' => $school->slug,
                    'logo' => $school->logo,
                    'address' => $school->address,
                    'active' => $school->active,
                ];
            });

            $response = [
                'requires_school_selection' => true,
                'available_schools' => $schoolsData,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'role' => $this->getUserPrimaryRole($user),
                    'avatar_url' => $user->avatar_url,
                ],
                'temp_token' => $this->createTempUserToken($user)
            ];

            return $this->respondWithData($response, 'User authenticated - school selection required');

        } catch (\Exception $e) {
            \Log::error('V5 Check User error', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                'An error occurred during authentication',
                500,
                'AUTH_ERROR'
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v5/auth/initial-login",
     *     summary="Initial login without season selection",
     *     tags={"V5 Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "school_id"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@school.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="school_id", type="integer", example=1),
     *             @OA\Property(property="remember_me", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful - may require season selection",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="requires_season_selection", type="boolean", example=true),
     *             @OA\Property(property="available_seasons", type="array", @OA\Items(ref="#/components/schemas/Season")),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function initialLogin(InitialLoginV5Request $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $schoolId = $request->input('school_id');
            $rememberMe = $request->boolean('remember_me', false);

            // 1. Verificar credenciales del usuario
            $user = User::where('email', $credentials['email'])
                ->where('active', true)
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return $this->respondWithError(
                    'Invalid credentials',
                    401,
                    'INVALID_CREDENTIALS'
                );
            }

            // 2. Verificar que la escuela existe y está activa
            $school = School::where('id', $schoolId)
                ->where('active', true)
                ->first();

            if (!$school) {
                return $this->respondWithError(
                    'School not found or inactive',
                    403,
                    'SCHOOL_NOT_FOUND'
                );
            }

            // 3. Verificar que el usuario tiene acceso a esta escuela
            if (!$this->userHasAccessToSchool($user, $school)) {
                return $this->respondWithError(
                    'User does not have access to this school',
                    403,
                    'SCHOOL_ACCESS_DENIED'
                );
            }

            // 4. Verificar permisos específicos del usuario para administrar la escuela
            if (!$this->userCanAdministerSchool($user, $school)) {
                return $this->respondWithError(
                    'Insufficient permissions to access this school',
                    403,
                    'INSUFFICIENT_PERMISSIONS'
                );
            }

            // 5. Obtener temporadas disponibles para la escuela
            $availableSeasons = Season::where('school_id', $schoolId)
                ->where('is_active', true)
                ->orderBy('is_current', 'desc')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function($season) {
                    return [
                        'id' => $season->id,
                        'name' => $season->name,
                        'start_date' => $season->start_date,
                        'end_date' => $season->end_date,
                        'is_active' => $season->is_active,
                        'is_current' => $season->is_current ?? false,
                        'is_historical' => $season->is_historical ?? false,
                    ];
                });

            // 6. Verificar si el usuario tiene una temporada asignada actualmente
            $currentSeason = $this->getUserCurrentSeason($user, $schoolId);

            // 7. Crear token temporal sin temporada específica
            $tokenName = "auth_v5_{$school->slug}_temp";
            $expiresAt = $rememberMe ? now()->addDays(30) : now()->addHours(2); // 2 horas para selección

            // Revocar tokens temporales anteriores
            $user->tokens()->where('name', 'LIKE', "%_temp")->delete();

            $token = $user->createToken($tokenName, [], $expiresAt);

            // 8. Agregar información de contexto al token (solo escuela, sin temporada)
            $token->accessToken->update([
                'school_id' => $school->id,
                'season_id' => null, // Sin temporada hasta que se seleccione
                'context_data' => json_encode([
                    'school_id' => $school->id,
                    'school_slug' => $school->slug,
                    'login_at' => now()->toISOString(),
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                    'is_temporary' => true
                ])
            ]);

            // 9. Preparar respuesta
            $response = [
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
                'requires_season_selection' => true,
                'available_seasons' => $availableSeasons,
                'current_season' => $currentSeason ? [
                    'id' => $currentSeason->id,
                    'name' => $currentSeason->name,
                    'start_date' => $currentSeason->start_date,
                    'end_date' => $currentSeason->end_date,
                    'is_active' => $currentSeason->is_active,
                    'is_current' => $currentSeason->is_current ?? false,
                ] : null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'role' => $this->getUserPrimaryRole($user),
                    'permissions' => $this->getUserPermissions($user, $school),
                    'avatar_url' => $user->avatar_url,
                ],
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slug' => $school->slug,
                    'logo' => $school->logo,
                    'timezone' => $school->timezone ?? 'Europe/Madrid',
                    'currency' => $school->currency ?? 'EUR',
                ]
            ];

            // 10. Si hay temporada actual válida, hacer login completo automáticamente
            if ($currentSeason && $this->isSeasonCurrentlyValid($currentSeason)) {
                return $this->completeLoginWithSeason($user, $school, $currentSeason, $rememberMe, $request);
            }

            // 11. Actualizar último login del usuario
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            return $this->respondWithData($response, 'Login successful - season selection required');

        } catch (\Exception $e) {
            \Log::error('V5 Initial Login error', [
                'email' => $request->input('email'),
                'school_id' => $request->input('school_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                'An error occurred during login',
                500,
                'LOGIN_ERROR'
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v5/auth/select-school",
     *     summary="Select school after user check",
     *     tags={"V5 Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"school_id"},
     *             @OA\Property(property="school_id", type="integer", example=2),
     *             @OA\Property(property="remember_me", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="School selected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="School selected successfully"),
     *             @OA\Property(property="requires_season_selection", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/LoginResponse")
     *         )
     *     )
     * )
     */
    public function selectSchool(SelectSchoolV5Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('api_v5')->user();
            $currentToken = $user->currentAccessToken();

            // Verificar que el token actual es temporal de usuario
            $contextData = json_decode($currentToken->context_data ?? '{}', true);

            // Debug log extenso
            \Log::info('SelectSchool debug', [
                'user_id' => $user->id,
                'token_id' => $currentToken->id,
                'token_name' => $currentToken->name,
                'school_id' => $currentToken->school_id,
                'season_id' => $currentToken->season_id,
                'context_data_raw' => $currentToken->context_data,
                'context_data_parsed' => $contextData,
                'is_temp_user' => $contextData['is_temp_user'] ?? 'not_set',
                'context_keys' => array_keys($contextData)
            ]);

            if (!($contextData['is_temp_user'] ?? false)) {
                \Log::error('SelectSchool failed - not temp user token', [
                    'expected' => 'is_temp_user = true',
                    'actual_context' => $contextData,
                    'token_name' => $currentToken->name
                ]);

                return $this->respondWithError(
                    'School selection not required',
                    400,
                    'SCHOOL_ALREADY_SELECTED'
                );
            }

            $schoolId = $request->input('school_id');
            $rememberMe = $request->boolean('remember_me', false);

            // Verificar que la escuela existe y está activa
            $school = School::where('id', $schoolId)
                ->where('active', true)
                ->first();

            if (!$school) {
                return $this->respondWithError(
                    'School not found or inactive',
                    404,
                    'SCHOOL_NOT_FOUND'
                );
            }

            // Verificar que el usuario tiene acceso a esta escuela
            if (!$this->userHasAccessToSchool($user, $school)) {
                return $this->respondWithError(
                    'User does not have access to this school',
                    403,
                    'SCHOOL_ACCESS_DENIED'
                );
            }

            // Revocar token temporal actual
            $currentToken->delete();

            // Proceder con login para la escuela seleccionada
            return $this->proceedWithSchoolLogin($user, $school, $rememberMe, $request);

        } catch (\Exception $e) {
            \Log::error('V5 School selection error', [
                'user_id' => Auth::guard('api_v5')->id(),
                'school_id' => $request->input('school_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                'An error occurred during school selection',
                500,
                'SCHOOL_SELECTION_ERROR'
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v5/auth/select-season",
     *     summary="Select season after initial login",
     *     tags={"V5 Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"season_id"},
     *             @OA\Property(property="season_id", type="integer", example=5),
     *             @OA\Property(property="create_new_season", type="boolean", example=false),
     *             @OA\Property(property="new_season_data", type="object",
     *                 @OA\Property(property="name", type="string", example="Temporada 2025-2026"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-12-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2026-04-30")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Season selected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Season selected successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/LoginResponse")
     *         )
     *     )
     * )
     */
    public function selectSeason(SelectSeasonV5Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('api_v5')->user();
            $currentToken = $user->currentAccessToken();
            $schoolId = $currentToken->school_id;

            $createNewSeason = $request->boolean('create_new_season', false);
            $seasonId = $request->input('season_id');

            // Verificar que el token actual es temporal
            $contextData = json_decode($currentToken->context_data ?? '{}', true);
            if (!($contextData['is_temporary'] ?? false)) {
                return $this->respondWithError(
                    'Season selection not required',
                    400,
                    'SEASON_ALREADY_SELECTED'
                );
            }

            $school = School::find($schoolId);
            if (!$school) {
                return $this->respondWithError(
                    'School not found',
                    404,
                    'SCHOOL_NOT_FOUND'
                );
            }

            $season = null;

            // Crear nueva temporada si se solicita
            if ($createNewSeason) {
                $newSeasonData = $request->input('new_season_data');
                $season = Season::create([
                    'name' => $newSeasonData['name'],
                    'start_date' => $newSeasonData['start_date'],
                    'end_date' => $newSeasonData['end_date'],
                    'school_id' => $schoolId,
                    'is_active' => true,
                    'is_current' => false, // La temporada actual se marca manualmente
                    'hour_start' => '08:00:00',
                    'hour_end' => '18:00:00',
                ]);

                \Log::info('V5 New season created', [
                    'season_id' => $season->id,
                    'season_name' => $season->name,
                    'school_id' => $schoolId,
                    'created_by' => $user->id
                ]);
            } else {
                // Usar temporada existente
                $season = Season::where('id', $seasonId)
                    ->where('school_id', $schoolId)
                    ->where('is_active', true)
                    ->first();

                if (!$season) {
                    return $this->respondWithError(
                        'Season not found or not active',
                        404,
                        'SEASON_NOT_FOUND'
                    );
                }
            }

            // Asignar temporada al usuario en user_season_roles
            $this->assignUserToSeason($user, $season);

            // Completar el login con la temporada seleccionada
            return $this->completeLoginWithSeason($user, $school, $season, false, $request);

        } catch (\Exception $e) {
            \Log::error('V5 Season selection error', [
                'user_id' => Auth::guard('api_v5')->id(),
                'season_id' => $request->input('season_id'),
                'create_new_season' => $request->input('create_new_season'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                'An error occurred during season selection',
                500,
                'SEASON_SELECTION_ERROR'
            );
        }
    }

    /**
     * Completar login con temporada específica
     */
    private function completeLoginWithSeason(User $user, School $school, Season $season, bool $rememberMe, $request): JsonResponse
    {
        // Revocar token temporal si existe
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        // Crear nuevo token con temporada
        $tokenName = "auth_v5_{$school->slug}_{$season->id}";
        $expiresAt = $rememberMe ? now()->addDays(30) : now()->addDays(1);

        $token = $user->createToken($tokenName, [], $expiresAt);

        // Agregar información de contexto al token
        $token->accessToken->update([
            'school_id' => $school->id,
            'season_id' => $season->id,
            'context_data' => json_encode([
                'school_id' => $school->id,
                'school_slug' => $school->slug,
                'season_id' => $season->id,
                'season_name' => $season->name,
                'login_at' => now()->toISOString(),
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'is_temporary' => false
            ])
        ]);

        // Registrar login exitoso
        $this->logSuccessfulLogin($user, $school, $season, $request);

        // Preparar respuesta completa
        $response = [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $this->getUserPrimaryRole($user),
                'permissions' => $this->getUserPermissions($user, $school),
                'avatar_url' => $user->avatar_url,
                'last_login_at' => $user->last_login_at?->toISOString(),
            ],
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'logo' => $school->logo,
                'timezone' => $school->timezone ?? 'Europe/Madrid',
                'currency' => $school->currency ?? 'EUR',
            ],
            'season' => [
                'id' => $season->id,
                'name' => $season->name,
                'start_date' => $season->start_date,
                'end_date' => $season->end_date,
                'is_active' => $season->is_active,
                'is_current' => $season->is_current ?? false,
            ]
        ];

        return $this->respondWithData($response, 'Login completed successfully');
    }

    /**
     * Obtener temporada actual del usuario para una escuela
     */
    private function getUserCurrentSeason(User $user, int $schoolId): ?Season
    {
        // 1. First check if there's a school's current season (marked as is_current)
        $currentSeason = Season::where('school_id', $schoolId)
            ->where('is_active', 1)
            ->where('is_current', 1)
            ->first();

        if ($currentSeason) {
            return $currentSeason;
        }

        // 2. Check for a season that contains the current date (automatic date-based selection)
        $today = now()->toDateString();
        $dateSeason = Season::where('school_id', $schoolId)
            ->where('is_active', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'desc') // Prefer more recent seasons if there are overlaps
            ->first();

        if ($dateSeason) {
            \Log::info('Auto-selected season based on current date', [
                'season_id' => $dateSeason->id,
                'season_name' => $dateSeason->name,
                'school_id' => $schoolId,
                'current_date' => $today
            ]);
            return $dateSeason;
        }

        // 3. Fall back to user-assigned seasons
        $userSeasonId = \DB::table('user_season_roles')
            ->join('seasons', 'user_season_roles.season_id', '=', 'seasons.id')
            ->where('user_season_roles.user_id', $user->id)
            ->where('seasons.school_id', $schoolId)
            ->where('seasons.is_active', 1)
            ->where('user_season_roles.is_active', 1)
            ->orderBy('user_season_roles.assigned_at', 'desc')
            ->value('seasons.id');

        if ($userSeasonId) {
            return Season::find($userSeasonId);
        }

        return null;
    }

    /**
     * Verificar si una temporada es válida actualmente
     */
    private function isSeasonCurrentlyValid(?Season $season): bool
    {
        if (!$season) {
            return false;
        }

        $now = Carbon::now();
        $startDate = Carbon::parse($season->start_date)->subDays(30); // Create copy
        $endDate = Carbon::parse($season->end_date)->addDays(30); // Create copy

        $isValid = $season->is_active &&
                   $now->gte($startDate) &&
                   $now->lte($endDate);

        // Debug log
        \Log::info('Season validity check', [
            'season_id' => $season->id,
            'season_name' => $season->name,
            'is_active' => $season->is_active,
            'now' => $now->toDateString(),
            'start_date_with_margin' => $startDate->toDateString(),
            'end_date_with_margin' => $endDate->toDateString(),
            'is_valid' => $isValid
        ]);

        return $isValid;
    }

    /**
     * Obtener escuelas disponibles del usuario
     */
    private function getUserAvailableSchools(User $user)
    {
        // Superadmin tiene acceso a todas las escuelas activas
        if ($user->hasRole('superadmin')) {
            return School::where('active', true)->get();
        }

        // Obtener escuelas donde el usuario tiene relación directa a través de SchoolUser
        $schools = $user->schools()->get();

        return $schools;
    }

    /**
     * Crear token temporal para usuario (sin contexto de escuela)
     */
    private function createTempUserToken(User $user): string
    {
        // Revocar tokens temporales anteriores
        $user->tokens()->where('name', 'LIKE', '%_temp_user')->delete();

        $tokenName = "auth_v5_temp_user_" . time();
        $expiresAt = now()->addMinutes(30); // 30 minutos para seleccionar escuela

        $token = $user->createToken($tokenName, [], $expiresAt);

        // Marcar como token temporal de usuario (sin contexto)
        $contextJson = json_encode([
            'is_temp_user' => true,
            'created_at' => now()->toISOString(),
            'expires_at' => $expiresAt->toISOString(),
        ]);

        $updateResult = $token->accessToken->update([
            'school_id' => null,
            'season_id' => null,
            'context_data' => $contextJson
        ]);

        // Verificar que se guardó correctamente
        $tokenAfterUpdate = $token->accessToken->fresh();

        // Debug log extenso
        \Log::info('TempUserToken created', [
            'token_id' => $token->accessToken->id,
            'token_name' => $tokenName,
            'context_json' => $contextJson,
            'update_result' => $updateResult,
            'token_context_after_update' => $tokenAfterUpdate->context_data,
            'parsed_context' => json_decode($tokenAfterUpdate->context_data ?? '{}', true),
            'school_id_after' => $tokenAfterUpdate->school_id,
            'season_id_after' => $tokenAfterUpdate->season_id
        ]);

        return $token->plainTextToken;
    }

    /**
     * Proceder con login para escuela específica (reutiliza lógica de initialLogin)
     */
    private function proceedWithSchoolLogin(User $user, School $school, bool $rememberMe, $request): JsonResponse
    {
        try {
            // Verificar permisos específicos del usuario para administrar la escuela
            if (!$this->userCanAdministerSchool($user, $school)) {
                return $this->respondWithError(
                    'Insufficient permissions to access this school',
                    403,
                    'INSUFFICIENT_PERMISSIONS'
                );
            }

            // Obtener temporadas disponibles para la escuela
            $availableSeasons = Season::where('school_id', $school->id)
                ->where('is_active', 1)
                ->orderBy('is_current', 'desc')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function($season) {
                    return [
                        'id' => $season->id,
                        'name' => $season->name,
                        'start_date' => $season->start_date,
                        'end_date' => $season->end_date,
                        'is_active' => $season->is_active,
                        'is_current' => $season->is_current ?? false,
                        'is_historical' => $season->is_historical ?? false,
                    ];
                });

            // Verificar si el usuario tiene una temporada asignada actualmente
            $currentSeason = $this->getUserCurrentSeason($user, $school->id);

            // Si hay temporada actual válida, hacer login completo automáticamente
            if ($currentSeason && $this->isSeasonCurrentlyValid($currentSeason)) {
                return $this->completeLoginWithSeason($user, $school, $currentSeason, $rememberMe, $request);
            }

            // Crear token temporal con contexto de escuela (sin temporada)
            $tokenName = "auth_v5_{$school->slug}_temp";
            $expiresAt = $rememberMe ? now()->addDays(30) : now()->addHours(2);

            // Revocar tokens temporales anteriores
            $user->tokens()->where('name', 'LIKE', "%_temp%")->delete();

            $token = $user->createToken($tokenName, [], $expiresAt);

            // Agregar información de contexto al token (solo escuela, sin temporada)
            $token->accessToken->update([
                'school_id' => $school->id,
                'season_id' => null,
                'context_data' => json_encode([
                    'school_id' => $school->id,
                    'school_slug' => $school->slug,
                    'login_at' => now()->toISOString(),
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                    'is_temporary' => true
                ])
            ]);

            // Preparar respuesta
            $response = [
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
                'requires_season_selection' => true,
                'available_seasons' => $availableSeasons,
                'current_season' => $currentSeason ? [
                    'id' => $currentSeason->id,
                    'name' => $currentSeason->name,
                    'start_date' => $currentSeason->start_date,
                    'end_date' => $currentSeason->end_date,
                    'is_active' => $currentSeason->is_active,
                    'is_current' => $currentSeason->is_current ?? false,
                ] : null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'role' => $this->getUserPrimaryRole($user),
                    'permissions' => $this->getUserPermissions($user, $school),
                    'avatar_url' => $user->avatar_url,
                ],
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slug' => $school->slug,
                    'logo' => $school->logo,
                    'timezone' => $school->timezone ?? 'Europe/Madrid',
                    'currency' => $school->currency ?? 'EUR',
                ]
            ];

            // Actualizar último login del usuario
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            return $this->respondWithData($response, 'Login successful - season selection required');

        } catch (\Exception $e) {
            \Log::error('V5 School Login error', [
                'user_id' => $user->id,
                'school_id' => $school->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respondWithError(
                'An error occurred during school login',
                500,
                'SCHOOL_LOGIN_ERROR'
            );
        }
    }

    /**
     * Asignar usuario a temporada
     */
    private function assignUserToSeason(User $user, Season $season): void
    {
        $role = $this->getUserPrimaryRole($user);

        // Crear o actualizar registro en user_season_roles
        \DB::table('user_season_roles')->updateOrInsert(
            [
                'user_id' => $user->id,
                'season_id' => $season->id,
            ],
            [
                'role' => $role,
                'is_active' => true,
                'assigned_at' => now(),
                'assigned_by' => $user->id, // Auto-asignación
                'updated_at' => now(),
            ]
        );

        \Log::info('V5 User assigned to season', [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'role' => $role
        ]);
    }

    /**
     * Responder con datos exitosos
     */
    private function respondWithData(array $data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Responder con error
     */
    private function respondWithError(string $message, int $status = 400, string $errorCode = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        return response()->json($response, $status);
    }
}
