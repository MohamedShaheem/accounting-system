<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesBook extends Model
{
    use HasFactory;

     protected $fillable = [
        'transaction_date',
        'invoice_type',
        'invoice_no',
        'name',
        'debit',
        'gold_weight',
        'silver_weight',
        'credit',
        'created_by',
        'edited_by',
        'sync'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'gold_weight' => 'decimal:3',
        'silver_weight' => 'decimal:3',
        'credit' => 'decimal:2'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
