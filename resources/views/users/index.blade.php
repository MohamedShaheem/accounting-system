@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')

<style>
#usersTable thead th {
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
</style>

<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">User Management</h1>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container">
        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="row justify-content-center">
                        <div class="col-lg-11 col-xl-10">
                            <div class="form-row align-items-end justify-content-center">

                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">Search</label>
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        name="search"
                                        placeholder="Name, Email, Username, Phone"
                                        value="{{ request('search') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted">Role</label>
                                    <select class="form-control form-control-sm" name="role">
                                        <option value="">All Roles</option>
                                        <option value="superadmin" {{ request('role')=='superadmin'?'selected':'' }}>
                                            Super Admin
                                        </option>
                                        <option value="admin" {{ request('role')=='admin'?'selected':'' }}>
                                            Admin
                                        </option>
                                        <option value="staff" {{ request('role')=='staff'?'selected':'' }}>
                                            Staff
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted">Status</label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status')=='active'?'selected':'' }}>
                                            Active
                                        </option>
                                        <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>
                                            Inactive
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end justify-content-center">
                                    <button type="submit" class="btn btn-sm btn-success mr-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('users.index') }}"
                                    class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User List</h3>
                <div class="card-tools">
                    <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
                         Add New User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead class="bg-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                {{-- <th>Status</th> --}}
                                <th>Joined</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username ?? '-' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>
                                    @if($user->role == 'superadmin')
                                        <span class="badge badge-danger">Super Admin</span>
                                    @elseif($user->role == 'admin')
                                        <span class="badge badge-primary">Admin</span>
                                    @else
                                        <span class="badge badge-secondary">Staff</span>
                                    @endif
                                </td>
                                {{-- <td>
                                    @if($user->status == 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Inactive</span>
                                    @endif
                                </td> --}}
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('users.edit', $user->id) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-btn" 
                                            data-id="{{ $user->id }}" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }}
                            of {{ $users->total() }} users
                        </div>
                        <div>
                            {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                    url: "{{ url('users') }}/" + id,
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
                        let message = 'Failed to delete user.';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire(
                            'Error!',
                            message,
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