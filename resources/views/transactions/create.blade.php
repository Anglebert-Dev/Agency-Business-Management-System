@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Create New Transaction</h4>
            </div>
                <form method="POST" action="{{ route('transactions.create') }}" id="transaction-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Transaction Details</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Transaction Type -->
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Transaction Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <option value="deposit">Deposit</option>
                                            <option value="withdrawal">Withdrawal</option>
                                        </select>
                                    </div>

                                    <!-- Amount and Fee -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="amount" class="form-label">Amount <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount"
                                                    name="amount" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fee" class="form-label">Fee</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="fee"
                                                    name="fee" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reference Number -->
                                    <div class="mb-3">
                                        <label for="reference" class="form-label">Reference Number</label>
                                        <input type="text" class="form-control" id="reference" name="reference">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Customer Selection Type -->
                                    <div class="mb-3">
                                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="customer_option"
                                                id="customer_option_existing" value="existing" checked>
                                            <label class="form-check-label" for="customer_option_existing">
                                                Existing Customer
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="customer_option"
                                                id="customer_option_new" value="new">
                                            <label class="form-check-label" for="customer_option_new">
                                                New Customer
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Existing Customer Selection -->
                                    <div id="existing_customer_div">
                                        <div class="mb-3">
                                            <label for="customer_id" class="form-label">Select Customer <span
                                                    class="text-danger">*</span></label>
                                            <select name="customer_id" id="customer_id" class="form-select">
                                                <option value="">-- Select Customer --</option>
                                                @foreach($customers as $customer)
                                                                                            @php
                                                                                                $accountNumbers = $customer->params['account_numbers'] ?? [];
                                                                                            @endphp

                                                                                            @if(count($accountNumbers) > 0)
                                                                                                @foreach($accountNumbers as $account)
                                                                                                    <option value="{{ $customer->id }}" data-account="{{ $account['number'] }}"
                                                                                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                                                                        {{ $customer->name }}
                                                                                                        {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                                                                                                        - {{ $account['number'] }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            @else
                                                                                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                                                                    {{ $customer->name }}
                                                                                                    {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                                                                                                    - No Account
                                                                                                </option>
                                                                                            @endif
                                                @endforeach
                                            </select>
                                            <input type="hidden" id="selected_account_number" name="account_numbers">
                                        </div>
                                    </div>

                                    <!-- New Customer Details -->
                                    <div id="new_customer_div" style="display: none;">
                                        <div class="mb-3">
                                            <label for="customer_name" class="form-label">Customer Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="customer_name" name="customer_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_phone" class="form-label">Customer Phone <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="customer_phone"
                                                name="customer_phone">
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_email" class="form-label">Customer Email</label>
                                            <input type="email" class="form-control" id="customer_email"
                                                name="customer_email">
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_address" class="form-label">Customer Address</label>
                                            <input type="text" class="form-control" id="customer_address"
                                                name="customer_address">
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_account_number" class="form-label">Account Number</label>
                                            <input type="text" name="new_account_number" id="new_account_number"
                                                class="form-control" value="{{ old('new_account_number') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details for Transfer -->
                    <div id="transfer_details" class="card mb-4" style="display: none;">
                        <div class="card-header bg-light">
                            <h5>Bank Transfer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_bank_id" class="form-label">Customer Bank <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="customer_bank_id" name="customer_bank_id">
                                            <option value="">Select Bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="transfer_account_number" class="form-label">Account Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="transfer_account_number"
                                            name="transfer_account_number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Sources -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5>Transaction Sources</h5>
                            <button type="button" class="btn btn-sm btn-success" id="add-source">
                                <i class="fas fa-plus"></i> Add Source
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <small>Add one or more sources for this transaction. The total of all sources should match
                                    the transaction amount.</small>
                            </div>

                            <div id="sources-container">
                                <div class="source-entry card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="mb-2">
                                                    <label class="form-label">Float Account <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select source-account"
                                                        name="sources[0][float_account_id]" required>
                                                        <option value="">Select Account</option>
                                                        @foreach($floatAccounts as $account)
                                                            <option value="{{ $account->id }}"
                                                                data-balance="{{ $account->balance }}">
                                                                {{ $account->name }} ({{ $account->bank->name }}) - Balance:
                                                                {{ number_format($account->balance) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="mb-2">
                                                    <label class="form-label">Amount <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0.01"
                                                        class="form-control source-amount" name="sources[0][amount]"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm form-control remove-source" disabled>
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="mb-0">
                                                    <label class="form-label">Reference (Optional)</label>
                                                    <input type="text" class="form-control" name="sources[0][reference]">
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="mb-0">
                                                    <label class="form-label">Note (Optional)</label>
                                                    <input type="text" class="form-control" name="sources[0][note]">
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="mb-0">
                                                    <label class="form-label">Proof Document</label>
                                                    <input type="file" class="form-control" name="sources[0][proof]" accept=".pdf,.jpg,.jpeg,.png">
                                                    <small class="text-muted">Accepted formats: PDF, JPG, PNG (Max 5MB)</small>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                    <!-- Counterparts Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5>Counterparts</h5>
                            <button type="button" class="btn btn-sm btn-success" id="add-counterpart">
                                <i class="fas fa-plus"></i> Add Counterpart
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <small>Add one or more counterparts for this transaction. Counterparts represent additional
                                    accounts involved in the transaction.</small>
                            </div>

                            <div id="counterparts-container">
                                <div class="counterpart-entry card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="mb-2">
                                                    <label class="form-label">Float Account <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select counterpart-account"
                                                        name="counterparts[0][float_account_id]">
                                                        <option value="">Select Account</option>
                                                        @foreach($floatAccounts as $account)
                                                            <option value="{{ $account->id }}"
                                                                data-balance="{{ $account->balance }}">
                                                                {{ $account->name }} ({{ $account->bank->name }}) - Balance:
                                                                {{ number_format($account->balance) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="mb-2">
                                                    <label class="form-label">Amount <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" min="0.01"
                                                        class="form-control counterpart-amount"
                                                        name="counterparts[0][amount]">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-2">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm form-control remove-counterpart"
                                                        disabled>
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="mb-0">
                                                    <label class="form-label">Reference (Optional)</label>
                                                    <input type="text" class="form-control"
                                                        name="counterparts[0][reference]">
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="mb-0">
                                                    <label class="form-label">Note (Optional)</label>
                                                    <input type="text" class="form-control" name="counterparts[0][note]">
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="mb-0">
                                                    <label class="form-label">Proof Document</label>
                                                    <input type="file" class="form-control" name="counterparts[0][proof]" accept=".pdf,.jpg,.jpeg,.png">
                                                    <small class="text-muted">Accepted formats: PDF, JPG, PNG (Max 5MB)</small>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <strong>Total Sources (Float):</strong>
                                                <span id="total-sources">0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <strong>Total Counterparts (Cash/Other):</strong>
                                                <span id="total-counterparts">0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <strong>Transaction Amount:</strong>
                                                <span id="display-amount">0.00</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Difference:</strong>
                                                <span id="difference" class="text-danger">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-end">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submit-btn">Create Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Customer Option Toggle
            const customerOptionRadios = document.querySelectorAll('input[name="customer_option"]');
            customerOptionRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.value === 'existing') {
                        document.getElementById('existing_customer_div').style.display = 'block';
                        document.getElementById('new_customer_div').style.display = 'none';
                        // Make existing customer fields required
                        document.getElementById('customer_id').setAttribute('required', 'required');
                        // Make new customer fields not required
                        document.getElementById('customer_name').removeAttribute('required');
                        document.getElementById('customer_phone').removeAttribute('required');
                    } else {
                        document.getElementById('existing_customer_div').style.display = 'none';
                        document.getElementById('new_customer_div').style.display = 'block';
                        // Make existing customer fields not required
                        document.getElementById('customer_id').removeAttribute('required');
                        // Make new customer fields required
                        document.getElementById('customer_name').setAttribute('required', 'required');
                        document.getElementById('customer_phone').setAttribute('required', 'required');
                    }
                });
            });

            // Transaction Type Change
            const typeSelect = document.getElementById('type');
            typeSelect.addEventListener('change', function () {
                const transferDetails = document.getElementById('transfer_details');
                const customerBankId = document.getElementById('customer_bank_id');
                const transferAccountNumber = document.getElementById('transfer_account_number');

                if (this.value === 'transfer') {
                    transferDetails.style.display = 'block';
                    customerBankId.setAttribute('required', 'required');
                    transferAccountNumber.setAttribute('required', 'required');
                } else {
                    transferDetails.style.display = 'none';
                    customerBankId.removeAttribute('required');
                    transferAccountNumber.removeAttribute('required');
                }
            });

            // Sources Management
            let sourceCount = 1;

            document.getElementById('add-source').addEventListener('click', function () {
                const container = document.getElementById('sources-container');
                const newSource = document.querySelector('.source-entry').cloneNode(true);

                // Update input names and clear values
                const inputs = newSource.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[\d+\]/, `[${sourceCount}]`));
                        input.value = '';
                    }
                });

                // Enable remove button for all but the first source
                const removeBtn = newSource.querySelector('.remove-source');
                removeBtn.disabled = false;
                removeBtn.addEventListener('click', function () {
                    newSource.remove();
                    updateTotals();
                });

                // Add event listeners to the new source inputs
                const newAmountInput = newSource.querySelector('.source-amount');
                newAmountInput.addEventListener('input', updateTotals);

                container.appendChild(newSource);
                sourceCount++;

                // Enable/disable remove buttons based on source count
                updateRemoveButtons();
            });

            document.querySelector('.source-amount').addEventListener('input', updateTotals);

            // Handle existing remove buttons
            document.querySelectorAll('.remove-source').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (document.querySelectorAll('.source-entry').length > 1) {
                        btn.closest('.source-entry').remove();
                        updateTotals();
                    }
                });
            });

            // Counterparts Management
            let counterpartCount = 1;

            document.getElementById('add-counterpart').addEventListener('click', function () {
                const container = document.getElementById('counterparts-container');
                const newCounterpart = document.querySelector('.counterpart-entry').cloneNode(true);

                // Update input names and clear values
                const inputs = newCounterpart.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[\d+\]/, `[${counterpartCount}]`));
                        input.value = '';
                    }
                });

                // Enable remove button
                const removeBtn = newCounterpart.querySelector('.remove-counterpart');
                removeBtn.disabled = false;
                removeBtn.addEventListener('click', function () {
                    newCounterpart.remove();
                    updateTotals();
                });

                // Add event listeners to the new counterpart inputs
                const newAmountInput = newCounterpart.querySelector('.counterpart-amount');
                newAmountInput.addEventListener('input', updateTotals);

                container.appendChild(newCounterpart);
                counterpartCount++;
                updateRemoveButtons();
            });

            // Add input event listener to initial counterpart amount
            document.querySelector('.counterpart-amount').addEventListener('input', updateTotals);

            // Update the validation in JavaScript
            function updateTotals() {
                const transactionAmount = parseFloat(document.getElementById('amount').value) || 0;
                let totalSources = 0;
                let totalCounterparts = 0;

                // Sum up sources
                document.querySelectorAll('.source-amount').forEach(input => {
                    totalSources += parseFloat(input.value) || 0;
                });

                // Sum up counterparts
                document.querySelectorAll('.counterpart-amount').forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    if (value > 0) {
                        totalCounterparts += value;
                    }
                });

                // Update display
                document.getElementById('total-sources').textContent = totalSources.toFixed(2);
                document.getElementById('total-counterparts').textContent = totalCounterparts.toFixed(2);
                document.getElementById('display-amount').textContent = transactionAmount.toFixed(2);

                const differenceElement = document.getElementById('difference');
                // Check if counterparts are provided (total > 0)
                if (totalCounterparts > 0) {
                    // Compare sources with counterparts
                    const difference = totalSources - totalCounterparts;
                    differenceElement.textContent = difference.toFixed(2);
                    
                    if (Math.abs(difference) < 0.01) {
                        differenceElement.classList.remove('text-danger');
                        differenceElement.classList.add('text-success');
                    } else {
                        differenceElement.classList.remove('text-success');
                        differenceElement.classList.add('text-danger');
                    }
                } else {
                    // If no counterparts, compare with transaction amount
                    const difference = totalSources - transactionAmount;
                    differenceElement.textContent = difference.toFixed(2);
                    
                    if (Math.abs(difference) < 0.01) {
                        differenceElement.classList.remove('text-danger');
                        differenceElement.classList.add('text-success');
                    } else {
                        differenceElement.classList.remove('text-success');
                        differenceElement.classList.add('text-danger');
                    }
                }
            }

            // Update form validation
            document.getElementById('transaction-form').addEventListener('submit', function (e) {
                const transactionAmount = parseFloat(document.getElementById('amount').value) || 0;
                let totalSources = 0;
                let totalCounterparts = 0;

                document.querySelectorAll('.source-amount').forEach(input => {
                    totalSources += parseFloat(input.value) || 0;
                });

                document.querySelectorAll('.counterpart-amount').forEach(input => {
                    totalCounterparts += parseFloat(input.value) || 0;
                });

                if (totalCounterparts > 0) {
                    // If counterparts exist, they must match sources
                    const difference = Math.abs(totalSources - totalCounterparts);
                    if (difference > 0.01) {
                        e.preventDefault();
                        alert('When counterparts are provided, their total must equal the total of sources.');
                        return false;
                    }
                } else {
                    // If no counterparts, sources must match transaction amount
                    const difference = Math.abs(totalSources - transactionAmount);
                    if (difference > 0.01) {
                        e.preventDefault();
                        alert('The total of sources must equal the transaction amount when no counterparts are provided.');
                        return false;
                    }
                }
            });

            // ... rest of the existing code ...
        });
    </script>
@endpush