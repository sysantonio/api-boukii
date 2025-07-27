<?php

namespace App\Http\Controllers\Admin\V3;

use App\Http\Controllers\AppBaseController;
use App\Services\Admin\V3\CourseStatsService;
use App\Services\Admin\V3\DashboardSummaryService;
use App\Services\Admin\V3\ReservationService;
use App\Services\Admin\V3\SalesService;
use App\Services\Admin\V3\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends AppBaseController
{
    protected DashboardSummaryService $summaryService;

    protected CourseStatsService $courseStatsService;

    protected SalesService $salesService;

    protected ReservationService $reservationService;

    protected WeatherService $weatherService;

    public function __construct(
        DashboardSummaryService $summaryService,
        CourseStatsService $courseStatsService,
        SalesService $salesService,
        ReservationService $reservationService,
        WeatherService $weatherService
    ) {
        $this->summaryService = $summaryService;
        $this->courseStatsService = $courseStatsService;
        $this->salesService = $salesService;
        $this->reservationService = $reservationService;
        $this->weatherService = $weatherService;
    }

    public function summary(Request $request): JsonResponse
    {
        $this->ensureSchoolInRequest($request);
        $data = $this->summaryService->getSummary($request);

        return $this->sendResponse($data, 'Dashboard summary retrieved');
    }

    public function courseStats(Request $request): JsonResponse
    {
        $this->ensureSchoolInRequest($request);
        $data = $this->courseStatsService->getCourseStats($request);

        return $this->sendResponse($data, 'Course statistics retrieved');
    }

    public function sales(Request $request): JsonResponse
    {
        $this->ensureSchoolInRequest($request);
        $data = $this->salesService->getSalesData($request);

        return $this->sendResponse($data, 'Sales data retrieved');
    }

    public function reservations(Request $request): JsonResponse
    {
        $this->ensureSchoolInRequest($request);
        $data = $this->reservationService->getReservationData($request);

        return $this->sendResponse($data, 'Reservation data retrieved');
    }

    public function weather(Request $request): JsonResponse
    {
        $this->ensureSchoolInRequest($request);
        $data = $this->weatherService->getWeather($request);

        return $this->sendResponse($data, 'Weather information retrieved');
    }
}
