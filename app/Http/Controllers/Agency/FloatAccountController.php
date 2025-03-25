<?php

namespace App\Http\Controllers\Agency;

use App\Models\Agency\FloatAccount;
use App\Models\Agency\Bank;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FloatAccountController extends Controller
{
    public function index()
    {
        $floatAccounts = FloatAccount::with(['bank', 'creator'])
            ->orderBy('name')
            ->get();

        return view('float_accounts.index', compact('floatAccounts'));
    }

    public function edit(Request $request, $uuid = null)
    {
        $floatAccount = $uuid ? FloatAccount::where('uuid', $uuid)->firstOrFail() : new FloatAccount();

        $banks = Bank::where('status', 'active')->get();

        if ($request->isMethod('post')) {
            try {
                $floatAccount = FloatAccount::_validateAndSave($request->all(), $floatAccount);
                $message = $uuid ? 'Float account updated successfully' : 'Float account created successfully';

                return redirect()->route('float_accounts.index')->with('success', $message);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }

        return view('float_accounts.form', compact('floatAccount', 'banks'));
    }

    public function delete($uuid)
    {
        try {
            FloatAccount::_delete($uuid);
            return redirect()->route('float_accounts.index')->with('success', 'Float account deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}