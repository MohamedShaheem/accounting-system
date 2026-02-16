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
                <h1 class="m-0">Add Individual Account Transaction</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('individual-account.index') }}">Individual Accounts</a></li>
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
            <form action="{{ route('individual-account.store') }}" method="POST" id="transactionForm">
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
                                        </div>
                                        <div class="ml-3 mr-3">
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
                        <!-- Individual Account Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="individual_account_id">Individual Account <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control @error('individual_account_id') is-invalid @enderror" 
                                            id="individual_account_id" name="individual_account_id" required>
                                        <option value="">--- Choose Individual ---</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" 
                                                {{ old('individual_account_id') == $account->id ? 'selected' : '' }}
                                                data-balance="{{ $account->current_balance }}">
                                                {{ $account->name }} ({{ $account->account_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('individual_account_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small id="currentBalance" class="form-text"></small>
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

                    <!-- Remark -->
                    {{-- <div class="row" id="remarkSection">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="remark">Remark (Optional)</label>
                                <input type="text" 
                                    class="form-control" 
                                    id="remark" 
                                    name="remark" 
                                    value="{{ old('remark') }}" 
                                    placeholder="Additional remark for this transaction">
                            </div>
                        </div>
                    </div> --}}


                </div>

                <div class="card-footer">
                    <button type="submit" id="saveBtn" class="btn btn-success">
                         Save Transaction
                    </button>
                    <a href="{{ route('individual-account.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Toggle daybook status badge
    function toggleDaybookStatus() {
        if ($('#daybook_entry').is(':checked')) {
            $('#statusOn').show();
            $('#statusOff').hide();
        } else {
            $('#statusOn').hide();
            $('#statusOff').show();
        }
    }

    // Initial check
    toggleDaybookStatus();

    // On toggle change
    $('#daybook_entry').change(function() {
        toggleDaybookStatus();
    });

    // toggle remark section
    function toggleRemark() {
        if ($('#daybook_entry').is(':checked')) {
            $('#remarkSection').slideDown();
        } else {
            $('#remarkSection').slideUp();
        }
    }

    toggleRemark();

    $('#daybook_entry').change(function() {
        toggleRemark();
    });



    // Show current balance when account is selected
    $('#individual_account_id').change(function() {
        let selectedOption = $(this).find('option:selected');
        let balance = parseFloat(selectedOption.data('balance'));
        
        if (balance !== undefined && !isNaN(balance)) {
            let balanceClass = balance >= 0 ? 'text-success' : 'text-danger';
            $('#currentBalance').html(
                `Current Balance: <span class="${balanceClass}"><strong>${balance.toFixed(2)}</strong></span>`
            );
        } else {
            $('#currentBalance').html('');
        }
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

        // Prevent double submit
        isSubmitting = true;

        $('#saveBtn')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    });
});
</script>

@endsection