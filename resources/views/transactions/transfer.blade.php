@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Float Account Transfer</h1>
        <a href="{{ route('float_accounts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('transactions.transfer') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="source_account_id" class="form-label">From Account</label>
                    <select class="form-select @error('source_account_id') is-invalid @enderror" 
                            id="source_account_id" name="source_account_id" required>
                        <option value="">Select source account</option>
                        @foreach($floatAccounts as $account)
                            <option value="{{ $account->id }}" 
                                data-currency="{{ $account->currency }}"
                                data-balance="{{ $account->balance }}"
                                data-account-id="{{ $account->id }}">
                                {{ $account->name }} ({{ $account->currency }} {{ number_format($account->balance) }})
                            </option>
                        @endforeach
                    </select>
                    @error('source_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="destination_account_id" class="form-label">To Account</label>
                    <select class="form-select @error('destination_account_id') is-invalid @enderror" 
                            id="destination_account_id" name="destination_account_id" required>
                        <option value="">Select destination account</option>
                        @foreach($floatAccounts as $account)
                            <option value="{{ $account->id }}" 
                                data-currency="{{ $account->currency }}"
                                data-account-id="{{ $account->id }}">
                                {{ $account->name }} ({{ $account->currency }})
                            </option>
                        @endforeach
                    </select>
                    @error('destination_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                           id="amount" name="amount" required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="reference" class="form-label">Reference (Optional)</label>
                    <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                           id="reference" name="reference">
                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Transfer</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sourceSelect = document.getElementById('source_account_id');
        const destSelect = document.getElementById('destination_account_id');
        
        function validateCurrencies() {
            const sourceOption = sourceSelect.selectedOptions[0];
            const destOption = destSelect.selectedOptions[0];
            
            if (sourceOption && destOption) {
                if(sourceOption.dataset.accountId && destOption.dataset.accountId && sourceOption.dataset.accountId === destOption.dataset.accountId) {
                    alert('Source and destination accounts cannot be the same.');
                    destSelect.value = '';
                    return;
                }
                const sourceCurrency = sourceOption.dataset.currency;
                const destCurrency = destOption.dataset.currency;
                
                if (sourceCurrency != undefined && destCurrency != undefined && sourceCurrency !== destCurrency) {
                    alert('Currency mismatch! Transfers must be between accounts with the same currency.');
                    destSelect.value = '';
                }
            
            }
        }
        
        sourceSelect.addEventListener('change', validateCurrencies);
        destSelect.addEventListener('change', validateCurrencies);
    });
</script>
@endpush
@endsection