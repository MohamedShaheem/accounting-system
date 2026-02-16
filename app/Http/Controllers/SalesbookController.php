<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Daybook;
use App\Models\SalesBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SalesbookController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 25;

        $dateFrom = $request->date_from ?? Carbon::today()->toDateString();
        $dateTo   = $request->date_to ?? Carbon::today()->toDateString();

        $query = SalesBook::query()
            ->whereDate('transaction_date', '>=', $dateFrom)
            ->whereDate('transaction_date', '<=', $dateTo);

        if ($request->filled('invoice_type') && $request->invoice_type !== 'all') {
            $query->where('invoice_type', $request->invoice_type);
        }

        $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        /* --------------------------------
        | GRAND TOTALS (FULL DATASET)
        * -------------------------------- */
        $totals = (clone $query)
            ->select('debit', 'credit', 'gold_weight', 'silver_weight')
            ->get();

        $totalDebit        = $totals->sum('debit');
        $totalCredit       = $totals->sum('credit');
        $totalGoldWeight   = $totals->sum('gold_weight');
        $totalSilverWeight = $totals->sum('silver_weight');


        /* --------------------------------
        | PAGINATED DATA
        * -------------------------------- */
        $salesbooks = $query->paginate($perPage)->withQueryString();

        return view('salesbook.index', compact(
            'salesbooks',
            'totalDebit',
            'totalCredit',
            'totalGoldWeight',
            'totalSilverWeight'
        ));

    }


    public function create()
    {
        return view('salesbook.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'transaction_date'        => 'required|date',
            'debit_credit'            => 'required|in:debit,credit',
            'invoice_type'            => 'required|in:sales,purchase',
            'invoice_no'              => 'nullable|string',
            'transaction_amount'      => 'required|numeric|min:0.01',
            'gold_weight'             => 'nullable|numeric|min:0',
            'name'                    => 'nullable|string',
            'daybook_entry'           => 'nullable|boolean',
            'daybook_remark'          => 'nullable|string|max:500',
            'transaction_description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Prepare debit / credit values
            $debit  = $validated['debit_credit'] === 'debit'
                ? $validated['transaction_amount']
                : 0.00;

            $credit = $validated['debit_credit'] === 'credit'
                ? $validated['transaction_amount']
                : 0.00;

            // Create SalesBook entry
            SalesBook::create([
                'transaction_date' => $validated['transaction_date'],
                'invoice_type'     => $validated['invoice_type'],
                'invoice_no'       => $validated['invoice_no'] ?? null,
                'name'             => $validated['name'] ?? null,
                'debit'            => $debit,
                'credit'           => $credit,
                'gold_weight'      => $validated['gold_weight'] ?? 0.000,
                'created_by'       => Auth::id()
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
                    'transaction_description' => $validated['transaction_description'],
                    'remark' => $request->daybook_remark ?? 'SalesBook Transaction Entry',
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()
                ->route('salesbook.index')
                ->with(
                    'success',
                    'Transaction created successfully'
                    . (!empty($validated['daybook_entry'])
                        ? ' (Day Book entry also created)'
                        : '')
                );

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create transaction: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $salesbook = SalesBook::findOrFail($id);
        return view('salesbook.edit', compact('salesbook'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'debit_credit' => 'required|in:debit,credit',
            'invoice_type' => 'required|in:sales,purchase',
            'invoice_no' => 'nullable|string',
            'transaction_amount' => 'required|numeric|min:0.01',
            'gold_weight' => 'nullable|numeric|min:0',
            'name' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $salesbook = SalesBook::findOrFail($id);

            // Prepare debit / credit values
            $debit = $validated['debit_credit'] === 'debit'
                ? $validated['transaction_amount']
                : 0.00;

            $credit = $validated['debit_credit'] === 'credit'
                ? $validated['transaction_amount']
                : 0.00;

            $salesbook->update([
                'transaction_date' => $validated['transaction_date'],
                'invoice_type' => $validated['invoice_type'],
                'invoice_no' => $validated['invoice_no'] ?? null,
                'name' => $validated['name'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'gold_weight' => $validated['gold_weight'] ?? 0.000,
                'edited_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()
                ->route('salesbook.index')
                ->with('success', 'Transaction updated successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $salesbook = SalesBook::findOrFail($id);
            $salesbook->delete();
            
            return response()->json(['success' => true, 'message' => 'Sales record deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete sales record: ' . $e->getMessage()], 500);
        }
    }

    public function transactions()
    {
        return view('salesbook.transactions');
    }
}