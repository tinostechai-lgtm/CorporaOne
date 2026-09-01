@extends('layouts.admin')

@section('page-title')
    {{ __('Ledger vs Bank Statement Verification') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bank-reconciliation.index') }}">{{ __('Bank Reconciliation') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bank-reconciliation.ledger-report', $ledgerId) }}">{{ __('Ledger Summary') }}</a></li>
    <li class="breadcrumb-item">{{ __('Verify Ledger') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('LEDGER TRANSACTIONS vs PDF UPLOAD') }}</h5>
                <small>Verify each ledger entry against the bank statement</small>
            </div>
            <div class="card-body">
                
                @php
                    // Initialize counters
                    $totalMatched = 0;
                    $totalUnmatched = 0;
                    $usedBankIndexes = [];
                    $reconciliationRows = [];
                    
                    // First, match ledger transactions with bank transactions
                    foreach($ledgerTransactions as $index => $ledgerTxn) {
                        $ledgerAmount = $ledgerTxn->debit + $ledgerTxn->credit;
                        $ledgerDate = $ledgerTxn->date;
                        $ledgerDesc = $ledgerTxn->description;
                        
                        $bestMatch = null;
                        $bestMatchIndex = -1;
                        $bestMatchScore = 0;
                        $matchStatus = 'Not Found';
                        $matchClass = 'danger';
                        $matchIcon = '❌';
                        $dateDiff = 0;
                        
                        // Find matching bank transaction
                        foreach($bankTransactions as $idx => $bankTxn) {
                            if(in_array($idx, $usedBankIndexes)) continue;
                            
                            $bankAmount = $bankTxn['amount'];
                            $bankDate = $bankTxn['date'];
                            $bankDesc = $bankTxn['description'];
                            
                            $amountDiff = abs($ledgerAmount - $bankAmount);
                            $amountTolerance = max(0.01, $ledgerAmount * 0.01);
                            
                            // Check if amounts match
                            if($amountDiff <= $amountTolerance) {
                                $bankTimestamp = strtotime($bankDate);
                                $ledgerTimestamp = strtotime($ledgerDate);
                                $dateDiff = abs($bankTimestamp - $ledgerTimestamp) / (60 * 60 * 24);
                                
                                // Check if dates are within 3 days
                                if($dateDiff <= 3) {
                                    $score = 100 - ($amountDiff / max($ledgerAmount, 0.01) * 50) - ($dateDiff * 10);
                                    if($score > $bestMatchScore) {
                                        $bestMatchScore = $score;
                                        $bestMatch = $bankTxn;
                                        $bestMatchIndex = $idx;
                                    }
                                }
                            }
                        }
                        
                        $isMatched = ($bestMatch && $bestMatchScore >= 70);
                        if($isMatched) {
                            $totalMatched++;
                            $matchStatus = 'MATCHED';
                            $matchClass = 'success';
                            $matchIcon = '✅';
                            $usedBankIndexes[] = $bestMatchIndex;
                        } else {
                            $totalUnmatched++;
                            $matchStatus = 'MISMATCH';
                            $matchClass = 'danger';
                            $matchIcon = '❌';
                        }
                        
                        $reconciliationRows[] = [
                            'type' => 'ledger',
                            'ledger' => $ledgerTxn,
                            'bank' => $bestMatch,
                            'isMatched' => $isMatched,
                            'matchScore' => $bestMatchScore,
                            'matchStatus' => $matchStatus,
                            'matchClass' => $matchClass,
                            'matchIcon' => $matchIcon,
                            'dateDiff' => $dateDiff,
                            'index' => $index + 1
                        ];
                    }
                    
                    // Add unmatched bank transactions
                    foreach($bankTransactions as $idx => $bankTxn) {
                        if(!in_array($idx, $usedBankIndexes)) {
                            $totalUnmatched++;
                            $reconciliationRows[] = [
                                'type' => 'bank_only',
                                'bank' => $bankTxn,
                                'ledger' => null,
                                'isMatched' => false,
                                'matchStatus' => 'EXTRA IN PDF',
                                'matchClass' => 'warning',
                                'matchIcon' => '⚠'
                            ];
                        }
                    }
                @endphp
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3>{{ $ledgerTransactions->count() }}</h3>
                                <small>Ledger Transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3>{{ count($bankTransactions) }}</h3>
                                <small>PDF Upload Transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>{{ $totalMatched }}</h3>
                                <small>Matched</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>{{ $totalUnmatched }}</h3>
                                <small>Unmatched</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison Table with Detailed Transaction Info -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="comparisonTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="3%">#</th>
                                <th width="30%">LEDGER TRANSACTION DETAILS</th>
                                <th width="30%">PDF UPLOAD (Bank Statement) DETAILS</th>
                                <th width="12%" class="text-center">STATUS</th>
                                <th width="25%">VERIFICATION & ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reconciliationRows as $row)
                                @php
                                    $rowClass = '';
                                    if($row['matchClass'] == 'success') $rowClass = 'table-success';
                                    if($row['matchClass'] == 'danger') $rowClass = 'table-danger';
                                    if($row['matchClass'] == 'warning') $rowClass = 'table-warning';
                                @endphp
                                <tr class="{{ $rowClass }}" id="row-{{ $row['type'] }}-{{ $row['ledger']->id ?? $loop->index }}">
                                    <td class="text-center align-middle">
                                        @if($row['type'] == 'ledger')
                                            <span class="badge bg-secondary">{{ $row['index'] }}</span>
                                        @else
                                            <i class="ti ti-file-text text-muted"></i>
                                        @endif
                                    </td>
                                    
                                    <!-- LEDGER TRANSACTION DETAILS -->
                                    <td class="align-middle">
                                        @if($row['ledger'])
                                            <div class="transaction-details" id="ledger-view-{{ $row['ledger']->id }}">
                                                <div class="detail-row">
                                                    <i class="ti ti-calendar text-primary"></i>
                                                    <strong>Date:</strong> 
                                                    <span class="badge bg-light text-dark">{{ \Auth::user()->dateFormat($row['ledger']->date) }}</span>
                                                </div>
                                                
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-file-description text-primary"></i>
                                                    <strong>Description:</strong>
                                                    <div class="mt-1 text-dark">{{ $row['ledger']->description }}</div>
                                                </div>
                                                
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-currency-dollar text-primary"></i>
                                                    <strong>Amount:</strong>
                                                    <span class="fw-bold {{ $row['isMatched'] ? 'text-success' : 'text-danger' }}" style="font-size: 16px;">
                                                        {{ \Auth::user()->priceFormat($row['ledger']->debit + $row['ledger']->credit) }}
                                                    </span>
                                                </div>
                                                
                                                @if($row['ledger']->debit > 0)
                                                <div class="detail-row mt-1">
                                                    <i class="ti ti-arrow-down text-danger"></i>
                                                    <strong>Debit:</strong> {{ \Auth::user()->priceFormat($row['ledger']->debit) }}
                                                </div>
                                                @endif
                                                
                                                @if($row['ledger']->credit > 0)
                                                <div class="detail-row mt-1">
                                                    <i class="ti ti-arrow-up text-success"></i>
                                                    <strong>Credit:</strong> {{ \Auth::user()->priceFormat($row['ledger']->credit) }}
                                                </div>
                                                @endif
                                                
                                                @if($row['ledger']->reference)
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-hash text-primary"></i>
                                                    <strong>Reference:</strong>
                                                    <code>{{ $row['ledger']->reference }}</code>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Edit Form for Unmatched Ledger -->
                                            @if(!$row['isMatched'])
                                            <div id="ledger-edit-{{ $row['ledger']->id }}" style="display: none;">
                                                <form onsubmit="updateLedger(event, {{ $row['ledger']->id }})">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Date</label>
                                                        <input type="date" name="date" class="form-control form-control-sm" 
                                                               value="{{ $row['ledger']->date }}" required>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="description" class="form-control form-control-sm" rows="2" required>{{ $row['ledger']->description }}</textarea>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Debit Amount</label>
                                                        <input type="number" name="debit" class="form-control form-control-sm" 
                                                               value="{{ $row['ledger']->debit }}" step="0.01">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Credit Amount</label>
                                                        <input type="number" name="credit" class="form-control form-control-sm" 
                                                               value="{{ $row['ledger']->credit }}" step="0.01">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="form-label">Reference</label>
                                                        <input type="text" name="reference" class="form-control form-control-sm" 
                                                               value="{{ $row['ledger']->reference }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="ti ti-device-floppy"></i> Save Changes
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-secondary" 
                                                                onclick="cancelEdit({{ $row['ledger']->id }})">
                                                            <i class="ti ti-x"></i> Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            @endif
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="ti ti-ban" style="font-size: 24px;"></i>
                                                <p class="mt-2 mb-0">No matching ledger entry</p>
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <!-- PDF UPLOAD (Bank Statement) DETAILS -->
                                    <td class="align-middle">
                                        @if($row['bank'])
                                            <div class="transaction-details">
                                                <div class="detail-row">
                                                    <i class="ti ti-calendar text-warning"></i>
                                                    <strong>Date:</strong> 
                                                    <span class="badge bg-light text-dark">{{ \Auth::user()->dateFormat($row['bank']['date']) }}</span>
                                                </div>
                                                
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-file-description text-warning"></i>
                                                    <strong>Description:</strong>
                                                    <div class="mt-1 text-dark">{{ $row['bank']['description'] }}</div>
                                                </div>
                                                
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-currency-dollar text-warning"></i>
                                                    <strong>Amount:</strong>
                                                    <span class="fw-bold {{ $row['isMatched'] ? 'text-success' : 'text-warning' }}" style="font-size: 16px;">
                                                        {{ \Auth::user()->priceFormat($row['bank']['amount']) }}
                                                    </span>
                                                </div>
                                                
                                                @if(isset($row['bank']['debit']) && $row['bank']['debit'] > 0)
                                                <div class="detail-row mt-1">
                                                    <i class="ti ti-arrow-down text-danger"></i>
                                                    <strong>Debit:</strong> {{ \Auth::user()->priceFormat($row['bank']['debit']) }}
                                                </div>
                                                @endif
                                                
                                                @if(isset($row['bank']['credit']) && $row['bank']['credit'] > 0)
                                                <div class="detail-row mt-1">
                                                    <i class="ti ti-arrow-up text-success"></i>
                                                    <strong>Credit:</strong> {{ \Auth::user()->priceFormat($row['bank']['credit']) }}
                                                </div>
                                                @endif
                                                
                                                @if(isset($row['bank']['reference']) && $row['bank']['reference'])
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-hash text-warning"></i>
                                                    <strong>Reference:</strong>
                                                    <code>{{ $row['bank']['reference'] }}</code>
                                                </div>
                                                @endif
                                                
                                                @if($row['isMatched'] && isset($row['matchScore']))
                                                <div class="detail-row mt-2">
                                                    <i class="ti ti-chart-pie text-info"></i>
                                                    <strong>Match Score:</strong>
                                                    <div class="progress mt-1" style="height: 20px;">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: {{ round($row['matchScore']) }}%" 
                                                             aria-valuenow="{{ round($row['matchScore']) }}" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                            {{ round($row['matchScore']) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="ti ti-file-pdf" style="font-size: 24px;"></i>
                                                <p class="mt-2 mb-0">No matching transaction found in PDF</p>
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <!-- STATUS -->
                                    <td class="text-center align-middle">
                                        <span class="badge bg-{{ $row['matchClass'] }}" style="font-size: 14px; padding: 8px 12px;">
                                            {{ $row['matchIcon'] }} {{ $row['matchStatus'] }}
                                        </span>
                                    </td>
                                    
                                    <!-- VERIFICATION & ACTIONS -->
                                    <td class="align-middle">
                                        @if($row['isMatched'])
                                            <div class="verification-details">
                                                <div class="alert alert-success mb-2 p-2">
                                                    <i class="ti ti-check-circle"></i>
                                                    <strong>Transaction Verified!</strong>
                                                </div>
                                                
                                                @php
                                                    $ledgerAmt = $row['ledger']->debit + $row['ledger']->credit;
                                                    $bankAmt = $row['bank']['amount'];
                                                @endphp
                                                
                                                @if($ledgerAmt == $bankAmt)
                                                    <div class="text-success mb-2">
                                                        <i class="ti ti-check"></i> Amount matches exactly
                                                    </div>
                                                @else
                                                    <div class="text-warning mb-2">
                                                        <i class="ti ti-alert-triangle"></i> Amount differs by {{ \Auth::user()->priceFormat(abs($ledgerAmt - $bankAmt)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($row['type'] == 'ledger')
                                            <div class="alert alert-danger mb-2 p-2">
                                                <i class="ti ti-alert-triangle"></i>
                                                <strong>Unmatched Ledger Entry</strong>
                                            </div>
                                            
                                            <!-- Action Buttons for Unmatched Ledger -->
                                            <div class="btn-group-vertical w-100" role="group">
                                                <button class="btn btn-sm btn-warning mb-1" onclick="showEditForm({{ $row['ledger']->id }})">
                                                    <i class="ti ti-edit"></i> Edit Transaction
                                                </button>
                                                <button class="btn btn-sm btn-info mb-1" onclick="matchWithBank({{ json_encode($row['ledger']) }}, {{ json_encode($row['bank'] ?? null) }})">
                                                    <i class="ti ti-link"></i> Match with Bank Transaction
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteLedgerEntry({{ $row['ledger']->id }})">
                                                    <i class="ti ti-trash"></i> Delete Transaction
                                                </button>
                                            </div>
                                            
                                            <div class="mt-2 text-muted small">
                                                <i class="ti ti-info-circle"></i> 
                                                Edit or match this transaction to resolve discrepancy
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-2 p-2">
                                                <i class="ti ti-alert-triangle"></i>
                                                <strong>Extra Bank Transaction</strong>
                                            </div>
                                            <div class="btn-group-vertical w-100" role="group">
                                                <button onclick="addToLedger({{ json_encode($row['bank']) }}, {{ $ledgerId }})" 
                                                        class="btn btn-sm btn-primary mb-1">
                                                    <i class="ti ti-plus"></i> Add to Ledger
                                                </button>
                                                <button onclick="ignoreTransaction(this, {{ json_encode($row['bank']) }})" 
                                                        class="btn btn-sm btn-secondary">
                                                    <i class="ti ti-check"></i> Mark as Ignored
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="ti ti-inbox" style="font-size: 48px;"></i>
                                        <p class="mt-2">No transactions to reconcile</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Summary Alert with Detailed Breakdown -->
                <div class="alert alert-{{ ($totalMatched == $ledgerTransactions->count() && $totalUnmatched == 0) ? 'success' : 'danger' }} mt-4">
                    <h5>
                        @if($totalMatched == $ledgerTransactions->count() && $totalUnmatched == 0)
                            <i class="ti ti-check-circle"></i> LEDGER IS CORRECT! All transactions matched successfully.
                        @else
                            <i class="ti ti-alert-triangle"></i> LEDGER HAS DISCREPANCIES!
                        @endif
                    </h5>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-success"><i class="ti ti-check-circle"></i> Matched</h6>
                                    <h3 class="text-success">{{ $totalMatched }}</h3>
                                    <p class="mb-0">transactions matched</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-danger"><i class="ti ti-alert-triangle"></i> Ledger Unmatched</h6>
                                    <h3 class="text-danger">{{ $ledgerTransactions->count() - $totalMatched }}</h3>
                                    <p class="mb-0">ledger entries not in bank</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-warning"><i class="ti ti-file-pdf"></i> PDF Unmatched</h6>
                                    <h3 class="text-warning">{{ count($bankTransactions) - $totalMatched }}</h3>
                                    <p class="mb-0">bank transactions not in ledger</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <a href="{{ route('bank-reconciliation.ledger-report', $ledgerId) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> Back to Ledger
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="ti ti-printer"></i> Print Report
                        </button>
                        <button onclick="exportToExcel()" class="btn btn-success">
                            <i class="ti ti-download"></i> Export to Excel
                        </button>
                        <button onclick="location.reload()" class="btn btn-warning">
                            <i class="ti ti-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show edit form for ledger entry
function showEditForm(ledgerId) {
    document.getElementById(`ledger-view-${ledgerId}`).style.display = 'none';
    document.getElementById(`ledger-edit-${ledgerId}`).style.display = 'block';
}

// Cancel edit
function cancelEdit(ledgerId) {
    document.getElementById(`ledger-view-${ledgerId}`).style.display = 'block';
    document.getElementById(`ledger-edit-${ledgerId}`).style.display = 'none';
}

// Update ledger entry via AJAX
function updateLedger(event, ledgerId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    fetch(`/bank-reconciliation/update-ledger/${ledgerId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if(result.success) {
            toastr.success('Ledger entry updated successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(result.error || 'Failed to update ledger entry');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred while updating');
    });
}

// Delete ledger entry
function deleteLedgerEntry(ledgerId) {
    if(confirm('Are you sure you want to delete this ledger entry? This action cannot be undone.')) {
        fetch(`/bank-reconciliation/delete-ledger/${ledgerId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                toastr.success('Ledger entry deleted successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(result.error || 'Failed to delete ledger entry');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred while deleting');
        });
    }
}

// Match ledger with bank transaction
function matchWithBank(ledger, bankTransaction) {
    if(!bankTransaction) {
        // If no bank transaction exists, prompt to create one
        if(confirm('No matching bank transaction found. Would you like to create one?')) {
            // Redirect to create bank transaction or show modal
            showCreateBankTransactionModal(ledger);
        }
        return;
    }
    
    if(confirm(`Match ledger entry with bank transaction?\n\nLedger: ${ledger.description}\nBank: ${bankTransaction.description}\nAmount: $${bankTransaction.amount}`)) {
        fetch('/bank-reconciliation/match-transaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ledger_id: ledger.id,
                bank_transaction: bankTransaction
            })
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                toastr.success('Transaction matched successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(result.error || 'Failed to match transaction');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred while matching');
        });
    }
}

// Add bank transaction to ledger
function addToLedger(transaction, ledgerId) {
    if(confirm(`Add this transaction to ledger?\n\nDate: ${transaction.date}\nDescription: ${transaction.description}\nAmount: $${transaction.amount}`)) {
        fetch('{{ route("bank-reconciliation.add-to-ledger") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ...transaction,
                ledger_id: ledgerId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                toastr.success('Transaction added to ledger successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.error || 'Error adding transaction');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred');
        });
    }
}

