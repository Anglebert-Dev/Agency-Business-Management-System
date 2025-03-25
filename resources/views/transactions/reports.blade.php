@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Transaction Reports</h1>
            <form class="d-flex gap-2">
                <div class="d-flex align-items-center">
                    <label for="start_date" class="me-2">From:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}"
                        max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="d-flex align-items-center">
                    <label for="end_date" class="me-2">To:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}"
                        max="{{ now()->format('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <div class="mb-3">
                    <a target="_blank"
                        href="{{ route('transactions.export-report', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="btn btn-success">
                        <i class="fas fa-download"></i> Download Report
                    </a>
                </div>
            </form>
        </div>

        <div class="row">
            <!-- Deposits Summary -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Deposits</h5>
                    </div>
                    <div class="card-body">
                        <h6>Total Transactions: {{ $summaries['deposits']['count'] }}</h6>
                        <h6>Total Amount: {{ number_format($summaries['deposits']['total_amount'], 2) }}</h6>
                        <h6>Total Fees: {{ number_format($summaries['deposits']['total_fee'], 2) }}</h6>

                        <hr>
                        <h6>By Float Account:</h6>
                        <ul class="list-unstyled">
                            @foreach($summaries['deposits']['by_account'] as $account => $amount)
                                <li>{{ $account }}: {{ number_format($amount, 2) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Withdrawals Summary -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Withdrawals</h5>
                    </div>
                    <div class="card-body">
                        <h6>Total Transactions: {{ $summaries['withdrawals']['count'] }}</h6>
                        <h6>Total Amount: {{ number_format($summaries['withdrawals']['total_amount'], 2) }}</h6>
                        <h6>Total Fees: {{ number_format($summaries['withdrawals']['total_fee'], 2) }}</h6>

                        <hr>
                        <h6>By Float Account:</h6>
                        <ul class="list-unstyled">
                            @foreach($summaries['withdrawals']['by_account'] as $account => $amount)
                                <li>{{ $account }}: {{ number_format($amount, 2) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Transfers Summary -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Internal Transfers</h5>
                    </div>
                    <div class="card-body">
                        <h6>Total Transactions: {{ $summaries['transfers']['count'] }}</h6>
                        <h6>Total Amount: {{ number_format($summaries['transfers']['total_amount'], 2) }}</h6>

                        <hr>
                        <h6>By Source Account:</h6>
                        <ul class="list-unstyled">
                            @foreach($summaries['transfers']['by_source'] as $account)
                                <li>{{ $account['name'] }}: {{ number_format($account['amount'], 2) }}</li>
                            @endforeach
                        </ul>

                        <hr>
                        <h6>By Destination Account:</h6>
                        <ul class="list-unstyled">
                            @foreach($summaries['transfers']['by_destination'] as $account)
                                <li>{{ $account['name'] }}: {{ number_format($account['amount'], 2) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Pending Counterparts Summary -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Pending Counterparts</h5>
                    </div>
                    <div class="card-body">
                        <h6>Total Amount Pending:</h6>
                        <h4 class="mb-3">{{ number_format($pendingCounterpartsTotal, 2) }}</h4>

                        <hr>
                        <h6>By Transaction Type:</h6>
                        <ul class="list-unstyled">
                            <li>Deposits: {{ number_format($pendingCounterparts['deposits'], 2) }}</li>
                            <li>Withdrawals: {{ number_format($pendingCounterparts['withdrawals'], 2) }}</li>
                            <li>Transfers: {{ number_format($pendingCounterparts['transfers'], 2) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Float Accounts  -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Float Accounts Summary</h5>
                    </div>
                    <div class="card-body">
                        @foreach($floatAccounts as $account)
                            <div class="border-bottom mb-3 pb-3">
                                <h6 class="fw-bold">{{ $account->name }}</h6>
                                <div class="small">
                                    <div>Bank: {{ $account->bank->name }}</div>
                                    <div>Account: {{ $account->account_number }}</div>
                                    <div class="mt-1">
                                        <span class="badge bg-primary">
                                            Balance: {{ number_format($account->balance, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detailed Transactions</h5>
                        <div>
                            <select id="counterpartFilter" class="form-select">
                                <option value="all">All Transactions</option>
                                <option value="pending">Pending Counterparts</option>
                                <option value="completed">Completed Counterparts</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="transactionsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Float Account</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Counterpart Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(['deposits', 'withdrawals', 'transfers'] as $type)
                                        @foreach($reports[$type] as $transaction)
                                            <tr>
                                                <td>{{ $transaction->created_at->format('H:i:s') }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'danger' : 'primary') }}">
                                                        {{ ucfirst($transaction->type) }}
                                                    </span>
                                                </td>
                                                <td>{{ number_format($transaction->amount, 2) }}</td>
                                                <td>{{ number_format($transaction->fee, 2) }}</td>
                                                <td>
                                                    @forelse($transaction->details as $detail)
                                                        @if($detail->floatAccount)
                                                            <span class="badge bg-info">{{ $detail->floatAccount->name }}</span><br>
                                                        @endif
                                                    @empty
                                                        N/A
                                                    @endforelse
                                                </td>
                                                <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($transaction->counterparts->isNotEmpty())
                                                        @foreach($transaction->counterparts as $counterpart)
                                                            <div class="mb-1">
                                                                <small class="d-block">
                                                                    <strong>{{ $counterpart->floatAccount->name }}</strong>
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    Amount: {{ number_format($counterpart->amount, 2) }}
                                                                </small>
                                                                @if($counterpart->reference)
                                                                    <small class="d-block text-muted">
                                                                        Ref: {{ $counterpart->reference }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                        <small class="badge bg-info">
                                                            Balance:
                                                            {{ number_format($transaction->amount - $transaction->counterparts->sum('amount'), 2) }}
                                                        </small>
                                                    @else
                                                        <div>
                                                            <span class="badge bg-secondary">No Counterpart</span>
                                                            <div class="mt-1">
                                                                <small class="badge bg-info">
                                                                    Balance: {{ number_format($transaction->amount, 2) }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');

                startDate.addEventListener('change', function () {
                    endDate.min = this.value;
                });

                endDate.addEventListener('change', function () {
                    startDate.max = this.value;
                });

                const counterpartFilter = document.getElementById('counterpartFilter');
                const table = document.getElementById('transactionsTable');
                const rows = table.getElementsByTagName('tr');

                counterpartFilter.addEventListener('change', function () {
                    const filterValue = this.value;

                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        const counterpartCell = row.cells[row.cells.length - 1];
                        const balance = counterpartCell.querySelector('.badge.bg-info')?.textContent || '';
                        const balanceAmount = balance.includes('Balance:') ?
                            parseFloat(balance.replace('Balance:', '').replace(/,/g, '')) : 0;

                        let showRow = false;
                        switch (filterValue) {
                            case 'all':
                                showRow = true;
                                break;
                            case 'pending':
                                showRow = balanceAmount > 0;
                                break;
                            case 'completed':
                                showRow = balanceAmount === 0 && !counterpartCell.textContent.includes('No Counterpart');
                                break;
                        }

                        row.style.display = showRow ? '' : 'none';
                    }
                });
            });
        </script>
    @endpush
@endsection