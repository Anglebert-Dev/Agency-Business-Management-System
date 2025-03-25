@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Transaction Details</h2>
            <div>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Transactions
                </a>
                <button class="btn btn-primary ms-2" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Transaction Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Transaction ID:</th>
                                <td>{{ $transaction->id }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    <span
                                        class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'warning' : 'info') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span
                                        class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td>{{ number_format($transaction->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Fee:</th>
                                <td>{{ number_format($transaction->fee, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Net Amount:</th>
                                <td>{{ number_format($transaction->amount_after_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Reference:</th>
                                <td>{{ $transaction->reference ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @if ($transaction->type == 'deposit' || $transaction->type == 'withdrawal')
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Customer:</th>
                                    <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $transaction->customer->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $transaction->customer->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Bank:</th>
                                    <td>
                                        @if(count($customerAccounts) > 0)
                                            @foreach($customerAccounts as $account)
                                                {{ $account['bank_name'] }}<br>
                                            @endforeach
                                        @else
                                            {{ $transaction->bank->name ?? 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Account Number:</th>
                                    <td>
                                        @if(count($customerAccounts) > 0)
                                            @foreach($customerAccounts as $account)
                                                {{ $account['number'] }}<br>
                                            @endforeach
                                        @else
                                            {{ $transaction->customer_account_number ?? 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
        @if($transaction->type == 'internal_transfer')
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Transfer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Source Account</h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $sourceAccount = App\Models\Agency\FloatAccount::with('bank')->find($transaction->source_account_id);
                                            @endphp
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th>Account Name:</th>
                                                    <td>{{ $sourceAccount->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Bank:</th>
                                                    <td>{{ $sourceAccount->bank->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Account Number:</th>
                                                    <td>{{ $sourceAccount->account_number ?? 'N/A' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Destination Account</h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $destinationAccount = App\Models\Agency\FloatAccount::with('bank')->find($transaction->destination_account_id);
                                            @endphp
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th>Account Name:</th>
                                                    <td>{{ $destinationAccount->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Bank:</th>
                                                    <td>{{ $destinationAccount->bank->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Account Number:</th>
                                                    <td>{{ $destinationAccount->account_number ?? 'N/A' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Transaction Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Bank</th>
                                                <th>Account Number</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Reference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($transaction->details as $detail)
                                                <tr>
                                                    <td>{{ $detail->floatAccount->name }}</td>
                                                    <td>{{ $detail->floatAccount->bank->name }}</td>
                                                    <td>{{ $detail->floatAccount->account_number }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $detail->type == 'counterpart' ? 'warning' : 'info' }}">
                                                            {{ ucfirst($detail->type) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ number_format($detail->amount, 2) }}</td>
                                                    <td>{{ $detail->reference ?? 'N/A' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No details found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                        @if($transaction->details->count() > 0 || $transaction->counterparts->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Transaction Documents</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if($transaction->details->count() > 0)
                                            <div class="col-md-6">
                                                <h6>Source Documents</h6>
                                                <div class="list-group">
                                                    @foreach($transaction->details as $detail)
                                                        @if($detail->proof_path)
                                                            <div class="list-group-item">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong>{{ $detail->floatAccount->name }}</strong>
                                                                        <br>
                                                                        <small>Amount: {{ number_format($detail->amount, 2) }}</small>
                                                                    </div>
                                                                    <div>
                                                                        @if(in_array(pathinfo($detail->proof_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                                                            <a href="{{ asset('storage/' . $detail->proof_path) }}"
                                                                                class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                                                data-bs-target="#imageModal"
                                                                                data-image="{{ asset('storage/' . $detail->proof_path) }}">
                                                                                View Image
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ asset('storage/' . $detail->proof_path) }}"
                                                                                class="btn btn-sm btn-primary" target="_blank">
                                                                                View PDF
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($transaction->counterparts->count() > 0)
                                            <div class="col-md-6">
                                                <h6>Counterpart Documents</h6>
                                                <div class="list-group">
                                                    @foreach($transaction->counterparts as $counterpart)
                                                        @if($counterpart->proof_path)
                                                            <div class="list-group-item">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong>{{ $counterpart->floatAccount->name }}</strong>
                                                                        <br>
                                                                        <small>Amount: {{ number_format($counterpart->amount, 2) }}</small>
                                                                    </div>
                                                                    <div>
                                                                        @if(in_array(pathinfo($counterpart->proof_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                                                            <a href="{{ asset('storage/' . $counterpart->proof_path) }}"
                                                                                class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                                                data-bs-target="#imageModal"
                                                                                data-image="{{ asset('storage/' . $counterpart->proof_path) }}">
                                                                                View Image
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ asset('storage/' . $counterpart->proof_path) }}"
                                                                                class="btn btn-sm btn-primary" target="_blank">
                                                                                View PDF
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Image Modal -->
                            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Document Preview</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="" class="img-fluid" id="modalImage">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const imageModal = document.getElementById('imageModal');
                                    if (imageModal) {
                                        imageModal.addEventListener('show.bs.modal', function (event) {
                                            const button = event.relatedTarget;
                                            const imageUrl = button.getAttribute('data-image');
                                            document.getElementById('modalImage').src = imageUrl;
                                        });
                                    }
                                });
                            </script>
                        @endpush

                        @if($transaction->counterparts->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>Counterpart Details</h5>
                                        @if(!$transaction->isCounterpartComplete())
                                            <a href="{{ route('transactions.add-counterpart', $transaction->uuid) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus"></i> Add Counterpart
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Float Account</th>
                                                    <th>Bank</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($transaction->counterparts as $counterpart)
                                                    <tr>
                                                        <td>{{ $counterpart->floatAccount->name }}</td>
                                                        <td>{{ $counterpart->floatAccount->bank->name }}</td>
                                                        <td>{{ number_format($counterpart->amount, 2) }}</td>
                                                        <td>{{ $counterpart->created_at->format('Y-m-d H:i:s') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">Total Counterpart Amount:</th>
                                                    <td colspan="2">
                                                        {{ number_format($transaction->getTotalCounterpartAmount(), 2) }}
                                                        @if(!$transaction->isCounterpartComplete())
                                                            <span class="badge bg-warning ms-2">
                                                                Pending:
                                                                {{ number_format($transaction->amount - $transaction->getTotalCounterpartAmount(), 2) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            @if(!$transaction->isCounterpartComplete())
                                <div class="card mb-4">
                                    <div class="card-body text-center">
                                        <p class="mb-3">No counterparts added yet.</p>
                                        <a href="{{ route('transactions.add-counterpart', $transaction->uuid) }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Counterpart
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif


    <style>
        @media print {

            nav,
            .btn,
            footer {
                display: none !important;
            }

            .container-fluid {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-header {
                background-color: #f1f1f1 !important;
                color: #000 !important;
            }

            body {
                background-color: white !important;
            }
        }
    </style>
@endsection