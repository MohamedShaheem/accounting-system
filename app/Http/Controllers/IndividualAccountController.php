<?php

namespace App\Http\Controllers;

use App\Models\IndividualAccount;
use App\Models\IndividualAccountTransaction;
use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndividualAccountController extends Controller
{
    // Overview of all individual accounts
    public function overview()
    {
        $accounts = IndividualAccount::withCount('transactions')
            ->orderBy('name', 'asc')
            ->get();

        return view('individual-account.overview', compact('accounts'));
    }

    // Store new account (AJAX)
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_no' => 'required|string|max:100|unique:individual_accounts,account_no',
            'current_balance' => 'nullable|numeric'
        ]);

        try {
            $account = IndividualAccount::create([
                'name' => $validated['name'],
                'account_no' => $validated['account_no'],
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
            $account = IndividualAccount::findOrFail($id);
            
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
        $account = IndividualAccount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_no' => 'required|string|max:100|unique:individual_accounts,account_no,' . $id,
            'current_balance' => 'nullable|numeric'
        ]);

        try {
            $account->update([
                'name' => $validated['name'],
                'account_no' => $validated['account_no'],
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
            $account = IndividualAccount::findOrFail($id);
            
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
        $query = IndividualAccountTransaction::with(['individualAccount', 'creator', 'editor'])
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

        if ($request->filled('individual_account_id')) {
            $query->where('individual_account_id', $request->individual_account_id);
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
                'account_name' => $transaction->individualAccount ? $transaction->individualAccount->name : '-',
                'account_no' => $transaction->individualAccount ? $transaction->individualAccount->account_no : '-',
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
        $accounts = IndividualAccount::orderBy('name', 'asc')->get();

        return view('individual-account.index', compact(
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
        $accounts = IndividualAccount::orderBy('name', 'asc')->get();
        return view('individual-account.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'individual_account_id' => 'required|exists:individual_accounts,id',
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0.01',
            'transaction_description' => 'nullable|string|max:1000',
            'remark' => 'nullable|string|max:500',
            'daybook_entry' => 'nullable|boolean'
        ]);

        DB::beginTransaction();

        try {
            // Get account
            $account = IndividualAccount::findOrFail($validated['individual_account_id']);

            // Fallback values
            $accountName = $account->name;

            $description = $accountName;

            $remark = !empty($validated['transaction_description'])
                ? $validated['transaction_description']
                : $accountName;

            $doubleEntry = $request->boolean('daybook_entry');

            // Create Individual Account Transaction
            $transaction = IndividualAccountTransaction::create([
                'individual_account_id' => $validated['individual_account_id'],
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $description,
                'remark' => $remark,
                'double_entry' => $doubleEntry,
                'created_by' => Auth::id()
            ]);

            // Update balance
            if ($validated['debit_credit'] == 'debit') {
                $account->current_balance += $validated['transaction_amount'];
            } else {
                $account->current_balance -= $validated['transaction_amount'];
            }

            $account->save();

            // Daybook entry
            if ($request->has('daybook_entry') && $request->daybook_entry == '1') {

                $daybookCreditDebit = $validated['debit_credit'] == 'debit'
                    ? 'credit'
                    : 'debit';

                Daybook::create([
                    'debit_credit' => $daybookCreditDebit,
                    'transaction_date' => $validated['transaction_date'],
                    'transaction_amount' => $validated['transaction_amount'],
                    'transaction_description' => $description,
                    'remark' => $remark,
                    'account_type' => 'individual_account',
                    'reference_id' => $transaction->id,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()
                ->route('individual-account.index')
                ->with(
                    'success',
                    'Transaction created successfully' .
                    ($request->has('daybook_entry')
                        ? ' (Day Book entry also created)'
                        : '')
                );

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
        $transaction = IndividualAccountTransaction::findOrFail($id);
        $accounts = IndividualAccount::orderBy('name', 'asc')->get();
        return view('individual-account.edit', compact('transaction', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $transaction = IndividualAccountTransaction::findOrFail($id);
        $account = $transaction->individualAccount;

        $validated = $request->validate([
            'individual_account_id' => 'required|exists:individual_accounts,id',
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0.01',
            'transaction_description' => 'nullable|string|max:1000',
            'remark' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            // Calculate net balance change
            $balanceChange = 0;

            // Undo old transaction
            $balanceChange += $transaction->debit_credit === 'debit' 
                ? -$transaction->transaction_amount 
                : $transaction->transaction_amount;

            // Apply new transaction
            $balanceChange += $validated['debit_credit'] === 'debit' 
                ? $validated['transaction_amount'] 
                : -$validated['transaction_amount'];

            // Update account balance in one step
            $account->current_balance += $balanceChange;
            $account->save();

            // Update the transaction
            $transaction->update([
                'individual_account_id' => $validated['individual_account_id'],
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' =>  $account->name,
                'remark' => $validated['transaction_description'] ?? $account->name . ' (Individual Acc)',
                'edited_by' => Auth::id()
            ]);

            // Update Daybook if double entry enabled
            if ($transaction->double_entry) {
                $daybook = Daybook::where('account_type', 'individual_account')
                    ->where('reference_id', $transaction->id)
                    ->first();

                if ($daybook) {
                    $daybookDebitCredit = $validated['debit_credit'] === 'debit' ? 'credit' : 'debit';

                    $daybook->update([
                        'debit_credit' => $daybookDebitCredit,
                        'transaction_date' => $validated['transaction_date'],
                        'transaction_amount' => $validated['transaction_amount'],
                        'transaction_description' => $account->name,
                        'remark' => $validated['transaction_description'] ?? $account->name . ' (Individual Acc)',
                        'edited_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('individual-account.index')
                ->with('success', 'Transaction updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $transaction = IndividualAccountTransaction::findOrFail($id);
            $account = $transaction->individualAccount;

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