@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')

<style>
#individualAccountTable thead th {
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
                <h1 class="m-0">Individual A/C Transaction List</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('individual-account.overview') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Accounts Overview
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container-fluid">
       <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('individual-account.index') }}" method="GET" id="filterForm">
                    <div class="row justify-content-center">
                        <div class="col-lg-11 col-xl-10">
                            <div class="form-row align-items-end justify-content-center">

                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted">From Date</label>
                                    <input type="date"
                                        class="form-control form-control-sm"
                                        name="date_from"
                                        value="{{ request('date_from', date('Y-m-d')) }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted">To Date</label>
                                    <input type="date"
                                        class="form-control form-control-sm"
                                        name="date_to"
                                        value="{{ request('date_to', date('Y-m-d')) }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted">Transaction Type</label>
                                    <select class="form-control form-control-sm"
                                            name="debit_credit">
                                        <option value="">All</option>
                                        <option value="debit" {{ request('debit_credit')=='debit'?'selected':'' }}>Debit</option>
                                        <option value="credit" {{ request('debit_credit')=='credit'?'selected':'' }}>Credit</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">Individual A/C</label>
                                    <select class="form-control form-control-sm"
                                            name="individual_account_id">
                                        <option value="">All Accounts</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ request('individual_account_id')==$account->id?'selected':'' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end justify-content-center">
                                    <button type="submit" class="btn btn-sm btn-success mr-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('individual-account.index') }}"
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


        <!-- Records Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Individual A/C Transaction List</h3>
                <div class="card-tools">
                    <a href="{{ route('individual-account.create') }}" class="btn btn-success btn-sm">
                         Add New Transaction
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="individualAccountTable" class="table table-bordered table-striped">
                        <thead class="bg-dark">
                            <tr>
                                <th>Seq</th>
                                <th>Individual A/C</th>
                                <th>Date</th>
                                <th>Transaction Description</th>
                                <th>Remark</th>
                                <th class="text-right">Debit Amount <br> <span class="text-success">{{ number_format($totalDebit, 2) }}</span></th>
                                <th class="text-right">Credit Amount <br> <span class="text-danger">{{ number_format($totalCredit, 2) }}</span></th>
                                <th class="text-right">Balance <br> <span class="{{ $finalBalance < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($finalBalance, 2) }}</span></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                            <tr>
                                <td>{{ $record['seq'] }}</td>
                                <td>
                                    {{ $record['account_name'] ?? '-' }}<br>
                                    <small class="text-muted">{{ $record['account_no'] ?? '' }}</small>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($record['transaction_date'])->format('d/m/Y') }}</td>
                                <td>{{ $record['transaction_description'] ?: '-' }}</td>
                                <td>
                                    {{ $record['remark'] ?: '-' }}
                                </td>
                                <td class="text-right">
                                    @if($record['debit_amount'] > 0)
                                        <span class="text-success">{{ number_format($record['debit_amount'], 2) }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($record['credit_amount'] > 0)
                                        <span class="text-danger">{{ number_format($record['credit_amount'], 2) }}</span>
                                    @endif
                                </td>
                                <td class="text-right {{ $record['balance'] < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($record['balance'], 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('individual-account.edit', $record['id']) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $record['id'] }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button> --}}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No records found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($records) > 0)
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="5" class="text-right">TOTALS:</td>
                                <td class="text-right text-success">{{ number_format($totalDebit, 2) }}</td>
                                <td class="text-right text-danger">{{ number_format($totalCredit, 2) }}</td>
                                <td class="text-right {{ $finalBalance < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($finalBalance, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }}
                            of {{ $transactions->total() }} records
                        </div>
                        <div>
                            {{ $transactions->appends(request()->query())->links('pagination::bootstrap-4') }}
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
        window.location.href = "{{ route('individual-account.index') }}";
    });

    // Delete functionality
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will also update the account balance!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('individual-account') }}/" + id,
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
                            'Failed to delete transaction.',
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