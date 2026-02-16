<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daybook extends Model
{
    use HasFactory;

    protected $fillable = [
        'debit_credit',
        'transaction_date',
        'transaction_amount',
        'transaction_description',
        'remark',
        'created_by',
        'edited_by',
        'sync',
        'account_type',
        'reference_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'transaction_amount' => 'decimal:2'
    ];

    public function invoices()
    {
        return $this->hasMany(DaybookAdAcInvoice::class);
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
