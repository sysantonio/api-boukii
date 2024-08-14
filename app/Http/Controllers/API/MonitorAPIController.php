<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMonitorAPIRequest;
use App\Http\Requests\API\UpdateMonitorAPIRequest;
use App\Http\Resources\API\MonitorResource;
use App\Models\Monitor;
use App\Repositories\MonitorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class MonitorController
 */

class MonitorAPIController extends AppBaseController
{
    /** @var  MonitorRepository */
    private $monitorRepository;

    public function __construct(MonitorRepository $monitorRepo)
    {
        $this->monitorRepository = $monitorRepo;
    }

    /**
     * @OA\Get(
     *      path="/monitors",
     *      summary="getMonitorList",
     *      tags={"Monitor"},
     *      description="Get all Monitors",
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
     *                  @OA\Items(ref="#/components/schemas/Monitor")
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
        $monitors = $this->monitorRepository->all(
            $request->except(['skip', 'limit', 'search', 'exclude', 'user', 'perPage', 'order', 'orderColumn', 'page', 'with']),
            $request->get('search'),
            $request->get('skip'),
            $request->get('limit'),
            $request->perPage,
            $request->get('with', []),
            $request->get('order', 'desc'),
            $request->get('orderColumn', 'id'),
            additionalConditions: function ($query) use ($request) {
                if ($request->has('school_id')) {
                    $query->whereHas('monitorsSchools', function ($query) use ($request) {
                        $query->where('school_id', $request['school_id']);
                        if ($request->has('school_active')) {
                            $query->where('active_school', $request['school_active']);
                        }
                    });
                }

                if ($request->has('sports_id') && is_array($request->sports_id)) {
                    $query->whereHas('monitorSportsDegrees', function ($query) use ($request) {
                        $query->whereIn('sport_id', $request->sports_id);
                    });
                }
            }
        );

        if ($request->has('with') && in_array('sports', $request->get('with')) && $request->has('school_id')) {
            $monitors->load('sports');

            $monitors->each(function ($monitor) use ($request) {
                $sportsDegrees = collect(); // Inicializamos una colección vacía para los deportes grados
                $degrees = \App\Models\MonitorSportsDegree::where('school_id', $request['school_id'])
                    ->where('monitor_id', $monitor->id)
                    ->with('sport') // Cargar la relación 'sport'
                    ->get(); // Obtener los objetos de deportes grados en lugar de solo los IDs

                // Agregamos los deportes grados encontrados a la colección
                $sportsDegrees = $sportsDegrees->merge($degrees);

                // Asignamos la colección de deportes grados al monitor
                $monitor->setRelation('sports', $sportsDegrees->pluck('sport'));
            });
        }

        return $this->sendResponse($monitors, 'Monitors retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/monitors",
     *      summary="createMonitor",
     *      tags={"Monitor"},
     *      description="Create Monitor",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Monitor")
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
     *                  ref="#/components/schemas/Monitor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateMonitorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        if(!empty($input['image'])) {
            $base64Image = $request->input('image');

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    $this->sendError('base64_decode failed');
                }
            } else {
                $this->sendError('did not match data URI with image data');
            }

            $imageName = 'monitor/image_'.time().'.'.$type;
            Storage::disk('public')->put($imageName, $imageData);
            $input['image'] = url(Storage::url($imageName));
        }

        $monitor = $this->monitorRepository->create($input);

        return $this->sendResponse($monitor, 'Monitor saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/monitors/{id}",
     *      summary="getMonitorItem",
     *      tags={"Monitor"},
     *      description="Get Monitor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Monitor",
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
     *                  ref="#/components/schemas/Monitor"
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
        /** @var Monitor $monitor */
        $monitor = $this->monitorRepository->find($id, with: $request->get('with', []));

        if (empty($monitor)) {
            return $this->sendError('Monitor not found');
        }

        return $this->sendResponse($monitor, 'Monitor retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/monitors/{id}",
     *      summary="updateMonitor",
     *      tags={"Monitor"},
     *      description="Update Monitor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Monitor",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Monitor")
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
     *                  ref="#/components/schemas/Monitor"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateMonitorAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Monitor $monitor */
        $monitor = $this->monitorRepository->find($id, with: $request->get('with', []));

        if (empty($monitor)) {
            return $this->sendError('Monitor not found');
        }

        $activeSchool = $request->get('school_active');
        $schoolId = $request->get('school_id');

        if(!empty($input['image'])) {
            $base64Image = $request->input('image');

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    $this->sendError('base64_decode failed');
                }
                $imageName = 'monitor/image_'.time().'.'.$type;
                Storage::disk('public')->put($imageName, $imageData);
                $input['image'] = url(Storage::url($imageName));
            } else {
                $this->sendError('did not match data URI with image data');
            }
        } else {
            $input = $request->except('image');
        }

        $monitor = $this->monitorRepository->update($input, $id);

        if (isset($activeSchool) && isset($schoolId)) {

            $monitorSchoolRelation = $monitor->monitorsSchools()->where('school_id', $schoolId)->first();
            if ($monitorSchoolRelation) {
                $monitorSchoolRelation->update(['active_school' => $activeSchool]);
            }
        }

        return $this->sendResponse(new MonitorResource($monitor), 'Monitor updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/monitors/{id}",
     *      summary="deleteMonitor",
     *      tags={"Monitor"},
     *      description="Delete Monitor",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Monitor",
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
        /** @var Monitor $monitor */
        $monitor = $this->monitorRepository->find($id);

        if (empty($monitor)) {
            return $this->sendError('Monitor not found');
        }

        $monitor->delete();

        return $this->sendSuccess('Monitor deleted successfully');
    }
}
