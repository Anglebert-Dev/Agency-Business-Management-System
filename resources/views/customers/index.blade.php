@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767px) {
        .table-responsive {
            margin: 1rem 0;
        }
        .table td, .table th {
            min-width: 120px;
        }
        .table td:last-child {
            min-width: 100px;
        }
        .badge {
            white-space: normal;
            text-align: left;
        }
    }
</style>
    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h2 class="h3 mb-0">Customers</h2>
            <a href="{{ route('customers.edit') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">New Customer</span>
            </a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="px-3">Name</th>
                                <th class="px-3">Email</th>
                                <th class="px-3">Phone</th>
                                <th class="px-3">Account Numbers</th>
                                <th class="px-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td class="px-3">{{ $customer->name }}</td>
                                    <td class="px-3">{{ $customer->email }}</td>
                                    <td class="px-3">{{ $customer->phone }}</td>
                                    <td class="px-3">
                                        @if(isset($customer->params['account_numbers']))
                                            @foreach($customer->params['account_numbers'] as $account)
                                                <div class="mb-1">
                                                    <span class="badge bg-light text-dark">
                                                        @if(isset($account['bank_id']) && isset($ordered_banks) && isset($ordered_banks[$account['bank_id']]))
                                                            {{ $ordered_banks[$account['bank_id']]->name }}:
                                                        @endif
                                                        {{ $account['number'] ?? 'No account number' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No accounts</span>
                                        @endif
                                    </td>
                                    <td class="px-3">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('customers.edit', $customer->uuid) }}"
                                                class="btn btn-sm btn-warning"
                                                data-bs-toggle="tooltip"
                                                title="Edit Customer">
                                                <i class="fas fa-edit"></i> <span class="d-none d-sm-inline">Edit</span>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-customer"
                                                data-customer-uuid="{{ $customer->uuid }}" 
                                                data-customer-name="{{ $customer->name }}"
                                                data-bs-toggle="tooltip"
                                                title="Delete Customer">
                                                <i class="fas fa-trash"></i> <span class="d-none d-sm-inline">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                            No customers registered yet
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete customer: <span id="customerNameToDelete"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteCustomerForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Delete customer modal handling
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                const deleteButtons = document.querySelectorAll('.delete-customer');
                const deleteForm = document.getElementById('deleteCustomerForm');
                const customerNameSpan = document.getElementById('customerNameToDelete');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const uuid = this.getAttribute('data-customer-uuid');
                        const name = this.getAttribute('data-customer-name');

                        deleteForm.action = `{{ url('/customers') }}/${uuid}`;
                        customerNameSpan.textContent = name;
                        deleteModal.show();
                    });
                });
            });
        </script>
@endsection