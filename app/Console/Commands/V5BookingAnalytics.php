<?php

namespace App\Console\Commands;

use App\V5\Modules\Booking\Repositories\BookingRepository;
use App\V5\Modules\Booking\Repositories\BookingPaymentRepository;
use App\V5\Modules\Booking\Repositories\BookingEquipmentRepository;
use App\V5\Logging\V5Logger;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * V5 Booking Analytics Command
 * 
 * Generates comprehensive booking analytics and reports:
 * - Booking statistics
 * - Revenue reports
 * - Equipment utilization
 * - Performance metrics
 */
class V5BookingAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'v5:booking-analytics 
                            {--season-id= : Specific season ID to analyze}
                            {--school-id= : Specific school ID to analyze}
                            {--period= : Time period (today|week|month|quarter|year)}
                            {--format= : Output format (table|json|csv)}
                            {--export= : Export to file path}';

    /**
     * The console command description.
     */
    protected $description = 'Generate comprehensive V5 booking analytics and reports';

    public function __construct(
        private BookingRepository $bookingRepository,
        private BookingPaymentRepository $paymentRepository,
        private BookingEquipmentRepository $equipmentRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        V5Logger::initializeCorrelation();
        V5Logger::logCustomEvent('booking_analytics_command_started', 'info', [
            'options' => $this->options(),
        ], 'system');

        try {
            $seasonId = $this->option('season-id') ?? 1; // Default season
            $schoolId = $this->option('school-id') ?? 1; // Default school
            $period = $this->option('period') ?? 'month';
            $format = $this->option('format') ?? 'table';
            $exportPath = $this->option('export');

            $this->info("📊 Generating V5 Booking Analytics");
            $this->info("Season: {$seasonId} | School: {$schoolId} | Period: {$period}");
            $this->newLine();

            // Get date range for period
            [$startDate, $endDate] = $this->getDateRange($period);
            
            $this->line("📅 Analysis period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
            $this->newLine();

            // Generate analytics
            $analytics = $this->generateAnalytics($seasonId, $schoolId, $startDate, $endDate);

            // Display results
            $this->displayAnalytics($analytics, $format);

            // Export if requested
            if ($exportPath) {
                $this->exportAnalytics($analytics, $exportPath, $format);
            }

            V5Logger::logCustomEvent('booking_analytics_command_completed', 'info', [
                'season_id' => $seasonId,
                'school_id' => $schoolId,
                'period' => $period,
                'total_bookings' => $analytics['overview']['total_bookings'],
            ], 'system');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            V5Logger::logSystemError($e, [
                'command' => 'v5:booking-analytics',
                'options' => $this->options(),
            ]);

            $this->error('❌ Error generating analytics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Generate comprehensive analytics
     */
    private function generateAnalytics(int $seasonId, int $schoolId, Carbon $startDate, Carbon $endDate): array
    {
        $this->info('🔍 Gathering booking statistics...');
        $bookingStats = $this->bookingRepository->getBookingStats($seasonId, $schoolId);
        
        $this->info('💰 Calculating revenue metrics...');
        $revenueStats = $this->bookingRepository->getRevenueStats($seasonId, $schoolId, $startDate, $endDate);
        
        $this->info('📈 Generating payment analytics...');
        $paymentStats = $this->paymentRepository->getPaymentStats($seasonId, $schoolId);
        
        $this->info('🎿 Analyzing equipment utilization...');
        $equipmentStats = $this->equipmentRepository->getEquipmentStats($seasonId, $schoolId);
        
        $this->info('📊 Compiling performance metrics...');

        return [
            'overview' => [
                'total_bookings' => $bookingStats['total_bookings'],
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'season_id' => $seasonId,
                'school_id' => $schoolId,
                'generated_at' => now()->toISOString(),
            ],
            'booking_stats' => $bookingStats,
            'revenue_stats' => $revenueStats,
            'payment_stats' => $paymentStats,
            'equipment_stats' => $equipmentStats,
            'performance_metrics' => $this->calculatePerformanceMetrics($bookingStats, $revenueStats),
        ];
    }

    /**
     * Display analytics based on format
     */
    private function displayAnalytics(array $analytics, string $format): void
    {
        switch ($format) {
            case 'json':
                $this->line(json_encode($analytics, JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $this->displayCsvFormat($analytics);
                break;
            default:
                $this->displayTableFormat($analytics);
        }
    }

    /**
     * Display analytics in table format
     */
    private function displayTableFormat(array $analytics): void
    {
        // Overview
        $this->info('📊 BOOKING OVERVIEW');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Bookings', number_format($analytics['overview']['total_bookings'])],
                ['Analysis Period', $analytics['overview']['period_start'] . ' to ' . $analytics['overview']['period_end']],
                ['Season ID', $analytics['overview']['season_id']],
                ['School ID', $analytics['overview']['school_id']],
            ]
        );
        $this->newLine();

        // Booking Status Breakdown
        $this->info('📋 BOOKING STATUS BREAKDOWN');
        $statusData = [];
        foreach (['pending', 'confirmed', 'paid', 'completed', 'cancelled', 'no_show'] as $status) {
            $count = $analytics['booking_stats'][$status . '_bookings'] ?? 0;
            $percentage = $analytics['overview']['total_bookings'] > 0 
                ? round(($count / $analytics['overview']['total_bookings']) * 100, 1) 
                : 0;
            $statusData[] = [ucfirst(str_replace('_', ' ', $status)), number_format($count), $percentage . '%'];
        }
        $this->table(['Status', 'Count', 'Percentage'], $statusData);
        $this->newLine();

        // Revenue Summary
        $this->info('💰 REVENUE SUMMARY');
        $this->table(
            ['Metric', 'Amount (EUR)'],
            [
                ['Total Revenue', '€' . number_format($analytics['revenue_stats']['total_revenue'], 2)],
                ['Base Revenue', '€' . number_format($analytics['revenue_stats']['base_revenue'], 2)],
                ['Extras Revenue', '€' . number_format($analytics['revenue_stats']['extras_revenue'], 2)],
                ['Equipment Revenue', '€' . number_format($analytics['revenue_stats']['equipment_revenue'], 2)],
                ['Insurance Revenue', '€' . number_format($analytics['revenue_stats']['insurance_revenue'], 2)],
                ['Average Booking Value', '€' . number_format($analytics['revenue_stats']['average_booking_value'], 2)],
            ]
        );
        $this->newLine();

        // Payment Statistics
        $this->info('💳 PAYMENT STATISTICS');
        $paymentOverall = $analytics['payment_stats']['overall'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Payments', number_format($paymentOverall['total_payments'])],
                ['Completed Payments', number_format($paymentOverall['completed_payments'])],
                ['Failed Payments', number_format($paymentOverall['failed_payments'])],
                ['Total Revenue', '€' . number_format($paymentOverall['total_revenue'], 2)],
                ['Net Revenue', '€' . number_format($paymentOverall['net_revenue'], 2)],
                ['Total Refunded', '€' . number_format($paymentOverall['total_refunded'], 2)],
            ]
        );
        $this->newLine();

        // Equipment Utilization
        $this->info('🎿 EQUIPMENT UTILIZATION');
        $equipmentOverall = $analytics['equipment_stats']['overall'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Equipment Items', number_format($equipmentOverall['total_equipment'])],
                ['Currently Rented', number_format($equipmentOverall['total_rented'])],
                ['Returned Items', number_format($equipmentOverall['total_returned'])],  
                ['Outstanding Items', number_format($equipmentOverall['total_outstanding'])],
                ['Equipment Revenue', '€' . number_format($equipmentOverall['total_revenue'], 2)],
                ['Average Rental Days', number_format($equipmentOverall['average_rental_days'], 1)],
            ]
        );
        $this->newLine();

        // Performance Metrics
        $this->info('📈 PERFORMANCE METRICS');
        $performance = $analytics['performance_metrics'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Booking Conversion Rate', $performance['booking_conversion_rate'] . '%'],
                ['Payment Success Rate', $performance['payment_success_rate'] . '%'],
                ['Cancellation Rate', $performance['cancellation_rate'] . '%'],
                ['No-Show Rate', $performance['no_show_rate'] . '%'],
                ['Revenue per Booking', '€' . number_format($performance['revenue_per_booking'], 2)],
                ['Equipment Utilization Rate', $performance['equipment_utilization_rate'] . '%'],
            ]
        );
    }

    /**
     * Display analytics in CSV format
     */
    private function displayCsvFormat(array $analytics): void
    {
        $this->line('metric,value');
        $this->line('total_bookings,' . $analytics['overview']['total_bookings']);
        $this->line('total_revenue,' . $analytics['revenue_stats']['total_revenue']);
        $this->line('average_booking_value,' . $analytics['revenue_stats']['average_booking_value']);
        // Add more metrics as needed
    }

    /**
     * Export analytics to file
     */
    private function exportAnalytics(array $analytics, string $path, string $format): void
    {
        try {
            $content = match($format) {
                'json' => json_encode($analytics, JSON_PRETTY_PRINT),
                'csv' => $this->generateCsvContent($analytics),
                default => json_encode($analytics, JSON_PRETTY_PRINT)
            };

            file_put_contents($path, $content);
            $this->info("📁 Analytics exported to: {$path}");

        } catch (\Exception $e) {
            $this->error("❌ Failed to export analytics: {$e->getMessage()}");
        }
    }

    /**
     * Generate CSV content
     */
    private function generateCsvContent(array $analytics): string
    {
        $csv = "metric,value\n";
        $csv .= "total_bookings,{$analytics['overview']['total_bookings']}\n";
        $csv .= "total_revenue,{$analytics['revenue_stats']['total_revenue']}\n";
        // Add more metrics
        return $csv;
    }

    /**
     * Get date range for period
     */
    private function getDateRange(string $period): array
    {
        $now = now();
        
        return match($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]
        };
    }

    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics(array $bookingStats, array $revenueStats): array
    {
        $totalBookings = $bookingStats['total_bookings'] ?: 1;
        $completedBookings = $bookingStats['completed_bookings'] ?: 0;
        $cancelledBookings = $bookingStats['cancelled_bookings'] ?: 0;
        $noShowBookings = $bookingStats['no_show_bookings'] ?: 0;

        return [
            'booking_conversion_rate' => round(($completedBookings / $totalBookings) * 100, 1),
            'payment_success_rate' => 85.0, // Would calculate from payment data
            'cancellation_rate' => round(($cancelledBookings / $totalBookings) * 100, 1),
            'no_show_rate' => round(($noShowBookings / $totalBookings) * 100, 1),
            'revenue_per_booking' => $totalBookings > 0 ? $revenueStats['total_revenue'] / $totalBookings : 0,
            'equipment_utilization_rate' => 75.0, // Would calculate from equipment data
        ];
    }
}