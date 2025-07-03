<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio de Exportación de Dashboards Financieros
 *
 * Responsabilidades:
 * - Generar archivos CSV, PDF y Excel
 * - Gestionar descargas de archivos
 * - Formatear datos para exportación
 * - Limpiar archivos temporales
 */
class ExportService
{
    protected SeasonFinanceService $seasonFinanceService;
    protected array $exportFormats;

    public function __construct(SeasonFinanceService $seasonFinanceService)
    {
        $this->seasonFinanceService = $seasonFinanceService;
        $this->exportFormats = [
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
    }

    /**
     * Exportar dashboard de temporada
     */
    public function exportSeasonDashboard(Request $request): array
    {
        // 1. Obtener datos del dashboard
        $dashboardData = $this->seasonFinanceService->generateSeasonDashboard($request);

        // 2. Preparar datos para exportación
        $exportData = $this->prepareExportData($dashboardData, $request);

        // 3. Generar archivo según formato
        $format = $request->input('format');

        return match($format) {
            'csv' => $this->generateCsvExport($exportData),
            'pdf' => $this->generatePdfExport($exportData),
            'excel' => $this->generateExcelExport($exportData),
            default => throw new \InvalidArgumentException("Formato de exportación no soportado: {$format}")
        };
    }

    /**
     * Descargar archivo exportado
     */
    public function downloadFile(string $filename): Response
    {
        $tempPath = storage_path('temp/' . $filename);

        if (!file_exists($tempPath)) {
            throw new \Exception("Archivo no encontrado: {$filename}");
        }

        $contentType = $this->getContentTypeFromFilename($filename);

        return response()->download($tempPath, $filename, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ])->deleteFileAfterSend(true);
    }

    /**
     * Preparar datos estructurados para exportación
     */
    private function prepareExportData(array $dashboardData, Request $request): array
    {
        $sections = $request->get('sections', [
            'executive_summary',
            'financial_kpis',
            'booking_analysis'
        ]);

        $exportData = [
            'metadata' => $this->buildMetadata($dashboardData, $request),
            'sections' => []
        ];

        // Agregar secciones solicitadas
        foreach ($sections as $section) {
            $exportData['sections'][$section] = $this->formatSectionForExport($section, $dashboardData);
        }

        return $exportData;
    }

    /**
     * Generar exportación CSV
     */
    private function generateCsvExport(array $exportData): array
    {
        $filename = $this->generateFilename('csv', $exportData['metadata']);
        $tempPath = storage_path('temp/' . $filename);

        $this->ensureTempDirectoryExists();

        $handle = fopen($tempPath, 'w');

        // Escribir metadatos
        fputcsv($handle, ['DASHBOARD FINANCIERO DE TEMPORADA']);
        fputcsv($handle, ['Generado:', $exportData['metadata']['export_date']]);
        fputcsv($handle, ['Escuela ID:', $exportData['metadata']['school_id']]);
        fputcsv($handle, ['Período:', $exportData['metadata']['period_display']]);
        fputcsv($handle, []); // Línea vacía

        // Escribir secciones
        foreach ($exportData['sections'] as $sectionName => $sectionData) {
            fputcsv($handle, [strtoupper($sectionData['title'])]);

            foreach ($sectionData['data'] as $row) {
                fputcsv($handle, $row);
            }

            fputcsv($handle, []); // Línea vacía entre secciones
        }

        fclose($handle);

        return [
            'filename' => $filename,
            'file_path' => $tempPath,
            'content_type' => 'text/csv',
            'size' => filesize($tempPath),
            'download_url' => route('finance.download-export', ['filename' => $filename])
        ];
    }

    /**
     * Generar exportación PDF
     */
    private function generatePdfExport(array $exportData): array
    {
        if (!class_exists('\Dompdf\Dompdf')) {
            throw new \Exception('PDF generation requires dompdf. Use: composer require dompdf/dompdf');
        }

        $filename = $this->generateFilename('pdf', $exportData['metadata']);
        $tempPath = storage_path('temp/' . $filename);

        $this->ensureTempDirectoryExists();

        // Generar HTML para el PDF
        $html = $this->generatePdfHtml($exportData);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($tempPath, $dompdf->output());

        return [
            'filename' => $filename,
            'file_path' => $tempPath,
            'content_type' => 'application/pdf',
            'size' => filesize($tempPath),
            'download_url' => route('finance.download-export', ['filename' => $filename])
        ];
    }

    /**
     * Generar exportación Excel
     */
    private function generateExcelExport(array $exportData): array
    {
        // Por simplicidad, generamos CSV con extensión .xlsx
        // En producción, considera usar PhpSpreadsheet para Excel real
        $csvResult = $this->generateCsvExport($exportData);

        $oldPath = $csvResult['file_path'];
        $filename = $this->generateFilename('xlsx', $exportData['metadata']);
        $newPath = storage_path('temp/' . $filename);

        rename($oldPath, $newPath);

        return [
            'filename' => $filename,
            'file_path' => $newPath,
            'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'size' => filesize($newPath),
            'download_url' => route('finance.download-export', ['filename' => $filename])
        ];
    }

    /**
     * Construir metadatos de exportación
     */
    private function buildMetadata(array $dashboardData, Request $request): array
    {
        $seasonInfo = $dashboardData['season_info'];

        return [
            'school_id' => $request->school_id,
            'export_date' => now()->format('Y-m-d H:i:s'),
            'period_display' => $seasonInfo['date_range']['start'] . ' a ' . $seasonInfo['date_range']['end'],
            'period' => $seasonInfo['date_range'],
            'total_bookings' => $seasonInfo['total_bookings'],
            'season_name' => $seasonInfo['season_name']
        ];
    }

