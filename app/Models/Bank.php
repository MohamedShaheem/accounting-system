<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

        protected $fillable = [
        'bank_name',
        'account_no',
        'current_balance'
    ];

    public function bankTransfer()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
