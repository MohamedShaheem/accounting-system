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
        --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --gradient-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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

    .futuristic-header h2 {
        color: white;
        font-weight: 700;
        font-size: 2.5rem;
        margin: 0 0 0.5rem 0;
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .futuristic-header p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.125rem;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .modern-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 2px solid var(--border-color);
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }

    .modern-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .modern-card.card-primary::before {
        background: var(--gradient-primary);
    }

    .modern-card.card-success::before {
        background: var(--gradient-success);
    }

    .modern-card.card-info::before {
        background: var(--gradient-info);
    }

    .modern-card.card-danger::before {
        background: var(--gradient-danger);
    }

    .modern-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
        border-color: var(--primary-blue);
    }

    .modern-card .card-body {
        padding: 1.75rem;
    }

    .stat-icon-container {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: transform 0.3s ease;
    }

    .modern-card:hover .stat-icon-container {
        transform: scale(1.1);
    }

    .icon-primary {
        background: var(--gradient-light);
        color: var(--primary-blue);
    }

    .icon-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
    }

    .icon-info {
        background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
        color: #0891b2;
    }

    .icon-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-secondary);
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .stat-details {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .chart-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .chart-card-header {
        background: var(--gradient-light);
        padding: 1.5rem;
        border: none;
    }

    .chart-card-header h6 {
        color: var(--primary-blue);
        font-weight: 600;
        font-size: 1rem;
        margin: 0;
    }

    .chart-card-body {
        padding: 1.5rem;
    }

    .overview-item {
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .overview-item:last-child {
        border-bottom: none;
    }

    .overview-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .overview-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .overview-progress {
        height: 6px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .overview-progress-bar {
        height: 100%;
        background: var(--gradient-primary);
        transition: width 0.3s ease;
    }

    .overview-extra {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .table-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .table-card-header {
        background: var(--gradient-light);
        padding: 1.5rem;
        border: none;
    }

    .table-card-header h6 {
        color: var(--primary-blue);
        font-weight: 600;
        font-size: 1rem;
        margin: 0;
    }

    .modern-table {
        margin: 0;
    }

    .modern-table thead th {
        background: #f1f5f9;
        color: var(--text-primary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem 1.5rem;
        border: none;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table tbody tr:hover {
        background: var(--gradient-light);
        transform: scale(1.01);
    }

    .modern-table tbody td {
        padding: 1rem 1.5rem;
        color: var(--text-secondary);
        font-weight: 500;
        border: none;
    }

    .badge-modern {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-success-modern {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .badge-danger-modern {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .futuristic-header h2 {
            font-size: 1.75rem;
        }
        
        .futuristic-header p {
            font-size: 1rem;
        }

        .modern-card .card-body {
            padding: 1.25rem;
        }
    }
</style>

<!-- Content Header -->
<section class="futuristic-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Dashboard Overview</h2>
                <p>Welcome back, {{ Auth::user()->name }}! Here's your jewellery shop summary.</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container pb-4">
    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <!-- Today's Daybook -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="modern-card card-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-label">Today's Daybook</div>
                            <div class="stat-value">
                                {{ number_format($todayDaybookBalance, 2) }}
                            </div>
                            <div class="stat-details">
                                <span class="text-success">↑ {{ number_format($todayDaybookDebit, 2) }}</span> | 
                                <span class="text-danger">↓ {{ number_format($todayDaybookCredit, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon-container icon-primary">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="modern-card card-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-label">Today's SalesBook</div>
                            <div class="stat-value">
                                {{ number_format($todaySalesbookBalance, 2) }}
                            </div>
                            <div class="stat-details">
                                <span class="text-success">↑ {{ number_format($todaySalesDebit, 2) }}</span> | 
                                <span class="text-danger">↓ {{ number_format($todaySalesCredit, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon-container icon-success">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Bank Activity -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="modern-card card-info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-label">Today's Bank</div>
                            <div class="stat-value">
                                {{ number_format($todayBankDebit - $todayBankCredit, 2) }}
                            </div>
                            <div class="stat-details">
                                <span class="text-success">↑ {{ number_format($todayBankDebit, 2) }}</span> | 
                                <span class="text-danger">↓ {{ number_format($todayBankCredit, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon-container icon-info">
                                <i class="fas fa-university"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Expenses -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="modern-card card-danger h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-label">Today's Expenses</div>
                            <div class="stat-value">
                                {{ number_format($expensesToday, 2) }}
                            </div>
                            <div class="stat-details">
                                <span class="text-muted">MTD: {{ number_format($expensesMonth, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon-container icon-danger">
                                <i class="fas fa-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daybook Trend (Last 7 Days) -->
        <div class="col-lg-8 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h6><i class="fas fa-chart-line"></i> Daybook Trend (Last 7 Days)</h6>
                </div>
                <div class="chart-card-body">
                    <canvas id="daybookChart" height="140"></canvas>
                </div>
            </div>
        </div>

        <!-- Expenses Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h6><i class="fas fa-chart-pie"></i> Top Expenses (This Month)</h6>
                </div>
                <div class="chart-card-body">
                    <canvas id="expensesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Overview & Sales Trend -->
    <div class="row mb-4">
        <!-- Monthly Overview -->
        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h6><i class="fas fa-calendar-alt"></i> This Month Overview</h6>
                </div>
                <div class="chart-card-body">
                    <div class="overview-item">
                        <div class="overview-label">Daybook Balance</div>
                        <div class="overview-value">{{ number_format($monthDaybookBalance, 2) }}</div>
                        <div class="overview-progress">
                            <div class="overview-progress-bar" style="width: {{ $monthDaybookDebit > 0 ? ($monthDaybookBalance / $monthDaybookDebit * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="overview-item">
                        <div class="overview-label">Sales Revenue</div>
                        <div class="overview-value text-success">{{ number_format($monthSalesDebit, 2) }}</div>
                        <div class="overview-extra">Gold: {{ number_format($monthGoldWeight, 3) }}g</div>
                    </div>

                    <div class="overview-item">
                        <div class="overview-label">Bank Activity</div>
                        <div class="overview-value">{{ number_format($monthBankDebit - $monthBankCredit, 2) }}</div>
                    </div>

                    <div class="overview-item">
                        <div class="overview-label">Borrow/Lend</div>
                        <div class="overview-value">{{ number_format($monthBorrowLendDebit - $monthBorrowLendCredit, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Trend (Last 6 Months) -->
        <div class="col-lg-8 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h6><i class="fas fa-chart-bar"></i> Sales Trend (Last 6 Months)</h6>
                </div>
                <div class="chart-card-body">
                    <canvas id="salesTrendChart" height="211"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="table-card-header">
                    <h6><i class="fas fa-history"></i> Recent Transactions</h6>
                </div>
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-right">Amount</th>
                                <th>Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                <td>{{ $transaction->transaction_description }}</td>
                                <td>
                                    <span class="badge-modern {{ $transaction->debit_credit === 'debit' ? 'badge-success-modern' : 'badge-danger-modern' }}">
                                        {{ ucfirst($transaction->debit_credit) }}
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold">
                                    {{ number_format($transaction->transaction_amount, 2) }}
                                </td>
                                <td>{{ $transaction->creator->name ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No recent transactions</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Daybook Trend Chart
const daybookCtx = document.getElementById('daybookChart').getContext('2d');
new Chart(daybookCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($last7Days->pluck('date')) !!},
        datasets: [{
            label: 'Debit',
            data: {!! json_encode($last7Days->pluck('debit')) !!},
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6
        }, {
            label: 'Credit',
            data: {!! json_encode($last7Days->pluck('credit')) !!},
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '600'
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Expenses Chart
const expensesCtx = document.getElementById('expensesChart').getContext('2d');
new Chart(expensesCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($expensesByCategory->pluck('expense_code')) !!},
        datasets: [{
            data: {!! json_encode($expensesByCategory->pluck('total')) !!},
            backgroundColor: [
                'rgba(6, 81, 243, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(6, 182, 212, 0.8)',
                'rgba(251, 146, 60, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 12,
                    font: {
                        size: 11,
                        weight: '600'
                    }
                }
            }
        }
    }
});

// Sales Trend Chart
const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(salesTrendCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlySales->pluck('month')) !!},
        datasets: [{
            label: 'Sales Debit',
            data: {!! json_encode($monthlySales->pluck('debit')) !!},
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 2,
            borderRadius: 8
        }, {
            label: 'Sales Credit',
            data: {!! json_encode($monthlySales->pluck('credit')) !!},
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: 'rgb(239, 68, 68)',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '600'
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

@endsection