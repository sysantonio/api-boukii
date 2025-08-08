@extends('adminlte::page')

@section('title', 'V5 Log Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Log Dashboard V5</h1>
        <div class="d-flex align-items-center">
            <select id="timeframe-select" class="form-control mr-2" style="width: 120px;">
                <option value="1h" {{ $timeframe == '1h' ? 'selected' : '' }}>Last 1h</option>
                <option value="6h" {{ $timeframe == '6h' ? 'selected' : '' }}>Last 6h</option>
                <option value="24h" {{ $timeframe == '24h' ? 'selected' : '' }}>Last 24h</option>
                <option value="7d" {{ $timeframe == '7d' ? 'selected' : '' }}>Last 7d</option>
                <option value="30d" {{ $timeframe == '30d' ? 'selected' : '' }}>Last 30d</option>
            </select>
            <button id="refresh-btn" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-logs">{{ number_format($overview['summary']['total_logs'] ?? 0) }}</h3>
                    <p>Total Logs</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ ($overview['summary']['error_rate'] ?? 0) > 5 ? 'danger' : 'success' }}">
                <div class="inner">
                    <h3 id="error-rate">{{ number_format($overview['summary']['error_rate'] ?? 0, 2) }}%</h3>
                    <p>Error Rate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ ($overview['summary']['payment_success_rate'] ?? 0) < 90 ? 'warning' : 'success' }}">
                <div class="inner">
                    <h3 id="payment-success-rate">{{ number_format($overview['summary']['payment_success_rate'] ?? 0, 1) }}%</h3>
                    <p>Payment Success Rate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ ($overview['summary']['avg_response_time'] ?? 0) > 2000 ? 'danger' : 'success' }}">
                <div class="inner">
                    <h3 id="avg-response-time">{{ number_format($overview['summary']['avg_response_time'] ?? 0) }}ms</h3>
                    <p>Avg Response Time</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Alerts -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i> Recent Alerts
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('v5.logs.realtime-alerts') }}" class="btn btn-sm btn-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Message</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recent-alerts">
                                @forelse($recentAlerts as $alert)
                                    <tr class="{{ $alert['resolved'] ?? false ? 'text-muted' : '' }}">
                                        <td>
                                            <span class="badge badge-{{ $alert['priority'] == 'critical' ? 'danger' : ($alert['priority'] == 'high' ? 'warning' : 'info') }}">
                                                {{ ucfirst($alert['priority']) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($alert['message'], 50) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($alert['timestamp'])->diffForHumans() }}</td>
                                        <td>
                                            @if(!($alert['resolved'] ?? false))
                                                <button class="btn btn-xs btn-success resolve-alert" 
                                                        data-alert-id="{{ $alert['alert_id'] }}">
                                                    Resolve
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent alerts</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-search"></i> Quick Search
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('v5.logs.search') }}" method="GET">
                        <div class="form-group">
                            <label>Search Text</label>
                            <input type="text" name="search_text" class="form-control" placeholder="Search in logs...">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Level</label>
                                    <select name="level" class="form-control">
                                        <option value="">All Levels</option>
                                        <option value="debug">Debug</option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="error">Error</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category" class="form-control">
                                        <option value="">All Categories</option>
                                        <option value="payment">Payment</option>
                                        <option value="booking">Booking</option>
                                        <option value="authentication">Authentication</option>
                                        <option value="system_error">System Error</option>
                                        <option value="performance">Performance</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="datetime-local" name="date_from" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="datetime-local" name="date_to" class="form-control">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-link"></i> Quick Access
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('v5.logs.payments') }}" class="btn btn-block btn-outline-primary">
                                <i class="fas fa-credit-card"></i><br>
                                Payment Logs
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('v5.logs.system-errors') }}" class="btn btn-block btn-outline-danger">
                                <i class="fas fa-bug"></i><br>
                                System Errors
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('v5.logs.performance') }}" class="btn btn-block btn-outline-warning">
                                <i class="fas fa-tachometer-alt"></i><br>
                                Performance
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('v5.logs.realtime-alerts') }}" class="btn btn-block btn-outline-info">
                                <i class="fas fa-bell"></i><br>
                                All Alerts
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('v5.logs.statistics') }}" class="btn btn-block btn-outline-success">
                                <i class="fas fa-chart-bar"></i><br>
                                Statistics
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('v5.logs.search') }}" class="btn btn-block btn-outline-secondary">
                                <i class="fas fa-search"></i><br>
                                Advanced Search
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-refresh dashboard every 30 seconds
    setInterval(function() {
        refreshDashboard();
    }, 30000);

    // Manual refresh button
    $('#refresh-btn').click(function() {
        refreshDashboard();
    });

    // Timeframe change
    $('#timeframe-select').change(function() {
        window.location.href = '{{ route("v5.logs.dashboard") }}?timeframe=' + $(this).val();
    });

    // Resolve alert
    $('.resolve-alert').click(function() {
        var alertId = $(this).data('alert-id');
        var button = $(this);
        
        $.post('{{ url("/v5/logs/alerts") }}/' + alertId + '/resolve', {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            button.closest('tr').addClass('text-muted');
            button.remove();
            toastr.success('Alert resolved successfully');
        }).fail(function() {
            toastr.error('Failed to resolve alert');
        });
    });
});

function refreshDashboard() {
    var timeframe = $('#timeframe-select').val();
    
    $.get('{{ route("v5.logs.ajax.overview") }}', { timeframe: timeframe })
        .done(function(data) {
            // Update stats
            $('#total-logs').text(new Intl.NumberFormat().format(data.summary.total_logs || 0));
            $('#error-rate').text((data.summary.error_rate || 0).toFixed(2) + '%');
            $('#payment-success-rate').text((data.summary.payment_success_rate || 0).toFixed(1) + '%');
            $('#avg-response-time').text(Math.round(data.summary.avg_response_time || 0) + 'ms');
            
            // Update card colors based on thresholds
            updateCardColor('#error-rate', data.summary.error_rate > 5 ? 'danger' : 'success');
            updateCardColor('#payment-success-rate', data.summary.payment_success_rate < 90 ? 'warning' : 'success');
            updateCardColor('#avg-response-time', data.summary.avg_response_time > 2000 ? 'danger' : 'success');
        });

    // Refresh alerts
    $.get('{{ route("v5.logs.ajax.alerts") }}')
        .done(function(data) {
            // Update alerts count and refresh table if needed
        });
}

function updateCardColor(selector, colorClass) {
    var card = $(selector).closest('.small-box');
    card.removeClass('bg-success bg-warning bg-danger bg-info');
    card.addClass('bg-' + colorClass);
}
</script>
@stop

@section('css')
<style>
.small-box .inner h3 {
    font-size: 2.2rem;
    font-weight: bold;
}

.card-tools .btn {
    margin-left: 5px;
}

.resolve-alert {
    padding: 2px 8px;
    font-size: 11px;
}

.badge {
    font-size: 0.75em;
}
</style>
@stop