<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseCrudController;
use App\Http\Requests\API\CreateBookingLogAPIRequest;
use App\Http\Requests\API\UpdateBookingLogAPIRequest;
use App\Http\Resources\API\BookingLogResource;
use App\Models\BookingLog;
use App\Repositories\BookingLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class BookingLogController
 */

class BookingLogAPIController extends BaseCrudController
{
    public function __construct(BookingLogRepository $bookingLogRepo)
    {
        parent::__construct($bookingLogRepo);
        $this->resource = BookingLogResource::class;
    }
    public function store(CreateBookingLogAPIRequest $request): JsonResponse
    {
        return parent::store($request);
    }

    public function update($id, UpdateBookingLogAPIRequest $request): JsonResponse
    {
        return parent::update($id, $request);
    }


}
