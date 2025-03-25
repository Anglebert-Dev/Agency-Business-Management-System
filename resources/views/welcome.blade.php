@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-body p-5">
                    <div class="row">
                        <div class="col-lg-6">
                            <h1 class="display-4 fw-bold text-primary mb-4">Agency Business Management</h1>
                            <p class="lead text-muted mb-4">Streamline your agency operations with our comprehensive management solution. Handle transactions, manage customers, and track float accounts efficiently.</p>
                            
                            @guest
                                <div class="d-grid gap-3 d-sm-flex">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">Login</a>
                                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">Register</a>
                                </div>
                            @else
                                <div class="d-grid gap-3 d-sm-flex">
                                    <a href="{{ route('transactions.index') }}" class="btn btn-primary btn-lg px-4">View Transactions</a>
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-primary btn-lg px-4">Manage Customers</a>
                                </div>
                            @endguest
                        </div>
                        <div class="col-lg-6">
                            <div class="row g-4 py-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <i class="fas fa-exchange-alt text-primary fa-2x mb-3"></i>
                                            <h5 class="card-title">Transactions</h5>
                                            <p class="card-text">Manage deposits, withdrawals, and transfers seamlessly.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <i class="fas fa-users text-primary fa-2x mb-3"></i>
                                            <h5 class="card-title">Customers</h5>
                                            <p class="card-text">Keep track of your customers and their transactions.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <i class="fas fa-university text-primary fa-2x mb-3"></i>
                                            <h5 class="card-title">Banks</h5>
                                            <p class="card-text">Manage bank accounts and financial institutions.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <i class="fas fa-wallet text-primary fa-2x mb-3"></i>
                                            <h5 class="card-title">Float Accounts</h5>
                                            <p class="card-text">Monitor and manage your float accounts effectively.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection