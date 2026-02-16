@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<style>
.badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
}
.card {
    border-radius: 13px;
}
</style>

<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Day Book Transaction</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('daybook.index') }}">Day Book Transactions</a></li>
                    <li class="breadcrumb-item active">Add Transaction</li>
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
            <form action="{{ route('daybook.store') }}" method="POST" id="transactionForm">
                @csrf
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
                                       value="{{ old('transaction_date', date('Y-m-d')) }}" 
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
                                    <option value="debit" {{ old('debit_credit') == 'debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="credit" {{ old('debit_credit') == 'credit' ? 'selected' : '' }}>Credit</option>
                                </select>
                                @error('debit_credit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
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
                                       value="{{ old('transaction_amount') }}" 
                                       placeholder="0.00" 
                                       required>
                                @error('transaction_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Invoice Numbers -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_numbers">Invoice Numbers</label>
                                <input type="text" 
                                       class="form-control @error('invoice_numbers') is-invalid @enderror" 
                                       id="invoice_numbers" 
                                       name="invoice_numbers" 
                                       value="{{ old('invoice_numbers') }}" 
                                       placeholder="Enter invoice numbers">
                                @error('invoice_numbers')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Transaction Description -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_description">Description <span class="text-danger">*</span></label>
                                <input class="form-control @error('transaction_description') is-invalid @enderror" 
                                          id="transaction_description" 
                                          name="transaction_description" 
                                          rows="3" 
                                          placeholder="Enter transaction description" 
                                          required>
                                @error('transaction_description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Remark -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="remark">Remark (Optional)</label>
                                <input type="text" 
                                       class="form-control @error('remark') is-invalid @enderror" 
                                       id="remark" 
                                       name="remark" 
                                       value="{{ old('remark') }}" 
                                       placeholder="Additional remarks">
                                @error('remark')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                         Save Transaction
                    </button>
                    <a href="{{ route('daybook.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Form validation
    $('#transactionForm').submit(function(e) {
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

        let description = $('#transaction_description').val().trim();
        if (description === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Description Required',
                text: 'Please enter a transaction description'
            });
            return false;
        }
    });
});
</script>

@endsection