<?php

namespace App\V5\Modules\Booking\Controllers;

use App\V5\Modules\Booking\Services\BookingService;
use App\V5\Modules\Booking\Requests\CreateBookingRequest;
use App\V5\Modules\Booking\Requests\UpdateBookingRequest;
use App\V5\Modules\Booking\Requests\UpdateBookingStatusRequest;
use App\V5\Modules\Booking\Requests\BookingFiltersRequest;
use App\V5\Logging\V5Logger;
use App\V5\Exceptions\V5ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * V5 Booking Controller
 * 
 * Handles all booking-related HTTP requests with comprehensive
 * validation, error handling, and response formatting.
 */
class BookingController
{
    public function __construct(
        private BookingService $bookingService,
        private V5ExceptionHandler $exceptionHandler
    ) {}

    /**
     * Get bookings list with filtering and pagination
     * 
     * @endpoint GET /api/v5/bookings
     */
    public function index(BookingFiltersRequest $request): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'bookings_list',
            'filters' => $request->getFilters(),
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $filters = $request->getFilters();
            $pagination = $request->getPagination();
            $includes = $request->getIncludes();

            $bookings = $this->bookingService->getBookings(
                $seasonId,
                $schoolId,
                $filters,
                $pagination['page'],
                $pagination['limit']
            );

