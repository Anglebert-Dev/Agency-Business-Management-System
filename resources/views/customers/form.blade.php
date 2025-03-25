@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h2 class="h3 mb-0">{{ $customer->exists ? 'Edit Customer' : 'Add New Customer' }}</h2>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Back</span>
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('customers.edit', $customer->uuid ?? null) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Customer Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                    value="{{ old('name', $customer->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                                    value="{{ old('phone', $customer->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email', $customer->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address"
                            rows="2">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card bg-light border mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Bank Accounts</h5>
                        </div>
                        <div class="card-body">
                            <div id="account-numbers-container">
                                @php 
                                    $accountNumbers = $customer->params['account_numbers'] ?? [];
                                @endphp

                                @if(count($accountNumbers) > 0)
                                    @foreach($accountNumbers as $account)
                                        <div class="row account-number-row g-2 mb-2">
                                            <div class="col-sm-5">
                                                <input type="text" class="form-control" name="account_numbers[]"
                                                    placeholder="Account Number" value="{{ $account['number'] }}">
                                            </div>
                                            <div class="col-sm-5">
                                                <select class="form-select" name="account_banks[]">
                                                    <option value="">Select Bank</option>
                                                    @foreach(\App\Models\Agency\Bank::all() as $bank)
                                                        <option value="{{ $bank->id }}" {{ $account['bank_id'] == $bank->id ? 'selected' : '' }}>
                                                            {{ $bank->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="button" class="btn btn-danger w-100 remove-account-number">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row account-number-row g-2 mb-2">
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="account_numbers[]"
                                                placeholder="Account Number">
                                        </div>
                                        <div class="col-sm-5">
                                            <select class="form-select" name="account_banks[]">
                                                <option value="">Select Bank</option>
                                                @foreach(\App\Models\Agency\Bank::all() as $bank)
                                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="button" class="btn btn-danger w-100 remove-account-number" style="display: none;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="add-account-number">
                                <i class="fas fa-plus"></i> Add Account
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            {{ $customer->exists ? 'Update Customer' : 'Create Customer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('account-numbers-container');
                const addButton = document.getElementById('add-account-number');

                addButton.addEventListener('click', function () {
                    const accountRow = document.querySelector('.account-number-row').cloneNode(true);

                    // Clear values
                    accountRow.querySelector('input[name="account_numbers[]"]').value = '';
                    accountRow.querySelector('select[name="account_banks[]"]').selectedIndex = 0;

                    // Show remove button
                    accountRow.querySelector('.remove-account-number').style.display = 'block';

                    // Add remove event
                    accountRow.querySelector('.remove-account-number').addEventListener('click', function () {
                        container.removeChild(accountRow);
                    });

                    container.appendChild(accountRow);
                });

                // Handle existing remove buttons
                document.querySelectorAll('.remove-account-number').forEach(button => {
                    button.addEventListener('click', function () {
                        container.removeChild(button.closest('.account-number-row'));
                    });
                });
            });
        </script>
    @endpush
@endsection