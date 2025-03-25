@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767px) {
        .table-responsive {
            margin: 1rem 0;
        }
        .table td, .table th {
            min-width: 100px;
        }
        .table td:last-child {
            min-width: 150px;
        }
        .table td:first-child {
            min-width: 60px;
        }
    }
</style>
<div class="container-fluid mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h2 class="h3 mb-0">Transactions</h2>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Transaction
        </a>
    </div>
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-3">ID</th>
                            <th class="px-3">Customer</th>
                            <th class="px-3">Type</th>
                            <th class="px-3">Amount</th>
                            <th class="px-3">Status</th>
                            <th class="px-3">Date</th>
                            <th class="px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-3">{{ $transaction->id }}</td>
                            <td class="px-3">{{ optional($transaction->customer)->name ?? 'N/A' }}</td>
                            <td class="px-3">
                                <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'warning' : 'info') }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-3">{{ number_format($transaction->amount) }}</td>
                            <td class="px-3">
                                <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td class="px-3">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-3">
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('transactions.show', $transaction->uuid) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$transaction->isCounterpartComplete() && in_array($transaction->type, ['deposit', 'withdrawal']))
                                        <a href="{{ route('transactions.add-counterpart', $transaction->uuid) }}" 
                                           class="btn btn-sm btn-warning" 
                                           data-bs-toggle="tooltip" 
                                           title="Add Counterpart">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-3">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection

