<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Agency\Bank;
use App\Models\Agency\Customer;
use App\Models\Agency\FloatAccount;
use App\Models\Transaction\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with([
            'customer' => function($query) {
                $query->select('id', 'name', 'email', 'phone');
            },
            'bank' => function($query) {
                $query->select('id', 'name', 'status');
            },
            'details.floatAccount' => function($query) {
                $query->select('id', 'name', 'balance', 'account_number', 'bank_id');
            },
            'details.floatAccount.bank'
        ])
        ->select('id', 'uuid', 'type', 'amount', 'fee', 'customer_id', 'customer_name', 'status', 'customer_account_number', 'customer_bank_id', 'created_at')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($transaction) {
            $transaction->has_counterpart = $transaction->details->contains('type', 'counterpart');
            return $transaction;
        });

        $customers = Customer::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('transactions.index', compact('transactions', 'customers'));
    }


    public function edit(Request $request)
    {
        $customers = Customer::all();
        $banks = Bank::where('status', 'active')->get();
        $floatAccounts = FloatAccount::all();
        
        if ($request->isMethod('post')) {
            try {
                $customerId = $request->customer_id;
                $customer = Customer::find($customerId);
                $customer_name = $customer->name;

                \Log::info($request);

                if (!$customer) {
                    throw new \Exception('Customer not found.');
                }

                $customerAccountNumber = $customer->params['account_numbers'][0]['number'] ?? null;

                if (!$customerAccountNumber) {
                    throw new \Exception('Customer account number not found.');
                }

                $transactionData = [
                    'type' => $request->type,
                    'amount' => $request->amount,
                    'fee' => $request->fee ?? 0,
                    'customer_id' => $customerId,
                    'customer_name' => $customer_name,
                    'customer_account_number' => $customerAccountNumber,
                    'customer_bank_id' => $request->customer_bank_id,
                    'sources' => $request->sources,
                ];

                if ($request->has('counterparts')) {
                    $transactionData['counterparts'] = $request->counterparts;
                }

                $transaction = Transaction::_validateAndSave($transactionData);

                return redirect()->route('transactions.show', $transaction->uuid)
                    ->with('success', 'Transaction created successfully.');
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->validator)->withInput();
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Transaction failed: ' . $e->getMessage())->withInput();
            }
        }

        return view('transactions.create', compact('customers', 'banks', 'floatAccounts'));
    }

    public function show($uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();
        
        $transaction->load([
            'customer' => function($query) {
                $query->select('id', 'name', 'email', 'phone', 'params');
            },
            'bank' => function($query) {
                $query->select('id', 'name', 'status');
            },
            'details.floatAccount' => function($query) {
                $query->select('id', 'name', 'balance', 'account_number', 'bank_id');
            },
            'details.floatAccount.bank'
        ]);

        $sourceAccount = FloatAccount::with('bank')->find($transaction->source_account_id);

        // Get customer's bank accounts
        $customerAccounts = [];
        if ($transaction->customer && isset($transaction->customer->params['account_numbers'])) {
            foreach ($transaction->customer->params['account_numbers'] as $account) {
                $bank = Bank::find($account['bank_id']);
                $customerAccounts[] = [
                    'bank_name' => $bank ? $bank->name : 'N/A',
                    'number' => $account['number']
                ];
            }
        }

        return view('transactions.show', compact('sourceAccount','transaction', 'customerAccounts'));
    }

    public function addCounterpart($uuid, Request $request)
    {
        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();
        
        if ($request->isMethod('post')) {
            try {
                $validated = $request->validate([
                    'float_account_id' => 'required|exists:float_accounts,id',
                    'amount' => 'required|numeric|min:0.01'
                ]);

                Transaction::_addCounterpart($transaction, $validated['float_account_id'], $validated['amount']);

                // Check if counterpart is complete and update status
                if ($transaction->isCounterpartComplete()) {
                    $transaction->status = 'completed';
                    $transaction->save();
                }

                return redirect()->route('transactions.show', $transaction->uuid)
                    ->with('success', 'Counterpart added successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }

        
        $usedAccountIds = $transaction->details->pluck('float_account_id')->toArray();
        
        $floatAccounts = FloatAccount::with('bank')
            ->orderBy('name')
            ->get()
            ->map(function($account) {
                $account->display_name = $account->name . ' (' . $account->bank->name . ') - Balance: ' . number_format($account->balance, 2);
                return $account;
            });

        return view('transactions.add-counterpart', compact('transaction', 'floatAccounts'));
    }


    public function transfer(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                Transaction::_transfer($request->all());
                return redirect()->back()->with('success', 'Transfer completed successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }

        $floatAccounts = FloatAccount::orderBy('name')->get();
        // dd($floatAccounts);
        return view('transactions.transfer', compact('floatAccounts'));

    }

    public function reports(Request $request)
    {
        $startDate = $request->start_date ?? now()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        
        $reportData = Transaction::generateReport($startDate, $endDate);

        return view('transactions.reports', array_merge(
            $reportData,
            compact('startDate', 'endDate')
        ));
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->start_date ?? now()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        
        $reportData = Transaction::generateReport($startDate, $endDate);
        // \Log::info($reportData);
        
        return Transaction::exportToSpreadsheet($reportData, $startDate, $endDate);
    }
}