// Ignore transaction
function ignoreTransaction(button, transaction) {
    if(confirm('Mark this transaction as ignored? It will not appear in future reconciliations.')) {
        fetch('/bank-reconciliation/ignore-transaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(transaction)
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                $(button).closest('tr').fadeOut();
                toastr.success('Transaction ignored');
            } else {
                toastr.error(result.error || 'Failed to ignore transaction');
            }
        });
    }
}

// Export to Excel
function exportToExcel() {
    let table = document.getElementById('comparisonTable');
    let html = table.outerHTML;
    let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    let link = document.createElement('a');
    link.href = url;
    link.download = 'reconciliation_report.xls';
    link.click();
}

// Toastr configuration
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};
</script>

<style>
.transaction-details {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.detail-row {
    font-size: 13px;
    line-height: 1.5;
}

.detail-row i {
    width: 20px;
    margin-right: 5px;
}

.table-success {
    background-color: #d4edda !important;
}

.table-danger {
    background-color: #f8d7da !important;
}

.table-warning {
    background-color: #fff3cd !important;
}

.badge.bg-success {
    background-color: #28a745 !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.btn-group-vertical .btn {
    border-radius: 6px;
    margin-bottom: 5px;
}

@media print {
    .btn-group, .btn, .alert a, .breadcrumb {
        display: none !important;
    }
    .card {
        border: none !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
}
</style>
@endsection