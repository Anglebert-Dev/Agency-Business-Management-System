@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Float Accounts</h1>
            <a href="{{ route('float_accounts.edit') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Float Account
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Bank</th>
                                <th>Account Number</th>
                                <th>Currency</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($floatAccounts as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>{{ $account->name }}</td>
                                    <td>{{ $account->bank->name }}</td>
                                    <td>{{ $account->account_number ?: 'N/A' }}</td>
                                    <td>{{ $account->currency }}</td>
                                    <td>{{ number_format($account->balance, 2) }}</td>
                                    <td>
                                        <a href="{{ route('float_accounts.edit', $account->uuid) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('float_accounts.delete', $account->uuid) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this float account?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No float accounts found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection