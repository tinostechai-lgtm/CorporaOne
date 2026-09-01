{{-- resources/views/bank-reconciliation/compare-with-ledger.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bank Reconciliation | Ledger vs Statement Verification</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            min-height: 100vh;
            color: #1e293b;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: 600;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            list-style: none;
            padding: 0 16px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .nav-link i {
            width: 20px;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 24px 32px;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-radius: 20px;
            padding: 16px 24px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
        }

        .page-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .page-title p {
            font-size: 14px;
            color: #64748b;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .language-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f1f5f9;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
        }

        .avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-card.matched .stat-icon { background: #dcfce7; color: #22c55e; }
        .stat-card.unmatched .stat-icon { background: #fee2e2; color: #ef4444; }
        .stat-card.ledger .stat-icon { background: #dbeafe; color: #3b82f6; }
        .stat-card.pdf .stat-icon { background: #fef3c7; color: #f59e0b; }

        /* Main Card */
        .main-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            padding: 24px 28px;
            border-bottom: 1px solid #e2e8f0;
            background: #fafbfc;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-header p {
            font-size: 14px;
            color: #64748b;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            padding: 0;
        }

        .reconciliation-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .reconciliation-table thead {
            background: #f8fafc;
        }

        .reconciliation-table th {
            padding: 16px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .reconciliation-table td {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .reconciliation-table tr:hover {
            background: #fafbfc;
        }

        /* Transaction Cards inside Table */
        .transaction-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            border-left: 3px solid;
        }

        .transaction-card.ledger-card {
            border-left-color: #3b82f6;
        }

        .transaction-card.pdf-card {
            border-left-color: #f59e0b;
        }

        .transaction-date {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .transaction-desc {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .transaction-amount {
            font-size: 18px;
            font-weight: 800;
        }

        .transaction-amount.debit { color: #ef4444; }
        .transaction-amount.credit { color: #22c55e; }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-matched {
            background: #dcfce7;
            color: #166534;
        }

        .status-mismatch {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-extra {
            background: #fef3c7;
            color: #92400e;
        }

        /* Verification Box */
        .verification-box {
            background: #fef9e7;
            border-radius: 12px;
            padding: 12px;
            max-width: 280px;
        }

        .verification-title {
            font-size: 12px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 8px;
        }

        .verification-text {
            font-size: 12px;
            color: #78350f;
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-add {
            background: #3b82f6;
            color: white;
        }

        .btn-add:hover {
            background: #2563eb;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 24px;
            color: #94a3b8;
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h2, .nav-link span {
                display: none;
            }
            .nav-link {
                justify-content: center;
            }
            .nav-link i {
                margin: 0;
            }
            .main-content {
                margin-left: 80px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-chart-line"></i> FinTech Pro</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-exchange-alt"></i><span>Bank Reconciliation</span></a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-book"></i><span>Ledger Summary</span></a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-check-circle"></i><span>Verify Ledger</span></a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-users"></i><span>User Management</span></a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">
                <h1>Ledger vs Bank Statement Verification</h1>
                <p><i class="fas fa-calendar-alt"></i> {{ now()->format('F d, Y') }} | Verify each ledger entry against the bank statement</p>
            </div>
            <div class="user-info">
                <div class="language-selector">
                    <i class="fas fa-globe"></i>
                    <span>English</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="avatar">
                    <span>C</span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card ledger">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-label">Ledger Transactions</div>
                <div class="stat-value">{{ $ledgerTransactions->count() ?? 0 }}</div>
            </div>
            <div class="stat-card pdf">
                <div class="stat-icon"><i class="fas fa-file-pdf"></i></div>
                <div class="stat-label">PDF Upload Transactions</div>
                <div class="stat-value">{{ $pdfTransactions->count() ?? 0 }}</div>
            </div>
            <div class="stat-card matched">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Matched</div>
                <div class="stat-value">{{ $matchedCount ?? 0 }}</div>
            </div>
            <div class="stat-card unmatched">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-label">Unmatched</div>
                <div class="stat-value">{{ $unmatchedCount ?? 0 }}</div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="main-card">
            <div class="card-header">
                <h2><i class="fas fa-search"></i> Reconciliation Details</h2>
                <p>Comparing ledger entries with bank statement transactions</p>
            </div>
            <div class="table-container">
                <table class="reconciliation-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Ledger Transaction</th>
                            <th width="35%">PDF Upload (Bank Statement)</th>
                            <th width="15%">Status</th>
                            <th width="10%">Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reconciliationData as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($item['ledger'])
                                <div class="transaction-card ledger-card">
                                    <div class="transaction-date">
                                        <i class="far fa-calendar-alt"></i> {{ $item['ledger']['date'] }}
                                    </div>
                                    <div class="transaction-desc">{{ $item['ledger']['description'] }}</div>
                                    <div class="transaction-amount debit">
                                        $ {{ number_format($item['ledger']['amount'], 2) }}
                                    </div>
                                    <small style="color:#64748b;">Ref: {{ $item['ledger']['reference'] ?? 'N/A' }}</small>
                                </div>
                                @else
                                <div class="transaction-card" style="background:#f1f5f9; text-align:center; padding:20px;">
                                    <i class="fas fa-minus-circle" style="color:#94a3b8;"></i>
                                    <p style="margin-top:8px; font-size:13px; color:#64748b;">No matching ledger entry</p>
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($item['pdf'])
                                <div class="transaction-card pdf-card">
                                    <div class="transaction-date">
                                        <i class="far fa-calendar-alt"></i> {{ $item['pdf']['date'] }}
                                    </div>
                                    <div class="transaction-desc">{{ $item['pdf']['description'] }}</div>
                                    <div class="transaction-amount {{ $item['pdf']['debit'] > 0 ? 'debit' : 'credit' }}">
                                        $ {{ number_format($item['pdf']['amount'], 2) }}
                                    </div>
                                    <small style="color:#64748b;">Ref: {{ $item['pdf']['reference'] ?? 'N/A' }}</small>
                                </div>
                                @else
                                <div class="transaction-card" style="background:#f1f5f9; text-align:center; padding:20px;">
                                    <i class="fas fa-minus-circle" style="color:#94a3b8;"></i>
                                    <p style="margin-top:8px; font-size:13px; color:#64748b;">No matching transaction found in PDF</p>
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($item['status'] === 'matched')
                                <span class="status-badge status-matched">
                                    <i class="fas fa-check-circle"></i> MATCHED
                                </span>
                                @elseif($item['status'] === 'mismatch')
                                <span class="status-badge status-mismatch">
                                    <i class="fas fa-times-circle"></i> MISMATCH
                                </span>
                                @elseif($item['status'] === 'extra_in_pdf')
                                <span class="status-badge status-extra">
                                    <i class="fas fa-exclamation-triangle"></i> EXTRA IN PDF
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="verification-box">
                                    <div class="verification-title">
                                        <i class="fas fa-info-circle"></i> Verification Note
                                    </div>
                                    <div class="verification-text">
                                        {!! $item['verification_note'] !!}
                                    </div>
                                    @if($item['status'] === 'extra_in_pdf')
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-add" onclick="addToLedger({{ json_encode($item['pdf']) }})">
                                            <i class="fas fa-plus"></i> Add to Ledger
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:60px;">
                                <i class="fas fa-inbox" style="font-size:48px; color:#cbd5e1;"></i>
                                <p style="margin-top:16px; color:#64748b;">No transactions to reconcile</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alert for Discrepancies -->
        @if(($unmatchedCount ?? 0) > 0)
        <div style="margin-top: 24px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 16px; padding: 20px; border-left: 4px solid #f59e0b;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #d97706;"></i>
                <div>
                    <strong style="color: #78350f; font-size: 16px;">LEDGER HAS DISCREPANCIES!</strong>
                    <p style="color: #78350f; margin-top: 4px;">Matched: {{ $matchedCount ?? 0 }} out of {{ $ledgerTransactions->count() ?? 0 }} transactions | Unmatched: {{ $unmatchedCount ?? 0 }} transactions need attention</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} FinTech Pro. All rights reserved. | Secure Bank Reconciliation System</p>
        </div>
    </div>

    <script>
        function addToLedger(transaction) {
            if(confirm(`Add ${transaction.description} ($${transaction.amount}) to ledger?`)) {
                // Make AJAX call to add transaction to ledger
                fetch('{{ route("bank-reconciliation.add-to-ledger") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(transaction)
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Error adding transaction');
                    }
                });
            }
        }
    </script>
</body>
</html>