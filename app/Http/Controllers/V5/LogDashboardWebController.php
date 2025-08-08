<?php

namespace App\Http\Controllers\V5;

use App\Http\Controllers\Controller;
use App\V5\Services\LogAnalysisService;
use App\V5\Logging\CorrelationTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LogDashboardWebController extends Controller
{
    private LogAnalysisService $logAnalysisService;

    public function __construct(LogAnalysisService $logAnalysisService)
    {
        $this->middleware('auth');
        $this->logAnalysisService = $logAnalysisService;
    }

    /**
     * Dashboard principal
     */
    public function index(Request $request)
    {
        $timeframe = $request->get('timeframe', '24h');
        $schoolId = $request->get('school_id');

        $overview = $this->logAnalysisService->getDashboardOverview($timeframe, $schoolId);
        $recentAlerts = Cache::get('v5_realtime_alerts', []);
        $recentAlerts = array_slice($recentAlerts, 0, 10);

        return view('v5.logs.dashboard', compact('overview', 'recentAlerts', 'timeframe'));
    }

    /**
     * Búsqueda de logs
     */
    public function search(Request $request)
    {
        $filters = $request->only([
            'correlation_id', 'user_id', 'booking_id', 'payment_id', 
            'level', 'category', 'date_from', 'date_to', 'search_text'
        ]);

        $filters['page'] = $request->get('page', 1);
        $filters['per_page'] = $request->get('per_page', 25);

        $results = $this->logAnalysisService->searchLogs($filters);

        return view('v5.logs.search', compact('results', 'filters'));
    }

    /**
     * Logs de pagos
     */
    public function payments(Request $request)
    {
        $filters = $request->only([
            'payment_id', 'booking_id', 'gateway', 'status', 
            'amount_min', 'amount_max', 'date_from', 'date_to'
        ]);

        $filters['page'] = $request->get('page', 1);
        $filters['per_page'] = $request->get('per_page', 25);

        $results = $this->logAnalysisService->getPaymentLogs($filters);

        return view('v5.logs.payments', compact('results', 'filters'));
    }

    /**
     * Detalle de correlación
     */
    public function correlationDetail(string $correlationId)
    {
        $flow = $this->logAnalysisService->getCorrelationFlow($correlationId);

        if (!$flow) {
            abort(404, 'Correlation not found');
        }

        return view('v5.logs.correlation-detail', compact('flow', 'correlationId'));
    }

    /**
     * Errores del sistema
     */
    public function systemErrors(Request $request)
    {
        $filters = $request->only([
            'exception_class', 'level', 'date_from', 'date_to', 'resolved'
        ]);

        $filters['page'] = $request->get('page', 1);
        $filters['per_page'] = $request->get('per_page', 25);

        $results = $this->logAnalysisService->getSystemErrors($filters);

        return view('v5.logs.system-errors', compact('results', 'filters'));
    }

    /**
     * Métricas de performance
     */
    public function performance(Request $request)
    {
        $filters = $request->only([
            'operation', 'min_duration', 'date_from', 'date_to'
        ]);

        $filters['page'] = $request->get('page', 1);
        $filters['per_page'] = $request->get('per_page', 25);

        $results = $this->logAnalysisService->getPerformanceMetrics($filters);

        return view('v5.logs.performance', compact('results', 'filters'));
    }

    /**
     * Alertas en tiempo real
     */
    public function realtimeAlerts(Request $request)
    {
        $filters = $request->only(['priority', 'category', 'unresolved_only']);
        $filters['limit'] = 50;

        $alerts = $this->logAnalysisService->getRealtimeAlerts($filters);

        return view('v5.logs.realtime-alerts', compact('alerts', 'filters'));
    }

    /**
     * Estadísticas y gráficos
     */
    public function statistics(Request $request)
    {
        $filters = $request->only(['timeframe', 'group_by', 'school_id']);
        $filters['timeframe'] = $filters['timeframe'] ?? '24h';
        $filters['group_by'] = $filters['group_by'] ?? 'hour';

        $statistics = $this->logAnalysisService->getLogStatistics($filters);

        return view('v5.logs.statistics', compact('statistics', 'filters'));
    }

    /**
     * Detalle de log específico
     */
    public function logDetail(string $logId)
    {
        $detail = $this->logAnalysisService->getLogDetail($logId);

        if (!$detail) {
            abort(404, 'Log entry not found');
        }

        return view('v5.logs.log-detail', compact('detail', 'logId'));
    }

    /**
     * API para actualizaciones AJAX
     */
    public function ajaxOverview(Request $request)
    {
        $timeframe = $request->get('timeframe', '24h');
        $schoolId = $request->get('school_id');

        $overview = $this->logAnalysisService->getDashboardOverview($timeframe, $schoolId);

        return response()->json($overview);
    }

    /**
     * API para alertas en tiempo real (para auto-refresh)
     */
    public function ajaxAlerts()
    {
        $alerts = Cache::get('v5_realtime_alerts', []);
        $recentAlerts = array_slice($alerts, 0, 5);

        return response()->json([
            'alerts' => $recentAlerts,
            'total_count' => count($alerts),
            'unresolved_count' => count(array_filter($alerts, fn($alert) => !($alert['resolved'] ?? false))),
        ]);
    }

    /**
     * Marcar alerta como resuelta
     */
    public function resolveAlert(Request $request, string $alertId)
    {
        $data = $request->validate([
            'resolution_notes' => 'sometimes|string|max:1000',
        ]);

        $result = $this->logAnalysisService->resolveAlert($alertId, $data);

        if ($request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with('success', 'Alert resolved successfully');
    }
}