            // Transform bookings for frontend
            $response = [
                'success' => true,
                'data' => [
                    'bookings' => $bookings->items(),
                    'pagination' => [
                        'current_page' => $bookings->currentPage(),
                        'per_page' => $bookings->perPage(),
                        'total' => $bookings->total(),
                        'last_page' => $bookings->lastPage(),
                        'from' => $bookings->firstItem(),
                        'to' => $bookings->lastItem(),
                    ],
                    'filters_applied' => array_filter($filters),
                ],
                'message' => 'Bookings retrieved successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'bookings_count' => $bookings->count(),
                'total_bookings' => $bookings->total(),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Create a new booking
     * 
     * @endpoint POST /api/v5/bookings
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_create',
            'type' => $request->input('type'),
            'participants_count' => count($request->input('participants', [])),
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $booking = $this->bookingService->createBooking(
                $request->validated(),
                $seasonId,
                $schoolId
            );

            $response = [
                'success' => true,
                'data' => [
                    'booking' => $booking->toFrontendArray(),
                ],
                'message' => 'Booking created successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Get specific booking by ID
     * 
     * @endpoint GET /api/v5/bookings/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_show',
            'booking_id' => $id,
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $booking = $this->bookingService->findBookingById($id, $seasonId, $schoolId);

            $response = [
                'success' => true,
                'data' => [
                    'booking' => $booking->toFrontendArray(),
                ],
                'message' => 'Booking retrieved successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'booking_reference' => $booking->booking_reference,
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Update booking
     * 
     * @endpoint PUT /api/v5/bookings/{id}
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_update',
            'booking_id' => $id,
            'updated_fields' => array_keys($request->validated()),
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $booking = $this->bookingService->updateBooking(
                $id,
                $request->validated(),
                $seasonId,
                $schoolId
            );

            $response = [
                'success' => true,
                'data' => [
                    'booking' => $booking->toFrontendArray(),
                ],
                'message' => 'Booking updated successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'booking_reference' => $booking->booking_reference,
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Delete booking
     * 
     * @endpoint DELETE /api/v5/bookings/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_delete',
            'booking_id' => $id,
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            $reason = $request->input('reason', 'Deleted via API');
            
            $result = $this->bookingService->deleteBooking($id, $seasonId, $schoolId, $reason);

            $response = [
                'success' => $result,
                'data' => null,
                'message' => $result ? 'Booking deleted successfully' : 'Failed to delete booking',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'deleted' => $result,
            ]);

            return response()->json($response, $result ? 200 : 500);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Update booking status
     * 
     * @endpoint PATCH /api/v5/bookings/{id}/status
     */
    public function updateStatus(UpdateBookingStatusRequest $request, int $id): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_status_update',
            'booking_id' => $id,
            'new_status' => $request->input('status'),
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $booking = $this->bookingService->updateBookingStatus(
                $id,
                $request->input('status'),
                $seasonId,
                $schoolId,
                $request->input('reason')
            );

            $response = [
                'success' => true,
                'data' => [
                    'booking' => $booking->toFrontendArray(),
                ],
                'message' => 'Booking status updated successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'booking_reference' => $booking->booking_reference,
                'new_status' => $booking->status,
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Get booking statistics
     * 
     * @endpoint GET /api/v5/bookings/stats
     */
    public function stats(Request $request): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_stats',
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $stats = $this->bookingService->getBookingStats($seasonId, $schoolId);

            $response = [
                'success' => true,
                'data' => [
                    'stats' => $stats,
                ],
                'message' => 'Booking statistics retrieved successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'total_bookings' => $stats['total_bookings'] ?? 0,
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Search bookings
     * 
     * @endpoint GET /api/v5/bookings/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
            'limit' => ['integer', 'min:1', 'max:50'],
        ]);

        V5Logger::logApiRequest($request, [
            'operation' => 'booking_search',
            'query' => $request->input('q'),
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            $query = $request->input('q');
            $limit = $request->input('limit', 20);
            
            $bookings = $this->bookingService->searchBookings($query, $seasonId, $schoolId, $limit);

            $response = [
                'success' => true,
                'data' => [
                    'bookings' => $bookings->map(fn($booking) => $booking->toFrontendArray()),
                    'query' => $query,
                    'results_count' => $bookings->count(),
                ],
                'message' => 'Search completed successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'results_count' => $bookings->count(),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Get upcoming bookings
     * 
     * @endpoint GET /api/v5/bookings/upcoming
     */
    public function upcoming(Request $request): JsonResponse
    {
        $request->validate([
            'days' => ['integer', 'min:1', 'max:30'],
            'limit' => ['integer', 'min:1', 'max:50'],
        ]);

        V5Logger::logApiRequest($request, [
            'operation' => 'booking_upcoming',
            'days' => $request->input('days', 7),
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            $days = $request->input('days', 7);
            $limit = $request->input('limit', 20);
            
            $bookings = $this->bookingService->getUpcomingBookings($seasonId, $schoolId, $days, $limit);

            $response = [
                'success' => true,
                'data' => [
                    'bookings' => $bookings->map(fn($booking) => $booking->toFrontendArray()),
                    'days_ahead' => $days,
                    'results_count' => $bookings->count(),
                ],
                'message' => 'Upcoming bookings retrieved successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'results_count' => $bookings->count(),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    /**
     * Get bookings requiring attention
     * 
     * @endpoint GET /api/v5/bookings/attention
     */
    public function attention(Request $request): JsonResponse
    {
        V5Logger::logApiRequest($request, [
            'operation' => 'booking_attention',
        ]);

        try {
            $seasonId = $request->get('season_id');
            $schoolId = $request->get('school_id');
            
            $bookingsRequiringAttention = $this->bookingService->getBookingsRequiringAttention(
                $seasonId, 
                $schoolId
            );

            $response = [
                'success' => true,
                'data' => [
                    'expired_pending' => $bookingsRequiringAttention['expired_pending']
                        ->map(fn($booking) => $booking->toFrontendArray()),
                    'unpaid_confirmed' => $bookingsRequiringAttention['unpaid_confirmed']
                        ->map(fn($booking) => $booking->toFrontendArray()),
                    'starting_soon' => $bookingsRequiringAttention['starting_soon']
                        ->map(fn($booking) => $booking->toFrontendArray()),
                ],
                'message' => 'Bookings requiring attention retrieved successfully',
                'timestamp' => now()->toISOString(),
            ];

            V5Logger::logApiResponse($request, $response, null, [
                'expired_pending_count' => count($bookingsRequiringAttention['expired_pending']),
                'unpaid_confirmed_count' => count($bookingsRequiringAttention['unpaid_confirmed']),
                'starting_soon_count' => count($bookingsRequiringAttention['starting_soon']),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }
}