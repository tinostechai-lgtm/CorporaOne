@php
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $company_logo = Utility::getValByName('company_logo');
@endphp

@if (!empty($sales) && count($sales['data'] ?? []) > 0)
    <div class="card">
        <div class="card-body">
            <div class="row mt-2">
                <div class="col-6">
                    <img src="{{$logo.'/'.(isset($company_logo) && !empty($company_logo)?$company_logo:'logo-dark.png')}}" width="120px;">
                </div>
                <div class="col-6 text-end">
                    <button type="button" class="btn btn-sm btn-primary" onclick="saveAsPDF()">
                        <i class="ti ti-download"></i> {{ __('Download PDF') }}
                    </button>
                </div>
            </div>
            <div id="printableArea">
                <div class="row mt-3">
                    <div class="col-6">
                        <h1 class="invoice-id h6">{{ $details['pos_id'] }}</h1>
                        <div class="date"><b>{{ __('Date') }}: </b>{{ $details['date'] }}</div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="text-dark "><b>{{ __('Warehouse') }}: </b>
                            {!! $details['warehouse']['details'] ?? $details['warehouse']['name'] !!}
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col contacts d-flex justify-content-between pb-4">
                        <div class="invoice-to">
                            <div class="text-dark h6"><b>{{ __('Billed To:') }}</b></div>
                            {!! $details['customer']['details'] ?? $details['customer']['name'] !!}
                        </div>
                        @if(!empty($details['customer']['shippdetails'] ?? null))
                            <div class="invoice-to">
                                <div class="text-dark h6"><b>{{ __('Shipped To:') }}</b></div>
                                {!! $details['customer']['shippdetails'] !!}
                            </div>
                        @endif
                        <div class="company-details">
                            <div class="text-dark h6"><b>{{ __('From:') }}</b></div>
                            {!! $details['user']['details'] ?? $details['user']['name'] !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                        <tr>
                            <th class="text-left">{{ __('Items') }}</th>
                            <th class="text-center">{{ __('Quantity') }}</th>
                            <th class="text-right">{{ __('Price') }}</th>
                            <th class="text-right">{{ __('Tax') }}</th>
                            <th class="text-right">{{ __('Tax Amount') }}</th>
                            <th class="text-right">{{ __('Total') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($sales['data'] as $key => $value)
                            <tr>
                                <td class="text-left">
                                    {{ $value['name'] }}
                                </td>
                                <td class="text-center">
                                    {{ $value['quantity'] }}
                                </td>
                                <td class="text-right">
                                    {{ $value['price'] }}
                                </td>
                                <td class="text-right">
                                    {!! $value['product_tax'] !!}
                                </td>
                                <td class="text-right">
                                    {{ $value['tax_amount'] }}
                                </td>
                                <td class="text-right">
                                    {{ $value['subtotal'] }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                        <tr>
                            <td colspan="5" class="text-end"><strong>{{ __('Sub Total') }}:</strong></td>
                            <td class="text-right"><strong>{{ $sales['sub_total'] }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end"><strong>{{ __('Discount') }}:</strong></td>
                            <td class="text-right"><strong>{{ $sales['discount'] }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end"><strong>{{ __('Total') }}:</strong></td>
                            <td class="text-right"><strong>{{ $sales['total'] }}</strong></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <form id="posPaymentForm" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="vc_name" value="{{ $details['customer']['name'] ?? '' }}">
                        <input type="hidden" name="warehouse_name" value="{{ $details['warehouse']['id'] ?? $request->warehouse_name }}">
                        <input type="hidden" name="discount" value="{{ preg_replace('/[^0-9.]/', '', $sales['discount']) }}">
                        <input type="hidden" name="quotation_id" value="{{ $request->quotation_id ?? 0 }}">
                        <input type="hidden" name="date" value="{{ $details['date'] }}">
                        <button type="button" class="btn btn-success" id="confirmPaymentBtn">
                            <i class="ti ti-credit-card me-2"></i>{{ __('Confirm Payment') }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-2"></i>{{ __('Cancel') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-warning">{{ __('No items in cart') }}</div>
@endif

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: 'POS-Invoice-{{ $details['pos_id'] }}.pdf',
            image: {type: 'jpeg', quality: 1},
            html2canvas: {scale: 4, dpi: 72, letterRendering: true},
            jsPDF: {unit: 'in', format: 'A4'}
        };
        html2pdf().set(opt).from(element).save();
    }

    $(document).on('click', '#confirmPaymentBtn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("Processing...") }}');
        
        $.ajax({
            url: '{{ route("pos.store") }}',
            type: 'POST',
            data: $('#posPaymentForm').serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.code == 200) {
                    // Close the current modal
                    $('#commonModal').modal('hide');
                    
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('success', response.success || 'Payment completed successfully');
                    } else if (typeof show_toastr === 'function') {
                        show_toastr('success', response.success || 'Payment completed successfully');
                    } else {
                        alert(response.success || 'Payment completed successfully');
                    }
                    
                    // Reload the page to show empty cart after 1.5 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $btn.prop('disabled', false).html(originalText);
                    if (typeof showToast === 'function') {
                        showToast('error', response.error || 'Error processing payment');
                    } else if (typeof show_toastr === 'function') {
                        show_toastr('error', response.error || 'Error processing payment');
                    } else {
                        alert(response.error || 'Error processing payment');
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                var errorMsg = 'Error processing payment';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                if (typeof showToast === 'function') {
                    showToast('error', errorMsg);
                } else if (typeof show_toastr === 'function') {
                    show_toastr('error', errorMsg);
                } else {
                    alert(errorMsg);
                }
                console.error('Payment Error:', xhr);
            }
        });
    });
</script>