<?php

namespace App\Models\Transaction;

use App\Models\Agency\FloatAccount;
use Illuminate\Database\Eloquent\Model;

class TransactionCounterpart extends Model
{
    protected $fillable = [
        'transaction_id',
        'float_account_id',
        'amount',
        'reference',
        'params'
    ];

    protected $casts = [
        'params' => 'array'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function floatAccount()
    {
        return $this->belongsTo(FloatAccount::class);
    }
}