<?php

namespace App\V5\Logging;

use Illuminate\Support\Facades\Log;

class PaymentLogger
{
    private const CHANNEL = 'v5_payments';

    /**
     * Log payment gateway communication
     */
    public static function logGatewayRequest(string $gateway, string $endpoint, array $requestData, string $operation): void
    {
        $sanitizedRequest = self::sanitizePaymentData($requestData);

        EnterpriseLogger::logPaymentOperation("gateway_request_{$operation}", [
            'gateway' => $gateway,
            'endpoint' => $endpoint,
            'operation' => $operation,
            'request_data' => $sanitizedRequest,
            'request_size_bytes' => strlen(json_encode($requestData)),
            'timestamp' => now()->toISOString(),
        ], 'info');

        // Separate payment-specific log
        Log::channel(self::CHANNEL)->info('Payment Gateway Request', [
            'correlation_id' => V5Logger::getCorrelationId(),
            'gateway' => $gateway,
            'operation' => $operation,
            'endpoint' => $endpoint,
            'request_data' => $sanitizedRequest,
        ]);
    }

    /**
     * Log payment gateway response
     */
    public static function logGatewayResponse(string $gateway, array $responseData, float $responseTime, string $operation): void
    {
        $sanitizedResponse = self::sanitizePaymentData($responseData);
        $isSuccess = self::isSuccessfulResponse($responseData);

        EnterpriseLogger::logPaymentOperation("gateway_response_{$operation}", [
            'gateway' => $gateway,
            'operation' => $operation,
            'response_data' => $sanitizedResponse,
            'response_time_ms' => round($responseTime * 1000, 2),
            'is_success' => $isSuccess,
            'gateway_transaction_id' => $responseData['transaction_id'] ?? $responseData['id'] ?? null,
            'gateway_status' => $responseData['status'] ?? null,
            'gateway_message' => $responseData['message'] ?? null,
            'timestamp' => now()->toISOString(),
        ], $isSuccess ? 'info' : 'error');

        Log::channel(self::CHANNEL)->{$isSuccess ? 'info' : 'error'}('Payment Gateway Response', [
            'correlation_id' => V5Logger::getCorrelationId(),
            'gateway' => $gateway,
            'operation' => $operation,
            'success' => $isSuccess,
            'response_time_ms' => round($responseTime * 1000, 2),
            'response_data' => $sanitizedResponse,
        ]);
    }

    /**
     * Log payment processing start
     */
    public static function logPaymentStart(array $paymentData): void
    {
        EnterpriseLogger::logPaymentOperation('payment_processing_started', [
            'payment_id' => $paymentData['payment_id'] ?? null,
            'booking_id' => $paymentData['booking_id'] ?? null,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'CHF',
            'payment_method' => $paymentData['payment_method'],
            'customer_id' => $paymentData['customer_id'] ?? null,
            'gateway' => $paymentData['gateway'] ?? null,
            'payment_type' => $paymentData['payment_type'] ?? 'booking', // booking, refund, partial_refund
            'idempotency_key' => $paymentData['idempotency_key'] ?? null,
        ], 'info');
    }

    /**
     * Log payment success
     */
    public static function logPaymentSuccess(array $paymentData): void
    {
        EnterpriseLogger::logPaymentOperation('payment_success', array_merge($paymentData, [
            'processing_time_ms' => $paymentData['processing_time_ms'] ?? null,
            'gateway_transaction_id' => $paymentData['gateway_transaction_id'] ?? null,
            'gateway_fee' => $paymentData['gateway_fee'] ?? null,
            'net_amount' => $paymentData['net_amount'] ?? null,
        ]), 'info');

        // Log for financial reconciliation
        self::logFinancialTransaction($paymentData, 'credit');
    }

    /**
     * Log payment failure
     */
    public static function logPaymentFailure(array $paymentData, string $reason): void
    {
        EnterpriseLogger::logPaymentOperation('payment_failed', array_merge($paymentData, [
            'failure_reason' => $reason,
            'gateway_error_code' => $paymentData['gateway_error_code'] ?? null,
            'gateway_error_message' => $paymentData['gateway_error_message'] ?? null,
            'retry_count' => $paymentData['retry_count'] ?? 0,
            'will_retry' => $paymentData['will_retry'] ?? false,
        ]), 'error');

        // High priority alert for payment failures
        Log::channel('v5_alerts')->error('Payment Failed', [
            'correlation_id' => V5Logger::getCorrelationId(),
            'payment_id' => $paymentData['payment_id'] ?? null,
            'booking_id' => $paymentData['booking_id'] ?? null,
            'amount' => $paymentData['amount'],
            'reason' => $reason,
            'requires_manual_review' => true,
        ]);
    }

    /**
     * Log refund operations
     */
    public static function logRefund(array $refundData, string $status): void
    {
        EnterpriseLogger::logPaymentOperation("refund_{$status}", [
            'refund_id' => $refundData['refund_id'] ?? null,
            'original_payment_id' => $refundData['original_payment_id'],
            'booking_id' => $refundData['booking_id'] ?? null,
            'refund_amount' => $refundData['refund_amount'],
            'refund_reason' => $refundData['refund_reason'] ?? null,
            'refund_type' => $refundData['refund_type'] ?? 'full', // full, partial, adjustment
            'gateway_refund_id' => $refundData['gateway_refund_id'] ?? null,
            'processing_fee_refunded' => $refundData['processing_fee_refunded'] ?? false,
            'initiated_by_user_id' => $refundData['initiated_by_user_id'] ?? null,
        ], $status === 'success' ? 'info' : 'error');

        if ($status === 'success') {
            self::logFinancialTransaction($refundData, 'debit');
        }
    }

