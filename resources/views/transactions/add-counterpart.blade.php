@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-3">Add Counterpart for Transaction #{{ $transaction->id }}</h5>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Transaction Amount</small>
                                <strong>{{ number_format($transaction->amount) }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Total Paid</small>
                                <strong>{{ number_format($transaction->getTotalCounterpartAmount()) }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Remaining</small>
                                <strong>{{ number_format($transaction->amount - $transaction->getTotalCounterpartAmount()) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('transactions.add-counterpart', $transaction->uuid) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="float_account_id" class="form-label">Float Account</label>
                            <select name="float_account_id" id="float_account_id" class="form-control" required>
                                <option value="">Select Float Account</option>
                                @foreach($floatAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                max="{{ $transaction->amount - $transaction->getTotalCounterpartAmount() }}"
                                value="{{ min($transaction->amount - $transaction->getTotalCounterpartAmount(), $transaction->amount) }}" 
                                required>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('transactions.show', $transaction->uuid) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Counterpart</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection