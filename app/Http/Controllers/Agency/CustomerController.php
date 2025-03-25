<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency\Bank;
use App\Models\Agency\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {
        $data['header'] = 'Customers';
        $customers = Customer::with('creator')->orderBy('name')->get();
        $data['customers'] = $customers;
        
        $banks = Bank::orderBy('name')->get(); 
        if(!$banks->isEmpty()) {
            foreach($banks as $one_bank) {
                $data['ordered_banks'][$one_bank->id] = $one_bank;
            }
        }
        
        return view('customers.index', $data);
    }

    public function edit(Request $request, $uuid = null)
    {
        $customer = $uuid ? Customer::where('uuid', $uuid)->firstOrFail() : new Customer();

        if ($request->isMethod('post')) {
            try {
                $customer = Customer::_validateAndSave($request->all(), $customer);
                $message = $uuid ? 'Customer updated successfully' : 'Customer created successfully';

                if ($request->ajax()) {
                    return response()->json(['success' => $message, 'data' => $customer]);
                }

                return redirect()->route('customers.index')->with('success', $message);
            } catch (\Exception $e) {
                if ($request->ajax()) {
                    return response()->json(['error' => $e->getMessage()], 422);
                }
                return back()->withErrors(['error' => $e->getMessage()])->withInput();
            }
        }

        if ($request->ajax()) {
            return response()->json(['customer' => $customer]);
        }

        $banks = Bank::orderBy('name')->get(); 
        return view('customers.form', compact('customer', 'banks'));
    }

    public function destroy(Request $request, $uuid)
    {
      
        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        try {
            $customer->delete();
            
            if ($request->ajax()) {
                return response()->json(['success' => 'Customer deleted successfully']);
            }
            
            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return redirect()->route('customers.index')
                ->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }
}