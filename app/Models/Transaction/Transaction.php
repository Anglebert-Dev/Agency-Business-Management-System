<?php

namespace App\Models\Transaction;

use App\Models\Agency\Bank;
use App\Models\Agency\Customer;
use App\Models\Agency\FloatAccount;
use App\Models\Transaction\TransactionDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'status',
        'amount',
        'fee',
        'amount_after_fee',
        'reference',
        'customer_id',
        'customer_account_number',
        'customer_bank_id',
        'customer_name',
        'customer_phone',
        'source_account_id',
        'destination_account_id',
        'insert_by',  // Add this
        'update_by'   // Add this
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->uuid = Str::uuid();
            $transaction->amount_after_fee = $transaction->amount - $transaction->fee;
            $transaction->insert_by = Auth::id();
        });

        static::updating(function ($transaction) {
            $transaction->update_by = Auth::id();
        });

        // Filter by ownership through float accounts
        static::addGlobalScope('owned', function ($query) {
            if (Auth::check()) {
                $query->whereHas('details.floatAccount', function ($q) {
                    $q->where('insert_by', Auth::id());
                })->orWhereHas('counterparts.floatAccount', function ($q) {
                    $q->where('insert_by', Auth::id());
                });
            }
        });
    }

    // Add relationship to User
    public function creator()
    {
        return $this->belongsTo(User::class, 'insert_by');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function counterparts()
    {
        return $this->hasMany(TransactionCounterpart::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'customer_bank_id');
    }

    // Validation rules
    public static function validationRules()
    {
        return [
            'rules' => [
                'type' => 'required|in:deposit,withdrawal,transfer',
                'amount' => 'required|numeric|min:0.01',
                'fee' => 'nullable|numeric|min:0',
                'customer_id' => 'required|exists:customers,id',
                'customer_account_number' => 'required_if:type,transfer|nullable',
                'customer_bank_id' => 'required_if:type,transfer|nullable|exists:banks,id',
                'customer_name' => 'nullable',
                'customer_phone' => 'nullable',
                'reference' => 'nullable|unique:transactions',
                'sources.*.proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'counterparts.*.proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ],
            'messages' => [
                'type.required' => 'Transaction type is required',
                'type.in' => 'Invalid transaction type. Must be deposit, withdrawal, or transfer',
                'amount.required' => 'Transaction amount is required',
                'amount.numeric' => 'Amount must be a valid number',
                'amount.min' => 'Amount must be greater than :min',
                'customer_id.required' => 'Customer ID is required',
                'customer_id.exists' => 'Selected customer does not exist',
                'customer_account_number.required_if' => 'Account number is required for transfer transactions',
                'customer_bank_id.required_if' => 'Bank is required for transfer transactions',
                'customer_bank_id.exists' => 'Selected bank does not exist',
                'reference.unique' => 'Transaction reference must be unique'
            ]
        ];
    }

    // Validate and save transaction data
    public static function _validateAndSave($data)
    {
        $validationRules = self::validationRules();
        $validator = Validator::make($data, $validationRules['rules'], $validationRules['messages']);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        $validatedData['status'] = 'pending';

        \DB::beginTransaction();

        try {
            $transaction = self::create($validatedData);

            // Process sources (float accounts)
            if (isset($data['sources']) && is_array($data['sources'])) {
                foreach ($data['sources'] as $source) {
                    $floatAccount = FloatAccount::findOrFail($source['float_account_id']);

                    $sourceData = TransactionDetail::validateData([
                        'transaction_id' => $transaction->id,
                        'float_account_id' => $source['float_account_id'],
                        'type' => $source['type'] ?? 'transaction',
                        'amount' => $source['amount'],
                        'reference' => $source['reference'] ?? null,
                        'params' => ['account_number' => $floatAccount->account_number],
                    ]);

                    $detail = $transaction->details()->create($sourceData);

                    if (isset($source['proof']) && $source['proof'] instanceof \Illuminate\Http\UploadedFile) {
                        if ($detail->proof_path) {
                            Storage::disk('public')->delete($detail->proof_path);
                        }
                        $path = $source['proof']->store('transaction-proofs', 'public');
                        $detail->proof_path = $path;
                        $detail->save();
                    }

                    if ($data['type'] == 'transfer' || $data['type'] == 'withdrawal') {

                        if (!$floatAccount->hasSufficientBalance($source['amount'])) {
                            throw new \Exception('Insufficient balance in float account: ' . $floatAccount->name);
                        }

                        $floatAccount->balance += $source['amount'];
                    } else if ($data['type'] == 'deposit') {
                        $floatAccount->balance -= $source['amount'];
                    }

                    $floatAccount->save();
                }
            }

            if (isset($data['counterparts']) && is_array($data['counterparts'])) {
                foreach ($data['counterparts'] as $counterpart) {
                    // Skip empty counterpart entries
                    if (empty($counterpart['float_account_id']) || empty($counterpart['amount'])) {
                        continue;
                    }

                    $floatAccount = FloatAccount::with('bank')->findOrFail($counterpart['float_account_id']);

                    $counterpartData = [
                        'float_account_id' => $counterpart['float_account_id'],
                        'amount' => $counterpart['amount'],
                        'reference' => $counterpart['reference'] ?? null,
                        'params' => [
                            'account_number' => $floatAccount->account_number,
                            'bank_id' => $floatAccount->bank->id
                        ]
                    ];

                    $detail = $transaction->counterparts()->create($counterpartData);

                    if (isset($counterpart['proof']) && $counterpart['proof'] instanceof \Illuminate\Http\UploadedFile) {
                        if ($detail->proof_path) {
                            Storage::disk('public')->delete($detail->proof_path);
                        }
                        $path = $counterpart['proof']->store('transaction-proofs', 'public');
                        $detail->proof_path = $path;
                        $detail->save();
                    }

                    if ($counterpart['amount'] == $data['amount']) {
                        if ($data['type'] == 'withdrawal') {
                            $floatAccount->balance -= $counterpart['amount'];
                        } elseif ($data['type'] == 'deposit') {
                            $floatAccount->balance += $counterpart['amount'];
                        }
                        $floatAccount->save();
                    }
                }
            }

            if (
                $data['type'] === 'transfer' ||
                (!in_array($data['type'], ['deposit', 'withdrawal']) ||
                    (isset($data['counterparts']) && $transaction->isCounterpartComplete()))
            ) {
                $transaction->status = 'completed';
                $transaction->save();
            }

            \DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }


    // Transfer money between float accounts
    public static function _transfer($data)
    {
        $validator = Validator::make($data, [
            'source_account_id' => 'required|exists:float_accounts,id',
            'destination_account_id' => 'required|exists:float_accounts,id|different:source_account_id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|unique:transactions,reference'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();

        try {
            $sourceAccount = FloatAccount::findOrFail($data['source_account_id']);
            $destinationAccount = FloatAccount::findOrFail($data['destination_account_id']);

            if ($sourceAccount->currency !== $destinationAccount->currency) {
                throw new \Exception("Currency mismatch: {$sourceAccount->currency} to {$destinationAccount->currency}");
            }

            if (!$sourceAccount->hasSufficientBalance($data['amount'])) {
                throw new \Exception("Insufficient balance in {$sourceAccount->name}");
            }

            $transaction = self::create([
                'type' => 'internal_transfer',
                'status' => 'pending',
                'amount' => $data['amount'],
                'reference' => $data['reference'],
                'source_account_id' => $data['source_account_id'],
                'destination_account_id' => $data['destination_account_id']
            ]);

            $sourceAccount->balance -= $data['amount'];
            $destinationAccount->balance += $data['amount'];

            $sourceAccount->save();
            $destinationAccount->save();

            $transaction->status = 'completed';
            $transaction->save();

            \DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }



    public static function _addCounterpart($transaction, $floatAccountId, $amount)
    {
        $floatAccount = FloatAccount::with('bank')->findOrFail($floatAccountId);

        if ($transaction->type === 'deposit' && !$floatAccount->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient balance in selected float account');
        }

        if ($transaction->type === 'deposit') {
            $floatAccount->balance += $amount;
        } else {
            $floatAccount->balance -= $amount;
        }
        $floatAccount->save();

        return $transaction->counterparts()->create([
            'float_account_id' => $floatAccountId,
            'amount' => $amount,
            'params' => [
                'account_number' => $floatAccount->account_number,
                'bank_id' => $floatAccount->bank->id
            ]
        ]);
    }

    public static function generateReport($startDate, $endDate)
    {
        // Get only float accounts owned by the current user
        $floatAccounts = FloatAccount::orderBy('name')->get();

        $reports = [
            'deposits' => self::with(['details.floatAccount', 'counterparts.floatAccount'])
                ->where('type', 'deposit')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->withoutGlobalScope('owned') // Temporarily disable scope for accurate reporting
                ->whereHas('details.floatAccount', function($q) {
                    $q->where('insert_by', Auth::id());
                })
                ->get(),

            'withdrawals' => self::with(['details.floatAccount', 'counterparts.floatAccount'])
                ->where('type', 'withdrawal')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->withoutGlobalScope('owned')
                ->whereHas('details.floatAccount', function($q) {
                    $q->where('insert_by', Auth::id());
                })
                ->get(),

            'transfers' => self::with(['details.floatAccount'])
                ->where('type', 'internal_transfer')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->withoutGlobalScope('owned')
                ->whereHas('details.floatAccount', function($q) {
                    $q->where('insert_by', Auth::id());
                })
                ->get()
        ];

        $summaries = [
            'deposits' => [
                'count' => $reports['deposits']->count(),
                'total_amount' => $reports['deposits']->sum('amount'),
                'total_fee' => $reports['deposits']->sum('fee'),
                'by_account' => $reports['deposits']->flatMap(function ($transaction) {
                    return $transaction->details->map(function ($detail) use ($transaction) {
                        return [
                            'float_account' => $detail->floatAccount->name,
                            'amount' => $detail->amount,
                            'type' => $detail->type
                        ];
                    });
                })->groupBy('float_account')->map(function ($group) {
                    return $group->sum('amount');
                }),
                'by_counterpart' => $reports['deposits']->flatMap(function ($transaction) {
                    return $transaction->counterparts->map(function ($counterpart) {
                        return [
                            'float_account' => $counterpart->floatAccount->name,
                            'amount' => $counterpart->amount
                        ];
                    });
                })->groupBy('float_account')->map(function ($group) {
                    return $group->sum('amount');
                }),
                'sources_total' => $reports['deposits']->sum(function ($transaction) {
                    return $transaction->details->sum('amount');
                }),
                'counterparts_total' => $reports['deposits']->sum(function ($transaction) {
                    return $transaction->counterparts->sum('amount');
                })
            ],
            'withdrawals' => [
                'count' => $reports['withdrawals']->count(),
                'total_amount' => $reports['withdrawals']->sum('amount'),
                'total_fee' => $reports['withdrawals']->sum('fee'),
                'by_account' => $reports['withdrawals']->flatMap->details
                    ->where('type', '!=', 'counterpart')
                    ->groupBy('floatAccount.name')
                    ->map->sum('amount'),
                'by_counterpart' => $reports['withdrawals']->flatMap->details
                    ->where('type', 'counterpart')
                    ->groupBy('floatAccount.name')
                    ->map->sum('amount'),
                'sources_total' => $reports['withdrawals']->flatMap->details
                    ->where('type', '!=', 'counterpart')
                    ->sum('amount'),
                'counterparts_total' => $reports['withdrawals']->flatMap->details
                    ->where('type', 'counterpart')
                    ->sum('amount')
            ],
            'transfers' => [
                'count' => $reports['transfers']->count(),
                'total_amount' => $reports['transfers']->sum('amount'),
                'by_source' => $reports['transfers']->groupBy('source_account_id')
                    ->map(function ($group, $accountId) use ($floatAccounts) {
                        return [
                            'name' => $floatAccounts->find($accountId)->name ?? 'Unknown',
                            'amount' => $group->sum('amount')
                        ];
                    }),
                'by_destination' => $reports['transfers']->groupBy('destination_account_id')
                    ->map(function ($group, $accountId) use ($floatAccounts) {
                        return [
                            'name' => $floatAccounts->find($accountId)->name ?? 'Unknown',
                            'amount' => $group->sum('amount')
                        ];
                    })
            ]
        ];

        $pendingCounterparts = [
            'deposits' => $reports['deposits']->sum(function ($transaction) {
                return $transaction->amount - $transaction->counterparts->sum('amount');
            }),
            'withdrawals' => $reports['withdrawals']->sum(function ($transaction) {
                return $transaction->amount - $transaction->counterparts->sum('amount');
            }),
            'transfers' => $reports['transfers']->sum(function ($transaction) {
                return $transaction->amount - $transaction->counterparts->sum('amount');
            })
        ];

        return [
            'reports' => $reports,
            'summaries' => $summaries,
            'floatAccounts' => $floatAccounts,
            'pendingCounterparts' => $pendingCounterparts,
            'pendingCounterpartsTotal' => array_sum($pendingCounterparts)
        ];
    }



    public static function exportToSpreadsheet($reportData, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();

        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary');

        $summarySheet->setCellValue('A1', 'Transaction Summary Report');
        $summarySheet->setCellValue('A2', 'Period: ' . $startDate . ' to ' . $endDate);
        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $summarySheet->getStyle('A2')->getFont()->setSize(12);

        $summarySheet->setCellValue('A4', 'Transaction Type');
        $summarySheet->setCellValue('B4', 'Count');
        $summarySheet->setCellValue('C4', 'Total Amount');
        $summarySheet->setCellValue('D4', 'Total Fees');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $summarySheet->getStyle('A4:D4')->applyFromArray($headerStyle);
        $summarySheet->getStyle('A4:D4')->getAlignment()->setWrapText(true);

        $row = 5;
        foreach (['deposits', 'withdrawals', 'transfers'] as $type) {
            $summarySheet->setCellValue('A' . $row, ucfirst($type));
            $summarySheet->setCellValue('B' . $row, $reportData['summaries'][$type]['count']);
            $summarySheet->setCellValue('C' . $row, $reportData['summaries'][$type]['total_amount']);
            $summarySheet->setCellValue('D' . $row, $reportData['summaries'][$type]['total_fee'] ?? 0);
            
            $summarySheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $summarySheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $summarySheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setWrapText(true);
            
            $fillColor = ($row % 2 == 0) ? 'F2F2F2' : 'FFFFFF';
            $summarySheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            $summarySheet->getStyle('B' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
        }

        $row += 2;
        $summarySheet->setCellValue('A' . $row, 'Pending Counterparts Summary');
        $summarySheet->getStyle('A' . $row)->getFont()->setBold(true);
        $summarySheet->mergeCells('A' . $row . ':B' . $row);

        $row++;
        $summarySheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($headerStyle);
        $summarySheet->setCellValue('A' . $row, 'Type');
        $summarySheet->setCellValue('B' . $row, 'Pending Amount');

        $row++;
        foreach (['deposits', 'withdrawals', 'transfers'] as $type) {
            $summarySheet->setCellValue('A' . $row, ucfirst($type));
            $summarySheet->setCellValue('B' . $row, $reportData['pendingCounterparts'][$type]);
            
            $summarySheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            
            $fillColor = ($row % 2 == 0) ? 'F2F2F2' : 'FFFFFF';
            $summarySheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);
            $summarySheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            $row++;
        }

        $summarySheet->setCellValue('A' . $row, 'Total');
        $summarySheet->setCellValue('B' . $row, $reportData['pendingCounterpartsTotal']);
        $summarySheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $summarySheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $summarySheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $summarySheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $transactionsSheet = $spreadsheet->createSheet();
        $transactionsSheet->setTitle('Transactions');

        $headers = ['Date/Time', 'Type', 'Amount', 'Fee', 'Float Account', 'Customer', 'Counterpart Details', 'Remaining Counterpart Amount', 'Status'];
        foreach (range('A', 'I') as $index => $column) {
            $transactionsSheet->setCellValue($column . '1', $headers[$index]);
        }
        $transactionsSheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        $row = 2;
        foreach (['deposits', 'withdrawals', 'transfers'] as $type) {
            foreach ($reportData['reports'][$type] as $transaction) {
                $transactionsSheet->setCellValue('A' . $row, $transaction->created_at->format('Y-m-d H:i:s'));
                $transactionsSheet->setCellValue('B' . $row, ucfirst($transaction->type));
                $transactionsSheet->setCellValue('C' . $row, $transaction->amount);
                $transactionsSheet->setCellValue('D' . $row, $transaction->fee);
                
                $floatAccounts = $transaction->details->map(function ($detail) {
                    return $detail->floatAccount ? $detail->floatAccount->name : 'N/A';
                })->implode(', ');
                $transactionsSheet->setCellValue('E' . $row, $floatAccounts);
                $transactionsSheet->setCellValue('F' . $row, $transaction->customer_name ?? 'N/A');

                $counterpartDetails = $transaction->counterparts->map(function ($counterpart) {
                    return $counterpart->floatAccount->name . ': ' . $counterpart->amount;
                })->implode("\n");
                
                $remainingAmount = $transaction->amount - $transaction->getTotalCounterpartAmount();
                
                $transactionsSheet->setCellValue('G' . $row, $counterpartDetails ?: 'No Counterpart');
                $transactionsSheet->setCellValue('H' . $row, $remainingAmount);
                
                $status = ucfirst($transaction->status);
                $transactionsSheet->setCellValue('I' . $row, $status);
                $statusColor = $status === 'Completed' ? '00B050' : 'FF0000';
                $transactionsSheet->getStyle('I' . $row)
                    ->getFont()
                    ->setColor(new Color($statusColor))
                    ->setBold(true);

                $transactionsSheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $transactionsSheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $transactionsSheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

                $transactionsSheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setWrapText(true);
                
                $row++;
            }
        }

        foreach (range('A', 'I') as $column) {
            $transactionsSheet->getColumnDimension($column)->setAutoSize(true);
            $transactionsSheet->getColumnDimension($column)->setWidth(12);
        }

        foreach (range('A', 'H') as $column) {
            $transactionsSheet->getColumnDimension($column)->setAutoSize(true);
            $transactionsSheet->getColumnDimension($column)->setWidth(12); 
        }

        $summarySheet->freezePane('A5');
        $transactionsSheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'transactions_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function getTotalCounterpartAmount()
    {
        return $this->counterparts()->sum('amount');
    }

    public function isCounterpartComplete()
    {
        return $this->getTotalCounterpartAmount() >= $this->amount;
    }


}




