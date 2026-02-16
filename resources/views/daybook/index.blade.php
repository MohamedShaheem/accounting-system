@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<style>
#daybookTable thead th {
    color: white;
}
.table td, .table th {
    vertical-align: middle;
}
.card.shadow-sm {
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
                <h1 class="m-0">Day Book Records</h1>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('daybook.index') }}" method="GET">
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
                                    <label class="small font-weight-bold text-muted">Transaction Type</label>
                                    <select class="form-control form-control-sm"
                                            name="transaction_type">
                                        <option value="all" {{ request('transaction_type','all')=='all'?'selected':'' }}>All</option>
                                        <option value="debit" {{ request('transaction_type')=='debit'?'selected':'' }}>Debit</option>
                                        <option value="credit" {{ request('transaction_type')=='credit'?'selected':'' }}>Credit</option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end justify-content-center">
                                    <button type="submit" class="btn btn-sm btn-success mr-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('daybook.index') }}"
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
                <h3 class="card-title">Day Book Records</h3>
                <div class="card-tools">
                    <a href="{{ route('daybook.create') }}" class="btn btn-success btn-sm">
                         Add New Transaction
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="daybookTable" class="table table-bordered table-striped">
                        <thead class="bg-dark">
                            <tr>
                                <th>Seq</th>
                                <th>Date</th>
                                <th>Transaction</th>
                                <th>Invoice No</th>
                                <th>Remark</th>
                                <th class="text-right">Debit Amount <br> <span class="text-right text-success">{{ number_format($totalDebit, 2) }}</span></th>
                                <th class="text-right">Credit Amount <br> <span class="text-right text-danger">{{ number_format($totalCredit, 2) }}</span></th>
                                <th class="text-right">Balance <br> <span class="text-right {{ $finalBalance < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($finalBalance, 2) }}</span></th>
                                {{-- <th class="text-center">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                            <tr>
                                <td>{{ $record['seq'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($record['date'])->format('d/m/Y') }}</td>
                                <td>{{ $record['transaction'] }}</td>
                                <td>{{ $record['invoice_no'] ?: '-' }}</td>
                                <td>{{ $record['remark'] ?: '-' }}</td>
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
                                {{-- <td class="text-center">
                                    @if(!empty($record['id']))
                                        <a href="{{ route('daybook.edit', $record['id']) }}"
                                        class="btn btn-sm btn-primary"
                                        title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $record['id'] }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td> --}}
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
                                {{-- <td></td> --}}
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Showing {{ $daybooks->firstItem() }} to {{ $daybooks->lastItem() }}
                                of {{ $daybooks->total() }} records
                            </div>

                            <div>
                                {{ $daybooks->links('pagination::bootstrap-4') }}
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
        window.location.href = "{{ route('daybook.index') }}";
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
                    url: "{{ url('daybook') }}/" + id,
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