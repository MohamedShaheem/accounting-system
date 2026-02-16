<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'debit_credit',
        'transaction_date',
        'transaction_amount',
        'transaction_description',
        'created_by',
        'edited_by',
        'sync',
        'double_entry'
    ];


    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
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
