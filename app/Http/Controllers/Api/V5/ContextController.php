<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Requests\V5\Context\SwitchSchoolRequest;
use App\Models\School;
use App\Services\V5\ContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContextController extends Controller
{
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

        $school = School::find($schoolId);
        if (! $school) {
            return $this->problem('School not found', Response::HTTP_NOT_FOUND);
        }

        $this->authorize('switch', $school);

        $context = $this->contextService->setSchool($user, $schoolId);
        if (! $context) {
            return $this->problem('No active access token', Response::HTTP_UNAUTHORIZED);
        }

        return response()->json($context);
    }

    private function problem(string $detail, int $status): JsonResponse
    {
        return response()->json([
            'type' => 'about:blank',
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail,
        ], $status, ['Content-Type' => 'application/problem+json']);
    }
}
