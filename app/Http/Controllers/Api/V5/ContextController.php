<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Requests\V5\Context\SwitchSchoolRequest;
use App\Models\School;
use App\Services\ContextService;
use App\Traits\ProblemDetails;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class ContextController extends Controller
{
    use ProblemDetails;

    public function __construct(private ContextService $contextService)
    {
    }

    /**
     * Get current context (school_id, season_id).
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json(
            $this->contextService->get($request->user())
        );
    }

    /**
     * Switch current school for the authenticated user.
     */
    public function switchSchool(SwitchSchoolRequest $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = (int) $request->validated()['school_id'];

        try {
            $school = School::findOrFail($schoolId);
        } catch (ModelNotFoundException $e) {
            return $this->problem('School not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $this->authorize('switch', $school);
        } catch (AuthorizationException $e) {
            return $this->problem($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        $context = $this->contextService->setSchool($user, $schoolId);

        return response()->json($context);
    }

    // Problem details generator provided by ProblemDetails trait.
}
