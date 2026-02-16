@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<style>
#salesTable thead th {
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
                <h1 class="m-0">Sales List</h1>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container-fluid">
        <!-- Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('salesbook.index') }}" method="GET">
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
                                    <label class="small font-weight-bold text-muted">Invoice Type</label>
                                    <select class="form-control form-control-sm"
                                            name="invoice_type">
                                        <option value="all" {{ request('invoice_type','all')=='all'?'selected':'' }}>
                                            All
                                        </option>
                                        <option value="sales" {{ request('invoice_type')=='sales'?'selected':'' }}>
                                            Sales
                                        </option>
                                        <option value="purchase" {{ request('invoice_type')=='purchase'?'selected':'' }}>
                                            Purchase
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end justify-content-center">
                                    <button type="submit" class="btn btn-sm btn-success mr-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('salesbook.index') }}"
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


        <!-- Sales Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sales List</h3>
                {{-- <div class="card-tools">
                    <a href="{{ route('salesbook.create') }}" class="btn btn-success btn-sm">
                         Add New Sales
                    </a>
                </div> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="salesTable" class="table table-bordered table-striped">
                        <thead class="bg-dark">
                            <tr>
                                <th>Seq</th>
                                <th>Date</th>
                                <th>Invoice Type</th>
                                <th>Invoice Number</th>
                                <th>Name</th>
                                <th class="text-right">Debit Amount <br> <span class="text-right text-success">{{ number_format($totalDebit, 2) }}</span></th>
                                <th class="text-right">
                                    Gold Wt <br>
                                    <span class="text-warning">{{ number_format($totalGoldWeight, 3) }}</span>
                                </th>

                                <th class="text-right">
                                    Silver Wt <br>
                                    <span class="text-info">{{ number_format($totalSilverWeight, 3) }}</span>
                                </th>
                                <th class="text-right">Credit Amount <br> <span class="text-right text-danger">{{ number_format($totalCredit, 2) }}</span></th>
                                {{-- <th class="text-center">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesbooks as $index => $sale)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->transaction_date)->format('d/m/Y') }}</td>
                                <td>{{ $sale->invoice_type }}</td>
                                <td>{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->name }}</td>
                                <td class="text-right">
                                    @if($sale->debit > 0)
                                        {{ number_format($sale->debit, 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ number_format($sale->gold_weight ?? 0, 3) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($sale->silver_weight ?? 0, 3) }}
                                </td>

                                <td class="text-right text-danger">
                                    @if($sale->credit > 0)
                                        {{ number_format($sale->credit, 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td>
                                {{-- <td class="text-center">
                                    <a href="{{ route('salesbook.edit', $sale->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $sale->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td> --}}
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No records found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($salesbooks) > 0)
                        <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="5" class="text-right">TOTALS:</td>
                            <td class="text-right">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-right">{{ number_format($totalGoldWeight, 3) }}</td>
                            <td class="text-right">{{ number_format($totalSilverWeight, 3) }}</td>
                            <td class="text-right text-danger">{{ number_format($totalCredit, 2) }}</td>
                            {{-- <td></td> --}}
                        </tr>
                        </tfoot>
                        @endif
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $salesbooks->firstItem() }} to {{ $salesbooks->lastItem() }}
                            of {{ $salesbooks->total() }} records
                        </div>

                        <div>
                            {{ $salesbooks->links('pagination::bootstrap-4') }}
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
        window.location.href = "{{ route('salesbook.index') }}";
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
                    url: "{{ url('salesbook') }}/" + id,
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
                            'Failed to delete sales record.',
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