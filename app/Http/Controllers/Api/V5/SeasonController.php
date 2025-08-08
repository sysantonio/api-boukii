<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V5\CreateSeasonV5Request;
use App\Http\Requests\API\V5\UpdateSeasonV5Request;
use App\Http\Resources\API\V5\SeasonV5Resource;
use App\Models\Season;
use App\Models\User;
use App\Services\SeasonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="V5 Seasons",
 *     description="Season management endpoints for V5 API"
 * )
 */
class SeasonController extends Controller
{
    private SeasonService $seasonService;

    public function __construct(SeasonService $seasonService)
    {
        $this->seasonService = $seasonService;
    }

    /**
     * @OA\Get(
     *     path="/api/v5/seasons",
     *     summary="Get all seasons for the current school context",
     *     tags={"V5 Seasons"}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            
            Log::info('SeasonController@index', [
                'school_id' => $schoolId,
                'user_id' => $request->user()->id
            ]);

            $seasons = $this->seasonService->getSeasonsForSchool($schoolId);

            return $this->successResponse(
                SeasonV5Resource::collection($seasons),
                'Seasons retrieved successfully'
            );

        } catch (\Exception $e) {
            Log::error('SeasonController@index failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return $this->errorResponse(
                'Failed to retrieve seasons',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v5/seasons/{id}",
     *     summary="Get a specific season by ID",
     *     tags={"V5 Seasons"}
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            
            $season = $this->seasonService->getSeasonForSchool($id, $schoolId);
            
            if (!$season) {
                return $this->errorResponse(
                    'Season not found or not accessible',
                    Response::HTTP_NOT_FOUND,
                    'SEASON_NOT_FOUND'
                );
            }

            Log::info('SeasonController@show', [
                'season_id' => $id,
                'school_id' => $schoolId,
                'user_id' => $request->user()->id
            ]);

            return $this->successResponse(
                new SeasonV5Resource($season),
                'Season retrieved successfully'
            );

        } catch (\Exception $e) {
            Log::error('SeasonController@show failed', [
                'season_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return $this->errorResponse(
                'Failed to retrieve season',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v5/seasons",
     *     summary="Create a new season for the current school",
     *     tags={"V5 Seasons"}
     * )
     */
    public function store(CreateSeasonV5Request $request): JsonResponse
    {
        Log::info('SeasonController@store reached', [
            'request_data' => $request->all(),
            'has_context_school_id' => $request->has('context_school_id'),
            'context_school_id' => $request->get('context_school_id')
        ]);
        
        DB::beginTransaction();
        
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            $user = $request->user();

            // Verify user has permission to create seasons for this school
            if (!$this->userCanManageSeasons($user, $schoolId)) {
                return $this->errorResponse(
                    'Insufficient permissions to create seasons',
                    Response::HTTP_FORBIDDEN,
                    'PERMISSION_DENIED'
                );
            }

            // Prepare season data with school context
            $seasonData = array_merge($request->validated(), [
                'school_id' => $schoolId,
                'created_by' => $user->id,
                'is_active' => $request->input('is_active', false)
            ]);

            $season = $this->seasonService->createSeason($seasonData);

            DB::commit();

            Log::info('SeasonController@store success', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'school_id' => $schoolId,
                'user_id' => $user->id
            ]);

