 @extends('layouts.admin')

@section('page-title')
    Invoice Extraction Results
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoice.extract') }}">Invoice Extraction</a></li>
    <li class="breadcrumb-item active">Results</li>
@endsection

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Invoice Extractor - Edit Results</h1>

            <!-- Summary Section -->
            <div class="card shadow p-4 mb-4">
                <h3 class="mb-3">Invoice Summary</h3>

                @if(count($invoices) > 1)
                <p class="lead">We've detected {{ count($invoices) }} invoices in your document.</p>

                <div class="row">
                    @foreach($invoices as $invoice)
                    <div class="col-md-6">
                        <div class="card invoice-card" data-invoice-id="{{ $invoice['invoice_no'] }}">
                            <div class="page-badge">{{ $invoice['page_start'] }}-{{ $invoice['page_end'] }}</div>
                            <div class="card-body">
                                <h5 class="card-title">Invoice #{{ $invoice['invoice_no'] }}</h5>
                                <p class="card-text">
                                    <strong>Date:</strong> {{ $invoice['date'] }}<br>
                                    <strong>Pages:</strong> {{ $invoice['page_start'] }} to {{ $invoice['page_end'] }}<br>
                                    <strong>Items:</strong> {{ count($invoice['item_list']) }}<br>
                                    <strong>Total:</strong> ₹{{ number_format($invoice['final_invoice_total'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <!-- Single Invoice Summary -->
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-4">
                            <p><b>Invoice No:</b> {{ $invoices[0]['invoice_no'] }}</p>
                            <p><b>Date:</b> {{ $invoices[0]['date'] }}</p>
                            <p><b>Pages:</b> {{ $invoices[0]['page_start'] }} to {{ $invoices[0]['page_end'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><b>Seller:</b> {{ $invoices[0]['seller']['name'] }}</p>
                            <p><b>Seller GSTIN:</b> {{ $invoices[0]['seller']['gstin'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><b>Buyer:</b> {{ $invoices[0]['buyer']['name'] }}</p>
                            <p><b>Buyer GSTIN:</b> {{ $invoices[0]['buyer']['gstin'] }}</p>
                            <p><b>Total:</b> ₹{{ number_format($invoices[0]['final_invoice_total'], 2) }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Invoice Details -->
            @foreach($invoices as $invoice)
            @php $invoice_index = $loop->index @endphp
            <div class="card shadow p-4 mt-3">
                <h4 class="mb-3">Invoice #{{ $invoice['invoice_no'] }} - Manual Correction</h4>

                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-4">
                            <p><b>Invoice No:</b> {{ $invoice['invoice_no'] }}</p>
                            <p><b>Date:</b> {{ $invoice['date'] }}</p>
                            <p><b>Pages:</b> {{ $invoice['page_start'] }} to {{ $invoice['page_end'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><b>Seller:</b> {{ $invoice['seller']['name'] }}</p>
                            <p><b>Seller GSTIN:</b> {{ $invoice['seller']['gstin'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><b>Buyer:</b> {{ $invoice['buyer']['name'] }}</p>
                            <p><b>Buyer GSTIN:</b> {{ $invoice['buyer']['gstin'] }}</p>
                            <p><b>Total:</b> ₹{{ number_format($invoice['final_invoice_total'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <form class="invoice-form" method="POST" action="{{ route('invoice.saveAndDownload') }}">
                    @csrf
                    <input type="hidden" name="invoices_data" value="{{ json_encode($invoices) }}">
                    <input type="hidden" class="invoice-index" value="{{ $invoice_index }}">
                    <input type="hidden" class="row-count" name="rowCount_{{ $invoice_index }}" value="{{ count($invoice['item_list']) }}">

                    <div class="tbl-box">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Page</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>GST %</th>
                                    <th>GST Amount</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody class="items-table">
                            @foreach($invoice['item_list'] as $index => $it)
                            <tr data-index="{{ $index }}" @if($it['__locked__'] ?? false) class="locked-row" @endif>
                                <td>
                                    <input type="text" name="page_{{ $index }}_{{ $invoice_index }}" class="form-control page-input" value="{{ $it['Page'] ?? $invoice['page_start'] }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="desc_{{ $index }}_{{ $invoice_index }}" class="form-control description-input" value="{{ $it['Description'] }}" @if($it['__locked__'] ?? false) readonly @endif>
                                    @if($it['__reconcile_note__'] ?? false)
                                    <div class="reconcile-note">{{ $it['__reconcile_note__'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="amount_{{ $index }}_{{ $invoice_index }}" class="form-control amount-input" value="{{ $it['Amount'] }}" @if($it['__locked__'] ?? false) readonly @endif>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="gst_percent_{{ $index }}_{{ $invoice_index }}" class="form-control gst-percent-input"
                                           value="{{ $it['GST %'] ?? '' }}" @if($it['__locked__'] ?? false) readonly @endif>
                                </td>
                                <td>
                                    @if($it['__locked__'] ?? false)
                                    <input type="text" name="gst_amount_{{ $index }}_{{ $invoice_index }}" class="form-control gst-amount-input locked-input"
                                           value="{{ $it['GST Amount'] ?? '' }}" readonly>
                                    @else
                                    <input type="number" step="0.01" name="gst_amount_{{ $index }}_{{ $invoice_index }}" class="form-control gst-amount-input"
                                           value="{{ $it['GST Amount'] ?? '' }}" readonly>
                                    @endif
                                </td>
                                <td>
                                    @if($it['__locked__'] ?? false)
                                    <input type="text" name="total_{{ $index }}_{{ $invoice_index }}" class="form-control total-input locked-input"
                                           value="{{ $it['Total Amount'] ?? '' }}" readonly>
                                    @else
                                    <input type="number" step="0.01" name="total_{{ $index }}_{{ $invoice_index }}" class="form-control total-input"
                                           value="{{ $it['Total Amount'] ?? '' }}" readonly>
                                    @endif
                                </td>
                                <td>
                                    @if(!($it['__locked__'] ?? false))
                                    <button type="button" class="btn btn-danger btn-sm deleteRow">Delete</button>
                                    @else
                                    <span class="badge bg-success">Locked</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="invoice-total">
                                    <td colspan="5" class="text-end"><strong>Invoice Total:</strong></td>
                                    <td id="invoice-total-{{ $invoice_index }}">{{ number_format($invoice['final_invoice_total'], 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="button" class="btn btn-primary mt-2 add-row-btn">➕ Add Row</button>
                </form>
            </div>
            @endforeach

            <!-- Action Buttons -->
            <div class="card shadow p-4 mt-3">
                <button type="button" id="saveAllBtn" class="btn btn-success w-100">
                     Save & Download Excel
                </button>
                <a href="{{ route('invoice.extract') }}" class="btn btn-secondary w-100 mt-2">Back to Upload</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Function to calculate GST and Total
function calculateRow(row) {
    // Skip calculation for locked rows
    if (row.hasClass('locked-row')) {
        return;
    }

    const amount = parseFloat(row.find('.amount-input').val()) || 0;
    const gstPercent = parseFloat(row.find('.gst-percent-input').val()) || 0;

    const gstAmount = amount * gstPercent / 100;
    const totalAmount = amount + gstAmount;

    row.find('.gst-amount-input').val(gstAmount.toFixed(2));
    row.find('.total-input').val(totalAmount.toFixed(2));

    // Update invoice total
    updateInvoiceTotal(row.closest('.card'));
}

// Function to update invoice total
function updateInvoiceTotal(card) {
    let totalAmount = 0;

    card.find('.items-table tr').each(function() {
        const rowTotal = parseFloat($(this).find('.total-input').val()) || 0;
        totalAmount += rowTotal;
    });

    const invoiceIndex = card.find('.invoice-index').val();
    $('#invoice-total-' + invoiceIndex).text(totalAmount.toFixed(2));
}

// Calculate on input change
$(document).on('input', '.amount-input, .gst-percent-input', function() {
    calculateRow($(this).closest('tr'));
});

// Add Row
$(document).on('click', '.add-row-btn', function () {
    const form = $(this).closest('.invoice-form');
    const tableBody = form.find('.items-table');
    const invoiceIndex = form.find('.invoice-index').val();

    let rowCount = parseInt(form.find('.row-count').val());

    const newRow = document.createElement('tr');
    newRow.setAttribute('data-index', rowCount);
    newRow.innerHTML = `
        <td><input type="text" name="page_${rowCount}_${invoiceIndex}" class="form-control page-input" value="${form.find('.page-input').first().val()}" readonly></td>
        <td><input type="text" name="desc_${rowCount}_${invoiceIndex}" class="form-control description-input"></td>
        <td><input type="number" step="0.01" name="amount_${rowCount}_${invoiceIndex}" class="form-control amount-input"></td>
        <td><input type="number" step="0.01" name="gst_percent_${rowCount}_${invoiceIndex}" class="form-control gst-percent-input"></td>
        <td><input type="number" step="0.01" name="gst_amount_${rowCount}_${invoiceIndex}" class="form-control gst-amount-input" readonly></td>
        <td><input type="number" step="0.01" name="total_${rowCount}_${invoiceIndex}" class="form-control total-input" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm deleteRow">Delete</button></td>
    `;

    tableBody.append(newRow);

    rowCount++;
    form.find('.row-count').val(rowCount);
});

// Delete Row
$(document).on('click', '.deleteRow', function () {
    $(this).closest('tr').remove();
    reindexRows($(this).closest('.invoice-form'));
    updateInvoiceTotal($(this).closest('.card'));
});

// Reindex Rows
function reindexRows(form) {
    const invoiceIndex = form.find('.invoice-index').val();
    const rows = form.find('.items-table tr');

    rows.each(function(index) {
        $(this).attr('data-index', index);

        $(this).find('.page-input').attr('name', `page_${index}_${invoiceIndex}`);
        $(this).find('.description-input').attr('name', `desc_${index}_${invoiceIndex}`);
        $(this).find('.amount-input').attr('name', `amount_${index}_${invoiceIndex}`);
        $(this).find('.gst-percent-input').attr('name', `gst_percent_${index}_${invoiceIndex}`);
        $(this).find('.gst-amount-input').attr('name', `gst_amount_${index}_${invoiceIndex}`);
        $(this).find('.total-input').attr('name', `total_${index}_${invoiceIndex}`);
    });

    form.find('.row-count').val(rows.length);
}

// Save All Invoices
$('#saveAllBtn').click(function() {
    $('.invoice-form').each(function() {
        reindexRows($(this));
    });

    $('.invoice-form').first().submit();
});

// Initial Calculation
$(document).ready(function() {
    $('.items-table tr').each(function() {
        calculateRow($(this));
    });

    $('.card').each(function() {
        updateInvoiceTotal($(this));
    });
});
</script>

<style>
body { background: #eef1f5; }
.card { border-radius: 12px; }
.tbl-box { max-height: 400px; overflow-y: auto; }
input { font-size: 14px; }
.btn-sm { padding: 3px 8px; font-size: 12px; }
.invoice-card {
    margin-bottom: 20px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}
.invoice-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.page-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #0d6efd;
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.invoice-header {
    background-color: #e9ecef;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.invoice-total {
    background-color: #f8f9fa;
    font-weight: bold;
}
.locked-row {
    background-color: #f8f9fa;
}
.locked-input {
    background-color: #e9ecef;
    border: 1px solid #ced4da;
}
.reconcile-note {
    font-size: 0.8em;
    color: #6c757d;
    font-style: italic;
}
</style>
@endsection
