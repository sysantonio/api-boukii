<?php

namespace App\V5\Modules\Booking\Services;

use App\V5\Modules\Booking\Repositories\BookingRepository;
use App\V5\Logging\V5Logger;
use Carbon\Carbon;

/**
 * V5 Booking Availability Service
 * 
 * Handles availability checking for courses, monitors, and resources.
 * Validates scheduling conflicts and provides alternative suggestions.
 */
class BookingAvailabilityService
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {}

    /**
     * Check availability for a booking request
     */
    public function checkAvailability(
        int $seasonId,
        int $schoolId,
        array $bookingData,
        ?int $excludeBookingId = null
    ): array {
        V5Logger::logPerformance('availability_check_started', 0, [
            'season_id' => $seasonId,
            'school_id' => $schoolId,
            'course_id' => $bookingData['course_id'] ?? null,
            'monitor_id' => $bookingData['monitor_id'] ?? null,
            'start_date' => $bookingData['start_date'] ?? null,
        ]);

        $startTime = microtime(true);

        try {
            $results = [
                'available' => true,
                'conflicts' => [],
                'warnings' => [],
                'suggestions' => [],
                'capacity_info' => [],
            ];

            $startDate = Carbon::parse($bookingData['start_date']);
            $startTime = $bookingData['start_time'] ?? null;
            $endTime = $bookingData['end_time'] ?? null;

            // Check course availability
            if (isset($bookingData['course_id'])) {
                $courseAvailability = $this->checkCourseAvailability(
                    $bookingData['course_id'],
                    $seasonId,
                    $schoolId,
                    $startDate,
                    $startTime,
                    $endTime,
                    count($bookingData['participants'] ?? [1]),
                    $excludeBookingId
                );

                if (!$courseAvailability['available']) {
                    $results['available'] = false;
                    $results['conflicts'] = array_merge($results['conflicts'], $courseAvailability['conflicts']);
                }

                $results['capacity_info']['course'] = $courseAvailability['capacity_info'];
            }

            // Check monitor availability
            if (isset($bookingData['monitor_id'])) {
                $monitorAvailability = $this->checkMonitorAvailability(
                    $bookingData['monitor_id'],
                    $seasonId,
                    $schoolId,
                    $startDate,
                    $startTime,
                    $endTime,
                    $excludeBookingId
                );

                if (!$monitorAvailability['available']) {
                    $results['available'] = false;
                    $results['conflicts'] = array_merge($results['conflicts'], $monitorAvailability['conflicts']);
                }

                $results['capacity_info']['monitor'] = $monitorAvailability['capacity_info'];
            }

            // Check equipment availability
            if (!empty($bookingData['equipment'])) {
                $equipmentAvailability = $this->checkEquipmentAvailability(
                    $bookingData['equipment'],
                    $seasonId,
                    $schoolId,
                    $startDate,
                    Carbon::parse($bookingData['end_date'] ?? $startDate),
                    $excludeBookingId
                );

                if (!$equipmentAvailability['available']) {
                    $results['available'] = false;
                    $results['conflicts'] = array_merge($results['conflicts'], $equipmentAvailability['conflicts']);
                }

                $results['capacity_info']['equipment'] = $equipmentAvailability['capacity_info'];
            }

            // Generate suggestions if not available
            if (!$results['available']) {
                $results['suggestions'] = $this->generateAvailabilitySuggestions(
                    $bookingData,
                    $seasonId,
                    $schoolId,
                    $results['conflicts']
                );
            }

            // Add warnings for potential issues
            $results['warnings'] = $this->generateAvailabilityWarnings($bookingData, $seasonId, $schoolId);

            $duration = microtime(true) - $startTime;
            V5Logger::logPerformance('availability_check_completed', $duration * 1000, [
                'available' => $results['available'],
                'conflicts_count' => count($results['conflicts']),
                'suggestions_count' => count($results['suggestions']),
            ]);

            return $results;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'operation' => 'availability_check',
                'booking_data' => $bookingData,
            ]);
            throw $e;
        }
    }

    /**
     * Check course availability
     */
    private function checkCourseAvailability(
        int $courseId,
        int $seasonId,
        int $schoolId,
        Carbon $startDate,
        ?string $startTime,
        ?string $endTime,
        int $requestedParticipants,
        ?int $excludeBookingId = null
    ): array {
        $availability = $this->bookingRepository->checkAvailability(
            $seasonId,
            $schoolId,
            $courseId,
            null,
            $startDate,
            $startTime,
            $endTime,
            $excludeBookingId
        );

        // Get course capacity information
        $courseCapacity = $this->getCourseCapacity($courseId);
        $currentBookings = $this->getCurrentCourseBookings($courseId, $startDate, $startTime, $endTime, $excludeBookingId);
        $currentParticipants = $currentBookings->sum('participant_count');

        $availableSpots = $courseCapacity['max_participants'] - $currentParticipants;
        $canAccommodate = $availableSpots >= $requestedParticipants;

        return [
            'available' => $availability['available'] && $canAccommodate,
            'conflicts' => $availability['conflicting_bookings'],
            'capacity_info' => [
                'max_participants' => $courseCapacity['max_participants'],
                'current_participants' => $currentParticipants,
                'available_spots' => $availableSpots,
                'requested_participants' => $requestedParticipants,
                'can_accommodate' => $canAccommodate,
            ],
        ];
    }

    /**
     * Check monitor availability
     */
    private function checkMonitorAvailability(
        int $monitorId,
        int $seasonId,
        int $schoolId,
        Carbon $startDate,
        ?string $startTime,
        ?string $endTime,
        ?int $excludeBookingId = null
    ): array {
        $availability = $this->bookingRepository->checkAvailability(
            $seasonId,
            $schoolId,
            null,
            $monitorId,
            $startDate,
            $startTime,
            $endTime,
            $excludeBookingId
        );

        // Get monitor schedule and workload information
        $monitorWorkload = $this->getMonitorWorkload($monitorId, $startDate);
        
        return [
            'available' => $availability['available'] && !$monitorWorkload['overloaded'],
            'conflicts' => $availability['conflicting_bookings'],
            'capacity_info' => [
                'current_bookings' => $monitorWorkload['current_bookings'],
                'recommended_max' => $monitorWorkload['recommended_max'],
                'overloaded' => $monitorWorkload['overloaded'],
                'fatigue_level' => $monitorWorkload['fatigue_level'],
            ],
        ];
    }

    /**
     * Check equipment availability
     */
    private function checkEquipmentAvailability(
        array $requestedEquipment,
        int $seasonId,
        int $schoolId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId = null
    ): array {
        $conflicts = [];
        $capacityInfo = [];

        foreach ($requestedEquipment as $equipment) {
            $equipmentType = $equipment['equipment_type'];
            $requestedQuantity = $equipment['quantity'] ?? 1;

            // Get available equipment of this type
            $availableEquipment = $this->getAvailableEquipment(
                $equipmentType,
                $seasonId,
                $schoolId,
                $startDate,
                $endDate,
                $excludeBookingId
            );

            $capacityInfo[$equipmentType] = [
                'requested' => $requestedQuantity,
                'available' => $availableEquipment['available_count'],
                'total_inventory' => $availableEquipment['total_count'],
                'can_fulfill' => $availableEquipment['available_count'] >= $requestedQuantity,
            ];

            if ($availableEquipment['available_count'] < $requestedQuantity) {
                $conflicts[] = [
                    'type' => 'equipment_shortage',
                    'equipment_type' => $equipmentType,
                    'requested' => $requestedQuantity,
                    'available' => $availableEquipment['available_count'],
                    'shortage' => $requestedQuantity - $availableEquipment['available_count'],
                ];
            }
        }

        return [
            'available' => empty($conflicts),
            'conflicts' => $conflicts,
            'capacity_info' => $capacityInfo,
        ];
    }

    /**
     * Generate availability suggestions
     */
    private function generateAvailabilitySuggestions(
        array $bookingData,
        int $seasonId,
        int $schoolId,
        array $conflicts
    ): array {
        $suggestions = [];

        foreach ($conflicts as $conflict) {
            switch ($conflict['type'] ?? 'general') {
                case 'equipment_shortage':
                    $suggestions[] = [
                        'type' => 'alternative_dates',
                        'description' => "Consider alternative dates when more {$conflict['equipment_type']} equipment is available",
                        'data' => $this->getAlternativeEquipmentDates($conflict, $seasonId, $schoolId),
                    ];
                    break;

                case 'schedule_conflict':
                    $suggestions[] = [
                        'type' => 'alternative_times',
                        'description' => 'Consider alternative time slots on the same date',
                        'data' => $this->getAlternativeTimeSlots($bookingData, $seasonId, $schoolId),
                    ];
                    break;

                case 'capacity_exceeded':
                    $suggestions[] = [
                        'type' => 'split_booking',
                        'description' => 'Consider splitting the group across multiple sessions',
                        'data' => $this->getSplitBookingSuggestions($bookingData, $seasonId, $schoolId),
                    ];
                    break;

                default:
                    $suggestions[] = [
                        'type' => 'alternative_dates',
                        'description' => 'Consider alternative dates with better availability',
                        'data' => $this->getAlternativeDates($bookingData, $seasonId, $schoolId),
                    ];
            }
        }

        return array_unique($suggestions, SORT_REGULAR);
    }

    /**
     * Generate availability warnings
     */
    private function generateAvailabilityWarnings(array $bookingData, int $seasonId, int $schoolId): array
    {
        $warnings = [];

        // Check for peak time booking
        if ($this->isPeakTime($bookingData)) {
            $warnings[] = [
                'type' => 'peak_time',
                'message' => 'This is a peak time slot - book early to secure availability',
                'severity' => 'info',
            ];
        }

        // Check for weather-dependent activities
        if ($this->isWeatherDependent($bookingData)) {
            $warnings[] = [
                'type' => 'weather_dependent',
                'message' => 'This activity is weather-dependent and may be cancelled due to poor conditions',
                'severity' => 'warning',
            ];
        }

        // Check for last-minute booking
        if ($this->isLastMinuteBooking($bookingData)) {
            $warnings[] = [
                'type' => 'last_minute',
                'message' => 'Last-minute bookings may have limited availability and options',
                'severity' => 'warning',
            ];
        }

        return $warnings;
    }

    /**
     * Helper methods
     */
    private function getCourseCapacity(int $courseId): array
    {
        // This would query the Course model
        // For now, return default values
        return [
            'max_participants' => 12,
            'min_participants' => 1,
        ];
    }

    private function getCurrentCourseBookings(
        int $courseId,
        Carbon $date,
        ?string $startTime,
        ?string $endTime,
        ?int $excludeBookingId
    ) {
        // This would query current bookings for the course
        // Return empty collection for now
        return collect();
    }

    private function getMonitorWorkload(int $monitorId, Carbon $date): array
    {
        // This would query monitor's current bookings and calculate workload
        return [
            'current_bookings' => 2,
            'recommended_max' => 6,
            'overloaded' => false,
            'fatigue_level' => 0.3,
        ];
    }

    private function getAvailableEquipment(
        string $equipmentType,
        int $seasonId,
        int $schoolId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeBookingId
    ): array {
        // This would query equipment inventory and current rentals
        return [
            'available_count' => 10,
            'total_count' => 15,
            'rented_count' => 5,
        ];
    }

    private function getAlternativeEquipmentDates(array $conflict, int $seasonId, int $schoolId): array
    {
        // Generate dates with better equipment availability
        return [
            ['date' => now()->addDays(2)->format('Y-m-d'), 'available_equipment' => 8],
            ['date' => now()->addDays(3)->format('Y-m-d'), 'available_equipment' => 12],
        ];
    }

    private function getAlternativeTimeSlots(array $bookingData, int $seasonId, int $schoolId): array
    {
        return [
            ['start_time' => '09:00', 'end_time' => '12:00', 'availability' => 'good'],
            ['start_time' => '14:00', 'end_time' => '17:00', 'availability' => 'excellent'],
        ];
    }

    private function getSplitBookingSuggestions(array $bookingData, int $seasonId, int $schoolId): array
    {
        $totalParticipants = count($bookingData['participants'] ?? []);
        
        return [
            [
                'session_1' => ['participants' => ceil($totalParticipants / 2), 'time' => '09:00-12:00'],
                'session_2' => ['participants' => floor($totalParticipants / 2), 'time' => '14:00-17:00'],
            ],
        ];
    }

    private function getAlternativeDates(array $bookingData, int $seasonId, int $schoolId): array
    {
        return [
            ['date' => now()->addDays(1)->format('Y-m-d'), 'availability' => 'good'],
            ['date' => now()->addDays(2)->format('Y-m-d'), 'availability' => 'excellent'],
            ['date' => now()->addDays(7)->format('Y-m-d'), 'availability' => 'excellent'],
        ];
    }

    private function isPeakTime(array $bookingData): bool
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        
        // Weekend or holiday period
        return $startDate->isWeekend() || $this->isHolidayPeriod($startDate);
    }

    private function isWeatherDependent(array $bookingData): bool
    {
        $type = $bookingData['type'] ?? '';
        $courseId = $bookingData['course_id'] ?? null;
        
        // This would check if the course/activity is weather-dependent
        return true; // Assuming ski activities are weather-dependent
    }

    private function isLastMinuteBooking(array $bookingData): bool
    {
        $startDate = Carbon::parse($bookingData['start_date'] ?? now());
        return now()->diffInHours($startDate) < 48;
    }

    private function isHolidayPeriod(Carbon $date): bool
    {
        // Check for holiday periods (Christmas, New Year, Easter, etc.)
        $month = $date->month;
        $day = $date->day;
        
        // Christmas/New Year period
        if ($month === 12 && $day >= 20) return true;
        if ($month === 1 && $day <= 10) return true;
        
        // Add other holiday periods as needed
        return false;
    }
}