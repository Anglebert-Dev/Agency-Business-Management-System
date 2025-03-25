@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h2 class="h3 mb-0">Float Accounts</h2>
            <a href="{{ route('float_accounts.edit') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Add Float Account</span>
            </a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="px-3">ID</th>
                                <th class="px-3">Name</th>
                                <th class="px-3">Bank</th>
                                <th class="px-3">Account Number</th>
                                <th class="px-3">Currency</th>
                                <th class="px-3">Balance</th>
                                <th class="px-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($floatAccounts as $account)
                                <tr>
                                    <td class="px-3">{{ $account->id }}</td>
                                    <td class="px-3">{{ $account->name }}</td>
                                    <td class="px-3">{{ $account->bank->name }}</td>
                                    <td class="px-3">{{ $account->account_number ?: 'N/A' }}</td>
                                    <td class="px-3">{{ $account->currency }}</td>
                                    <td class="px-3">{{ number_format($account->balance) }}</td>
                                    <td class="px-3">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('float_accounts.edit', $account->uuid) }}"
                                                class="btn btn-sm btn-info"
                                                title="Edit Account">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                class="btn btn-sm btn-danger delete-account"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-account-name="{{ $account->name }}"
                                                data-account-uuid="{{ $account->uuid }}"
                                                title="Delete Account">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-money-bill fa-2x mb-3 d-block"></i>
                                            No float accounts found
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

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the float account: <strong id="accountNameToDelete"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const accountName = button.getAttribute('data-account-name');
                    const accountUuid = button.getAttribute('data-account-uuid');
                    
                    document.getElementById('accountNameToDelete').textContent = accountName;
                    document.getElementById('deleteForm').action = 
                        "{{ route('float_accounts.delete', '') }}/" + accountUuid;
                });
            }
        });
    </script>
@endsection