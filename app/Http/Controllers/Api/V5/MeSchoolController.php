<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeSchoolController extends Controller
{
    /**
     * List schools visible to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', School::class);

        $schools = $request->user()->schools()->paginate();

        return SchoolResource::collection($schools)->response();
    }
}
