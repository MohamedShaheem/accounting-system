@extends(Auth::user()->role === 'superadmin' ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
<style>
    :root {
        --primary-blue: #0651f3;
        --secondary-blue: #1d66db;
        --light-blue: #0662d1;
        --accent-blue: #1e40af;
        --bg-light: #f8fafc;
        --card-bg: #ffffff;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --success-green: #10b981;
        --gradient-primary: linear-gradient(135deg, #032bad 0%, #0d2fc9 100%);
        --gradient-primary-btn: linear-gradient(135deg, #0242f3 0%, #1b51e7 100%);
        --gradient-light: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }

    body {
        background: var(--bg-light);
    }

    .futuristic-header {
        background: var(--gradient-primary);
        padding: 3rem 0 8rem;
        position: relative;
        overflow: hidden;
        margin-bottom: -5rem;
    }

    .futuristic-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .futuristic-header h1 {
        color: white;
        font-weight: 700;
        font-size: 2.5rem;
        margin: 0;
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .futuristic-header .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .futuristic-header .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.9);
    }

    .futuristic-header .breadcrumb-item.active {
        color: rgba(255, 255, 255, 0.7);
    }

    .sync-animation {
        display: none;
        text-align: center;
        margin: 30px 0;
        padding: 40px 20px;
        background: var(--gradient-light);
        border-radius: 20px;
        border: 2px solid rgba(37, 99, 235, 0.1);
    }

    .spinner-container {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
    }

    .spinner-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 4px solid #e0e7ff;
        border-top: 4px solid var(--primary-blue);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .spinner-ring:nth-child(2) {
        border-top-color: var(--light-blue);
        animation-duration: 1.5s;
        opacity: 0.6;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .sync-icon {
        font-size: 40px;
        color: var(--primary-blue);
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
    }

    .progress-text {
        color: var(--primary-blue);
        font-size: 18px;
        font-weight: 600;
        margin-top: 15px;
    }

    .modern-card {
        background: var(--card-bg);
        border-radius: 24px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 2rem;
    }

    .modern-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    .modern-card-header {
        background: var(--gradient-primary);
        padding: 2rem;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .modern-card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 100% 0%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .modern-card-header h3 {
        color: white;
        font-weight: 600;
        font-size: 1.25rem;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .modern-card-body {
        padding: 2rem;
    }

    .modern-card-footer {
        padding: 1.5rem 2rem;
        background: transparent;
        border-top: 1px solid var(--border-color);
    }

    .form-group label {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .btn-sync {
        background: var(--gradient-primary-btn);
        border: none;
        color: white;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-sync::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-sync:hover::before {
        left: 100%;
    }

    .btn-sync:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        color: white;
    }

    .btn-sync:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .stats-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
    }

    .stats-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.15);
        border-color: var(--primary-blue);
    }

    .stats-card .stat-icon {
        width: 60px;
        height: 60px;
        background: var(--gradient-light);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: var(--primary-blue);
        font-size: 24px;
    }

    .stats-card .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--primary-blue);
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stats-card .stat-label {
        font-size: 0.875rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .history-card {
        background: var(--card-bg);
        border-radius: 24px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .history-card-header {
        background: var(--gradient-light);
        padding: 1.5rem 2rem;
        border: none;
    }

    .history-card-header h3 {
        color: var(--primary-blue);
        font-weight: 600;
        font-size: 1.125rem;
        margin: 0;
    }

    .history-card-header:hover {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    }

    .toggle-icon {
        color: var(--primary-blue);
        font-size: 1.25rem;
        transition: transform 0.3s ease;
    }

    .toggle-icon.rotate {
        transform: rotate(180deg);
    }

    .history-table-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
    }

    .history-table-container.show {
        max-height: 500px;
    }

    .history-table {
        margin: 0;
    }

    .history-table thead th {
        background: #f1f5f9;
        color: var(--text-primary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem 1.5rem;
        border: none;
    }

    .history-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .history-table tbody tr:hover {
        background: var(--gradient-light);
        transform: scale(1.01);
    }

    .history-table tbody td {
        padding: 1rem 1.5rem;
        color: var(--text-secondary);
        font-weight: 500;
        border: none;
    }

    .alert {
        border-radius: 16px;
        border: none;
        padding: 1.25rem 1.5rem;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .text-danger {
        color: #ef4444;
    }


    /* Responsive adjustments */
    @media (max-width: 768px) {
        .futuristic-header h1 {
            font-size: 1.75rem;
        }
        
        .modern-card-body {
            padding: 1.5rem;
        }
    }
</style>

<!-- Content Header -->
<section class="futuristic-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-sync-alt floating-icon"></i> Data Sync</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Sync</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content pb-4">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <!-- Sync Form Card -->
                <div class="modern-card">
                    <div class="modern-card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class="fas fa-cloud-download-alt"></i> Sync Transactions
                            </h3>
                            <small class="text-white-50">Jewel Plaza POS System</small>
                        </div>

                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-white mr-2" style="font-size: 1.2rem;"></i>
                            <span class="text-white" id="current-date" style="font-size: 1rem; font-weight: 500;"></span>
                        </div>
                    </div>

                    
                    <form id="syncForm">
                        <div class="modern-card-body">
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date">Select Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="date" name="date" required>
                                    </div>
                                </div>
                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date (Optional)</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date">
                                    </div>
                                </div> --}}
                            </div>

                            <!-- Sync Animation -->
                            <div class="sync-animation" id="syncAnimation">
                                <div class="spinner-container">
                                    <div class="spinner-ring"></div>
                                    <div class="spinner-ring"></div>
                                </div>
                                <i class="fas fa-database sync-icon"></i>
                                <div class="progress-text">Syncing data, please wait...</div>
                            </div>

                            <!-- Result Container -->
                            <div id="resultContainer" style="display: none; margin-top: 20px;">
                                <div class="alert" id="resultAlert"></div>
                            </div>
                        </div>
                        
                        <div class="modern-card-footer">
                            <button type="submit" class="btn btn-sync btn-block" id="syncBtn">
                                <i class="fas fa-cloud-download-alt"></i> Start Synchronization
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Stats Cards -->
                <div id="statsGrid" class="row" style="display: none;">
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <div class="stat-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-number" id="processedCount">0</div>
                            <div class="stat-label">Processed Invoices</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <div class="stat-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="stat-number" id="insertedCount">0</div>
                            <div class="stat-label">Inserted</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <div class="stat-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="stat-number" id="updatedCount">0</div>
                            <div class="stat-label">Updated</div>
                        </div>
                    </div>
                </div>

                {{-- Sync History --}}
                @if(isset($latestSyncs) && $latestSyncs->count())
                <div class="row">
                    <div class="col-md-12">
                        <div class="history-card">
                            <div class="history-card-header" style="cursor: pointer;" id="historyToggle">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">
                                        <i class="fas fa-history"></i> Last 5 Sync Records
                                    </h3>
                                    <i class="fas fa-chevron-down toggle-icon" id="toggleIcon"></i>
                                </div>
                            </div>

                            <div class="p-0 history-table-container" id="historyTableContainer" style="display: none;">
                                <table class="table history-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Synced At</th>
                                            <th>Synced By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestSyncs as $index => $sync)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $sync->synced_at->format('Y-m-d') }}
                                            </td>
                                            <td>
                                                {{ $sync->user->name ?? 'System' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</section>

<script>
    // Set current date
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
</script>

<script>
$(document).ready(function() {
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    $('#date').val(today);

    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

     $('#syncForm').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Start Data Sync?',
            text: 'This process may take some time. Do you want to continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Start Sync',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            confirmButtonColor: '#0651f3',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            // Hide previous results
            $('#resultContainer').hide();
            $('#statsGrid').hide();

            // Show animation
            $('#syncAnimation').show();

            // Disable button
            $('#syncBtn')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Syncing...');

            const formData = {
                date: $('#date').val(),
                end_date: $('#end_date').val()
            };

            $.ajax({
                url: '{{ route("sync.process") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#syncAnimation').hide();

                    toastr.success(response.message);

                    $('#resultAlert')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .html('<i class="fas fa-check-circle"></i> <strong>Success!</strong> ' + response.message);

                    $('#resultContainer').show();

                    if (response.data) {
                        $('#processedCount').text(response.data.orders.processed || 0);
                        $('#insertedCount').text(response.data.orders.inserted || 0);
                        $('#updatedCount').text(response.data.orders.updated || 0);
                        $('#statsGrid').fadeIn();
                    }

                    $('#syncBtn')
                        .prop('disabled', false)
                        .html('<i class="fas fa-cloud-download-alt"></i> Start Synchronization');
                },
                error: function(xhr) {
                    $('#syncAnimation').hide();

                    let errorMessage = 'An error occurred during sync';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    toastr.error(errorMessage);

                    $('#resultAlert')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .html('<i class="fas fa-exclamation-circle"></i> <strong>Error!</strong> ' + errorMessage);

                    $('#resultContainer').show();

                    $('#syncBtn')
                        .prop('disabled', false)
                        .html('<i class="fas fa-cloud-download-alt"></i> Start Synchronization');
                }
            });
        });
    });

    // Date validations
    $('#date, #end_date').on('change', function () {
        const startDate = $('#date').val();
        const endDate   = $('#end_date').val();

        // Today's date (YYYY-MM-DD)
        const today = new Date().toISOString().split('T')[0];

        // 1️⃣ Start date must not be in the future
        if (startDate && startDate > today) {
            toastr.warning('Start date cannot be in the future');
            $('#date').val('');
            return;
        }

        // 2️⃣ End date must not be before start date
        if (startDate && endDate && endDate < startDate) {
            toastr.warning('End date must be after or equal to start date');
            $('#end_date').val('');
            return;
        }

        // 3️⃣ End date must not be in the future
        if (endDate && endDate > today) {
            toastr.warning('End date cannot be in the future');
            $('#end_date').val('');
            return;
        }
    });

    // Toggle History Table Dropdown
    $('#historyToggle').on('click', function() {
        const container = $('#historyTableContainer');
        const icon = $('#toggleIcon');
        
        if (container.is(':visible')) {
            container.slideUp(300);
            container.removeClass('show');
            icon.removeClass('rotate');
        } else {
            container.slideDown(300);
            container.addClass('show');
            icon.addClass('rotate');
        }
    });

});
</script>
@endsection