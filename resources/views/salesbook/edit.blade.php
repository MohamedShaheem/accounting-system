@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Sales Book Transaction</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('salesbook.index') }}">Sales Book Transactions</a></li>
                    <li class="breadcrumb-item active">Edit Transaction</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaction Details</h3>
            </div>
            <form action="{{ route('salesbook.update', $salesbook->id) }}" method="POST" id="transactionForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <!-- Transaction Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_date">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('transaction_date') is-invalid @enderror" 
                                       id="transaction_date" 
                                       name="transaction_date" 
                                       value="{{ old('transaction_date', $salesbook->transaction_date->format('Y-m-d')) }}" 
                                       required>
                                @error('transaction_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Transaction Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="debit_credit">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('debit_credit') is-invalid @enderror" 
                                        id="debit_credit" name="debit_credit" required>
                                    <option value="">--- Select Debit or Credit ---</option>
                                    <option value="debit" {{ old('debit_credit', $salesbook->debit > 0 ? 'debit' : 'credit') == 'debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="credit" {{ old('debit_credit', $salesbook->debit > 0 ? 'debit' : 'credit') == 'credit' ? 'selected' : '' }}>Credit</option>
                                </select>
                                @error('debit_credit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Invoice Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_type">Invoice Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('invoice_type') is-invalid @enderror" 
                                        id="invoice_type" name="invoice_type" required>
                                    <option value="">--- Select Invoice Type ---</option>
                                    <option value="sales" {{ old('invoice_type', $salesbook->invoice_type) == 'sales' ? 'selected' : '' }}>Sales</option>
                                    <option value="purchase" {{ old('invoice_type', $salesbook->invoice_type) == 'purchase' ? 'selected' : '' }}>Purchase</option>
                                </select>
                                @error('invoice_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div> 
                        
                        <!-- Invoice No -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_no">Invoice No</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="invoice_no" 
                                       name="invoice_no" 
                                       value="{{ old('invoice_no', $salesbook->invoice_no) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Transaction Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control @error('transaction_amount') is-invalid @enderror" 
                                       id="transaction_amount" 
                                       name="transaction_amount" 
                                       value="{{ old('transaction_amount', $salesbook->debit > 0 ? $salesbook->debit : $salesbook->credit) }}" 
                                       placeholder="0.00" 
                                       required>
                                @error('transaction_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $salesbook->name) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">                   
                        <!-- Gold Weight -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gold_weight">Gold Weight</label>
                                <input type="number" 
                                       step="0.001" 
                                       class="form-control @error('gold_weight') is-invalid @enderror" 
                                       id="gold_weight" 
                                       name="gold_weight" 
                                       value="{{ old('gold_weight', $salesbook->gold_weight) }}" 
                                       placeholder="0.000">
                                @error('gold_weight')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" id="saveBtn" class="btn btn-success">
                         Update Transaction
                    </button>
                    <a href="{{ route('salesbook.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    let isSubmitting = false;

    $('#transactionForm').on('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        let amount = parseFloat($('#transaction_amount').val());

        if (amount <= 0 || isNaN(amount)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Amount',
                text: 'Please enter a valid amount greater than 0'
            });
            return false;
        }

        isSubmitting = true;

        $('#saveBtn')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    });
});
</script>

@endsection