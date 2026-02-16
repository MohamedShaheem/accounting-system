<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaybookAdAcInvoice extends Model
{
    use HasFactory;

     protected $fillable = [
        'daybook_id',
        'invoice_no'
    ];

    public function daybook()
    {
        return $this->belongsTo(Daybook::class);
    }
}
