<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBorrowLendAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'current_balance'
    ];

    protected $casts = [
        'current_balance' => 'decimal:2'
    ];

    public function transactions()
    {
        return $this->hasMany(CashBorrowLendTransaction::class);
    }
}