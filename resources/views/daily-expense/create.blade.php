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

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.card {
    border-radius: 13px;
}
</style>

<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Daily Expense</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('daily-expense.index') }}">Daily Expenses</a></li>
                    <li class="breadcrumb-item active">Add Expense</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content pb-3">
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Expense Details</h3>
            </div>
            <form action="{{ route('daily-expense.store') }}" method="POST" id="expenseForm">
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
                        <!-- Expense Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_date">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('expense_date') is-invalid @enderror" 
                                       id="expense_date" 
                                       name="expense_date" 
                                       value="{{ old('expense_date', date('Y-m-d')) }}" 
                                       required>
                                @error('expense_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Expense Code -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_code_id">Expense Code <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('expense_code_id') is-invalid @enderror" 
                                        id="expense_code_id" 
                                        name="expense_code_id" 
                                        required>
                                    <option value="">--- Select Expense Code ---</option>
                                    @foreach($expenseCodes as $code)
                                        <option value="{{ $code->id }}" {{ old('expense_code_id') == $code->id ? 'selected' : '' }}>
                                            {{ $code->expense_code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('expense_code_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Expense Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       class="form-control @error('expense_amount') is-invalid @enderror" 
                                       id="expense_amount" 
                                       name="expense_amount" 
                                       value="{{ old('expense_amount') }}" 
                                       placeholder="0.00" 
                                       required>
                                @error('expense_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_description">Description</label>
                                <textarea class="form-control @error('expense_description') is-invalid @enderror" 
                                          id="expense_description" 
                                          name="expense_description" 
                                          rows="1" 
                                          placeholder="Enter expense description">{{ old('expense_description') }}</textarea>
                                @error('expense_description')
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
                         Save Expense
                    </button>
                    <a href="{{ route('daily-expense.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'default',
        width: '100%',
        placeholder: '--- Select Expense Code ---'
    });

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

    $('#expenseForm').on('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        let amount = parseFloat($('#expense_amount').val());
        let expenseCode = $('#expense_code_id').val();

        if (!expenseCode) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Missing Expense Code',
                text: 'Please select an expense code'
            });
            return false;
        }

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