    /**
     * Log webhook received from payment gateway
     */
    public static function logWebhook(string $gateway, string $eventType, array $webhookData): void
    {
        $sanitizedData = self::sanitizePaymentData($webhookData);

        EnterpriseLogger::logPaymentOperation('webhook_received', [
            'gateway' => $gateway,
            'event_type' => $eventType,
            'webhook_id' => $webhookData['id'] ?? null,
            'transaction_id' => $webhookData['transaction_id'] ?? null,
            'webhook_data' => $sanitizedData,
            'webhook_signature' => $webhookData['signature'] ?? null,
            'verification_status' => $webhookData['verification_status'] ?? 'pending',
        ], 'info');

        Log::channel(self::CHANNEL)->info('Payment Webhook', [
            'correlation_id' => V5Logger::getCorrelationId(),
            'gateway' => $gateway,
            'event_type' => $eventType,
            'webhook_data' => $sanitizedData,
        ]);
    }

    /**
     * Log financial transactions for reconciliation
     */
    private static function logFinancialTransaction(array $transactionData, string $type): void
    {
        Log::channel('v5_financial')->info('Financial Transaction', [
            'correlation_id' => V5Logger::getCorrelationId(),
            'transaction_type' => $type, // credit, debit
            'transaction_id' => $transactionData['payment_id'] ?? $transactionData['refund_id'] ?? null,
            'booking_id' => $transactionData['booking_id'] ?? null,
            'amount' => $transactionData['amount'] ?? $transactionData['refund_amount'] ?? null,
            'currency' => $transactionData['currency'] ?? 'CHF',
            'gateway' => $transactionData['gateway'] ?? null,
            'gateway_transaction_id' => $transactionData['gateway_transaction_id'] ?? $transactionData['gateway_refund_id'] ?? null,
            'fee' => $transactionData['gateway_fee'] ?? null,
            'net_amount' => $transactionData['net_amount'] ?? null,
            'school_id' => $transactionData['school_id'] ?? null,
            'season_id' => $transactionData['season_id'] ?? null,
            'timestamp' => now()->toISOString(),
            'reconciliation_status' => 'pending',
        ]);
    }

    /**
     * Log fraud detection events
     */
    public static function logFraudDetection(array $fraudData): void
    {
        EnterpriseLogger::logPaymentOperation('fraud_detection', [
            'payment_id' => $fraudData['payment_id'] ?? null,
            'risk_score' => $fraudData['risk_score'] ?? null,
            'risk_level' => $fraudData['risk_level'] ?? null, // low, medium, high, critical
            'fraud_indicators' => $fraudData['fraud_indicators'] ?? [],
            'action_taken' => $fraudData['action_taken'] ?? null, // blocked, flagged, allowed
            'customer_id' => $fraudData['customer_id'] ?? null,
            'ip_address' => $fraudData['ip_address'] ?? null,
            'geolocation' => $fraudData['geolocation'] ?? null,
            'device_fingerprint' => $fraudData['device_fingerprint'] ?? null,
        ], 'warning');

        // Critical fraud alerts
        if (($fraudData['risk_level'] ?? '') === 'critical') {
            Log::channel('v5_security')->critical('Fraud Detection Alert', [
                'correlation_id' => V5Logger::getCorrelationId(),
                'payment_id' => $fraudData['payment_id'] ?? null,
                'risk_score' => $fraudData['risk_score'],
                'requires_immediate_attention' => true,
            ]);
        }
    }

    /**
     * Log subscription events (for recurring payments)
     */
    public static function logSubscriptionEvent(string $event, array $subscriptionData): void
    {
        EnterpriseLogger::logPaymentOperation("subscription_{$event}", [
            'subscription_id' => $subscriptionData['subscription_id'],
            'customer_id' => $subscriptionData['customer_id'] ?? null,
            'plan_id' => $subscriptionData['plan_id'] ?? null,
            'status' => $subscriptionData['status'] ?? null,
            'next_billing_date' => $subscriptionData['next_billing_date'] ?? null,
            'amount' => $subscriptionData['amount'] ?? null,
            'trial_end_date' => $subscriptionData['trial_end_date'] ?? null,
        ], 'info');
    }

    /**
     * Sanitize payment data by removing/masking sensitive information
     */
    private static function sanitizePaymentData(array $data): array
    {
        $sensitiveFields = [
            'card_number', 'cvv', 'card_token', 'bank_account', 'iban',
            'account_number', 'routing_number', 'api_key', 'secret_key',
            'private_key', 'signature_key',
        ];

        $sanitized = $data;

        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                if ($field === 'card_number' && is_string($sanitized[$field])) {
                    // Keep first 4 and last 4 digits
                    $cardNumber = preg_replace('/\D/', '', $sanitized[$field]);
                    if (strlen($cardNumber) >= 8) {
                        $sanitized[$field] = substr($cardNumber, 0, 4).str_repeat('*', strlen($cardNumber) - 8).substr($cardNumber, -4);
                    } else {
                        $sanitized[$field] = '[REDACTED]';
                    }
                } else {
                    $sanitized[$field] = '[REDACTED]';
                }
            }
        }

        // Recursively sanitize nested arrays
        foreach ($sanitized as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizePaymentData($value);
            }
        }

        return $sanitized;
    }

    /**
     * Determine if payment response is successful
     */
    private static function isSuccessfulResponse(array $responseData): bool
    {
        $successStatuses = ['success', 'completed', 'approved', 'paid'];
        $status = strtolower($responseData['status'] ?? '');

        return in_array($status, $successStatuses) ||
               (isset($responseData['success']) && $responseData['success'] === true);
    }
}
