<?php

namespace Tests\Unit\V5;

use Tests\TestCase;
use App\V5\Logging\V5Logger;
use App\V5\Logging\CorrelationTracker;
use App\V5\Logging\ContextProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class V5LoggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the logging configuration
        Config::set('v5_logging.sensitive_fields', ['test_sensitive']);
        Config::set('v5_logging.monitoring.performance_thresholds', [
            'slow_request_ms' => 1000,
            'very_slow_request_ms' => 2000,
            'critical_request_ms' => 5000,
        ]);
    }

    public function test_correlation_id_initialization()
    {
        $correlationId = V5Logger::initializeCorrelation();
        
        $this->assertNotNull($correlationId);
        $this->assertStringContainsString('v5_corr_', $correlationId);
        $this->assertEquals($correlationId, V5Logger::getCorrelationId());
    }

    public function test_correlation_id_can_be_set_externally()
    {
        $customCorrelationId = 'custom_correlation_123';
        
        V5Logger::initializeCorrelation($customCorrelationId);
        
        $this->assertEquals($customCorrelationId, V5Logger::getCorrelationId());
    }

    public function test_persistent_context_management()
    {
        V5Logger::setPersistentContext(['user_id' => 123, 'session_id' => 'abc']);
        V5Logger::setPersistentContext(['additional_data' => 'test']);
        
        $summary = V5Logger::getFlowSummary();
        
        $this->assertArrayHasKey('persistent_context', $summary);
        $this->assertEquals(123, $summary['persistent_context']['user_id']);
        $this->assertEquals('abc', $summary['persistent_context']['session_id']);
        $this->assertEquals('test', $summary['persistent_context']['additional_data']);
        
        V5Logger::clearPersistentContext();
        
        $summaryAfterClear = V5Logger::getFlowSummary();
        $this->assertEmpty($summaryAfterClear['persistent_context']);
    }

    public function test_api_request_logging()
    {
        Log::shouldReceive('channel')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('API Request Received', \Mockery::type('array'));

        $request = Request::create('/test', 'POST', ['param' => 'value']);
        $request->headers->set('Content-Type', 'application/json');
        
        V5Logger::logApiRequest($request, ['additional' => 'context']);
    }

    public function test_api_response_logging_with_different_status_codes()
    {
        Log::shouldReceive('channel')
            ->andReturnSelf();
        
        // Test successful response
        Log::shouldReceive('info')
            ->once()
            ->with('API Response Sent', \Mockery::type('array'));

        $request = Request::create('/test', 'GET');
        $response = new Response('Success', 200);
        
        V5Logger::logApiResponse($request, $response, 0.5);
        
        // Test error response
        Log::shouldReceive('warning')
            ->once()
            ->with('API Response Sent', \Mockery::type('array'));

        $errorResponse = new Response('Not Found', 404);
        V5Logger::logApiResponse($request, $errorResponse, 0.3);
    }

    public function test_authentication_event_logging()
    {
        // Mock the request with a session to avoid session issues
        $request = Request::create('/auth', 'GET');
        $request->setLaravelSession(app('session')->driver());
        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
        
        Log::shouldReceive('channel')
            ->andReturnSelf();
        
        Log::shouldReceive('warning')
            ->once()
            ->with('Authentication Event', \Mockery::type('array'));

        Log::shouldReceive('warning')
            ->once()
            ->with('Security Event', \Mockery::type('array'));

        V5Logger::logAuthEvent('login_failed', [
            'user_id' => 123,
            'reason' => 'invalid_password'
        ]);
    }

    public function test_business_event_logging()
    {
        Log::shouldReceive('channel')
            ->with('v5_enterprise')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Business Event', \Mockery::type('array'));

        V5Logger::logBusinessEvent('booking', 'created', [
            'booking_id' => 456,
            'client_id' => 789,
            'amount' => 150.00
        ]);
    }

    public function test_performance_logging_with_different_thresholds()
    {
        Log::shouldReceive('channel')
            ->with('v5_performance')
            ->andReturnSelf();
        
        // Test normal performance (info level)
        Log::shouldReceive('info')
            ->once()
            ->with('Performance Metric', \Mockery::type('array'));

        V5Logger::logPerformance('fast_operation', 0.5);
        
        // Test slow performance (warning level)
        Log::shouldReceive('warning')
            ->once()
            ->with('Performance Metric', \Mockery::type('array'));

        V5Logger::logPerformance('slow_operation', 1.5);
        
        // Test critical performance (critical level + alert)
        Log::shouldReceive('critical')
            ->once()
            ->with('Performance Metric', \Mockery::type('array'));
            
        Log::shouldReceive('channel')
            ->with('v5_alerts')
            ->andReturnSelf();
            
        Log::shouldReceive('critical')
            ->once()
            ->with('Critical Performance Alert', \Mockery::type('array'));

        V5Logger::logPerformance('critical_operation', 6.0);
    }

    public function test_security_event_logging()
    {
        // Mock the request with a session to avoid session issues
        $request = Request::create('/security', 'GET');
        $request->setLaravelSession(app('session')->driver());
        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
        
        Log::shouldReceive('channel')
            ->with('v5_security')
            ->andReturnSelf();
        
        Log::shouldReceive('warning')
            ->once()
            ->with('Security Event', \Mockery::type('array'));

        V5Logger::logSecurityEvent('suspicious_activity', 'warning', [
            'ip' => '192.168.1.100',
            'attempts' => 5
        ]);
    }

    public function test_database_operation_logging()
    {
        Log::shouldReceive('channel')
            ->with('v5_enterprise')
            ->andReturnSelf();
        
        Log::shouldReceive('debug')
            ->once()
            ->with('Database Operation', \Mockery::type('array'));

        V5Logger::logDatabaseOperation('select', 'users', [
            'duration_ms' => 50,
            'affected_rows' => 10
        ]);
    }

    public function test_cache_operation_logging()
    {
        Log::shouldReceive('channel')
            ->with('v5_enterprise')
            ->andReturnSelf();
        
        Log::shouldReceive('debug')
            ->once()
            ->with('Cache Operation', \Mockery::type('array'));

        V5Logger::logCacheOperation('get', 'user:123', [
            'hit' => true,
            'ttl' => 3600
        ]);
    }

    public function test_validation_error_logging()
    {
        Log::shouldReceive('channel')
            ->with('v5_enterprise')
            ->andReturnSelf();
        
        Log::shouldReceive('warning')
            ->once()
            ->with('Validation Error', \Mockery::type('array'));

        $errors = [
            'email' => ['The email field is required.'],
            'password' => ['The password must be at least 8 characters.']
        ];

        V5Logger::logValidationError($errors);
    }

    public function test_custom_event_logging()
    {
        Log::shouldReceive('channel')
            ->with('v5_enterprise')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->once()
            ->with('Custom Event', \Mockery::type('array'));

        V5Logger::logCustomEvent('user_profile_updated', 'info', [
            'user_id' => 123,
            'updated_fields' => ['name', 'email']
        ]);
    }

    public function test_system_error_logging()
    {
        Log::shouldReceive('channel')
            ->with('v5_enterprise')
            ->andReturnSelf();
        
        Log::shouldReceive('error')
            ->once()
            ->with('System Error', \Mockery::type('array'));

        $exception = new \Exception('Test exception', 500);
        
        V5Logger::logSystemError($exception, ['additional' => 'context']);
    }

    public function test_context_processor_injects_request_values()
    {
        $request = Request::create('/test', 'GET', ['season_id' => 1, 'school_id' => 2]);
        $request->headers->set('User-Agent', 'phpunit-agent');
        $request->server->set('REMOTE_ADDR', '123.123.123.123');
        $request->setUserResolver(fn () => (object) ['id' => 99]);
        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);

        V5Logger::initializeCorrelation('corr-id-1');

        $processor = new ContextProcessor();
        $record = $processor([]);

        $this->assertEquals('corr-id-1', $record['extra']['correlation_id']);
        $this->assertEquals(1, $record['extra']['season_id']);
        $this->assertEquals(2, $record['extra']['school_id']);
        $this->assertEquals('123.123.123.123', $record['extra']['ip']);
        $this->assertEquals('phpunit-agent', $record['extra']['user_agent']);
    }

    public function test_sensitive_data_sanitization()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass(V5Logger::class);
        $method = $reflection->getMethod('sanitizeData');
        $method->setAccessible(true);

        $testData = [
            'username' => 'john_doe',
            'password' => 'secret123',
            'card_number' => '1234567890123456',
            'test_sensitive' => 'should_be_redacted',
            'nested' => [
                'token' => 'secret_token',
                'public_data' => 'visible'
            ]
        ];

        $sanitized = $method->invoke(null, $testData);

        $this->assertEquals('john_doe', $sanitized['username']);
        $this->assertEquals('se*****23', $sanitized['password']);
        $this->assertStringContainsString('*', $sanitized['test_sensitive']);
        $this->assertEquals('1234********3456', $sanitized['card_number']);
        $this->assertStringContainsString('*', $sanitized['nested']['token']);
        $this->assertEquals('visible', $sanitized['nested']['public_data']);
    }

    public function test_flow_summary_generation()
    {
        V5Logger::initializeCorrelation();
        V5Logger::setPersistentContext(['test' => 'data']);
        
        $summary = V5Logger::getFlowSummary();
        
        $this->assertArrayHasKey('correlation_id', $summary);
        $this->assertArrayHasKey('persistent_context', $summary);
        $this->assertArrayHasKey('correlation_summary', $summary);
        $this->assertEquals('data', $summary['persistent_context']['test']);
    }

    public function test_end_request_cleanup()
    {
        // Mock the request to avoid session issues  
        $this->app->instance('request', new Request());
        
        // Mock Log facade calls that will be made during endRequest
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info')->once();
        
        V5Logger::clearPersistentContext(); // Start clean
        V5Logger::setPersistentContext(['test' => 'data']);
        
        $this->assertNotEmpty(V5Logger::getFlowSummary()['persistent_context']);
        
        V5Logger::endRequest(['final' => 'data']);
        
        // Verify context is cleared, but correlation might persist from previous tests
        $this->assertEmpty(V5Logger::getFlowSummary()['persistent_context']);
    }
}