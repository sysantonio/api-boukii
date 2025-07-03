<?php

namespace App\Services\Finance\Analyzers;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

/**
 * Calculadora de KPIs Financieros
 *
 * Responsabilidades:
 * - Calcular KPIs ejecutivos
 * - Calcular métricas de ingresos
 * - Analizar eficiencia de cobro
 * - Debug de cálculos de reservas
 */
class KpiCalculator
{
    /**
     * Calcular KPIs ejecutivos principales
     */
    public function calculateExecutiveKpis(array $classification, Request $request): array
    {
        $productionBookings = $classification['production'];

        return [
            'total_bookings' => $productionBookings->count(),
            'total_clients' => $this->calculateTotalClients($productionBookings),
            'total_participants' => $this->calculateTotalParticipants($productionBookings),
            'revenue_expected' => $this->calculateTotalExpectedRevenue($productionBookings),
            'revenue_received' => $this->calculateTotalReceivedRevenue($productionBookings),
            'revenue_pending' => $this->calculateTotalPendingRevenue($productionBookings),
            'collection_efficiency' => $this->calculateCollectionEfficiency($productionBookings),
            'consistency_rate' => $this->calculateConsistencyRate($productionBookings),
            'average_booking_value' => $this->calculateAverageBookingValue($productionBookings)
        ];
    }

    /**
     * Calcular total de ingresos esperados
     */
    public function calculateTotalExpectedRevenue(Collection $bookings): float
    {
        return $bookings->sum(function ($booking) {
            return $this->calculateBookingExpectedRevenue($booking);
        });
    }

    /**
     * Calcular total de ingresos recibidos
     */
    public function calculateTotalReceivedRevenue(Collection $bookings): float
    {
        return $bookings->sum(function ($booking) {
            return $this->calculateBookingReceivedRevenue($booking);
        });
    }

    /**
     * Calcular total de ingresos pendientes
     */
    public function calculateTotalPendingRevenue(Collection $bookings): float
    {
        return $this->calculateTotalExpectedRevenue($bookings) - $this->calculateTotalReceivedRevenue($bookings);
    }

    /**
     * Calcular eficiencia de cobro
     */
    public function calculateCollectionEfficiency(Collection $bookings): float
    {
        $expected = $this->calculateTotalExpectedRevenue($bookings);
        $received = $this->calculateTotalReceivedRevenue($bookings);

        return $expected > 0 ? round(($received / $expected) * 100, 2) : 100;
    }

    /**
     * Calcular valor promedio por reserva
     */
    public function calculateAverageBookingValue(Collection $bookings): float
    {
        if ($bookings->isEmpty()) {
            return 0;
        }

        $totalRevenue = $this->calculateTotalExpectedRevenue($bookings);
        return round($totalRevenue / $bookings->count(), 2);
    }

    /**
     * Debug de cálculo de una reserva específica
     */
    public function debugBookingCalculation(Booking $booking): array
    {
        return [
            'booking_id' => $booking->id,
            'expected_revenue' => $this->calculateBookingExpectedRevenue($booking),
            'received_revenue' => $this->calculateBookingReceivedRevenue($booking),
            'pending_revenue' => $this->calculateBookingPendingRevenue($booking),
            'payment_status' => $booking->status,
            'calculation_method' => $this->getCalculationMethod($booking),
            'breakdown' => $this->getRevenueBreakdown($booking)
        ];
    }

    /**
     * Calcular total de clientes únicos
     */
    private function calculateTotalClients(Collection $bookings): int
    {
        return $bookings->pluck('client_id')->unique()->count();
    }

    /**
     * Calcular total de participantes
     */
    private function calculateTotalParticipants(Collection $bookings): int
    {
        return $bookings->sum(function ($booking) {
            return $booking->bookingUsers->count();
        });
    }

    /**
     * Calcular ingresos esperados de una reserva
     */
    private function calculateBookingExpectedRevenue(Booking $booking): float
    {
        // Placeholder - implementar según servicio de cálculo de precios
        return $booking->total_amount ?? 0;
    }

    /**
     * Calcular ingresos recibidos de una reserva
     */
    private function calculateBookingReceivedRevenue(Booking $booking): float
    {
        return $booking->payments->where('status', 'completed')->sum('amount');
    }

    /**
     * Calcular ingresos pendientes de una reserva
     */
    private function calculateBookingPendingRevenue(Booking $booking): float
    {
        return $this->calculateBookingExpectedRevenue($booking) - $this->calculateBookingReceivedRevenue($booking);
    }

    /**
     * Calcular tasa de consistencia
     */
    private function calculateConsistencyRate(Collection $bookings): float
    {
        // Placeholder - implementar según lógica de consistencia específica
        return 95.0;
    }

    /**
     * Obtener método de cálculo utilizado
     */
    private function getCalculationMethod(Booking $booking): string
    {
        // Placeholder - determinar método según configuración
        return 'standard';
    }

    /**
     * Obtener desglose de ingresos
     */
    private function getRevenueBreakdown(Booking $booking): array
    {
        return [
            'base_price' => 0, // Implementar
            'extras_price' => 0, // Implementar
            'insurance_price' => 0, // Implementar
            'discounts_applied' => 0, // Implementar
            'taxes' => 0 // Implementar
        ];
    }
}
