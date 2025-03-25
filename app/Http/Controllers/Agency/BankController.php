<?php

namespace App\Http\Controllers\Agency;

use App\Models\Agency\Bank;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::with('creator')->get();
        return view('banks.index', compact('banks'));
    }

    public function edit(Request $request, $uuid = null)
    {
        $bank = $uuid ? Bank::where('uuid', $uuid)->firstOrFail() : new Bank();
        // The global scope will automatically ensure the user can only edit their own banks
        
        if (!$bank && $uuid) {
            return response()->json(['error' => 'Bank not found'], 404);
        }

        if ($request->isMethod('post')) {
            try {
                $bank = Bank::_validateAndSave($request->all(), $bank);
                $message = $uuid ? 'Bank updated successfully' : 'Bank created successfully';

                return response()->json(['success' => $message, 'data' => $bank]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
        }

        return response()->json(['bank' => $bank]);
    }


}