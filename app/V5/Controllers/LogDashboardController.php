<?php

namespace App\V5\Controllers;

use App\V5\BaseV5Controller;
use App\V5\Services\LogAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogDashboardController extends BaseV5Controller
{
    private LogAnalysisService $logAnalysisService;

    public function __construct(LogAnalysisService $logAnalysisService)
    {
        $this->logAnalysisService = $logAnalysisService;
        parent::__construct($logAnalysisService);
    }

    /**
     * Get log dashboard overview
     */
    public function overview(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '24h');
        $schoolId = $request->get('school_id');

        $overview = $this->logAnalysisService->getDashboardOverview($timeframe, $schoolId);

        return $this->respond($overview);
    }

    /**
     * Search logs with advanced filters
     */
    public function searchLogs(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'correlation_id' => 'sometimes|string',
            'user_id' => 'sometimes|integer',
            'booking_id' => 'sometimes|string',
            'payment_id' => 'sometimes|string',
            'level' => 'sometimes|in:debug,info,warning,error,critical',
            'category' => 'sometimes|in:payment,booking,authentication,system_error,performance',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'search_text' => 'sometimes|string|max:255',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $results = $this->logAnalysisService->searchLogs($filters);

        return $this->respond($results);
    }

    /**
     * Get payment logs with financial context
     */
    public function paymentLogs(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'payment_id' => 'sometimes|string',
            'booking_id' => 'sometimes|string',
            'gateway' => 'sometimes|string',
            'status' => 'sometimes|in:success,failed,pending,refunded',
            'amount_min' => 'sometimes|numeric|min:0',
            'amount_max' => 'sometimes|numeric|min:0|gte:amount_min',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $results = $this->logAnalysisService->getPaymentLogs($filters);

        return $this->respond($results);
    }

    /**
     * Get correlation flow for debugging
     */
    public function correlationFlow(Request $request, string $correlationId): JsonResponse
    {
        $flow = $this->logAnalysisService->getCorrelationFlow($correlationId);

        if (! $flow) {
            return $this->respond(['error' => 'Correlation not found'], 404);
        }

        return $this->respond($flow);
    }

    /**
     * Get system errors with context
     */
    public function systemErrors(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'exception_class' => 'sometimes|string',
            'level' => 'sometimes|in:error,critical',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'resolved' => 'sometimes|boolean',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $results = $this->logAnalysisService->getSystemErrors($filters);

        return $this->respond($results);
    }

    /**
     * Get performance metrics
     */
    public function performanceMetrics(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'operation' => 'sometimes|string',
            'min_duration' => 'sometimes|integer|min:0',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $results = $this->logAnalysisService->getPerformanceMetrics($filters);

        return $this->respond($results);
    }

    /**
     * Get authentication events
     */
    public function authenticationEvents(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'user_id' => 'sometimes|integer',
            'event_type' => 'sometimes|in:login,logout,login_failed,account_locked',
            'suspicious_only' => 'sometimes|boolean',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $results = $this->logAnalysisService->getAuthenticationEvents($filters);

        return $this->respond($results);
    }

    /**
     * Get real-time alerts
     */
    public function realtimeAlerts(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'priority' => 'sometimes|in:high,medium,low',
            'category' => 'sometimes|string',
            'unresolved_only' => 'sometimes|boolean',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $alerts = $this->logAnalysisService->getRealtimeAlerts($filters);

        return $this->respond($alerts);
    }

    /**
     * Export logs to CSV/Excel
     */
    public function exportLogs(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'format' => 'required|in:csv,excel',
            'correlation_id' => 'sometimes|string',
            'category' => 'sometimes|string',
            'level' => 'sometimes|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'max_records' => 'sometimes|integer|min:1|max:10000',
        ]);

        $exportData = $this->logAnalysisService->exportLogs($filters);

        return $this->respond([
            'export_id' => $exportData['export_id'],
            'download_url' => $exportData['download_url'],
            'expires_at' => $exportData['expires_at'],
        ]);
    }

    /**
     * Get log statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'timeframe' => 'sometimes|in:1h,6h,24h,7d,30d',
            'group_by' => 'sometimes|in:hour,day,category,level',
            'school_id' => 'sometimes|integer',
        ]);

        $statistics = $this->logAnalysisService->getLogStatistics($filters);

        return $this->respond($statistics);
    }

    /**
     * Get detailed log entry
     */
    public function logDetail(Request $request, string $logId): JsonResponse
    {
        $detail = $this->logAnalysisService->getLogDetail($logId);

        if (! $detail) {
            return $this->respond(['error' => 'Log entry not found'], 404);
        }

        return $this->respond($detail);
    }

    /**
     * Mark alert as resolved
     */
    public function resolveAlert(Request $request, string $alertId): JsonResponse
    {
        $data = $request->validate([
            'resolution_notes' => 'sometimes|string|max:1000',
        ]);

        $result = $this->logAnalysisService->resolveAlert($alertId, $data);

        return $this->respond($result);
    }

    /**
     * Get financial reconciliation logs
     */
    public function financialReconciliation(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'gateway' => 'sometimes|string',
            'school_id' => 'sometimes|integer',
            'reconciliation_status' => 'sometimes|in:pending,matched,discrepancy',
        ]);

        $reconciliation = $this->logAnalysisService->getFinancialReconciliation($filters);

        return $this->respond($reconciliation);
    }
}
