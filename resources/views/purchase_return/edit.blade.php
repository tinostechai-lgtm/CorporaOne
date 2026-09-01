{{-- resources/views/purchase_return/edit.blade.php --}}
@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Purchase Return') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-return.index') }}">{{ __('Purchase Returns') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('purchase-return.index') }}" class="btn btn-sm btn-secondary">
        <i class="ti ti-arrow-left"></i> {{ __('Back') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Edit Purchase Return') }} #{{ $purchaseReturn->id }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::model($purchaseReturn, ['route' => ['purchase-return.update', Crypt::encrypt($purchaseReturn->id)], 'method' => 'PUT', 'id' => 'purchase-return-form']) }}
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('supplier', __('Supplier/Vendor'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::select('supplier', $venders, $purchaseReturn->supplier, [
                                    'class' => 'form-control ' . ($errors->has('supplier') ? 'is-invalid' : ''),
                                    'required' => true,
                                    'placeholder' => __('Select Vendor')
                                ]) }}
                                @error('supplier')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('return_date', __('Return Date'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::date('return_date', $purchaseReturn->return_date, [
                                    'class' => 'form-control ' . ($errors->has('return_date') ? 'is-invalid' : ''),
                                    'required' => true
                                ]) }}
                                @error('return_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('description', __('Notes/Description'), ['class' => 'form-label']) }}
                                {{ Form::textarea('description', $purchaseReturn->description, [
                                    'class' => 'form-control',
                                    'rows' => 3,
                                    'placeholder' => __('Enter any additional notes...')
                                ]) }}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Return Items') }}</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-item">
                                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="items-table">
                                            <thead>
                                                <tr>
                                                    <th width="40%">{{ __('Product') }}</th>
                                                    <th width="15%">{{ __('Quantity') }}</th>
                                                    <th width="15%">{{ __('Price') }}</th>
                                                    <th width="15%">{{ __('Total') }}</th>
                                                    <th width="15%">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-body">
                                                @foreach($items as $index => $item)
                                                <tr id="item-row-{{ $index }}">
                                                    <td>
                                                        <select name="items[{{ $index }}][product_id]" class="form-control product-select" onchange="selectProduct({{ $index }})">
                                                            <option value="">{{ __('Select Product') }}</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" 
                                                                    data-name="{{ $product->name }}" 
                                                                    data-price="{{ $product->purchase_price }}"
                                                                    {{ isset($item['product_id']) && $item['product_id'] == $product->id ? 'selected' : '' }}>
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="items[{{ $index }}][product_name]" class="product-name-{{ $index }}" value="{{ $item['product_name'] }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-{{ $index }}" min="0.01" step="0.01" value="{{ $item['quantity'] }}" required onkeyup="calculateRow({{ $index }})" onchange="calculateRow({{ $index }})">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][price]" class="form-control price-{{ $index }}" min="0" step="0.01" value="{{ $item['price'] }}" required onkeyup="calculateRow({{ $index }})" onchange="calculateRow({{ $index }})">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][total]" class="form-control row-total-{{ $index }}" value="{{ $item['total'] }}" readonly>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remove-item" data-index="{{ $index }}">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>{{ __('Subtotal:') }}</strong></td>
                                                    <td><strong id="subtotal">{{ $purchaseReturn->total_amount }}</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>{{ __('Total Amount:') }}</strong></td>
                                                    <td><strong id="total-amount">{{ $purchaseReturn->total_amount }}</strong></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="ti ti-device-floppy"></i> {{ __('Update Purchase Return') }}
                            </button>
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    let nextIndex = {{ count($items) }};
    let products = @json($products);

    // Function to add new item row
    $('#add-item').on('click', function() {
        let productOptions = '<option value="">{{ __('Select Product') }}</option>';
        products.forEach(product => {
            productOptions += `<option value="${product.id}" data-name="${product.name}" data-price="${product.purchase_price || 0}">${product.name}</option>`;
        });

        let html = `
            <tr id="item-row-${nextIndex}">
                <td>
                    <select name="items[${nextIndex}][product_id]" class="form-control product-select" onchange="selectProduct(${nextIndex})">
                        ${productOptions}
                    </select>
                    <input type="hidden" name="items[${nextIndex}][product_name]" class="product-name-${nextIndex}">
                </td>
                <td>
                    <input type="number" name="items[${nextIndex}][quantity]" class="form-control quantity-${nextIndex}" min="0.01" step="0.01" value="1" required onkeyup="calculateRow(${nextIndex})" onchange="calculateRow(${nextIndex})">
                </td>
                <td>
                    <input type="number" name="items[${nextIndex}][price]" class="form-control price-${nextIndex}" min="0" step="0.01" value="0" required onkeyup="calculateRow(${nextIndex})" onchange="calculateRow(${nextIndex})">
                </td>
                <td>
                    <input type="number" name="items[${nextIndex}][total]" class="form-control row-total-${nextIndex}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${nextIndex}">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items-body').append(html);
        nextIndex++;
    });

    // Function to handle product selection
    function selectProduct(index) {
        let select = $(`select[name="items[${index}][product_id]"]`);
        let selectedOption = select.find('option:selected');
        let price = selectedOption.data('price') || 0;
        let name = selectedOption.data('name') || selectedOption.text();
        
        $(`.price-${index}`).val(price);
        $(`.product-name-${index}`).val(name);
        calculateRow(index);
    }

    // Function to calculate row total
    function calculateRow(index) {
        let quantity = $(`.quantity-${index}`).val() || 0;
        let price = $(`.price-${index}`).val() || 0;
        let total = (quantity * price).toFixed(2);
        $(`.row-total-${index}`).val(total);
        calculateTotals();
    }

    // Function to calculate all totals
    function calculateTotals() {
        let subtotal = 0;
        $('input[class*="row-total"]').each(function() {
            let val = parseFloat($(this).val()) || 0;
            subtotal += val;
        });
        
        $('#subtotal').text(subtotal.toFixed(2));
        $('#total-amount').text(subtotal.toFixed(2));
    }

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        let index = $(this).data('index');
        $(`#item-row-${index}`).remove();
        calculateTotals();
    });

    // Form validation before submit
    $('#purchase-return-form').on('submit', function(e) {
        let hasItems = $('#items-body tr').length > 0;
        
        if (!hasItems) {
            e.preventDefault();
            alert('{{ __("Please add at least one item to the return.") }}');
            return false;
        }
        
        // Validate each row has product selected
        let isValid = true;
        $('select[name*="[product_id]"]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('{{ __("Please select a product for each row.") }}');
            return false;
        }
    });

    // Initialize calculations for existing rows
    $(document).ready(function() {
        calculateTotals();
    });
</script>
@endpush