            return $this->successResponse(
                new SeasonV5Resource($season),
                'Season created successfully',
                Response::HTTP_CREATED
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Validation failed',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'VALIDATION_ERROR',
                $e->errors()
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('SeasonController@store failed', [
                'error' => $e->getMessage(),
                'school_id' => $this->getSchoolIdFromContext($request),
                'user_id' => $request->user()->id
            ]);

            return $this->errorResponse(
                'Failed to create season: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v5/seasons/{id}",
     *     summary="Update an existing season",
     *     tags={"V5 Seasons"}
     * )
     */
    public function update(UpdateSeasonV5Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            $user = $request->user();

            // Find season within school context
            $season = $this->seasonService->getSeasonForSchool($id, $schoolId);
            
            if (!$season) {
                return $this->errorResponse(
                    'Season not found or not accessible',
                    Response::HTTP_NOT_FOUND,
                    'SEASON_NOT_FOUND'
                );
            }

            // Check if season can be modified
            if (!$this->seasonCanBeModified($season)) {
                return $this->errorResponse(
                    'Season cannot be modified (closed or historical)',
                    Response::HTTP_CONFLICT,
                    'SEASON_IMMUTABLE'
                );
            }

            // Verify permissions
            if (!$this->userCanManageSeasons($user, $schoolId)) {
                return $this->errorResponse(
                    'Insufficient permissions to update seasons',
                    Response::HTTP_FORBIDDEN,
                    'PERMISSION_DENIED'
                );
            }

            $updatedSeason = $this->seasonService->updateSeason($season, $request->validated());

            DB::commit();

            Log::info('SeasonController@update success', [
                'season_id' => $id,
                'season_name' => $updatedSeason->name,
                'school_id' => $schoolId,
                'user_id' => $user->id
            ]);

            return $this->successResponse(
                new SeasonV5Resource($updatedSeason),
                'Season updated successfully'
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            return $this->errorResponse(
                'Validation failed',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'VALIDATION_ERROR',
                $e->errors()
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('SeasonController@update failed', [
                'season_id' => $id,
                'error' => $e->getMessage(),
                'school_id' => $this->getSchoolIdFromContext($request),
                'user_id' => $request->user()->id
            ]);

            return $this->errorResponse(
                'Failed to update season',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v5/seasons/{id}",
     *     summary="Delete a season (soft delete)",
     *     tags={"V5 Seasons"}
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            $user = $request->user();

            $season = $this->seasonService->getSeasonForSchool($id, $schoolId);
            
            if (!$season) {
                return $this->errorResponse(
                    'Season not found or not accessible',
                    Response::HTTP_NOT_FOUND,
                    'SEASON_NOT_FOUND'
                );
            }

            // Check if season can be deleted
            if (!$this->seasonCanBeDeleted($season)) {
                return $this->errorResponse(
                    'Season cannot be deleted (has associated data)',
                    Response::HTTP_CONFLICT,
                    'SEASON_HAS_DATA'
                );
            }

            // Verify permissions
            if (!$this->userCanManageSeasons($user, $schoolId)) {
                return $this->errorResponse(
                    'Insufficient permissions to delete seasons',
                    Response::HTTP_FORBIDDEN,
                    'PERMISSION_DENIED'
                );
            }

            $this->seasonService->deleteSeason($season);

            DB::commit();

            Log::info('SeasonController@destroy success', [
                'season_id' => $id,
                'season_name' => $season->name,
                'school_id' => $schoolId,
                'user_id' => $user->id
            ]);

            return $this->successResponse(
                null,
                'Season deleted successfully',
                Response::HTTP_NO_CONTENT
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('SeasonController@destroy failed', [
                'season_id' => $id,
                'error' => $e->getMessage(),
                'school_id' => $this->getSchoolIdFromContext($request),
                'user_id' => $request->user()->id
            ]);

            return $this->errorResponse(
                'Failed to delete season',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v5/seasons/{id}/close",
     *     summary="Close a season (mark as historical)",
     *     tags={"V5 Seasons"}
     * )
     */
    public function close(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            $user = $request->user();

            $season = $this->seasonService->getSeasonForSchool($id, $schoolId);
            
            if (!$season) {
                return $this->errorResponse(
                    'Season not found or not accessible',
                    Response::HTTP_NOT_FOUND,
                    'SEASON_NOT_FOUND'
                );
            }

            if ($season->is_closed) {
                return $this->errorResponse(
                    'Season is already closed',
                    Response::HTTP_CONFLICT,
                    'SEASON_ALREADY_CLOSED'
                );
            }

            // Verify permissions
            if (!$this->userCanManageSeasons($user, $schoolId)) {
                return $this->errorResponse(
                    'Insufficient permissions to close seasons',
                    Response::HTTP_FORBIDDEN,
                    'PERMISSION_DENIED'
                );
            }

            $closedSeason = $this->seasonService->closeSeason($season, $user->id);

            DB::commit();

            Log::info('SeasonController@close success', [
                'season_id' => $id,
                'season_name' => $closedSeason->name,
                'school_id' => $schoolId,
                'user_id' => $user->id
            ]);

            return $this->successResponse(
                new SeasonV5Resource($closedSeason),
                'Season closed successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('SeasonController@close failed', [
                'season_id' => $id,
                'error' => $e->getMessage(),
                'school_id' => $this->getSchoolIdFromContext($request),
                'user_id' => $request->user()->id
            ]);

            return $this->errorResponse(
                'Failed to close season',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v5/seasons/current",
     *     summary="Get the current active season for the school",
     *     tags={"V5 Seasons"}
     * )
     */
    public function current(Request $request): JsonResponse
    {
        try {
            $schoolId = $this->getSchoolIdFromContext($request);
            
            $currentSeason = $this->seasonService->getCurrentSeasonForSchool($schoolId);
            
            if (!$currentSeason) {
                return $this->errorResponse(
                    'No active season found for this school',
                    Response::HTTP_NOT_FOUND,
                    'NO_ACTIVE_SEASON'
                );
            }

            Log::info('SeasonController@current', [
                'season_id' => $currentSeason->id,
                'school_id' => $schoolId,
                'user_id' => $request->user()->id
            ]);

            return $this->successResponse(
                new SeasonV5Resource($currentSeason),
                'Current season retrieved successfully'
            );

        } catch (\Exception $e) {
            Log::error('SeasonController@current failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return $this->errorResponse(
                'Failed to retrieve current season',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Extract school ID from request context (set by middleware)
     */
    private function getSchoolIdFromContext(Request $request): int
    {
        // School ID is set by ContextMiddleware in the request
        $schoolId = $request->get('context_school_id');
        
        if (!$schoolId) {
            throw new \Exception('School context not found in request (middleware not applied?)');
        }

        return (int) $schoolId;
    }

    /**
     * Check if user can manage seasons for the given school
     */
    private function userCanManageSeasons(User $user, int $schoolId): bool
    {
        Log::info('Checking season permissions', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'school_id' => $schoolId
        ]);
        
        // For now, allow any authenticated user - proper role/permission system to be implemented later
        $canManage = true;
                     
        Log::info('Permission check result', ['can_manage' => $canManage]);
        return $canManage;
    }

    /**
     * Check if season can be modified
     */
    private function seasonCanBeModified(Season $season): bool
    {
        return !$season->is_closed && !$season->is_historical;
    }

    /**
     * Check if season can be deleted
     */
    private function seasonCanBeDeleted(Season $season): bool
    {
        return !$season->is_closed && 
               !$season->is_historical;
    }

    /**
     * Success response helper
     */
    private function successResponse($data, string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response helper
     */
    private function errorResponse(
        string $message, 
        int $status, 
        ?string $errorCode = null, 
        $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}