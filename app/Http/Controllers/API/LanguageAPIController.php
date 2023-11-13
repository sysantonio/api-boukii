<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateLanguageAPIRequest;
use App\Http\Requests\API\UpdateLanguageAPIRequest;
use App\Http\Resources\API\LanguageResource;
use App\Models\Language;
use App\Repositories\LanguageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class LanguageController
 */

class LanguageAPIController extends AppBaseController
{
    /** @var  LanguageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepo)
    {
        $this->languageRepository = $languageRepo;
    }

    /**
     * @OA\Get(
     *      path="/languages",
     *      summary="getLanguageList",
     *      tags={"Language"},
     *      description="Get all Languages",
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
     *                  @OA\Items(ref="#/components/schemas/Language")
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
        $languages = $this->languageRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(LanguageResource::collection($languages), 'Languages retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/languages",
     *      summary="createLanguage",
     *      tags={"Language"},
     *      description="Create Language",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Language")
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
     *                  ref="#/components/schemas/Language"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateLanguageAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $language = $this->languageRepository->create($input);

        return $this->sendResponse(new LanguageResource($language), 'Language saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/languages/{id}",
     *      summary="getLanguageItem",
     *      tags={"Language"},
     *      description="Get Language",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Language",
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
     *                  ref="#/components/schemas/Language"
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
        /** @var Language $language */
        $language = $this->languageRepository->find($id);

        if (empty($language)) {
            return $this->sendError('Language not found');
        }

        return $this->sendResponse(new LanguageResource($language), 'Language retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/languages/{id}",
     *      summary="updateLanguage",
     *      tags={"Language"},
     *      description="Update Language",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Language",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Language")
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
     *                  ref="#/components/schemas/Language"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateLanguageAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Language $language */
        $language = $this->languageRepository->find($id);

        if (empty($language)) {
            return $this->sendError('Language not found');
        }

        $language = $this->languageRepository->update($input, $id);

        return $this->sendResponse(new LanguageResource($language), 'Language updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/languages/{id}",
     *      summary="deleteLanguage",
     *      tags={"Language"},
     *      description="Delete Language",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Language",
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
        /** @var Language $language */
        $language = $this->languageRepository->find($id);

        if (empty($language)) {
            return $this->sendError('Language not found');
        }

        $language->delete();

        return $this->sendSuccess('Language deleted successfully');
    }
}
