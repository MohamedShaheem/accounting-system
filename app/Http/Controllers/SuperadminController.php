<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransaction;
use App\Models\CashBorrowLendTransaction;
use App\Models\DailyExpense;
use App\Models\Daybook;
use App\Models\IndividualAccountTransaction;
use App\Models\SalesBook;
use App\Models\Bank;
use App\Models\IndividualAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperadminController extends Controller
{
    public function index()
    {
        // Date ranges
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // ============ DAYBOOK ANALYTICS ============
        $daybookToday = Daybook::whereDate('transaction_date', $today)->get();
        $daybookMonth = Daybook::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])->get();
        
        $todayDaybookDebit = $daybookToday->where('debit_credit', 'debit')->sum('transaction_amount');
        $todayDaybookCredit = $daybookToday->where('debit_credit', 'credit')->sum('transaction_amount');
        $todayDaybookBalance = $todayDaybookDebit - $todayDaybookCredit;
        
        $monthDaybookDebit = $daybookMonth->where('debit_credit', 'debit')->sum('transaction_amount');
        $monthDaybookCredit = $daybookMonth->where('debit_credit', 'credit')->sum('transaction_amount');
        $monthDaybookBalance = $monthDaybookDebit - $monthDaybookCredit;

        // ============ SALES ANALYTICS ============
        $salesToday = SalesBook::whereDate('transaction_date', $today)->get();
        $salesMonth = SalesBook::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])->get();
        
        $todaySalesDebit = $salesToday->sum('debit');
        $todaySalesCredit = $salesToday->sum('credit');
        $todayGoldWeight = $salesToday->sum('gold_weight');
        $todaySalesbookBalance = $todaySalesDebit - $todaySalesCredit;
        
        $monthSalesDebit = $salesMonth->sum('debit');
        $monthSalesCredit = $salesMonth->sum('credit');
        $monthGoldWeight = $salesMonth->sum('gold_weight');

        // ============ BANK TRANSACTIONS ============
        $bankTransactionsToday = BankTransaction::whereDate('transaction_date', $today)->get();
        $bankTransactionsMonth = BankTransaction::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])->get();
        
        $todayBankDebit = $bankTransactionsToday->where('debit_credit', 'debit')->sum('transaction_amount');
        $todayBankCredit = $bankTransactionsToday->where('debit_credit', 'credit')->sum('transaction_amount');
        
        $monthBankDebit = $bankTransactionsMonth->where('debit_credit', 'debit')->sum('transaction_amount');
        $monthBankCredit = $bankTransactionsMonth->where('debit_credit', 'credit')->sum('transaction_amount');

        // Bank Balances
        $bankBalances = Bank::select('bank_name', 'current_balance')->get();

        // ============ DAILY EXPENSES ============
        $expensesToday = DailyExpense::whereDate('expense_date', $today)->sum('expense_amount');
        $expensesMonth = DailyExpense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('expense_amount');
        
        // Expenses by category (this month)
        $expensesByCategory = DailyExpense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->join('daily_expense_codes', 'daily_expenses.expense_code_id', '=', 'daily_expense_codes.id')
            ->select('daily_expense_codes.expense_code', DB::raw('SUM(expense_amount) as total'))
            ->groupBy('daily_expense_codes.id', 'daily_expense_codes.expense_code')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ============ INDIVIDUAL ACCOUNTS ============
        $individualAccounts = IndividualAccount::select('name', 'current_balance')
            ->orderByDesc('current_balance')
            ->limit(10)
            ->get();

        // ============ CASH BORROW/LEND ============
        $borrowLendMonth = CashBorrowLendTransaction::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])->get();
        $monthBorrowLendDebit = $borrowLendMonth->where('debit_credit', 'debit')->sum('transaction_amount');
        $monthBorrowLendCredit = $borrowLendMonth->where('debit_credit', 'credit')->sum('transaction_amount');

        // ============ CHARTS DATA ============
        // Last 7 days daybook trend
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayData = Daybook::whereDate('transaction_date', $date)->get();
            
            $last7Days->push([
                'date' => $date->format('M d'),
                'debit' => $dayData->where('debit_credit', 'debit')->sum('transaction_amount'),
                'credit' => $dayData->where('debit_credit', 'credit')->sum('transaction_amount'),
            ]);
        }

        // Monthly sales trend (last 6 months)
        $monthlySales = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $salesData = SalesBook::whereBetween('transaction_date', [$monthStart, $monthEnd])->get();
            
            $monthlySales->push([
                'month' => $monthStart->format('M Y'),
                'debit' => $salesData->sum('debit'),
                'credit' => $salesData->sum('credit'),
                'gold_weight' => $salesData->sum('gold_weight'),
            ]);
        }

        // Recent transactions
        $recentTransactions = Daybook::with('creator')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('superadmin.index', compact(
            'todayDaybookDebit',
            'todayDaybookCredit',
            'todayDaybookBalance',
            'monthDaybookDebit',
            'monthDaybookCredit',
            'monthDaybookBalance',
            'todaySalesDebit',
            'todaySalesCredit',
            'todayGoldWeight',
            'monthSalesDebit',
            'monthSalesCredit',
            'monthGoldWeight',
            'todayBankDebit',
            'todayBankCredit',
            'monthBankDebit',
            'monthBankCredit',
            'bankBalances',
            'expensesToday',
            'expensesMonth',
            'expensesByCategory',
            'individualAccounts',
            'monthBorrowLendDebit',
            'monthBorrowLendCredit',
            'last7Days',
            'monthlySales',
            'recentTransactions',
            'todaySalesbookBalance'
        ));
    }
}