@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')

<style>
#loginHistoryTable thead th {
    color: white;
}
.table td, .table th {
    vertical-align: middle;
}
.card.shadow-sm {
    border: none;
    border-radius: 0.75rem;
}
.form-control-sm {
    border-radius: 0.45rem;
}
label.small {
    margin-bottom: 0.3rem;
}
.card{
    border-radius: 13px;
}
.badge {
    padding: 0.4em 0.8em;
    font-size: 0.85em;
}
.device-icon {
    font-size: 1.2em;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Login History</h1>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\LoginHistory::where('status', 'success')->whereDate('login_at', today())->count() }}</h3>
                        <p>Today's Logins</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\LoginHistory::where('status', 'success')->whereDate('login_at', '>=', now()->subDays(7))->count() }}</h3>
                        <p>This Week</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\LoginHistory::where('status', 'failed')->whereDate('login_at', '>=', now()->subDays(30))->count() }}</h3>
                        <p>Failed Attempts (30d)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\User::whereHas('latestLogin', function($q) { $q->whereDate('login_at', today()); })->count() }}</h3>
                        <p>Active Users Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('login-history.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="small font-weight-bold text-muted">From Date</label>
                            <input type="date"
                                class="form-control form-control-sm"
                                name="date_from"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="small font-weight-bold text-muted">To Date</label>
                            <input type="date"
                                class="form-control form-control-sm"
                                name="date_to"
                                value="{{ request('date_to', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="small font-weight-bold text-muted">User</label>
                            <select class="form-control form-control-sm" name="user_id">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id')==$user->id?'selected':'' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="small font-weight-bold text-muted">Status</label>
                            <select class="form-control form-control-sm" name="status">
                                <option value="">All</option>
                                <option value="success" {{ request('status')=='success'?'selected':'' }}>
                                    Success
                                </option>
                                <option value="failed" {{ request('status')=='failed'?'selected':'' }}>
                                    Failed
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="small font-weight-bold text-muted">Device</label>
                            <select class="form-control form-control-sm" name="device_type">
                                <option value="">All Devices</option>
                                <option value="desktop" {{ request('device_type')=='desktop'?'selected':'' }}>
                                    Desktop
                                </option>
                                <option value="mobile" {{ request('device_type')=='mobile'?'selected':'' }}>
                                    Mobile
                                </option>
                                <option value="tablet" {{ request('device_type')=='tablet'?'selected':'' }}>
                                    Tablet
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-success mr-2">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                            <a href="{{ route('login-history.index') }}"
                            class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-4">
                            <input type="text"
                                class="form-control form-control-sm"
                                name="search"
                                placeholder="Search by IP, Browser, Platform, User Name or Email"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Login History Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Login History Records</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-warning btn-sm" id="clearOldRecords">
                        <i class="fas fa-trash"></i> Clear Old Records
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="loginHistoryTable" class="table table-bordered table-striped table-sm">
                        <thead class="bg-dark">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Login Time</th>
                                <th>Logout Time</th>
                                <th>Duration</th>
                                <th>IP Address</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>Platform</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loginHistories as $history)
                            <tr>
                                <td>{{ $loop->iteration + ($loginHistories->currentPage() - 1) * $loginHistories->perPage() }}</td>
                                <td>
                                    <a href="{{ route('login-history.user', $history->user_id) }}" 
                                       class="text-primary">
                                        {{ $history->user->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $history->user->email }}</small>
                                </td>
                               <td>
                                    {{ $history->login_at->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}
                                </td>
                                <td>
                                    @if($history->logout_at)
                                        {{ $history->logout_at->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}
                                    @else
                                        <span class="badge badge-info">Active</span>
                                    @endif
                                </td>

                                <td>
                                    @if($history->logout_at)
                                        {{ $history->login_at->diffForHumans($history->logout_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $history->ip_address }}</code>
                                    @if($history->location)
                                        <br><small>{{ $history->location }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($history->device_type == 'desktop')
                                        <i class="fas fa-desktop device-icon text-primary"></i>
                                    @elseif($history->device_type == 'mobile')
                                        <i class="fas fa-mobile-alt device-icon text-success"></i>
                                    @else
                                        <i class="fas fa-tablet-alt device-icon text-info"></i>
                                    @endif
                                    {{ ucfirst($history->device_type) }}
                                </td>
                                <td>{{ $history->browser }}</td>
                                <td>{{ $history->platform }}</td>
                                <td>
                                    @if($history->status == 'success')
                                        <span class="badge badge-success">Success</span>
                                    @else
                                        <span class="badge badge-danger">Failed</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-btn" 
                                            data-id="{{ $history->id }}" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No login history found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $loginHistories->firstItem() ?? 0 }} to {{ $loginHistories->lastItem() ?? 0 }}
                            of {{ $loginHistories->total() }} records
                        </div>
                        <div>
                            {{ $loginHistories->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Delete functionality
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('login-history') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to delete record.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Clear old records
    $('#clearOldRecords').click(function() {
        Swal.fire({
            title: 'Clear Old Records',
            input: 'number',
            inputLabel: 'Delete records older than (days)',
            inputValue: 90,
            showCancelButton: true,
            confirmButtonText: 'Clear',
            confirmButtonColor: '#f39c12',
            inputValidator: (value) => {
                if (!value || value < 1) {
                    return 'Please enter a valid number of days'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('login-history.clear-old') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        days: result.value
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire(
                                'Cleared!',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to clear old records.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endsection