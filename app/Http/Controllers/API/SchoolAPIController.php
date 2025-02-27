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
use Illuminate\Support\Facades\Storage;

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
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id')
        );

        return $this->sendResponse($schools, 'Schools retrieved successfully');
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

        return $this->sendResponse($school, 'School saved successfully');
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
    public function show($id, Request $request): JsonResponse
    {
        /** @var School $school */
        $school = $this->schoolRepository->find($id, with: $request->get('with', []));

        if (empty($school)) {
            return $this->sendError('School not found');
        }

        return $this->sendResponse($school, 'School retrieved successfully');
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
        $school = $this->schoolRepository->find($id, with: $request->get('with', []));

        if (empty($school)) {
            return $this->sendError('School not found');
        }

        // Procesar imágenes en el campo settings si existen
        if (!empty($input['settings'])) {
            $settings = json_decode($input['settings'], true);

            if (isset($settings['bookingPage'])) {
                // Guardar imágenes de sponsors si son base64
                if (!empty($settings['bookingPage']['sponsors']) && is_array($settings['bookingPage']['sponsors'])) {
                    foreach ($settings['bookingPage']['sponsors'] as $key => $sponsorImage) {
                        if ($this->isBase64Image($sponsorImage)) {
                            $settings['bookingPage']['sponsors'][$key] = $this->saveBase64Image($sponsorImage, 'sponsors');
                        }
                    }
                }

                // Guardar imágenes del banner (desktopImg y mobileImg) si son base64
                if (!empty($settings['bookingPage']['banner']['desktopImg']) && $this->isBase64Image($settings['bookingPage']['banner']['desktopImg'])) {
                    $settings['bookingPage']['banner']['desktopImg'] = $this->saveBase64Image($settings['bookingPage']['banner']['desktopImg'], 'banners');
                }
                if (!empty($settings['bookingPage']['banner']['mobileImg']) && $this->isBase64Image($settings['bookingPage']['banner']['mobileImg'])) {
                    $settings['bookingPage']['banner']['mobileImg'] = $this->saveBase64Image($settings['bookingPage']['banner']['mobileImg'], 'banners');
                }
            }

            // Convertimos de nuevo a JSON después de modificarlo
            $input['settings'] = json_encode($settings);
        }

        $school = $this->schoolRepository->update($input, $id);

        return $this->sendResponse(new SchoolResource($school), 'School updated successfully');
    }

    /**
     * Función para guardar imágenes base64 y devolver la URL pública
     */
    private function saveBase64Image($base64Image, $folder)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return null; // Si la decodificación falla, devuelve null
            }

            // Generar un nombre único para la imagen
            $imageName = $folder . '/image_' . time() . '_' . uniqid() . '.' . $type;
            Storage::disk('public')->put($imageName, $imageData);

            return url(Storage::url($imageName)); // Devolver la URL pública de la imagen
        }

        return null; // Si la imagen no es válida, devuelve null
    }

    /**
     * Función para verificar si un string es una imagen en base64
     */
    private function isBase64Image($str)
    {
        return preg_match('/^data:image\/(\w+);base64,/', $str);
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
        $school->load('sports');

        return $this->sendResponse($school,'School sports updated successfully');
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
