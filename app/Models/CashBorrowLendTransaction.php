<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBorrowLendTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_borrow_lend_account_id',
        'debit_credit',
        'transaction_date',
        'transaction_amount',
        'transaction_description',
        'created_by',
        'edited_by',
        'double_entry',
        'remark'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'transaction_amount' => 'decimal:2'
    ];

    public function cashBorrowLendAccount()
    {
        return $this->belongsTo(CashBorrowLendAccount::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}