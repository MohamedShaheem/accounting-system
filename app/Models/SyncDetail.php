<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'synced_at',
        'synced_by',
    ];

        protected $casts = [
    'synced_at' => 'datetime',
];

    public function user()
    {
        return $this->belongsTo(User::class, 'synced_by');
    }



}
