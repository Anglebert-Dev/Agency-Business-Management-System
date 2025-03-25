<?php

use App\Http\Controllers\Agency\BankController;
use App\Http\Controllers\Agency\CustomerController;
use App\Http\Controllers\Agency\FloatAccountController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\Transaction\TransactionDetailController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::match(['GET', 'POST'], '/login', [AuthController::class, 'login'])->name('login');
Route::match(['GET', 'POST'], '/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    // Profile route
    Route::match(['GET', 'POST'], '/profile', [AuthController::class, 'profile'])->name('profile');

    // Bank routes
    Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
    Route::match(['GET', 'POST'], '/banks/edit/{uuid?}', [BankController::class, 'edit'])->name('banks.edit');

    // Customer routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::match(['GET', 'POST'], '/customers/edit/{uuid?}', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::delete('/customers/{uuid}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Float Account routes
    Route::get('/float-accounts', [FloatAccountController::class, 'index'])->name('float_accounts.index');
    Route::match(['GET', 'POST'], '/float-accounts/edit/{uuid?}', [FloatAccountController::class, 'edit'])->name('float_accounts.edit');
    Route::delete('/float-accounts/{uuid}', [FloatAccountController::class, 'delete'])->name('float_accounts.delete');

    // Transaction routes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/reports', [TransactionController::class, 'reports'])->name('reports');
        Route::get('/export-report', [TransactionController::class, 'exportReport'])->name('export-report');
        Route::match(['GET', 'POST'], '/create', [TransactionController::class, 'edit'])->name('create');
        Route::match(['GET', 'POST'], '/transfer', [TransactionController::class, 'transfer'])->name('transfer');
        Route::match(['GET', 'POST'], '/{uuid}/add-counterpart', [TransactionController::class, 'addCounterpart'])->name('add-counterpart');
        Route::get('/{uuid}', [TransactionController::class, 'show'])->name('show');
        Route::match(['GET', 'POST'], '/profile', [AuthController::class, 'profile'])->name('profile');
    });
});



