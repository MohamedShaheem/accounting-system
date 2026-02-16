<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankTransactionController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->toDateString();
        $dateTo   = $request->date_to ?? now()->toDateString();

        $query = BankTransaction::with(['bank', 'creator', 'editor'])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        // Date filter (default today)
        $query->whereDate('transaction_date', '>=', $dateFrom)
            ->whereDate('transaction_date', '<=', $dateTo);

        if ($request->filled('debit_credit')) {
            $query->where('debit_credit', $request->debit_credit);
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        $transactions = $query->paginate(50)->withQueryString();


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
                'bank_name' => $transaction->bank ? $transaction->bank->bank_name : '-',
                'transaction_date' => $transaction->transaction_date,
                'transaction_description' => $transaction->transaction_description,
                'debit_amount' => $debitAmount,
                'credit_amount' => $creditAmount,
                'balance' => $balance
            ];
        }

        $finalBalance = $balance;

        // Get all banks for filter dropdown
        $banks = Bank::orderBy('bank_name', 'asc')->get();

        return view('bank-transaction.index', compact(
            'transactions',
            'records',
            'totalDebit',
            'totalCredit',
            'finalBalance',
            'banks'
        ));
    }

    public function create()
    {
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        return view('bank-transaction.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0.01',
            'transaction_description' => 'nullable|string|max:1000',
            'daybook_entry' => 'nullable|boolean',
            'daybook_remark' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        
        try {
            $bank = Bank::where('id', $validated['bank_id'])->lockForUpdate()->first();

            if (!$bank) {
                throw new \Exception('Bank not found');
            }

            if ($validated['debit_credit'] === 'credit') {
                $newBalance = $bank->current_balance - $validated['transaction_amount'];
            } else {
                $newBalance = $bank->current_balance + $validated['transaction_amount'];
            }

            if ($newBalance < 0) {
                throw new \Exception('Insufficient bank balance');
            }

            $bank->update([
                'current_balance' => $newBalance
            ]);
            
            $doubleEntry = $request->boolean('daybook_entry');


            // Create Bank Transaction
            $bankTransaction = BankTransaction::create([
                'bank_id' => $validated['bank_id'],
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $validated['transaction_description'],
                'created_by' => Auth::id(),
                'double_entry' => $doubleEntry,
            ]);
            

            // Create Day Book Entry if toggle is on
            if ($request->has('daybook_entry') && $request->daybook_entry == '1') {

                if($validated['debit_credit'] == 'debit'){
                    $daybookCreditDebit = 'credit';
                }elseif($validated['debit_credit'] == 'credit'){
                    $daybookCreditDebit = 'debit';
                }

                Daybook::create([
                    'debit_credit' => $daybookCreditDebit,
                    'transaction_date' => $validated['transaction_date'],
                    'transaction_amount' => $validated['transaction_amount'],
                    'transaction_description' => 'Bank Transaction',
                    'remark' => $validated['transaction_description'] ?? 'Bank Transaction',
                    'account_type' => 'bank_transaction',
                    'reference_id' => $bankTransaction->id,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()
                ->route('bank-transaction.index')
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
        $transaction = BankTransaction::findOrFail($id);
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        return view('bank-transaction.edit', compact('transaction', 'banks'));
    }

    public function update(Request $request, $id)
    {
        $transaction = BankTransaction::findOrFail($id);
        $bank = $transaction->bank;

        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0',
            'transaction_description' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            // Calculate net balance change
            $balanceChange = 0;

            // Undo old transaction from bank
            $balanceChange += $transaction->debit_credit === 'debit'
                ? -$transaction->transaction_amount
                : $transaction->transaction_amount;

            // Apply new transaction
            $balanceChange += $validated['debit_credit'] === 'debit'
                ? $validated['transaction_amount']
                : -$validated['transaction_amount'];

            // Update bank balance in one step
            $bank->current_balance += $balanceChange;

            if ($bank->current_balance < 0) {
                throw new \Exception('Insufficient bank balance');
            }

            $bank->save();

            // Update the transaction
            $transaction->update([
                'bank_id' => $validated['bank_id'],
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $validated['transaction_description'] ?? 'Bank Transaction',
                'edited_by' => Auth::id()
            ]);

            // Update Daybook if double entry enabled
            if ($transaction->double_entry) {
                $daybook = Daybook::where('account_type', 'bank_transaction')
                    ->where('reference_id', $transaction->id)
                    ->first();

                if ($daybook) {
                    $daybookDebitCredit = $validated['debit_credit'] === 'debit' ? 'credit' : 'debit';

                    $daybook->update([
                        'debit_credit' => $daybookDebitCredit,
                        'transaction_date' => $validated['transaction_date'],
                        'transaction_amount' => $validated['transaction_amount'],
                        'transaction_description' =>  'Bank Transaction',
                        'remark' => $validated['transaction_description'] ?? 'Bank Transaction',
                        'edited_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('bank-transaction.index')
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
            $transaction = BankTransaction::findOrFail($id);
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction'
            ], 500);
        }
    }
}