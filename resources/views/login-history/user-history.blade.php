@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')

<style>
#loginHistoryTable thead th {
    color: white;
}
.table td, .table th {
    vertical-align: middle;
}
.card{
    border-radius: 13px;
}
.device-icon {
    font-size: 1.2em;
}
.user-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 13px;
    padding: 20px;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Login History - {{ $user->name }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('login-history.index') }}">Login History</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container-fluid">
        <!-- User Info Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="user-info-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">{{ $user->name }}</h3>
                            <p class="mb-1"><i class="fas fa-envelope mr-2"></i>{{ $user->email }}</p>
                            <p class="mb-1"><i class="fas fa-user-tag mr-2"></i>{{ ucfirst($user->role) }}</p>
                            @if($user->phone)
                                <p class="mb-0"><i class="fas fa-phone mr-2"></i>{{ $user->phone }}</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-right">
                            <h5>Total Logins</h5>
                            <h2>{{ $loginHistories->total() }}</h2>
                            @if($user->latestLogin)
                                <p class="mb-0">Last Login: {{ $user->latestLogin->login_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $loginHistories->where('status', 'success')->count() }}</h3>
                        <p>Successful Logins</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $loginHistories->where('status', 'failed')->count() }}</h3>
                    <p>Failed Attempts</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $loginHistories->where('device_type', 'desktop')->count() }}</h3>
                    <p>Desktop Logins</p>
                </div>
                <div class="icon">
                    <i class="fas fa-desktop"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $loginHistories->where('device_type', 'mobile')->count() }}</h3>
                    <p>Mobile Logins</p>
                </div>
                <div class="icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Login History Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Login Sessions</h3>
            <div class="card-tools">
                <a href="{{ route('login-history.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to All History
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="loginHistoryTable" class="table table-bordered table-striped">
                    <thead class="bg-dark">
                        <tr>
                            <th>#</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Duration</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Browser</th>
                            <th>Platform</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loginHistories as $history)
                        <tr>
                            <td>{{ $loop->iteration + ($loginHistories->currentPage() - 1) * $loginHistories->perPage() }}</td>
                            <td>{{ $history->login_at->format('d/m/Y h:i A') }}</td>
                            <td>
                                @if($history->logout_at)
                                    {{ $history->logout_at->format('d/m/Y h:i A') }}
                                @else
                                    <span class="badge badge-info">Active</span>
                                @endif
                            </td>
                            <td>
                                @if($history->logout_at)
                                    {{ $history->login_at->diffForHumans($history->logout_at, true) }}
                                @else
                                    {{ $history->login_at->diffForHumans() }}
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No login history found</td>
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
                        {{ $loginHistories->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection