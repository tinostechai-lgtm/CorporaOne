{{-- resources/views/purchase_return/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Purchase Return') }} #{{ $purchaseReturn->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .header h1 {
            margin: 0 0 5px;
            color: #444;
            font-size: 24px;
        }
        .header h2 {
            margin: 0;
            color: #666;
            font-size: 20px;
            font-weight: normal;
        }
        .company-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .info-section {
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
        }
        .info-box {
            flex: 1;
            min-width: 250px;
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 14px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        table th {
            background: #444;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: normal;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            font-size: 14px;
        }
        .total-row {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: normal;
        }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; color: #fff; }
        .badge-success { background: #28a745; color: #fff; }
        .badge-danger { background: #dc3545; color: #fff; }
        .badge-secondary { background: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName ?? config('app.name') }}</h1>
        <p>{{ $companyAddress ?? '' }}</p>
        <p>{{ __('Email') }}: {{ $companyEmail ?? '' }} | {{ __('Phone') }}: {{ $companyPhone ?? '' }}</p>
        <h2>{{ __('Purchase Return') }} #{{ $purchaseReturn->id }}</h2>
    </div>

    <div class="company-info">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>{{ __('Supplier:') }}</strong> {{ $purchaseReturn->supplier }}
            </div>
            <div>
                <span class="badge badge-{{ $purchaseReturn->status == 'pending' ? 'warning' : ($purchaseReturn->status == 'approved' ? 'info' : ($purchaseReturn->status == 'completed' ? 'success' : ($purchaseReturn->status == 'rejected' ? 'danger' : 'secondary'))) }}">
                    {{ ucfirst($purchaseReturn->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-label">{{ __('Return Date') }}</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($purchaseReturn->return_date)->format('F d, Y') }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">{{ __('Total Amount') }}</div>
            <div class="info-value">{{ number_format($purchaseReturn->total_amount, 2) }}</div>
        </div>
    </div>

    @if($purchaseReturn->description)
    <div style="margin-bottom: 20px;">
        <div class="info-label">{{ __('Notes') }}</div>
        <div class="info-value">{{ $purchaseReturn->description }}</div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Product') }}</th>
                <th class="text-center">{{ __('Quantity') }}</th>
                <th class="text-end">{{ __('Unit Price') }}</th>
                <th class="text-end">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['product_name'] }}</td>
                <td class="text-center">{{ $item['quantity'] }}</td>
                <td class="text-end">{{ number_format($item['price'], 2) }}</td>
                <td class="text-end">{{ number_format($item['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div style="margin-bottom: 5px;">
            <span style="display: inline-block; width: 150px;">{{ __('Subtotal:') }}</span>
            <span style="font-weight: bold;">{{ number_format($purchaseReturn->total_amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span style="display: inline-block; width: 150px;">{{ __('Total Amount:') }}</span>
            <span>{{ number_format($purchaseReturn->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>{{ __('Generated on') }} {{ \Carbon\Carbon::now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>