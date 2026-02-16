@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<style>
.custom-switch-lg .custom-control-label::before {
    height: 1.5rem;
    width: 3rem;
    border-radius: 1rem;
    top: -0.25rem;
}

.custom-switch-lg .custom-control-label::after {
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    top: -0rem;
}

.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1.5rem);
}

.card.bg-light {
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.custom-control-label {
    cursor: pointer;
}

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
                <h1 class="m-0">Add Bank Transaction</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('bank-transaction.index') }}">Bank Transactions</a></li>
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
            <form action="{{ route('bank-transaction.store') }}" method="POST" id="transactionForm">
                @csrf
                <div class="card-body">
                     <!-- Day Book Entry Toggle -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                <i class="fas fa-book text-primary"></i> Day Book Entry
                                            </h5>
                                            {{-- <p class="mb-0 text-muted small">
                                                When enabled, this transaction will also be recorded in the Day Book for double-entry bookkeeping
                                            </p> --}}
                                        </div>
                                        <div class="ml-3 mr-3">
                                            {{-- <div class="custom-control custom-switch custom-switch-lg custom-switch-off-secondary custom-switch-on-primary"> --}}
                                            <div class="custom-control custom-switch custom-switch-lg">
                                                <input type="checkbox" 
                                                       class="custom-control-input" 
                                                       id="daybook_entry" 
                                                       name="daybook_entry" 
                                                       value="1"
                                                       {{ old('daybook_entry', '1') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="daybook_entry"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="toggleStatus" class="mt-2">
                                        <span class="badge badge-success" id="statusOn" style="display: none;">
                                            <i class="fas fa-check-circle"></i> Double Entry Enabled
                                        </span>
                                        <span class="badge badge-secondary" id="statusOff" style="display: none;">
                                            <i class="fas fa-times-circle"></i> Single Entry Only
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Bank Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_id">Bank <span class="text-danger">*</span></label>
                                <select class="form-control @error('bank_id') is-invalid @enderror" 
                                        id="bank_id" name="bank_id" required>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
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
                                       value="{{ old('transaction_date', date('Y-m-d')) }}" 
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
                                    <option value="debit" {{ old('debit_credit') == 'debit' ? 'selected' : '' }}>Debit</option>
                                    <option value="credit" {{ old('debit_credit') == 'credit' ? 'selected' : '' }}>Credit</option>
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
                                       value="{{ old('transaction_amount') }}" 
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
                                          placeholder="Enter transaction description">{{ old('transaction_description') }}</textarea>
                                @error('transaction_description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Daybook Remark (Only shown if toggle is on) -->
                    {{-- <div class="row" id="daybookRemarkSection" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="daybook_remark">Day Book Remark (Optional)</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="daybook_remark" 
                                       name="daybook_remark" 
                                       value="{{ old('daybook_remark') }}" 
                                       placeholder="Additional remark for day book entry">
                            </div>
                        </div>
                    </div> --}}
                </div>

                <div class="card-footer">
                    <button type="submit" id="saveBtn" class="btn btn-success">
                         Save Transaction
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
    // Toggle daybook remark section and status badge
    function toggleDaybookRemark() {
        if ($('#daybook_entry').is(':checked')) {
            $('#daybookRemarkSection').slideDown();
            $('#statusOn').show();
            $('#statusOff').hide();
        } else {
            $('#daybookRemarkSection').slideUp();
            $('#statusOn').hide();
            $('#statusOff').show();
        }
    }

    // Initial check
    toggleDaybookRemark();

    // On toggle change
    $('#daybook_entry').change(function() {
        toggleDaybookRemark();
    });

    // Form validation
    let isSubmitting = false;

    $('#transactionForm').submit(function(e) {
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

        // prevent double submit
        isSubmitting = true;

        $('#saveBtn')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    });
});
</script>

@endsection