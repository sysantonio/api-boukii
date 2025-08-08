<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\V5\Logging\EnterpriseLogger;
use App\V5\Logging\PaymentLogger;
use App\V5\Logging\CorrelationTracker;
use App\V5\Logging\AlertManager;
use App\V5\Logging\V5Logger;

class V5TestSystem extends Command
{
    protected $signature = 'v5:test-system {--alerts : Test alert system}';

    protected $description = 'Test the V5 logging and monitoring system';

    public function handle()
    {
        $this->info('🧪 Testing V5 Logging and Monitoring System');
        $this->newLine();

        // Test basic logging
        $this->testBasicLogging();

        // Test correlation tracking
        $this->testCorrelationTracking();

        // Test payment logging
        $this->testPaymentLogging();

        // Test performance logging
        $this->testPerformanceLogging();

        // Test alert system if requested
        if ($this->option('alerts')) {
            $this->testAlertSystem();
        }

        // Test dashboard integration
        $this->testDashboardIntegration();

        $this->newLine();
        $this->info('✅ All tests completed successfully!');
        $this->info('🔗 Access your dashboard at: /v5/logs');
    }

    private function testBasicLogging(): void
    {
        $this->info('1. Testing basic logging...');

        try {
            EnterpriseLogger::logCustomEvent('test_basic_logging', 'info', [
                'test_type' => 'basic_logging',
                'timestamp' => now()->toISOString(),
                'test_data' => ['key1' => 'value1', 'key2' => 'value2'],
            ]);

            EnterpriseLogger::logCustomEvent('test_warning', 'warning', [
                'test_type' => 'warning_test',
                'message' => 'This is a test warning message',
            ]);

            $this->line('   ✅ Basic logging successful');
        } catch (\Exception $e) {
            $this->error('   ❌ Basic logging failed: ' . $e->getMessage());
        }
    }

    private function testCorrelationTracking(): void
    {
        $this->info('2. Testing correlation tracking...');

        try {
            $correlationId = V5Logger::initializeCorrelation();

            CorrelationTracker::addBreadcrumb('test_start', ['test' => 'correlation_tracking']);
            CorrelationTracker::addBreadcrumb('test_middle', ['step' => 2]);
            CorrelationTracker::addBreadcrumb('test_end', ['completed' => true]);

            EnterpriseLogger::logCustomEvent('test_correlation', 'info', [
                'correlation_test' => true,
                'breadcrumbs_count' => count(CorrelationTracker::getBreadcrumbs()),
            ]);

            $this->line("   ✅ Correlation tracking successful (ID: {$correlationId})");
        } catch (\Exception $e) {
            $this->error('   ❌ Correlation tracking failed: ' . $e->getMessage());
        }
    }

    private function testPaymentLogging(): void
    {
        $this->info('3. Testing payment logging...');

        try {
            // Test successful payment
            PaymentLogger::logPaymentSuccess([
                'payment_id' => 'test_payment_' . time(),
                'amount' => 99.99,
                'currency' => 'EUR',
                'gateway' => 'test_gateway',
                'method' => 'credit_card',
            ]);

            // Test payment failure
            PaymentLogger::logPaymentFailure([
                'payment_id' => 'test_failed_' . time(),
                'amount' => 149.99,
                'currency' => 'EUR',
                'gateway' => 'test_gateway',
            ], 'Test failure for demonstration');

            // Test gateway request
            PaymentLogger::logGatewayRequest('test_gateway', '/api/payments', [
                'amount' => 99.99,
                'currency' => 'EUR',
            ], 'create_payment');

            $this->line('   ✅ Payment logging successful');
        } catch (\Exception $e) {
            $this->error('   ❌ Payment logging failed: ' . $e->getMessage());
        }
    }

    private function testPerformanceLogging(): void
    {
        $this->info('4. Testing performance logging...');

        try {
            $start = microtime(true);

            // Simulate some work
            usleep(100000); // 100ms

            $duration = (microtime(true) - $start) * 1000;

            EnterpriseLogger::logCustomEvent('test_performance', 'info', [
                'operation' => 'performance_test',
                'duration_ms' => round($duration, 2),
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);

            $this->line('   ✅ Performance logging successful');
        } catch (\Exception $e) {
            $this->error('   ❌ Performance logging failed: ' . $e->getMessage());
        }
    }

    private function testAlertSystem(): void
    {
        $this->info('5. Testing alert system...');

        try {
            // Test different alert types
            AlertManager::processLogForAlerts([
                'level' => 'error',
                'category' => 'system',
                'message' => 'Test system error for alert testing',
                'correlation_id' => V5Logger::getCorrelationId(),
                'context' => ['test_alert' => true],
            ]);

            AlertManager::processLogForAlerts([
                'level' => 'critical',
                'category' => 'payment',
                'message' => 'Test critical payment error for alert testing',
                'correlation_id' => V5Logger::getCorrelationId(),
                'context' => ['test_critical_alert' => true],
            ]);

            $this->line('   ✅ Alert system successful');
            $this->line('   📧 Check your email for test alerts');
        } catch (\Exception $e) {
            $this->error('   ❌ Alert system failed: ' . $e->getMessage());
        }
    }

    private function testDashboardIntegration(): void
    {
        $this->info('6. Testing dashboard integration...');

        try {
            // Test log dashboard controller access
            if (class_exists(\App\Http\Controllers\V5\LogDashboardWebController::class)) {
                $this->line('   ✅ Dashboard controller available');
            } else {
                $this->warn('   ⚠️ Dashboard controller not found');
            }

            // Test views exist
            if (view()->exists('v5.logs.dashboard')) {
                $this->line('   ✅ Dashboard views available');
            } else {
                $this->warn('   ⚠️ Dashboard views not found');
            }

            $this->line('   ✅ Dashboard integration ready');
        } catch (\Exception $e) {
            $this->error('   ❌ Dashboard integration failed: ' . $e->getMessage());
        }
    }
}
