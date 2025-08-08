<?php

namespace App\Http\Middleware\V5;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\V5\Logging\EnterpriseLogger;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log incoming request
        $this->logIncomingRequest($request);

        $response = $next($request);

        // Log outgoing response
        $this->logOutgoingResponse($request, $response);

        return $response;
    }

    /**
     * Log incoming request details
     */
    private function logIncomingRequest(Request $request): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ];

        // Only log request data for non-GET requests
        if (!$request->isMethod('GET')) {
            $logData['request_data'] = $this->sanitizeRequestData($request->all());
        }

        EnterpriseLogger::logCustomEvent('incoming_request', 'info', $logData);
    }

    /**
     * Log outgoing response details
     */
    private function logOutgoingResponse(Request $request, Response $response): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'response_size' => strlen($response->getContent()),
            'user_id' => $request->user()?->id,
        ];

        // Log errors with higher priority
        $level = $response->getStatusCode() >= 400 ? 'warning' : 'info';
        if ($response->getStatusCode() >= 500) {
            $level = 'error';
            $logData['response_content'] = substr($response->getContent(), 0, 1000);
        }

        EnterpriseLogger::logCustomEvent('outgoing_response', $level, $logData);
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'x-api-key', 'cookie', 'x-auth-token'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize request data to remove sensitive information
     */
    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key', 'secret',
            'card_number', 'cvv', 'card_token', 'bank_account', 'ssn'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}