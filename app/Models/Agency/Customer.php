<?php

namespace App\Models\Agency;

use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'address',
        'params',
        'insert_by',
        'update_by'
    ];

    protected $casts = [
        'params' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            $customer->uuid = Str::uuid();
            $customer->insert_by = Auth::id();
        });

        static::updating(function ($customer) {
            $customer->update_by = Auth::id();
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

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('insert_by', $userId);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    // Validation rules
    public static function validationRules($id = null)
    {
        return [
            'name' => 'required',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'phone' => 'nullable|unique:customers,phone,' . $id,
            'address' => 'nullable',
            'account_numbers' => 'nullable|array',
            'account_banks' => 'nullable|array',
        ];
    }

    // Validate and save customer data
    // Inside the _validateAndSave method
    public static function _validateAndSave($data, $customer = null)
    {
        $customer = $customer ?: new self();

        // Validate the data
        $validator = Validator::make($data, self::validationRules($customer->id ?? null));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        // Initialize or get existing params
        $params = $customer->params ?? [];

        // Process account numbers directly here
        if (isset($data['account_numbers']) && is_array($data['account_numbers'])) {
            $accountNumbers = [];

            foreach ($data['account_numbers'] as $index => $accountNumber) {
                if (!empty($accountNumber)) {
                    $bankId = $data['account_banks'][$index] ?? null;

                    // Check if this account number already exists to preserve added_at
                    $existingAccount = null;
                    if (isset($params['account_numbers'])) {
                        foreach ($params['account_numbers'] as $acc) {
                            if ($acc['number'] === $accountNumber) {
                                $existingAccount = $acc;
                                break;
                            }
                        }
                    }

                    $accountNumbers[] = [
                        'number' => $accountNumber,
                        'bank_id' => $bankId,
                        'added_at' => $existingAccount ? $existingAccount['added_at'] : now()->toDateTimeString()
                    ];
                }
            }

            $params['account_numbers'] = $accountNumbers;
        }

        // Fill and save the customer
        $customer->fill([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
            'params' => $params
        ]);

        $customer->save();

        return $customer;
    }

    // Delete a customer by UUID
    public static function _delete($uuid)
    {
        $customer = self::where('uuid', $uuid)->first();

        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        $customer->delete();

        return true;
    }


}