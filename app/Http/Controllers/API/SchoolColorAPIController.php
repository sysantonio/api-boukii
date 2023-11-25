<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSchoolColorAPIRequest;
use App\Http\Requests\API\UpdateSchoolColorAPIRequest;
use App\Http\Resources\API\SchoolColorResource;
use App\Models\SchoolColor;
use App\Repositories\SchoolColorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SchoolColorController
 */

class SchoolColorAPIController extends AppBaseController
{
    /** @var  SchoolColorRepository */
    private $schoolColorRepository;

    public function __construct(SchoolColorRepository $schoolColorRepo)
    {
        $this->schoolColorRepository = $schoolColorRepo;
    }

    /**
     * @OA\Get(
     *      path="/school-colors",
     *      summary="getSchoolColorList",
     *      tags={"SchoolColor"},
     *      description="Get all SchoolColors",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/SchoolColor")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $schoolColors = $this->schoolColorRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(SchoolColorResource::collection($schoolColors), 'School Colors retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/school-colors",
     *      summary="createSchoolColor",
     *      tags={"SchoolColor"},
     *      description="Create SchoolColor",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolColor")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/SchoolColor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSchoolColorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $schoolColor = $this->schoolColorRepository->create($input);

        return $this->sendResponse(new SchoolColorResource($schoolColor), 'School Color saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/school-colors/{id}",
     *      summary="getSchoolColorItem",
     *      tags={"SchoolColor"},
     *      description="Get SchoolColor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolColor",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/SchoolColor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        /** @var SchoolColor $schoolColor */
        $schoolColor = $this->schoolColorRepository->find($id);

        if (empty($schoolColor)) {
            return $this->sendError('School Color not found');
        }

        return $this->sendResponse(new SchoolColorResource($schoolColor), 'School Color retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/school-colors/{id}",
     *      summary="updateSchoolColor",
     *      tags={"SchoolColor"},
     *      description="Update SchoolColor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolColor",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/SchoolColor")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/SchoolColor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSchoolColorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var SchoolColor $schoolColor */
        $schoolColor = $this->schoolColorRepository->find($id);

        if (empty($schoolColor)) {
            return $this->sendError('School Color not found');
        }

        $schoolColor = $this->schoolColorRepository->update($input, $id);

        return $this->sendResponse(new SchoolColorResource($schoolColor), 'SchoolColor updated successfully');
    }

    /**
     * @OA\Put(
     *      path="/api/school-colors/multiple",
     *      summary="Update or insert school colors",
     *      tags={"School Colors"},
     *      description="Update or insert school colors in bulk.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  required={"school_id", "name", "color"},
     *                  @OA\Property(property="school_id", type="integer", description="School ID"),
     *                  @OA\Property(property="name", type="string", description="Color Name"),
     *                  @OA\Property(property="color", type="string", description="Color Code"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Colors updated or inserted successfully",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *      ),
     * )
     */
    public function updateMultiple(Request $request)
    {
        $dataToUpdate = $request->json()->all();

        if (empty($dataToUpdate)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        try {
            foreach ($dataToUpdate as $data) {
                SchoolColor::updateOrInsert(
                    [
                        'school_id' => $data['school_id'],
                        'name' => $data['name'],
                    ],
                    [
                        'color' => $data['color'],
                    ]
                );
            }

            return response()->json(['message' => 'Colors updated or inserted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating or inserting colors'], 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/school-colors/{id}",
     *      summary="deleteSchoolColor",
     *      tags={"SchoolColor"},
     *      description="Delete SchoolColor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of SchoolColor",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy($id): JsonResponse
    {
        /** @var SchoolColor $schoolColor */
        $schoolColor = $this->schoolColorRepository->find($id);

        if (empty($schoolColor)) {
            return $this->sendError('School Color not found');
        }

        $schoolColor->delete();

        return $this->sendSuccess('School Color deleted successfully');
    }
}
