@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')

<style>
.balance-positive { color: #28a745; }
.balance-negative { color: #dc3545; }
.card {
    border-radius: 13px;
}
.card-header{
    border-radius: 13px 13px 0 0;
}
</style>

<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Cash Borrow/Lend Accounts Overview</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#accountModal" id="addAccountBtn">
                         Add New Account
                    </button>
                    <a href="{{ route('cash-borrow-lend.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container">

        @if($accounts->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark">
                        <h3 class="card-title">Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h6 class="text-muted">Total Accounts</h6>
                                <h4>{{ $accounts->count() }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Total Borrowed</h6>
                                <h4 class="text-success">
                                    {{ number_format($accounts->where('current_balance','>',0)->sum('current_balance'),2) }}
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Total Lent</h6>
                                <h4 class="text-danger">
                                    {{ number_format(abs($accounts->where('current_balance','<',0)->sum('current_balance')),2) }}
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Net Balance</h6>
                                <h4 class="{{ $accounts->sum('current_balance') >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($accounts->sum('current_balance'),2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cash Borrow/Lend Accounts</h3>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Account Name</th>
                                    <th class="text-right">Balance</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $index => $account)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $account->name }}</strong></td>
                                    <td class="text-right {{ $account->current_balance >= 0 ? 'balance-positive' : 'balance-negative' }}">
                                        {{ number_format($account->current_balance,2) }}
                                    </td>
                                    <td class="text-center">{{ $account->transactions_count }}</td>
                                    <td class="text-center">
                                        @if($account->current_balance > 0)
                                            <span class="badge badge-success">Borrowed</span>
                                        @elseif($account->current_balance < 0)
                                            <span class="badge badge-danger">Lent</span>
                                        @else
                                            <span class="badge badge-secondary">Balanced</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('cash-borrow-lend.index', ['cash_borrow_lend_account_id' => $account->id]) }}"
                                               class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-primary edit-account-btn"
                                                    data-id="{{ $account->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {{-- <button type="button" class="btn btn-danger delete-account-btn"
                                                    data-id="{{ $account->id }}" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button> --}}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No Cash Borrow/Lend Accounts Found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<div class="modal fade" id="accountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalLabel">Add New Account</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form id="accountForm">
                @csrf
                <input type="hidden" id="account_id">
                <input type="hidden" id="form_method" value="POST">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Account Name *</label>
                        <input type="text" class="form-control" id="name" name="name">
                        <span class="invalid-feedback d-block" id="name-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Initial Balance</label>
                        <input type="number" step="0.01" class="form-control" id="current_balance" name="current_balance" value="0.00">
                        <span class="invalid-feedback d-block" id="current_balance-error"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" type="submit">
                         Save Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    $('#addAccountBtn').click(() => {
        $('#accountForm')[0].reset();
        $('#account_id').val('');
        $('#form_method').val('POST');
        $('#accountModalLabel').text('Add New Account');
        $('.invalid-feedback').text('');
    });

    $('.edit-account-btn').click(function () {
        let id = $(this).data('id');
        $.get("{{ url('cash-borrow-lend-get') }}/" + id, res => {
            if (res.success) {
                $('#account_id').val(res.account.id);
                $('#name').val(res.account.name);
                $('#current_balance').val(res.account.current_balance);
                $('#form_method').val('PUT');
                $('#accountModalLabel').text('Edit Account');
                $('#accountModal').modal('show');
            }
        });
    });

    $('#accountForm').submit(function (e) {
        e.preventDefault();
        let id = $('#account_id').val();
        let method = $('#form_method').val();
        let url = method === 'PUT'
            ? "{{ url('cash-borrow-lend-update') }}/" + id
            : "{{ route('cash-borrow-lend-store') }}";

        $.ajax({
            url, type: method, data: $(this).serialize(),
            success: res => location.reload(),
            error: xhr => {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, (k, v) => {
                        $('#' + k + '-error').text(v[0]);
                    });
                }
            }
        });
    });

    $('.delete-account-btn').click(function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true
        }).then(r => {
            if (r.isConfirmed) {
                $.ajax({
                    url: "{{ url('cash-borrow-lend-delete') }}/" + id,
                    type: 'DELETE',
                    data: {_token: "{{ csrf_token() }}"},
                    success: () => location.reload()
                });
            }
        });
    });
});
</script>

@endsection