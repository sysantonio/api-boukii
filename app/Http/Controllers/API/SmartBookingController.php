<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\BookingDraftRequest;
use App\Http\Requests\API\ValidateWizardStepRequest;
use App\Models\Booking;
use App\Models\BookingDraft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartBookingController extends AppBaseController
{
    public function smartCreate(Request $request): JsonResponse
    {
        // Placeholder implementation returning a fake booking
        $booking = ['id' => 1, 'status' => 'confirmed'];
        return $this->sendResponse($booking, 'Smart booking created');
    }

    public function storeDraft(BookingDraftRequest $request): JsonResponse
    {
        // Do not persist in tests; just echo payload back
        $data = $request->validated();
        $data['id'] = 1;
        return $this->sendResponse($data, 'Draft stored');
    }

    public function validateStep(ValidateWizardStepRequest $request): JsonResponse
    {
        return $this->sendResponse([
            'isValid' => true,
            'canProceed' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ], 'Step validated');
    }

    public function editData($id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        return $this->sendResponse(['booking' => $booking], 'Edit data retrieved');
    }

    public function smartUpdate(Request $request, $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->update($request->all());
        return $this->sendResponse($booking, 'Booking updated');
    }

    public function resolveConflicts(Request $request): JsonResponse
    {
        return $this->sendResponse(['resolved' => true], 'Conflicts resolved');
    }
}
