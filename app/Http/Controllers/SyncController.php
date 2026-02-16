<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Daybook;
use App\Models\DaybookAdAcInvoice;
use App\Models\SalesBook;
use App\Models\SyncDetail;
use Carbon\Carbon;
use Exception;

class SyncController extends Controller
{
    
    private $systemAUrl = 'http://127.0.0.1:8000/api/v1/sync/orders';
    
    /**
     * Show sync page
     */
    public function index()
    {
        $latestSyncs = SyncDetail::with('user')
            ->orderBy('synced_at', 'desc')
            ->limit(5)
            ->get();

        return view('sync.index', compact('latestSyncs'));
    }

    /**
     * Sync orders from System A
     */
    public function syncOrders(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date'
        ]);

        try {
            // Call System A API
            $response = Http::timeout(120)
            ->withHeaders([
                'X-Service-Token' => config('services.pos.sync_token'),
            ])
            ->post($this->systemAUrl, [
                'date' => $request->date,
                'end_date' => $request->end_date ?? $request->date,
            ]);


            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch data from Jewel Plaza POS System'
                ], 500);
            }

            $data = $response->json();

            if (!$data['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'POS System returned an error'
                ], 500);
            }

            // Process orders
            $ordersResult = $this->processOrders($data['data']['orders'], $request->date, $request->end_date);

            // Process purchases
            $purchasesResult = $this->processPurchases($data['data']['purchases'], $request->date, $request->end_date);

            // Process purchases
            $advancesResult = $this->processAdvances($data['data']['advances'], $request->date, $request->end_date);

            // Process creditpayments
            $mainCreditPaymentsResult = $this->processCreditPayments($data['data']['credit_payments'], $request->date, $request->end_date);

            SyncDetail::create([
                'synced_at' => now(),
                'synced_by' => auth()->id(),
            ]);

            // Combine both results
            $result = [
                'orders' => $ordersResult,
                'purchases' => $purchasesResult,
                'advances' => $advancesResult,
                'MainCreditPayments' => $mainCreditPaymentsResult
            ];

            // daybook data entry calling helper function
            $start = Carbon::parse($request->date);
            $end   = Carbon::parse($request->end_date ?? $request->date);

            while ($start->lte($end)) {
                $this->syncSalesBookToDaybook($start->toDateString());
                $start->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data synced successfully',
                'data' => $result
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process orders and insert into System B database
     */
    private function processOrders($orders, $startDate, $endDate = null)
    {
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'sales_by_date' => [],
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($orders as $orderData) {
                $order = $orderData['order'];

                // $total = (float) $order['total'];
                // $balance = (float) $order['balance'];
                 
                $orderDetails = $orderData['order_details'];
                $goldExchanges = $orderData['gold_exchanges'];
                $advanceUses = $orderData['advance_uses'];
                $payments = $orderData['payments'];
                $goldAdvanceUses = $orderData['gold_advance_uses'];
                $bankTransferAmount = floatval($order['bank_transfer']);

                $transactionDate = Carbon::parse($order['created_at'])->format('Y-m-d');
                $customerId = $order['customer_id'];
                $isCreditInvoice = $order['is_credit_invoice'];
                $invoiceNo = $order['invoice_no'];
                $customerName = $order['customer']['name'];
                $total = floatval($order['total']);
                $balance = floatval($order['balance']);

                // process sales
                if (!isset($stats['sales_by_date'][$transactionDate])) {
                    $stats['sales_by_date'][$transactionDate] = 0;
                }
                $stats['sales_by_date'][$transactionDate] += $total;

                if ($isCreditInvoice == 0) {
                    $this->processCashSales($transactionDate, $invoiceNo, $total, $goldExchanges, $advanceUses, $bankTransferAmount, $stats);
                } else {
                    // Credit Sales
                    $this->processCreditSales(
                        $transactionDate,
                        $invoiceNo,
                        $customerName,
                        $total,
                        $balance,
                        $goldExchanges,
                        $advanceUses,
                        $goldAdvanceUses,
                        $payments,
                        $stats
                    );
                }

                $stats['processed']++;
            }

            DB::commit();

            return $stats;

        } catch (Exception $e) {
            DB::rollBack();
            $stats['errors'][] = $e->getMessage();
            throw $e;
        }
    }

    // helper function to insert sales data to day book
    private function syncSalesBookToDaybook($date)
    {
        $totals = SalesBook::whereDate('transaction_date', $date)
            ->selectRaw('
                SUM(debit) as total_debit,
                SUM(credit) as total_credit
            ')
            ->first();

        $totalDebit  = $totals->total_debit ?? 0;
        $totalCredit = $totals->total_credit ?? 0;

        // Insert SALES (Debit)
        if ($totalDebit > 0) {
            Daybook::updateOrCreate(
                [
                    'transaction_date'        => $date,
                    'transaction_description' => 'Sales',
                    'debit_credit'            => 'debit',
                    'sync'                    => 1,
                ],
                [
                    'transaction_amount' => $totalDebit,
                    'remark'             => 'Sales',
                    'created_by' => auth()->id(),
                ]
            );
        }

        // Insert PURCHASE (Credit)
        if ($totalCredit > 0) {
            Daybook::updateOrCreate(
                [
                    'transaction_date'        => $date,
                    'transaction_description' => 'Purchase',
                    'debit_credit'            => 'credit',
                    'sync'                    => 1,
                ],
                [
                    'transaction_amount' => $totalCredit,
                    'remark'             => 'Purchase',
                    'created_by' => auth()->id(),
                ]
            );
        }
    }


    /**
     * Process cash sales
     */
    private function processCashSales($transactionDate, $invoiceNo, $total, $goldExchanges, $advanceUses, $bankTransferAmount, &$stats)
    {
        // 1. Calculate exchange totals
        $totalGoldWeight = 0;
        $totalSilverWeight = 0;
        $totalExchangeAmount = 0;

        foreach ($goldExchanges as $exchange) {
            $type   = strtolower($exchange['gold_rate_type'] ?? 'gold');
            $weight = (float) $exchange['gold_weight'];

            if ($type === 'silver') {
                $totalSilverWeight += $weight;
            } else { // default to gold
                $totalGoldWeight += $weight;
            }

            $totalExchangeAmount += (float) $exchange['gold_purchased_amount'];
        }


        // 2. Insert / update sales book
        $salesBook = SalesBook::updateOrCreate(
            [
                'transaction_date' => $transactionDate,
                'invoice_no' => $invoiceNo,
                'sync' => 1,

            ],
            [
                'invoice_type' => 'sales',
                'name' => null,
                'gold_weight'   => $totalGoldWeight,
                'silver_weight' => $totalSilverWeight,
                'debit' => $total,    
                'credit' => $totalExchangeAmount,
                'created_by' => auth()->id(),
            ]
        );

        if ($salesBook->wasRecentlyCreated) {
            $stats['inserted']++;
        } else {
            $stats['updated']++;
        }


        // Process bank_transfer
        if ($bankTransferAmount > 0) {

            $bankDaybook = Daybook::updateOrCreate(
                [
                    'transaction_date'        => $transactionDate,
                    'transaction_description' => 'Bank Transfer - ' . $invoiceNo,
                    'sync' => 1,

                ],
                [
                    'debit_credit'       => 'credit',
                    'transaction_amount' => $bankTransferAmount,
                    'remark'             => 'Bank Transfer',
                    'created_by' => auth()->id(),
                ]
            );

            $bankTransaction = BankTransaction::updateOrCreate(
                [
                    'transaction_date'        => $transactionDate,
                    'transaction_description' => 'Bank Transfer - ' . $invoiceNo,
                    'sync' => 1,
                ],
                [
                    'bank_id'            => 1,
                    'debit_credit'       => 'debit',
                    'transaction_amount' => $bankTransferAmount,
                    'created_by' => auth()->id(),
                ]
            );

            $stats[$bankDaybook->wasRecentlyCreated ? 'inserted' : 'updated']++;
            $stats[$bankTransaction->wasRecentlyCreated ? 'inserted' : 'updated']++;
        }


        // Process cash advance uses
        foreach ($advanceUses as $advanceUse) {
            $amount = floatval($advanceUse['amount']);
            if ($amount <= 0) {
                continue;
            }

            $advanceDaybook = Daybook::updateOrCreate(
                [
                    'transaction_date'        => $transactionDate,
                    'transaction_description' => 'AD - ' . $invoiceNo,
                    'remark'                  => 'Advance Used',
                    'sync' => 1,

                ],
                [
                    'debit_credit'            => 'credit',
                    'transaction_amount'      => $amount,
                    'created_by' => auth()->id(),
                ]
            );

            if ($advanceDaybook->wasRecentlyCreated) {
                $stats['inserted']++;
            } else {
                $stats['updated']++;
            }
            // DaybookAdAcInvoice::updateOrCreate(
            //     [
            //         'daybook_id' => $advanceDaybook->id,
            //         'invoice_no' => $invoiceNo
            //     ],
            //     [
            //         'daybook_id' => $advanceDaybook->id,
            //         'invoice_no' => $invoiceNo
            //     ]
            // );
        }

    }

    /**
     * Process credit sales (customer_id != 1)
     */
    private function processCreditSales(
        $transactionDate,
        $invoiceNo,
        $customerName,
        $total,
        $balance,
        $goldExchanges,
        $advanceUses,
        $goldAdvanceUses,
        $payments,
        &$stats
    ) {
        // Calculate exchange values
        $totalGoldWeight = 0;
        $totalSilverWeight = 0;
        $totalExchangeAmount = 0;

        foreach ($goldExchanges as $exchange) {
            $type   = strtolower($exchange['gold_rate_type'] ?? 'gold');
            $weight = (float) $exchange['gold_weight'];

            if ($type === 'silver') {
                $totalSilverWeight += $weight;
            } else { // default to gold
                $totalGoldWeight += $weight;
            }

            $totalExchangeAmount += (float) $exchange['gold_purchased_amount'];
        }

        // Always insert/update sales_book
        $salesBook = SalesBook::updateOrCreate(
            [
                'transaction_date' => $transactionDate,
                'invoice_no' => $invoiceNo,
                'sync' => 1,
            ],
            [
                'invoice_type' => 'sales',
                'name' => $customerName,
                'debit' => $total,
                'gold_weight'   => $totalGoldWeight,
                'silver_weight' => $totalSilverWeight,
                'credit' => $totalExchangeAmount,
                'created_by' => auth()->id(),
            ]
        );

        $salesBook->wasRecentlyCreated ? $stats['inserted']++ : $stats['updated']++;

        /**
         * Determine balance amount:
         * 1. Use $balance field
         * 2. If $balance <= 0, sum all payments with is_credit_payment = 1
         */
        $balanceAmount = floatval($balance);

        if ($balanceAmount <= 0) {
            $creditPayments = array_filter($payments, function ($p) {
                return isset($p['is_credit_payment']) && $p['is_credit_payment'] == 1;
            });

            $balanceAmount = array_sum(array_map(function ($p) {
                return floatval($p['amount']);
            }, $creditPayments));
        }

        $hasBalance = $balanceAmount > 0;

        // Insert balance into Daybook if exists
        if ($hasBalance) {
            $balanceDaybook = Daybook::firstOrCreate(
                [
                    'transaction_date' => $transactionDate,
                    'debit_credit' => 'credit',
                    'remark' => 'AC',
                    'transaction_description' => 'AC - ' . $invoiceNo,
                    'sync' => 1,
                ],
                [
                    'transaction_amount' => $balanceAmount,
                    'created_by' => auth()->id(),
                ]
            );

            $balanceDaybook->wasRecentlyCreated ? $stats['inserted']++ : $stats['updated']++;

            // Link invoice
            DaybookAdAcInvoice::firstOrCreate(
                [
                    'daybook_id' => $balanceDaybook->id,
                    'invoice_no' => $invoiceNo
                ]
            );
        }

        // Process cash advance uses
        foreach ($advanceUses as $advanceUse) {
            $amount = floatval($advanceUse['amount']);
            if ($amount <= 0) continue;

            $advanceDaybook = Daybook::updateOrCreate(
                [
                    'transaction_date' => $transactionDate,
                    'transaction_description' => 'AD - ' . $invoiceNo,
                    'remark' => 'Advance Used',
                    'sync' => 1,
                ],
                [
                    'debit_credit' => 'credit',
                    'transaction_amount' => $amount,
                    'created_by' => auth()->id(),
                ]
            );

            $advanceDaybook->wasRecentlyCreated ? $stats['inserted']++ : $stats['updated']++;
        }


        // Process gold advance uses
        // $totalGoldAdvance = 0;
        // foreach ($goldAdvanceUses as $goldAdvanceUse) {
        //     $totalGoldAdvance += floatval($goldAdvanceUse['amount']);
        // }

        // if ($totalGoldAdvance > 0) {
        //     $goldAdvanceDaybook = Daybook::firstOrCreate(
        //         [
        //             'transaction_date' => $transactionDate,
        //             'transaction_description' => 'AD',
        //             'debit_credit' => 'debit'
        //         ],
        //         [
        //             'transaction_amount' => 0,
        //             'remark' => 'Gold Advance Used'
        //         ]
        //     );

        //     // Update the amount
        //     $goldAdvanceDaybook->increment('transaction_amount', $totalGoldAdvance);

        //     if ($goldAdvanceDaybook->wasRecentlyCreated) {
        //         $stats['inserted']++;
        //     } else {
        //         $stats['updated']++;
        //     }

        //     // Insert invoice in daybook_ad_ac_invoices
        //     DaybookAdAcInvoice::firstOrCreate(
        //         [
        //             'daybook_id' => $goldAdvanceDaybook->id,
        //             'invoice_no' => $invoiceNo
        //         ]
        //     );
        // }
    }

    /**
     * Process purchases and insert individual records into sales_book
     */
    private function processPurchases($purchases, $startDate, $endDate = null)
    {
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'errors' => [],
            'total_amount' => 0, // To accumulate total purchase amount
        ];

        DB::beginTransaction();

        try {
            foreach ($purchases as $purchaseData) {
                // Skip pending purchases
                if ($purchaseData['status'] !== 'complete') {
                    continue;
                }

                $transactionDate = Carbon::parse($purchaseData['created_at'])->format('Y-m-d');
                $invoiceNo = $purchaseData['invoice_no'];
                $customerName = $purchaseData['customer_name'] ?? null;

                foreach ($purchaseData['details'] as $detail) {
                    $amount = floatval($detail['gold_purchased_amount']);
                    if ($amount <= 0) continue;

                    // Insert each purchase detail individually into sales_book
                    $salesBook = SalesBook::updateOrCreate(
                        [
                            'transaction_date' => $transactionDate,
                            'invoice_no' => $invoiceNo,
                            'gold_weight' => floatval($detail['gold_gram']),
                            'debit' => 0, // Always credit for purchase
                            'sync' => 1,

                        ],
                        [
                            'invoice_type' => 'purchase',
                            'name' => $customerName,
                            'credit' => $amount,
                            'created_by' => auth()->id(),

                        ]
                    );

                    if ($salesBook->wasRecentlyCreated) {
                        $stats['inserted']++;
                    } else {
                        $stats['updated']++;
                    }

                    $stats['processed']++;
                    $stats['total_amount'] += $amount; // accumulate total
                }
            }

            DB::commit();

            return $stats;

        } catch (Exception $e) {
            DB::rollBack();
            $stats['errors'][] = $e->getMessage();
            throw $e;
        }
    }

    private function processAdvances($advances, $startDate, $endDate = null)
    {
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'errors' => [],
            'total_amount' => 0, // To accumulate total purchase amount
        ];

        DB::beginTransaction();

        try {
            foreach ($advances as $advanceData) {

                $transactionDate = Carbon::parse($advanceData['updated_at'])->format('Y-m-d');
                $customerName = $advanceData['customer_name'] ?? null;

                foreach ($advanceData['details'] as $detail) {
                    $amount = floatval($detail['amount']);
                    if ($amount <= 0) continue;

                    // Insert each purchase detail individually into sales_book
                    $dayBook = Daybook::updateOrCreate(
                        [
                            'transaction_date' => $transactionDate,
                            'remark' => 'AD',
                            'transaction_description' => $customerName,
                            'sync' => 1,
                        ],
                        [
                            'debit_credit' => 'debit',
                            'transaction_amount' => $amount,
                            'created_by' => auth()->id(),
                            
                        ]
                    );
                }
            }

            DB::commit();

            return $stats;

        } catch (Exception $e) {
            DB::rollBack();
            $stats['errors'][] = $e->getMessage();
            throw $e;
        }
    }


    private function processCreditPayments($MainCreditPayments, $startDate, $endDate = null)
    {
        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'errors' => [],
            'total_amount' => 0,
        ];

        DB::beginTransaction();

        try {
            foreach ($MainCreditPayments as $payment) {
                $transactionDate = Carbon::parse($payment['created_at'])->format('Y-m-d');
                $invoiceNo = $payment['invoice_no'];
                $amount = floatval($payment['amount']);
                $paymentMethod = strtolower($payment['payment_method'] ?? '');

                if ($amount <= 0) continue;

                // Insert into Daybook
                $creditPaymentDaybook = Daybook::firstOrCreate(
                    [
                        'transaction_date' => $transactionDate,
                        'transaction_description' => 'AC - ' . $invoiceNo,
                        'remark' => 'AC',
                        'sync' => 1,
                    ],
                    [
                        'debit_credit' => 'debit',
                        'transaction_amount' => $amount,
                        'created_by' => auth()->id(),
                    ]
                );

                $creditPaymentDaybook->wasRecentlyCreated ? $stats['inserted']++ : $stats['updated']++;

                // If bank transfer, also insert a BankTransaction and update Daybook as credit
                if ($paymentMethod === 'bank_transfer') {
                    $bankDaybook = Daybook::updateOrCreate(
                        [
                            'transaction_date'        => $transactionDate,
                            'transaction_description' => 'Bank Transfer AC- ' . $invoiceNo,
                            'sync' => 1,
                        ],
                        [
                            'debit_credit'       => 'credit',
                            'transaction_amount' => $amount,
                            'remark'             => 'Bank Transfer',
                            'created_by'         => auth()->id(),
                        ]
                    );

                    $bankTransaction = BankTransaction::updateOrCreate(
                        [
                            'transaction_date'        => $transactionDate,
                            'transaction_description' => 'Bank Transfer AC- ' . $invoiceNo,
                            'sync' => 1,
                        ],
                        [
                            'bank_id'            => 1,
                            'debit_credit'       => 'debit',
                            'transaction_amount' => $amount,
                            'created_by'         => auth()->id(),
                        ]
                    );

                    $stats[$bankDaybook->wasRecentlyCreated ? 'inserted' : 'updated']++;
                    $stats[$bankTransaction->wasRecentlyCreated ? 'inserted' : 'updated']++;
                }

                $stats['processed']++;
                $stats['total_amount'] += $amount;
            }

            DB::commit();
            return $stats;

        } catch (Exception $e) {
            DB::rollBack();
            $stats['errors'][] = $e->getMessage();
            throw $e;
        }
    }

}
