@extends('adminlte::page')

@section('title', 'Log Search - V5 Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Log Search</h1>
        <a href="{{ route('v5.logs.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
@stop

@section('content')
    <!-- Search Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Search Filters
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('v5.logs.search') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Search Text</label>
                            <input type="text" name="search_text" class="form-control" 
                                   value="{{ $filters['search_text'] ?? '' }}" 
                                   placeholder="Search in message, correlation_id, etc...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Level</label>
                            <select name="level" class="form-control">
                                <option value="">All Levels</option>
                                <option value="debug" {{ ($filters['level'] ?? '') == 'debug' ? 'selected' : '' }}>Debug</option>
                                <option value="info" {{ ($filters['level'] ?? '') == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="warning" {{ ($filters['level'] ?? '') == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="error" {{ ($filters['level'] ?? '') == 'error' ? 'selected' : '' }}>Error</option>
                                <option value="critical" {{ ($filters['level'] ?? '') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                <option value="payment" {{ ($filters['category'] ?? '') == 'payment' ? 'selected' : '' }}>Payment</option>
                                <option value="booking" {{ ($filters['category'] ?? '') == 'booking' ? 'selected' : '' }}>Booking</option>
                                <option value="authentication" {{ ($filters['category'] ?? '') == 'authentication' ? 'selected' : '' }}>Authentication</option>
                                <option value="system_error" {{ ($filters['category'] ?? '') == 'system_error' ? 'selected' : '' }}>System Error</option>
                                <option value="performance" {{ ($filters['category'] ?? '') == 'performance' ? 'selected' : '' }}>Performance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>User ID</label>
                            <input type="number" name="user_id" class="form-control" 
                                   value="{{ $filters['user_id'] ?? '' }}" 
                                   placeholder="User ID">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Correlation ID</label>
                            <input type="text" name="correlation_id" class="form-control" 
                                   value="{{ $filters['correlation_id'] ?? '' }}" 
                                   placeholder="Correlation ID">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Booking ID</label>
                            <input type="text" name="booking_id" class="form-control" 
                                   value="{{ $filters['booking_id'] ?? '' }}" 
                                   placeholder="Booking ID">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Payment ID</label>
                            <input type="text" name="payment_id" class="form-control" 
                                   value="{{ $filters['payment_id'] ?? '' }}" 
                                   placeholder="Payment ID">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="datetime-local" name="date_from" class="form-control" 
                                   value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="datetime-local" name="date_to" class="form-control" 
                                   value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('v5.logs.search') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    @if(isset($results))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Search Results 
                <span class="badge badge-info">{{ $results['pagination']['total'] ?? 0 }} total</span>
            </h3>
            <div class="card-tools">
                <span class="text-muted">
                    Search completed in {{ $results['search_metadata']['search_time_ms'] ?? 0 }}ms
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            @if(!empty($results['data']))
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="120">Timestamp</th>
                                <th width="80">Level</th>
                                <th width="100">Category</th>
                                <th>Message</th>
                                <th width="120">User/Correlation</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['data'] as $log)
                                <tr class="log-level-{{ $log['level'] ?? 'info' }}">
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($log['timestamp'])->format('m/d H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $log['level'] == 'error' || $log['level'] == 'critical' ? 'danger' : ($log['level'] == 'warning' ? 'warning' : 'info') }}">
                                            {{ ucfirst($log['level'] ?? 'info') }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ ucfirst($log['category'] ?? 'general') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ Str::limit($log['message'] ?? '', 80) }}</div>
                                        @if(!empty($log['operation']))
                                            <small class="text-muted">Op: {{ $log['operation'] }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($log['user_id']))
                                            <small>User: {{ $log['user_id'] }}</small><br>
                                        @endif
                                        @if(!empty($log['correlation_id']))
                                            <a href="{{ route('v5.logs.correlation-detail', $log['correlation_id']) }}" 
                                               class="text-primary" style="font-size: 11px;">
                                                {{ Str::limit($log['correlation_id'], 15) }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($log['id']))
                                            <a href="{{ route('v5.logs.log-detail', $log['id']) }}" 
                                               class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($results['pagination']['last_page'] > 1)
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info">
                                    Showing {{ (($results['pagination']['current_page'] - 1) * $results['pagination']['per_page']) + 1 }} 
                                    to {{ min($results['pagination']['current_page'] * $results['pagination']['per_page'], $results['pagination']['total']) }} 
                                    of {{ $results['pagination']['total'] }} entries
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="float-right">
                                    {{ 
                                        // Simple pagination implementation
                                        // In real implementation, you'd create proper pagination links
                                    }}
                                    @for($i = 1; $i <= $results['pagination']['last_page']; $i++)
                                        <a href="{{ route('v5.logs.search') }}?{{ http_build_query(array_merge($filters, ['page' => $i])) }}" 
                                           class="btn btn-sm {{ $i == $results['pagination']['current_page'] ? 'btn-primary' : 'btn-outline-primary' }}">
                                            {{ $i }}
                                        </a>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="card-body text-center text-muted">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <p>No logs found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
    @endif
@stop

@section('css')
<style>
.log-level-error, .log-level-critical {
    background-color: #fff5f5;
}
.log-level-warning {
    background-color: #fffbf0;
}
.log-level-debug {
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
}

.btn-xs {
    padding: 1px 5px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 3px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-submit form on certain changes
    $('select[name="level"], select[name="category"]').change(function() {
        if ($(this).val() !== '') {
            // Optional: auto-submit on filter change
            // $(this).closest('form').submit();
        }
    });

    // Add tooltips for truncated text
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop