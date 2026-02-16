@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')

<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Daily Expense Codes</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container">

        {{-- Expense Code Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Expense Code List</h3>
                <div class="card-tools">
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createModal">
                         Add Expense Code
                    </button>
                </div>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-dark">
                            <tr>
                                <th width="80">#</th>
                                <th>Expense Code</th>
                                <th width="120" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($codes as $index => $code)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $code->expense_code }}</td>
                                    <td class="text-center">
                                        <button
                                            class="btn btn-sm btn-primary editBtn"
                                            data-id="{{ $code->id }}"
                                            data-code="{{ $code->expense_code }}"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No expense codes found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</section>

{{-- Create Modal --}}
<div class="modal fade" id="createModal">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('daily-expense-code.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Expense Code</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Expense Code</label>
                        <input type="text" name="expense_code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">
                         Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Expense Code</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Expense Code</label>
                        <input type="text" name="expense_code" id="editExpenseCode" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">
                         Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).on('click', '.editBtn', function () {
    let id = $(this).data('id');
    let code = $(this).data('code');

    $('#editExpenseCode').val(code);
    $('#editForm').attr('action', '/daily-expense-code/' + id);
    $('#editModal').modal('show');
});
</script>

<style>
.table td, .table th {
    vertical-align: middle;
}
</style>

@endsection
