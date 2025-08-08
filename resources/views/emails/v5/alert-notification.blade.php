<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>V5 Alert Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            padding: 20px;
            text-align: center;
            color: white;
        }
        .header.critical {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .header.high {
            background: linear-gradient(135deg, #fd7e14, #e55a00);
        }
        .header.medium {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        }
        .header.low {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .priority-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            background: rgba(255,255,255,0.2);
        }
        .content {
            padding: 30px;
        }
        .alert-message {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .alert-message.critical {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .alert-message.high {
            border-left-color: #fd7e14;
            background: #ffeaa0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th,
        .details-table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            width: 150px;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #0d6efd;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }
        .context-data {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        .urgent-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa0;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .urgent-notice.critical {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header {{ $alert['priority'] ?? 'medium' }}">
            <h1>
                @if($isCritical)
                    🚨 CRITICAL ALERT
                @elseif($isHighPriority)
                    ⚠️ HIGH PRIORITY ALERT
                @else
                    📊 SYSTEM ALERT
                @endif
            </h1>
            <div class="priority-badge">
                {{ strtoupper($alert['priority'] ?? 'medium') }} PRIORITY
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            @if($alert['requires_immediate_attention'] ?? false)
                <div class="urgent-notice {{ $isCritical ? 'critical' : '' }}">
                    <strong>⚡ IMMEDIATE ATTENTION REQUIRED</strong><br>
                    This alert requires immediate investigation and action.
                </div>
            @endif

            <!-- Alert Message -->
            <div class="alert-message {{ $alert['priority'] ?? 'medium' }}">
                <h3 style="margin: 0 0 10px 0;">{{ $alert['message'] }}</h3>
                <small style="color: #6c757d;">
                    Alert Type: {{ ucwords(str_replace('_', ' ', $alert['type'] ?? 'system_alert')) }}
                </small>
            </div>

            <!-- Alert Details -->
            <table class="details-table">
                <tr>
                    <th>Alert ID</th>
                    <td><code>{{ $alert['alert_id'] ?? 'N/A' }}</code></td>
                </tr>
                <tr>
                    <th>Timestamp</th>
                    <td>{{ \Carbon\Carbon::parse($alert['timestamp'])->format('Y-m-d H:i:s T') }}</td>
                </tr>
                @if(!empty($alert['correlation_id']))
                <tr>
                    <th>Correlation ID</th>
                    <td><code>{{ $alert['correlation_id'] }}</code></td>
                </tr>
                @endif
                @if(!empty($alert['data']['user_id']))
                <tr>
                    <th>User ID</th>
                    <td>{{ $alert['data']['user_id'] }}</td>
                </tr>
                @endif
                @if(!empty($alert['data']['payment_id']))
                <tr>
                    <th>Payment ID</th>
                    <td><code>{{ $alert['data']['payment_id'] }}</code></td>
                </tr>
                @endif
                @if(!empty($alert['data']['booking_id']))
                <tr>
                    <th>Booking ID</th>
                    <td><code>{{ $alert['data']['booking_id'] }}</code></td>
                </tr>
                @endif
                @if(!empty($alert['data']['gateway']))
                <tr>
                    <th>Payment Gateway</th>
                    <td>{{ $alert['data']['gateway'] }}</td>
                </tr>
                @endif
                @if(!empty($alert['data']['amount']))
                <tr>
                    <th>Amount</th>
                    <td>{{ $alert['data']['currency'] ?? 'CHF' }} {{ number_format($alert['data']['amount'], 2) }}</td>
                </tr>
                @endif
            </table>

            <!-- Context Data -->
            @if(!empty($alert['data']) && is_array($alert['data']))
                <h4>Additional Context:</h4>
                <div class="context-data">
                    @foreach($alert['data'] as $key => $value)
                        @if(!in_array($key, ['user_id', 'payment_id', 'booking_id', 'gateway', 'amount', 'currency']))
                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> 
                            @if(is_array($value))
                                {{ json_encode($value, JSON_PRETTY_PRINT) }}
                            @else
                                {{ $value }}
                            @endif
                            <br>
                        @endif
                    @endforeach
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $dashboardUrl }}" class="btn btn-primary">
                    🔍 View Dashboard
                </a>
                @if(!empty($alert['correlation_id']))
                    <a href="{{ $dashboardUrl }}/correlation/{{ $alert['correlation_id'] }}" class="btn btn-secondary">
                        📊 View Flow Details
                    </a>
                @endif
            </div>

            <!-- Troubleshooting Tips -->
            @if($alert['type'] === 'payment_failure_threshold')
                <div style="background: #e7f3ff; border: 1px solid #bee5eb; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <h4 style="color: #0c5460;">💡 Troubleshooting Tips for Payment Failures:</h4>
                    <ul style="margin: 10px 0;">
                        <li>Check payment gateway status and connectivity</li>
                        <li>Verify API credentials and configuration</li>
                        <li>Review recent gateway error messages</li>
                        <li>Check for network or DNS issues</li>
                        <li>Monitor transaction volume patterns</li>
                    </ul>
                </div>
            @elseif($alert['type'] === 'error_rate_threshold')
                <div style="background: #fff3cd; border: 1px solid #ffeaa0; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <h4 style="color: #856404;">💡 Troubleshooting Tips for High Error Rate:</h4>
                    <ul style="margin: 10px 0;">
                        <li>Check application logs for recurring errors</li>
                        <li>Monitor database performance and connectivity</li>
                        <li>Verify external API dependencies</li>
                        <li>Check server resource utilization</li>
                        <li>Review recent code deployments</li>
                    </ul>
                </div>
            @elseif($alert['type'] === 'fraud_detection')
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 15px; margin: 20px 0;">
                    <h4 style="color: #721c24;">🛡️ Fraud Alert - Immediate Action Required:</h4>
                    <ul style="margin: 10px 0;">
                        <li>Review transaction details immediately</li>
                        <li>Check customer verification status</li>
                        <li>Verify IP geolocation and device fingerprint</li>
                        <li>Consider blocking suspicious transactions</li>
                        <li>Contact customer if needed for verification</li>
                    </ul>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                This is an automated alert from Boukii V5 Monitoring System.<br>
                Generated at {{ now()->format('Y-m-d H:i:s T') }}
            </p>
            <p>
                <strong>Need help?</strong> Contact your system administrator or check the 
                <a href="{{ $dashboardUrl }}" style="color: #0d6efd;">Log Dashboard</a> for more details.
            </p>
        </div>
    </div>
</body>
</html>