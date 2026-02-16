<?php

namespace App\Http\Controllers;

use App\Models\DailyExpense;
use App\Models\DailyExpenseCode;
use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyExpenseController extends Controller
{
    public function daily_expense_code()
    {
        $codes = DailyExpenseCode::latest()->get();
        return view('daily-expense.expense-code.index', compact('codes'));
    }

    public function store_daily_expense_code(Request $request)
    {
        $request->validate([
            'expense_code' => 'required|string|max:255|unique:daily_expense_codes,expense_code',
        ]);

        DailyExpenseCode::create([
            'expense_code' => $request->expense_code,
        ]);

        return redirect()
            ->route('daily-expense-code.index')
            ->with('success', 'Expense code created successfully');
    }

    public function update_daily_expense_code(Request $request, $id)
    {
        $request->validate([
            'expense_code' => 'required|string|max:255|unique:daily_expense_codes,expense_code,' . $id,
        ]);

        $code = DailyExpenseCode::findOrFail($id);
        $code->update([
            'expense_code' => $request->expense_code,
        ]);

        return redirect()
            ->route('daily-expense-code.index')
            ->with('success', 'Expense code updated successfully');
    }

    public function index(Request $request)
    {
        $query = DailyExpense::with(['expenseCode', 'creator']);

        $dateFrom = $request->date_from ?? now()->toDateString();
        $dateTo   = $request->date_to ?? now()->toDateString();

        $query->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo);

        if ($request->filled('expense_code_id') && $request->expense_code_id != 'all') {
            $query->where('expense_code_id', $request->expense_code_id);
        }

        $totalAmount = (clone $query)->sum('expense_amount');

        $expenses = $query
            ->latest('expense_date')
            ->paginate(50)
            ->withQueryString();

        $expenseCodes = DailyExpenseCode::orderBy('expense_code')->get();

        return view('daily-expense.index', compact(
            'expenses',
            'totalAmount',
            'expenseCodes'
        ));
    }

    public function create()
    {
        $expenseCodes = DailyExpenseCode::orderBy('expense_code')->get();
        return view('daily-expense.create', compact('expenseCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date'        => 'required|date',
            'expense_code_id'     => 'required|exists:daily_expense_codes,id',
            'expense_description' => 'nullable|string|max:1000',
            'expense_amount'      => 'required|numeric|min:0.01',
            'daybook_entry'       => 'nullable|boolean',
            'daybook_remark'      => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Get expense code text
            $expenseCode = DailyExpenseCode::findOrFail($validated['expense_code_id']);

            // Fallback values
            $description = $validated['expense_description'] ?: $expenseCode->expense_code;

            $doubleEntry = $request->boolean('daybook_entry');

            // Create Daily Expense entry
            $transaction = DailyExpense::create([
                'expense_date'        => $validated['expense_date'],
                'expense_code_id'     => $validated['expense_code_id'],
                'expense_description' => $description,
                'expense_amount'      => $validated['expense_amount'],
                'double_entry'        => $doubleEntry,
                'created_by'          => Auth::id()
            ]);

            // Daybook entry
            if ($request->has('daybook_entry') && $request->daybook_entry == '1') {

                $remark = $request->expense_description ?: $expenseCode->expense_code;

                Daybook::create([
                    'debit_credit'            => 'credit',
                    'transaction_date'        => $validated['expense_date'],
                    'transaction_amount'      => $validated['expense_amount'],
                    'transaction_description' => $expenseCode->expense_code,
                    'remark'                  => $remark,
                    'account_type'            => 'daily_expense',
                    'reference_id'            => $transaction->id,
                    'created_by'              => Auth::id()
                ]);
            }

            DB::commit();


            return redirect()
                ->route('daily-expense.index')
                ->with(
                    'success',
                    'Expense created successfully'
                    . (!empty($validated['daybook_entry'])
                        ? ' (Day Book entry also created)'
                        : '')
                );

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create expense: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $expense = DailyExpense::findOrFail($id);
        $expenseCodes = DailyExpenseCode::orderBy('expense_code')->get();
        return view('daily-expense.edit', compact('expense', 'expenseCodes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'expense_date'        => 'required|date',
            'expense_code_id'     => 'required|exists:daily_expense_codes,id',
            'expense_description' => 'nullable|string|max:1000',
            'expense_amount'      => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $expense = DailyExpense::findOrFail($id);
            $expenseCode = DailyExpenseCode::findOrFail($validated['expense_code_id']);

            // Fallback description
            $description = $validated['expense_description'] ?: $expenseCode->expense_code;

            // Update Daily Expense
            $expense->update([
                'expense_date'        => $validated['expense_date'],
                'expense_code_id'     => $validated['expense_code_id'],
                'expense_description' => $description,
                'expense_amount'      => $validated['expense_amount'],
                'edited_by'           => Auth::id()
            ]);

            // Update Daybook if double entry is enabled
            if ($expense->double_entry) {
                $daybook = Daybook::where('account_type', 'daily_expense')
                    ->where('reference_id', $expense->id)
                    ->first();

                if ($daybook) {
                    $daybook->update([
                        'debit_credit' => 'credit',
                        'transaction_date' => $validated['expense_date'],
                        'transaction_amount' => $validated['expense_amount'],
                        'transaction_description' => $description,
                        'remark' => $request->daybook_remark ?? $expenseCode->expense_code,
                        'edited_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('daily-expense.index')
                ->with('success', 'Expense updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $expense = DailyExpense::findOrFail($id);
            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense'
            ], 500);
        }
    }
}