<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Requests\V5\Context\SwitchSchoolRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContextController extends Controller
{
    /**
     * Get current context (school_id, season_id).
     */
    public function show(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $context = [
            'school_id' => null,
            'season_id' => null,
        ];

        if ($token && $token->context_data) {
            $data = is_array($token->context_data)
                ? $token->context_data
                : json_decode($token->context_data, true);
            $context['school_id'] = $data['school_id'] ?? null;
            $context['season_id'] = $data['season_id'] ?? null;
        }

        return response()->json($context);
    }

    /**
     * Switch current school for the authenticated user.
     */
    public function switchSchool(SwitchSchoolRequest $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = (int) $request->validated()['school_id'];

        $token = $user->currentAccessToken();
        if (! $token) {
            return $this->problem('No active access token', Response::HTTP_UNAUTHORIZED);
        }

        $contextData = $token->context_data ?? [];
        if (! is_array($contextData)) {
            $contextData = json_decode($contextData, true) ?? [];
        }

        $contextData['school_id'] = $schoolId;
        $contextData['season_id'] = null;

        $token->context_data = $contextData;
        $token->save();

        return response()->json([
            'school_id' => $schoolId,
            'season_id' => null,
        ]);
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
