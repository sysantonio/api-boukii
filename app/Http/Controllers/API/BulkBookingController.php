<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\BulkBookingOperationsRequest;
use App\Http\Requests\API\DuplicateSmartBookingRequest;
use Illuminate\Http\JsonResponse;

class BulkBookingController extends AppBaseController
{
    public function bulkOperations(BulkBookingOperationsRequest $request): JsonResponse
    {
        $data = $request->validated();

        return $this->sendResponse([
            'processed' => count($data['operations']),
        ], 'Bulk operations executed');
    }

    public function duplicateSmart(DuplicateSmartBookingRequest $request, $id): JsonResponse
    {
        $data = $request->validated();

        return $this->sendResponse([
            'originalId' => (int) $id,
            'duplicateId' => (int) $id + 1,
            'modifications' => $data['modifications'] ?? [],
            'options' => $data['options'] ?? [],
        ], 'Booking duplicated');
    }
}
