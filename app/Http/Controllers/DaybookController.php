<?php

namespace App\Http\Controllers;

use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DaybookController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 25;

        /* -------------------------------
        | Date Handling
        * ------------------------------- */
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        /* -------------------------------
        | Opening Balance (before range)
        * ------------------------------- */
        $openingBalance = Daybook::where('transaction_date', '<', $dateFrom)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN debit_credit='debit' THEN transaction_amount ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN debit_credit='credit' THEN transaction_amount ELSE 0 END),0) as balance
            ")
            ->value('balance') ?? 0;

        /* -------------------------------
        | Base Query (Filtered Period)
        * ------------------------------- */
        $query = Daybook::with(['invoices:id,daybook_id,invoice_no'])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($request->filled('transaction_type') && $request->transaction_type !== 'all') {
            $query->where('debit_credit', $request->transaction_type);
        }

        $query->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        /* -------------------------------
        | Totals (Selected Period Only)
        * ------------------------------- */
        // Build a separate query for totals to avoid ORDER BY issues
        $totalsQuery = Daybook::whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($request->filled('transaction_type') && $request->transaction_type !== 'all') {
            $totalsQuery->where('debit_credit', $request->transaction_type);
        }

        $totals = $totalsQuery
            ->selectRaw("
                COALESCE(SUM(CASE WHEN debit_credit='debit' THEN transaction_amount END),0) AS total_debit,
                COALESCE(SUM(CASE WHEN debit_credit='credit' THEN transaction_amount END),0) AS total_credit
            ")
            ->first();

        $totalDebit = $totals->total_debit ?? 0;
        $totalCredit = $totals->total_credit ?? 0;

        $finalBalance = $openingBalance + ($totalDebit - $totalCredit);

        /* -------------------------------
        | Pagination
        * ------------------------------- */
        $daybooks = $query->paginate($perPage)->withQueryString();

        /* -------------------------------
        | Running Balance
        * ------------------------------- */
        $balance = $openingBalance;
        $records = [];

        // Opening Row
        $records[] = [
            'seq' => '-',
            'date' => $dateFrom->toDateString(),
            'transaction' => 'Opening Balance',
            'invoice_no' => '-',
            'remark' => '-',
            'debit_amount' => 0,
            'credit_amount' => 0,
            'balance' => $balance,
            'id' => null,
        ];

        foreach ($daybooks as $index => $daybook) {
            $debit = $daybook->debit_credit === 'debit' ? $daybook->transaction_amount : 0;
            $credit = $daybook->debit_credit === 'credit' ? $daybook->transaction_amount : 0;

            $balance += $debit;
            $balance -= $credit;

            $records[] = [
                'seq' => $daybooks->firstItem() + $index,
                'date' => $daybook->transaction_date,
                'transaction' => $daybook->transaction_description,
                'invoice_no' => $daybook->invoices->pluck('invoice_no')->implode(', '),
                'remark' => $daybook->remark,
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'balance' => $balance,
                'id' => $daybook->id,
            ];
        }

        return view('daybook.index', compact(
            'records',
            'daybooks',
            'totalDebit',
            'totalCredit',
            'finalBalance'
        ));
    }

    public function create()
    {
        return view('daybook.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0',
            'transaction_description' => 'required|string|max:255',
            'remark' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $daybook = Daybook::create([
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $validated['transaction_description'],
                'remark' => $validated['remark'],
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('daybook.index')->with('success', 'Transaction added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add transaction: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $daybook = Daybook::with('invoices')->findOrFail($id);
        return view('daybook.edit', compact('daybook'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'debit_credit' => 'required|in:debit,credit',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric|min:0',
            'transaction_description' => 'required|string|max:255',
            'remark' => 'nullable|string',
            'invoice_numbers' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            $daybook = Daybook::findOrFail($id);
            
            $daybook->update([
                'debit_credit' => $validated['debit_credit'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_amount' => $validated['transaction_amount'],
                'transaction_description' => $validated['transaction_description'],
                'remark' => $validated['remark'],
                'edited_by' => auth()->id(),
            ]);

            // Delete existing invoices and create new ones
            $daybook->invoices()->delete();
            
            if (!empty($validated['invoice_numbers'])) {
                $invoiceNumbers = array_map('trim', explode(',', $validated['invoice_numbers']));
                
                foreach ($invoiceNumbers as $invoiceNo) {
                    if (!empty($invoiceNo)) {
                        $daybook->invoices()->create([
                            'invoice_no' => $invoiceNo
                        ]);
                    }
                }
            }

            DB::commit();
            
            return redirect()
                ->route('daybook.index')
                ->with('success', 'Transaction updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $daybook = Daybook::findOrFail($id);
            $daybook->invoices()->delete();
            $daybook->delete();
            
            return response()->json(['success' => true, 'message' => 'Transaction deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete transaction: ' . $e->getMessage()], 500);
        }
    }
}