<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSchoolAPIRequest;
use App\Http\Requests\API\UpdateSchoolAPIRequest;
use App\Http\Resources\API\SchoolResource;
use App\Models\School;
use App\Models\SchoolSport;
use App\Repositories\SchoolRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SchoolController
 */

class SchoolAPIController extends AppBaseController
{
    /** @var  SchoolRepository */
    private $schoolRepository;

    public function __construct(SchoolRepository $schoolRepo)
    {
        $this->schoolRepository = $schoolRepo;
    }

    /**
     * @OA\Get(
     *      path="/schools",
     *      summary="getSchoolList",
     *      tags={"School"},
     *      description="Get all Schools",
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
     *                  @OA\Items(ref="#/components/schemas/School")
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
        $schools = $this->schoolRepository->all(
             $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage
        );

        return $this->sendResponse(SchoolResource::collection($schools), 'Schools retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/schools",
     *      summary="createSchool",
     *      tags={"School"},
     *      description="Create School",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/School")
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
     *                  ref="#/components/schemas/School"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateSchoolAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $school = $this->schoolRepository->create($input);

        return $this->sendResponse(new SchoolResource($school), 'School saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/schools/{id}",
     *      summary="getSchoolItem",
     *      tags={"School"},
     *      description="Get School",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of School",
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
     *                  ref="#/components/schemas/School"
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
        /** @var School $school */
        $school = $this->schoolRepository->find($id);

        if (empty($school)) {
            return $this->sendError('School not found');
        }

        return $this->sendResponse(new SchoolResource($school), 'School retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/schools/{id}",
     *      summary="updateSchool",
     *      tags={"School"},
     *      description="Update School",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of School",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/School")
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
     *                  ref="#/components/schemas/School"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateSchoolAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var School $school */
        $school = $this->schoolRepository->find($id);

        if (empty($school)) {
            return $this->sendError('School not found');
        }

        $school = $this->schoolRepository->update($input, $id);

        return $this->sendResponse(new SchoolResource($school), 'School updated successfully');
    }

    /**
     * @OA\Put(
     *      path="/schools/{id}/sports",
     *      summary="updateSchoolSports",
     *      tags={"School"},
     *      description="Update School Sports",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of School",
     *          @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *           @OA\Property(
     *               property="sport_ids",
     *               type="array",
     *               @OA\Items(type="integer"),
     *               description="An array of Sport IDs to synchronize with the school."
     *           )
     *        )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="School not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  description="Error message"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  description="Error message"
     *              )
     *          )
     *      )
     * )
     */
    public function updateSchoolSports($schoolId, Request $request): JsonResponse
    {
        $input = $request->all();

        /** @var School $school */
        $school = School::find($schoolId);

        if (empty($school)) {
            return $this->sendError('School not found');
        }

        // Sincroniza los deportes relacionados con los IDs proporcionados en $input['sport_ids']
        $school->sports()->sync($input['sport_ids']);

        return $this->sendResponse('School sports updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/schools/{id}",
     *      summary="deleteSchool",
     *      tags={"School"},
     *      description="Delete School",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of School",
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
        /** @var School $school */
        $school = $this->schoolRepository->find($id);

        if (empty($school)) {
            return $this->sendError('School not found');
        }

        $school->delete();

        return $this->sendSuccess('School deleted successfully');
    }
}
