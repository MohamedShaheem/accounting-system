<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndividualAccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'individual_account_id',
        'debit_credit',
        'transaction_date',
        'transaction_amount',
        'transaction_description',
        'remark',
        'double_entry',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'transaction_amount' => 'decimal:2'
    ];

    public function individualAccount()
    {
        return $this->belongsTo(IndividualAccount::class);
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