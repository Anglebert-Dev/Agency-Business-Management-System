<?php

namespace App\Models\Agency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'short_name',
        'logo',
        'status',
        'insert_by',
        'update_by'
    ];

    // Add relationship to User
    public function creator()
    {
        return $this->belongsTo(User::class, 'insert_by');
    }

    // Add scope for user's banks
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('insert_by', $userId);
    }

    // Add scope for active banks
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Modify boot method to ensure ownership
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bank) {
            $bank->uuid = Str::uuid();
            $bank->insert_by = Auth::id();
        });

        static::updating(function ($bank) {
            $bank->update_by = Auth::id();
        });

        // Filter by ownership
        static::addGlobalScope('owned', function ($query) {
            if (Auth::check()) {
                $query->where('insert_by', Auth::id());
            }
        });
    }

    public function floatAccounts()
    {
        return $this->hasMany(FloatAccount::class);
    }

    // Validation rules
    public static function validationRules($id = null)
    {
        return [
            'name' => 'required|string|max:255|unique:banks,name,' . $id,
            'short_name' => 'nullable|string|max:50',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Changed to handle file upload
            'status' => 'nullable|in:active,inactive',
        ];
    }

    // Validate bank data
    public static function _validateAndSave($data, $bank = null)
    {
        $bank = $bank ?: new self();

        // Validate the data using existing validationRules function
        $validator = Validator::make($data, self::validationRules($bank->id ?? null));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        // Handle logo upload
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            if ($bank->exists && $bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }
            $validatedData['logo'] = $data['logo']->store('bank_logos', 'public');
        }

        // Fill and save the bank
        $bank->fill($validatedData);
        $bank->save();

        return $bank;
    }


}