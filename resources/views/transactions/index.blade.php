@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Transactions</h2>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Transaction
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ optional($transaction->customer)->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'warning' : 'info') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td>{{ number_format($transaction->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $transaction->uuid) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$transaction->isCounterpartComplete() && in_array($transaction->type, ['deposit', 'withdrawal']))
                                    <a href="{{ route('transactions.add-counterpart', $transaction->uuid) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-plus"></i> Add Counterpart
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection