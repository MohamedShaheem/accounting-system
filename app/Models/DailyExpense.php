<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_date',
        'expense_code_id',
        'expense_description',
        'expense_amount',
        'created_by',
        'edited_by',
        'double_entry'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'expense_amount' => 'decimal:2',
    ];

    public function expenseCode()
    {
        return $this->belongsTo(DailyExpenseCode::class, 'expense_code_id');
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