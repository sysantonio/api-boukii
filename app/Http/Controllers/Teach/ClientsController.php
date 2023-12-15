<?php

namespace App\Http\Controllers\Teach;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\API\BookingResource;
use App\Models\Booking;
use App\Models\BookingUser;
use App\Models\Client;
use App\Models\CourseSubgroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class ClientsController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Get(
     *      path="/teach/clients",
     *      summary="getClientList",
     *      tags={"Teach"},
     *      description="Get all Clients of monitor",
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
     *                  @OA\Items(ref="#/components/schemas/Client")
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
        $monitorId = $this->getMonitor($request)->id;

        $clients = Client::with(['sports', 'utilizers', 'main', 'evaluations.degree',
            'evaluations.evaluationFulfilledGoals', 'observations'])
            ->whereHas('bookingUsers', function ($query) use ($monitorId) {
                $query->where('monitor_id', $monitorId);
            })->distinct()->get();


        return response()->json($clients);
    }

    /**
     * @OA\Get(
     *      path="/teach/clients/{id}",
     *      summary="getClientItemMonitor",
     *      tags={"Teach"},
     *      description="Get Client",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Client",
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
     *                  ref="#/components/schemas/Client"
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
        $monitorId = $this->getMonitor($request)->id;

        // Comprueba si el cliente principal tiene booking_users asociados con el ID del monitor
        $client = Client::with('sports', 'utilizers', 'main',
            'evaluations.degree', 'evaluations.evaluationFulfilledGoals',
            'observations')->whereHas('bookingUsers', function ($query) use ($monitorId) {
            $query->where('monitor_id', $monitorId);
        })->find($id);

        if (empty($client)) {
            return $this->sendError('Client does not have booking_users with the specified monitor');
        }

        return $this->sendResponse($client, 'Client retrieved successfully');
    }

    /**
     * @OA\Get(
     *      path="/teach/clients/{id}/bookings",
     *      summary="getClientBookingsList",
     *      tags={"Teach"},
     *      description="Get all Client bookings",
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
     *                  @OA\Items(ref="#/components/schemas/BookingUser")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getBookings($id, Request $request): JsonResponse
    {
        $monitorId = $this->getMonitor($request)->id;
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $bookingQuery = BookingUser::with('booking', 'course.courseDates')
            ->where('client_id', $id);

        if ($dateStart && $dateEnd) {
            // Busca en el rango de fechas proporcionado para las reservas
            $bookingQuery->whereBetween('date', [$dateStart, $dateEnd]);
        }

        $bookings = $bookingQuery->get();

        return $this->sendResponse($bookings, 'Bookings returned successfully');
    }

    /**
     * @OA\Post(
     *      path="/teach/clients/transfer",
     *      summary="transferClients",
     *      tags={"Teach"},
     *      description="Transfer clients",
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
     *                  @OA\Items(ref="#/components/schemas/BookingUser")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function transferClients(Request $request): JsonResponse
    {
        $initialSubgroup = CourseSubgroup::with('courseGroup.courseDate')->find($request->initialSubgroupId);
        $targetSubgroup = CourseSubgroup::find($request->targetSubgroupId);

        if (!$initialSubgroup || !$targetSubgroup) {
            // Manejar error
            return $this->sendError('No existe el subgrupo');
        }

        $initialGroup = $initialSubgroup->courseGroup;
        $initialSubgroupPosition =
            $initialGroup->courseSubgroups->sortBy('id')->search(function ($subgroup) use ($initialSubgroup) {
                return $subgroup->id == $initialSubgroup->id;
            });

        if ($request->moveAllDays) {
            $courseDates = $initialGroup->course->courseDates;

            foreach ($courseDates as $courseDate) {
                $groups = $courseDate->courseGroups->where('degree_id', $initialGroup->degree_id);
                DB::beginTransaction();
                foreach ($groups as $group) {
                    if ($group->courseSubgroups->count() == $initialGroup->courseSubgroups->count()) {
                        $newTargetSubgroup = $group->courseSubgroups->sortBy('id')[$initialSubgroupPosition] ?? null;

                        if ($newTargetSubgroup) {
                            $this->moveUsers($initialSubgroup, $newTargetSubgroup, $request->clientIds);
                        } else {
                            DB::rollBack();
                            return $this->sendError('Some groups are not identical');
                        }
                    } else {
                        DB::rollBack();
                        return $this->sendError('Some groups are not identical');
                    }
                }
                DB::commit();
            }
        } else {
            $this->moveUsers($initialSubgroup, $targetSubgroup, $request->clientIds);
        }

        return $this->sendResponse([], 'Bookings returned successfully');
    }


    private function moveUsers($initialCourseDate, $targetSubgroup, $clientIds)
    {
        // Mover los usuarios
        foreach ($clientIds as $clientId) {
            BookingUser::where('course_date_id', $initialCourseDate->id)
                ->where('client_id', $clientId)
                ->update(['course_subgroup_id' => $targetSubgroup->id,
                    'course_group_id', $targetSubgroup->course_group_id,
                    'degree_id', $targetSubgroup->degree_id]);
        }
    }
}
