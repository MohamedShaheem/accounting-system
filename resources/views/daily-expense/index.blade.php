@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<style>
#expenseTable thead th {
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
</style>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Daily Expense List</h1>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('daily-expense.index') }}" method="GET">
                    <div class="row justify-content-center">
                        <div class="col-lg-10 col-xl-9">
                            <div class="form-row align-items-end justify-content-center">

                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">From Date</label>
                                    <input type="date"
                                        class="form-control form-control-sm"
                                        name="date_from"
                                        value="{{ request('date_from', \Carbon\Carbon::today()->toDateString()) }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">To Date</label>
                                    <input type="date"
                                        class="form-control form-control-sm"
                                        name="date_to"
                                        value="{{ request('date_to', \Carbon\Carbon::today()->toDateString()) }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">Expense Code</label>
                                    <select class="form-control form-control-sm"
                                            name="expense_code_id">
                                        <option value="all"
                                            {{ request('expense_code_id','all')=='all'?'selected':'' }}>
                                            All Codes
                                        </option>
                                        @foreach($expenseCodes as $code)
                                            <option value="{{ $code->id }}"
                                                {{ request('expense_code_id')==$code->id?'selected':'' }}>
                                                {{ $code->expense_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end justify-content-center">
                                    <button type="submit" class="btn btn-sm btn-success mr-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('daily-expense.index') }}"
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


        <!-- Expense Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daily Expense List</h3>
                <div class="card-tools">
                    <a href="{{ route('daily-expense.create') }}" class="btn btn-success btn-sm">
                         Add New Daily Expense
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="expenseTable" class="table table-bordered table-striped">
                        <thead class="bg-dark">
                            <tr>
                                <th>Seq</th>
                                <th>Date</th>
                                <th>Expense Code</th>
                                <th>Description</th>
                                <th class="text-right">Amount <br> <span class="text-right text-info">{{ number_format($totalAmount, 2) }}</span></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $index => $expense)
                            <tr>
                                <td>{{ $expenses->firstItem() + $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                                <td>{{ $expense->expenseCode->expense_code ?? 'N/A' }}</td>
                                <td>{{ $expense->expense_description ?? '-' }}</td>
                                <td class="text-right">{{ number_format($expense->expense_amount, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('daily-expense.edit', $expense->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $expense->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button> --}}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No records found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($expenses) > 0)
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="4" class="text-right">TOTAL:</td>
                                <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }}
                            of {{ $expenses->total() }} records
                        </div>

                        <div>
                            {{ $expenses->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Remove Filter Button
    $('#removeFilter').click(function() {
        window.location.href = "{{ route('daily-expense.index') }}";
    });

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
                    url: "{{ url('daily-expense') }}/" + id,
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
                            'Failed to delete expense record.',
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