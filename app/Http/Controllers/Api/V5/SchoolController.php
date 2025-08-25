<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * Publicly list schools.
     */
    public function index(Request $request): JsonResponse
    {
        $query = School::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $orderBy = $request->query('orderBy', 'name');
        $direction = $request->query('orderDirection', 'asc');
        $columns = [
            'name' => 'name',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
        $orderBy = $columns[$orderBy] ?? 'name';
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $query->orderBy($orderBy, $direction);

        $perPage = $request->integer('perPage', 20);
        $page = $request->integer('page', 1);
        $schools = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => SchoolResource::collection($schools->items()),
            'meta' => [
                'total' => $schools->total(),
                'page' => $schools->currentPage(),
                'perPage' => $schools->perPage(),
                'lastPage' => $schools->lastPage(),
                'from' => $schools->firstItem(),
                'to' => $schools->lastItem(),
            ],
        ]);
    }

    /**
     * Show a single school.
     */
    public function show(School $school): JsonResponse
    {
        return SchoolResource::make($school)->response();
    }
}
