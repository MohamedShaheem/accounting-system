<?php

namespace App\Http\Controllers;

use App\Models\CashBorrowLendAccount;
use App\Models\CashBorrowLendTransaction;
use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashBorrowLendController extends Controller
{
    // Overview of all cash borrow lend accounts
    public function overview()
    {
        $accounts = CashBorrowLendAccount::withCount('transactions')
            ->orderBy('name', 'asc')
            ->get();

        return view('cash-borrow-lend.overview', compact('accounts'));
    }

    // Store new account (AJAX)
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_balance' => 'nullable|numeric'
        ]);

        try {
            $account = CashBorrowLendAccount::create([
                'name' => $validated['name'],
                'current_balance' => $validated['current_balance'] ?? 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'account' => $account
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get account details (AJAX)
    public function getAccount($id)
    {
        try {
            $account = CashBorrowLendAccount::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'account' => $account
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }
    }

    // Update account (AJAX)
    public function updateAccount(Request $request, $id)
    {
        $account = CashBorrowLendAccount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_balance' => 'nullable|numeric'
        ]);

        try {
            $account->update([
                'name' => $validated['name'],
                'current_balance' => $validated['current_balance'] ?? $account->current_balance
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully',
                'account' => $account
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete account (AJAX)
    public function destroyAccount($id)
    {
        try {
            $account = CashBorrowLendAccount::findOrFail($id);
            
            // Check if account has transactions
            if ($account->transactions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account with existing transactions'
                ], 400);
            }

            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account'
            ], 500);
        }
    }

    // Transaction list with filters
    public function index(Request $request)
    {
        $query = CashBorrowLendTransaction::with(['cashBorrowLendAccount', 'creator', 'editor'])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('debit_credit')) {
            $query->where('debit_credit', $request->debit_credit);
        }

        if ($request->filled('cash_borrow_lend_account_id')) {
            $query->where('cash_borrow_lend_account_id', $request->cash_borrow_lend_account_id);
        }

        $transactions = $query->paginate(50);

        // Calculate records with running balance
        $records = [];
        $balance = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        $seq = 1;

        foreach ($transactions as $transaction) {
            $debitAmount = 0;
            $creditAmount = 0;

            if ($transaction->debit_credit === 'debit') {
                $debitAmount = $transaction->transaction_amount;
                $balance += $debitAmount;
                $totalDebit += $debitAmount;
            } else {
                $creditAmount = $transaction->transaction_amount;
                $balance -= $creditAmount;
                $totalCredit += $creditAmount;
            }

            $records[] = [
                'seq' => $seq++,
                'id' => $transaction->id,
                'account_name' => $transaction->cashBorrowLendAccount ? $transaction->cashBorrowLendAccount->name : '-',
                'transaction_date' => $transaction->transaction_date,
                'transaction_description' => $transaction->transaction_description,
                'remark' => $transaction->remark,
                'debit_amount' => $debitAmount,
                'credit_amount' => $creditAmount,
                'balance' => $balance
            ];
        }

        $finalBalance = $balance;

        // Get all accounts for filter dropdown
        $accounts = CashBorrowLendAccount::orderBy('name', 'asc')->get();

        return view('cash-borrow-lend.index', compact(
            'transactions',
            'records',
            'totalDebit',
            'totalCredit',
            'finalBalance',
            'accounts'
        ));
    }

    public function create()
    {
        $accounts = CashBorrowLendAccount::orderBy('name', 'asc')->get();
        return view('cash-borrow-lend.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cash_borrow_lend_account_id' => 'required|exists:cash_borrow_lend_accounts,id',
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0.01',
            'transaction_description' => 'nullable|string|max:1000',
            'remark' => 'nullable|string|max:500',
            'daybook_entry' => 'nullable|boolean'
        ]);

        DB::beginTransaction();
        
        try {
            $account = CashBorrowLendAccount::findOrFail($validated['cash_borrow_lend_account_id']);

            // Fallback description if null
            $description = $account->name . ' (CashBorrowLendAcc)';
            $remark = !empty($validated['transaction_description'])
                ? $validated['transaction_description']
                : $description;

            $doubleEntry = $request->boolean('daybook_entry');
            

            // Create Cash Borrow Lend Transaction
            $transaction = CashBorrowLendTransaction::create([
                'cash_borrow_lend_account_id' => $validated['cash_borrow_lend_account_id'],
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $description,
                'remark' => $remark,
                'created_by' => Auth::id(),
                'double_entry' => $doubleEntry,
            ]);

            // Update account current balance
            if ($validated['debit_credit'] == 'debit') {
                $account->current_balance += $validated['transaction_amount'];
            } else {
                $account->current_balance -= $validated['transaction_amount'];
            }
            $account->save();

            // Create Day Book Entry if toggle is on (opposite debit/credit)
            if ($request->has('daybook_entry') && $request->daybook_entry == '1') {
                $daybookCreditDebit = $validated['debit_credit'] == 'debit' ? 'credit' : 'debit';

                Daybook::create([
                    'debit_credit' => $daybookCreditDebit,
                    'transaction_date' => $validated['transaction_date'],
                    'transaction_amount' => $validated['transaction_amount'],
                    'transaction_description' => $description,
                    'remark' => $remark,
                    'account_type' => 'cash_borrow_lend_account',
                    'reference_id' => $transaction->id,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()
                ->route('cash-borrow-lend.index')
                ->with('success', 'Transaction created successfully' . 
                    ($request->has('daybook_entry') ? ' (Day Book entry also created)' : ''));
                    
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create transaction: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $transaction = CashBorrowLendTransaction::findOrFail($id);
        $accounts = CashBorrowLendAccount::orderBy('name', 'asc')->get();
        return view('cash-borrow-lend.edit', compact('transaction', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $transaction = CashBorrowLendTransaction::findOrFail($id);
        $oldAccount = $transaction->cashBorrowLendAccount;

        $validated = $request->validate([
            'cash_borrow_lend_account_id' => 'required|exists:cash_borrow_lend_accounts,id',
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0.01',
            'transaction_description' => 'nullable|string|max:1000',
            'remark' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Reverse old transaction
            $oldAccount->current_balance += $transaction->debit_credit === 'debit'
                ? -$transaction->transaction_amount
                : $transaction->transaction_amount;
            $oldAccount->save();

            // Update transaction
            $transaction->update([
                'cash_borrow_lend_account_id' => $validated['cash_borrow_lend_account_id'],
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $oldAccount->name . ' (CashBorrowLendAcc)',
                'remark' => $validated['transaction_description'] ?? $oldAccount->name . ' (CashBorrowLendAcc)',
                'edited_by' => Auth::id()
            ]);

            // Apply new transaction
            $newAccount = CashBorrowLendAccount::findOrFail($validated['cash_borrow_lend_account_id']);
            $newAccount->current_balance += $validated['debit_credit'] === 'debit'
                ? $validated['transaction_amount']
                : -$validated['transaction_amount'];
            $newAccount->save();

            // Update Daybook if double entry
            if ($transaction->double_entry) {
                $daybook = Daybook::where('account_type', 'cash_borrow_lend_account')
                    ->where('reference_id', $transaction->id)
                    ->first();
                if ($daybook) {
                    $daybook->update([
                        'debit_credit' => $validated['debit_credit'] === 'debit' ? 'credit' : 'debit',
                        'transaction_date' => $validated['transaction_date'],
                        'transaction_amount' => $validated['transaction_amount'],
                        'transaction_description' => $oldAccount->name . ' (CashBorrowLendAcc)',
                        'remark' => $validated['transaction_description'] ?? $oldAccount->name . ' (CashBorrowLendAcc)',
                        'edited_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('cash-borrow-lend.index')->with('success', 'Transaction updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $transaction = CashBorrowLendTransaction::findOrFail($id);
            $account = $transaction->cashBorrowLendAccount;

            // Reverse the transaction from account balance
            if ($transaction->debit_credit == 'debit') {
                $account->current_balance -= $transaction->transaction_amount;
            } else {
                $account->current_balance += $transaction->transaction_amount;
            }
            $account->save();

            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction'
            ], 500);
        }
    }
}