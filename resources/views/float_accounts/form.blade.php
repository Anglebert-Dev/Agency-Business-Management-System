@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>{{ $floatAccount->uuid ? 'Edit Float Account' : 'Add New Float Account' }}</h1>
            <a href="{{ route('float_accounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('float_accounts.edit', $floatAccount->uuid ?? null) }}" method="POST">
                    @csrf
                
                    <div class="mb-3">
                        <label for="name" class="form-label">Account Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                            value="{{ old('name', $floatAccount->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bank_id" class="form-label">Bank</label>
                        <select class="form-select @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id"
                            required>
                            <option value="">Select a bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id', $floatAccount->bank_id) == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('bank_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                            <option value="RWF" {{ old('currency', $floatAccount->currency) == 'RWF' ? 'selected' : '' }}>RWF</option>
                            <option value="USD" {{ old('currency', $floatAccount->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency', $floatAccount->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="account_number" class="form-label">Account Number</label>
                        <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                            id="account_number" name="account_number"
                            value="{{ old('account_number', $floatAccount->account_number) }}">
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="balance" class="form-label">Initial Balance</label>
                        <input type="number" step="0.01" class="form-control @error('balance') is-invalid @enderror"
                            id="balance" name="balance" value="{{ old('balance', $floatAccount->balance) }}">
                        @error('balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ $floatAccount->uuid ? 'Update Float Account' : 'Create Float Account' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection