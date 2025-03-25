// routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agency\BankController;
use App\Http\Controllers\Agency\CustomerController;
use App\Http\Controllers\Agency\FloatAccountController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\Transaction\TransactionDetailController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('banks', BankController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('float-accounts', FloatAccountController::class);
Route::apiResource('transactions', TransactionController::class);

// Nested API routes for transaction details
Route::apiResource('transactions.details', TransactionDetailController::class);