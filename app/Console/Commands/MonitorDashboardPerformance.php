<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitorDashboardPerformance extends Command
{
    protected $signature = 'dashboard:monitor {school_id}';
    protected $description = 'Monitorear performance del dashboard financiero';

    public function handle()
    {
        $schoolId = $this->argument('school_id');

        $this->info("🔍 Analizando performance para escuela {$schoolId}...");

        // Análisis de datos
        $bookingsCount = DB::table('bookings')
            ->where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->count();

        $bookingUsersCount = DB::table('booking_users')
            ->join('bookings', 'bookings.id', '=', 'booking_users.booking_id')
            ->where('bookings.school_id', $schoolId)
            ->whereNull('bookings.deleted_at')
            ->count();

        $paymentsCount = DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->where('bookings.school_id', $schoolId)
            ->whereNull('payments.deleted_at')
            ->count();

        // Test de performance
        $this->info("\n📊 Estadísticas de datos:");
        $this->table(['Tabla', 'Registros'], [
            ['Bookings', number_format($bookingsCount)],
            ['Booking Users', number_format($bookingUsersCount)],
            ['Payments', number_format($paymentsCount)]
        ]);

        // Estimación de tiempos
        $this->info("\n⏱️ Estimaciones de tiempo:");
        $estimates = [
            ['fast (500 bookings)', $this->estimateTime($bookingsCount, 500)],
            ['balanced (1500 bookings)', $this->estimateTime($bookingsCount, 1500)],
            ['detailed (todas)', $this->estimateTime($bookingsCount, $bookingsCount)]
        ];

        $this->table(['Nivel', 'Tiempo Estimado'], $estimates);

        // Recomendaciones
        $this->info("\n💡 Recomendaciones:");
        if ($bookingsCount > 10000) {
            $this->warn("- Muchas reservas ({$bookingsCount}). Usa nivel 'fast' para uso diario.");
        }
        if ($bookingUsersCount > 50000) {
            $this->warn("- Considera implementar particionado de tablas.");
        }
        if ($paymentsCount > 20000) {
            $this->warn("- Muchos pagos. El análisis detallado será lento.");
        }
    }

    private function estimateTime(int $total, int $limit): string
    {
        $processed = min($total, $limit);
        $baseTime = 0.003; // 3ms por booking procesado
        $estimated = $processed * $baseTime;

        if ($estimated < 1) {
            return round($estimated * 1000) . 'ms';
        } else {
            return round($estimated, 1) . 's';
        }
    }
}
