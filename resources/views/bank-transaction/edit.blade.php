@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Bank Transaction</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('bank-transaction.index') }}">Bank Transactions</a></li>
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
            <form action="{{ route('bank-transaction.update', $transaction->id) }}" method="POST" id="transactionForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <!-- Bank Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_id">Bank <span class="text-danger">*</span></label>
                                <select class="form-control @error('bank_id') is-invalid @enderror" 
                                        id="bank_id" name="bank_id" required>
                                    <option value="">--- Choose Bank ---</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" 
                                            {{ old('bank_id', $transaction->bank_id) == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Transaction Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_date">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('transaction_date') is-invalid @enderror" 
                                       id="transaction_date" 
                                       name="transaction_date" 
                                       value="{{ old('transaction_date', $transaction->transaction_date) }}" 
                                       required>
                                @error('transaction_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Transaction Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="debit_credit">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('debit_credit') is-invalid @enderror" 
                                        id="debit_credit" name="debit_credit" required>
                                    <option value="">--- Select Debit or Credit ---</option>
                                    <option value="debit" {{ old('debit_credit', $transaction->debit_credit) == 'debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="credit" {{ old('debit_credit', $transaction->debit_credit) == 'credit' ? 'selected' : '' }}>Credit</option>
                                </select>
                                @error('debit_credit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Transaction Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control @error('transaction_amount') is-invalid @enderror" 
                                       id="transaction_amount" 
                                       name="transaction_amount" 
                                       value="{{ old('transaction_amount', $transaction->transaction_amount) }}" 
                                       placeholder="0.00" 
                                       required>
                                @error('transaction_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="transaction_description">Description</label>
                                <textarea class="form-control @error('transaction_description') is-invalid @enderror" 
                                          id="transaction_description" 
                                          name="transaction_description" 
                                          rows="3" 
                                          placeholder="Enter transaction description">{{ old('transaction_description', $transaction->transaction_description) }}</textarea>
                                @error('transaction_description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                         Update Transaction
                    </button>
                    <a href="{{ route('bank-transaction.index') }}" class="btn btn-secondary">
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
    });
});
</script>
@endsection