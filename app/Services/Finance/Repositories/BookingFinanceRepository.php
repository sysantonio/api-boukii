<?php

namespace App\Services\Finance\Repositories;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Repositorio de Finanzas de Reservas
 *
 * Responsabilidades:
 * - Obtener reservas con optimizaciones
 * - Aplicar filtros financieros
 * - Gestionar relaciones de datos
 * - Optimizar consultas según nivel
 */
class BookingFinanceRepository
{
    // Cursos a excluir
    const EXCLUDED_COURSES = [260, 243];

    /**
     * Obtener reservas de temporada optimizadas
     */
    public function getSeasonBookingsOptimized(Request $request, array $dateRange, string $optimizationLevel): Collection
    {
        return $this->buildBaseQuery($request, $dateRange)->get();

        /*// Aplicar optimizaciones según nivel
        switch ($optimizationLevel) {
            case 'fast':
                return $this->applyFastOptimizations($query);
            case 'detailed':
                return $this->applyDetailedOptimizations($query);
            default: // balanced
                return $this->applyBalancedOptimizations($query);
        }*/
    }

    /**
     * Obtener todas las reservas de una escuela
     */
    public function getAllSchoolBookings(int $schoolId): Collection
    {
        return Booking::with([
            'bookingUsers.course',
            'clientMain',
            'payments',
            'vouchersLogs'
        ])
            ->where('school_id', $schoolId)
            ->get();
    }

    /**
     * Construir query base
     */
    private function buildBaseQuery(Request $request, array $dateRange)
    {
        $query = Booking::query()
            ->with([
                'bookingUsers' => function($q) {
                    $q->with(['course.sport', 'client', 'bookingUserExtras.courseExtra']);
                },
                'payments',
                'vouchersLogs.voucher',
                'clientMain',
                'school'
            ])
            ->where('school_id', $request->school_id);

        // Aplicar filtros de fecha
        if (isset($dateRange['start_date']) && isset($dateRange['end_date'])) {
            $query->whereHas('bookingUsers', function($q) use ($dateRange) {
                $q->whereBetween('date', [$dateRange['start_date'], $dateRange['end_date']]);
            });
        }

        // Excluir cursos especificados
        $query->whereDoesntHave('bookingUsers.course', function($q) {
            $q->whereIn('id', self::EXCLUDED_COURSES);
        });

        return $query;
    }

    /**
     * Aplicar optimizaciones rápidas
     */
    private function applyFastOptimizations($query): Collection
    {
        return $query->with([
            'clientMain:id,name,email',
            'bookingUsers:id,booking_id,course_id,client_id,date',
            'payments:id,booking_id,amount,status'
        ])->get();
    }

    /**
     * Aplicar optimizaciones balanceadas
     */
    private function applyBalancedOptimizations($query): Collection
    {
        return $query->with([
            'bookingUsers.course.sport',
            'bookingUsers.client',
            'bookingUsers.bookingUserExtras.courseExtra',
            'payments',
            'vouchersLogs.voucher',
            'clientMain'
        ])->get();
    }

    /**
     * Aplicar optimizaciones detalladas
     */
    private function applyDetailedOptimizations($query): Collection
    {
        return $query->with([
            'bookingUsers.course.sport',
            'bookingUsers.course.courseExtras',
            'bookingUsers.client',
            'bookingUsers.bookingUserExtras.courseExtra',
            'bookingUsers.monitor',
            'payments',
            'vouchersLogs.voucher',
            'clientMain',
            'school',
            'bookingLogs'
        ])->get();
    }
}
