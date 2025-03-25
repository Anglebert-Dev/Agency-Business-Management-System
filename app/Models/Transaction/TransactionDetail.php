<?php

namespace App\Models\Transaction;

use App\Models\Agency\FloatAccount;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id', 'float_account_id', 'type', 
        'amount', 'reference', 'params'
    ];

    protected $casts = [
        'params' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function floatAccount()
    {
        return $this->belongsTo(FloatAccount::class);
    }
    
    // Validation rules
    public static function validationRules()
    {
        return [
            'transaction_id' => 'required|exists:transactions,id',
            'float_account_id' => 'required|exists:float_accounts,id',
            'type' => 'required|in:transaction,counterpart',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|unique:transaction_details',
            'params' => 'nullable|array',
            // 'params.customer_account_number' => 'required|string',
        ];
    }
    
    // Validate transaction detail data
    public static function validateData($data)
    {
        $validator = Validator::make($data, self::validationRules());
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
}
