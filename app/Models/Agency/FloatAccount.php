<?php

namespace App\Models\Agency;

use App\Models\Transaction\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class FloatAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'bank_id', 'name', 'account_number', 'balance','currency',
        'insert_by', 'update_by'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($account) {
            $account->uuid = Str::uuid();
            $account->insert_by = Auth::id();
        });
        
        static::updating(function ($account) {
            $account->update_by = Auth::id();
        });

        // Filter by ownership
        static::addGlobalScope('owned', function ($query) {
            if (Auth::check()) {
                $query->where('insert_by', Auth::id());
            }
        });
    }

    // Add relationship to User
    public function creator()
    {
        return $this->belongsTo(User::class, 'insert_by');
    }

    // Add scope for user's float accounts
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('insert_by', $userId);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
    
    // Validation rules
    public static function validationRules($id = null)
    {
        return [
            'bank_id' => 'required|exists:banks,id',
            'name' => 'required|string|max:255|unique:float_accounts,name,' . $id,
            'account_number' => 'nullable|string|max:50',
            'currency' => 'required|string|max:3',
            'balance' => 'nullable|numeric|min:0',
        ];
    }


      // Check if account has sufficient balance
      public function hasSufficientBalance($amount, $currency = null)
      {
          if ($currency && $this->currency !== $currency) {
              throw new \Exception("Currency mismatch. Account is in {$this->currency}, but transaction is in {$currency}");
          }
          return $this->balance >= $amount;
      }
    

    // Validate and save float account data (matches your format)
    public static function _validateAndSave($data, $floatAccount = null)
    {
        $floatAccount = $floatAccount ?: new self();

        // Validate the data using existing validationRules function
        $validator = Validator::make($data, self::validationRules($floatAccount->id ?? null));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        // Fill and save the float account
        $floatAccount->fill($validatedData);
        $floatAccount->save();

        return $floatAccount;
    }

    // Delete a float account (matches your format)
    public static function _delete($uuid)
    {
        $floatAccount = self::where('uuid', $uuid)->first();

        if (!$floatAccount) {
            throw new \Exception('Float account not found');
        }

        // Check if the account has any transactions
        if ($floatAccount->transactionDetails()->exists()) {
            throw new \Exception('Cannot delete float account with associated transactions');
        }

        // Soft delete the account
        $floatAccount->delete();

        return true;
    }
}