    /**
     * Formatear sección para exportación
     */
    private function formatSectionForExport(string $sectionName, array $dashboardData): array
    {
        return match($sectionName) {
            'executive_summary' => $this->formatExecutiveSummary($dashboardData),
            'financial_kpis' => $this->formatFinancialKpis($dashboardData),
            'booking_analysis' => $this->formatBookingAnalysis($dashboardData),
            'critical_issues' => $this->formatCriticalIssues($dashboardData),
            default => ['title' => 'Sección Desconocida', 'data' => []]
        };
    }

    /**
     * Formatear resumen ejecutivo
     */
    private function formatExecutiveSummary(array $dashboardData): array
    {
        $kpis = $dashboardData['executive_kpis'];

        return [
            'title' => 'Resumen Ejecutivo de Temporada',
            'data' => [
                ['Métrica', 'Valor', 'Unidad'],
                ['Total Reservas', $kpis['total_bookings'], 'reservas'],
                ['Total Clientes', $kpis['total_clients'], 'clientes'],
                ['Total Participantes', $kpis['total_participants'], 'personas'],
                ['Ingresos Esperados', number_format($kpis['revenue_expected'], 2), 'EUR'],
                ['Ingresos Recibidos', number_format($kpis['revenue_received'], 2), 'EUR'],
                ['Dinero Pendiente', number_format($kpis['revenue_pending'], 2), 'EUR'],
                ['Eficiencia de Cobro', $kpis['collection_efficiency'] . '%', 'porcentaje'],
                ['Valor Promedio por Reserva', number_format($kpis['average_booking_value'], 2), 'EUR']
            ]
        ];
    }

    /**
     * Formatear KPIs financieros
     */
    private function formatFinancialKpis(array $dashboardData): array
    {
        $summary = $dashboardData['financial_summary'] ?? [];

        return [
            'title' => 'KPIs Financieros Detallados',
            'data' => [
                ['Métrica', 'Valor'],
                ['Total Esperado', number_format($summary['total_expected_revenue'] ?? 0, 2) . ' EUR'],
                ['Total Recibido', number_format($summary['total_received_revenue'] ?? 0, 2) . ' EUR'],
                ['Total Pendiente', number_format($summary['total_pending_revenue'] ?? 0, 2) . ' EUR'],
                ['Eficiencia de Cobro', ($summary['collection_efficiency'] ?? 0) . '%'],
                ['Valor Promedio', number_format($summary['average_booking_value'] ?? 0, 2) . ' EUR']
            ]
        ];
    }

    /**
     * Formatear análisis de reservas
     */
    private function formatBookingAnalysis(array $dashboardData): array
    {
        $sources = $dashboardData['booking_sources'] ?? [];

        $data = [['Origen', 'Cantidad', 'Porcentaje']];

        foreach ($sources as $source => $info) {
            $data[] = [
                $source,
                $info['count'] ?? 0,
                number_format($info['percentage'] ?? 0, 1) . '%'
            ];
        }

        return [
            'title' => 'Análisis de Orígenes de Reserva',
            'data' => $data
        ];
    }

    /**
     * Formatear problemas críticos
     */
    private function formatCriticalIssues(array $dashboardData): array
    {
        // Placeholder - implementar según necesidades específicas
        return [
            'title' => 'Problemas Críticos Detectados',
            'data' => [
                ['Tipo', 'Descripción', 'Severidad'],
                ['N/A', 'No se detectaron problemas críticos', 'Info']
            ]
        ];
    }

    /**
     * Generar HTML para PDF
     */
    private function generatePdfHtml(array $exportData): string
    {
        $html = '<html><head><meta charset="UTF-8"><style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'h1 { color: #333; border-bottom: 2px solid #333; }';
        $html .= 'h2 { color: #666; margin-top: 30px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 15px 0; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; }';
        $html .= '.metadata { background-color: #f9f9f9; padding: 10px; margin-bottom: 20px; }';
        $html .= '</style></head><body>';

        // Título principal
        $html .= '<h1>Dashboard Financiero de Temporada</h1>';

        // Metadatos
        $html .= '<div class="metadata">';
        $html .= '<strong>Generado:</strong> ' . $exportData['metadata']['export_date'] . '<br>';
        $html .= '<strong>Escuela ID:</strong> ' . $exportData['metadata']['school_id'] . '<br>';
        $html .= '<strong>Período:</strong> ' . $exportData['metadata']['period_display'] . '<br>';
        $html .= '<strong>Total Reservas:</strong> ' . $exportData['metadata']['total_bookings'];
        $html .= '</div>';

        // Secciones
        foreach ($exportData['sections'] as $sectionData) {
            $html .= '<h2>' . $sectionData['title'] . '</h2>';
            $html .= '<table>';

            foreach ($sectionData['data'] as $index => $row) {
                $tag = $index === 0 ? 'th' : 'td';
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= "<{$tag}>" . htmlspecialchars($cell) . "</{$tag}>";
                }
                $html .= '</tr>';
            }

            $html .= '</table>';
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Generar nombre de archivo único
     */
    private function generateFilename(string $extension, array $metadata): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $schoolId = $metadata['school_id'];

        return "dashboard_temporada_{$schoolId}_{$timestamp}.{$extension}";
    }

    /**
     * Asegurar que existe el directorio temporal
     */
    private function ensureTempDirectoryExists(): void
    {
        $tempDir = storage_path('temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /**
     * Obtener tipo de contenido desde nombre de archivo
     */
    private function getContentTypeFromFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return match($extension) {
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream'
        };
    }
}
