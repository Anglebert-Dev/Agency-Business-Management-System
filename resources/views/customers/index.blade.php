@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Customers</h1>
            <a href="{{ route('customers.edit') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Customer
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Account Numbers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>
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
                                <td>
                                    <a href="{{ route('customers.edit', $customer->uuid) }}"
                                        class="btn btn-sm btn-warning">Edit</a>
                                    <button class="btn btn-sm btn-danger delete-customer"
                                        data-customer-uuid="{{ $customer->uuid }}" data-customer-name="{{ $customer->name }}">
                                        Delete
                                    </button>
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

    <!-- Delete Confirmation Modal -->
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
    @endpush
@endsection