@php
    $logo = \App\Models\Utility::get_file('uploads/logo');
    $company_favicon = Utility::getValByName('company_favicon');
    $SITE_RTL = Utility::getValByName('SITE_RTL');
    $setting = \App\Models\Utility::colorset();
    $color = 'theme-3';
    if (!empty($setting['color'])) {
        $color = $setting['color'];
    }

    if (isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $themeColor = 'theme-3';
    } else {
        $themeColor = $color;
    }
    
    $lastsegment = request()->segment(count(request()->segments()));
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ !empty($companySettings['header_text']) ? $companySettings['header_text']->value : config('app.name', 'ERPGO SaaS') }} - {{ __('POS') }}</title>

    <link rel="icon" href="{{ asset(Storage::url('uploads/logo/')) . '/' . (isset($companySettings['company_favicon']) && !empty($companySettings['company_favicon']) ? $companySettings['company_favicon']->value : 'favicon.png') }}" type="image" sizes="16x16">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    <!--bootstrap switch-->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">

    <!-- vendor css -->
    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif
    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="main-style-link">

    <style>
        .bg-color { background: linear-gradient(141.55deg, #4f46e5 3.46%, #7c3aed 99.86%); }
        .product-listing { max-height: 500px; overflow-y: auto; padding: 10px; }
        .carttable-scroll { max-height: 400px; overflow-y: auto; }
        .toacart { cursor: pointer; transition: all 0.2s ease; }
        .toacart:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
        .cat-active { background: #4f46e5; border-radius: 8px; }
        .category-select { cursor: pointer; padding: 8px 12px; margin: 4px; border-radius: 8px; transition: all 0.2s ease; }
        .category-select:hover { background: #f3f4f6; }
        .cat-active .category-select { color: white; }
        .cat-active .category-select:hover { background: #4f46e5; }
        
        .quantity {
            display: inline-flex;
            align-items: center;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .quantity .qty-minus,
        .quantity .qty-plus {
            width: 32px;
            height: 32px;
            background: #f9fafb;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            color: #374151;
        }
        
        .quantity .qty-minus:hover,
        .quantity .qty-plus:hover { background: #e5e7eb; }
        
        .quantity .qty-input {
            width: 50px;
            height: 32px;
            text-align: center;
            border: none;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }
        
        .quantity .qty-input::-webkit-outer-spin-button,
        .quantity .qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .quantity .qty-input[type=number] { -moz-appearance: textfield; }
        
        .cart-images {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .remove-item {
            background: #ef4444;
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .remove-item:hover { background: #dc2626; }
        
        .btn-primary {
            background: #4f46e5;
            border: none;
        }
        
        .btn-primary:hover { background: #4338ca; }
        .btn-primary:disabled { background: #9ca3af; cursor: not-allowed; }
        
        .btn-outline-danger {
            border: 1px solid #ef4444;
            color: #ef4444;
        }
        
        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
        }
        
        .btn-outline-danger:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body class="{{ $themeColor }}">
    <div class="container-fluid px-2">
        <div class="row">
            <div class="col-12">
                <div class="mt-2 pos-top-bar bg-color d-flex justify-content-between align-items-center p-3 rounded">
                    <span class="text-white h5 mb-0"><i class="ti ti-shopping-cart me-2"></i>{{ __('POS') }}</span>
                    <a href="{{ route('dashboard') }}" class="text-white"><i class="ti ti-home"></i></a>
                </div>
            </div>
        </div>
        
        <div class="mt-3 row">
            <!-- Left side - Products -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text" id="searchproduct" data-url="{{ route('search.products') }}" placeholder="{{ __('Search by Name') }}" class="form-control" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" id="barcodeScanner" data-url="{{ route('search.products') }}" placeholder="{{ __('Search by SKU') }}" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <div class="d-flex flex-wrap" id="categories-listing"></div>
                        </div>
                        
                        <div class="product-listing">
                            <div class="row g-3" id="product-listing">
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Cart -->
            <div class="col-lg-5 ps-lg-0">
                <div class="card">
                    <div class="card-header p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select class="form-select" id="customer">
                                    <option value="">{{ __('Select Customer') }}</option>
                                    @foreach($customers as $key => $value)
                                        <option value="{{ $key }}" {{ $customer == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="vc_name_hidden" value="{{ $customer }}">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="warehouse">
                                    <option value="">{{ __('Select Warehouse') }}</option>
                                    @foreach($warehouses as $key => $value)
                                        <option value="{{ $key }}" {{ $warehouseId == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="warehouse_name_hidden" value="{{ $warehouseId }}">
                                <input type="hidden" id="quotation_id" value="{{ $id }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-3 carttable-scroll">
                        @php
                            $total = 0;
                            $cart = session($lastsegment, []);
                        @endphp
                        
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50"></th>
                                    <th>{{ __('Name') }}</th>
                                    <th width="120" class="text-center">{{ __('QTY') }}</th>
                                    <th width="80" class="text-end">{{ __('Price') }}</th>
                                    <th width="100" class="text-end">{{ __('Total') }}</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                @if(!empty($cart) && count($cart) > 0)
                                    @foreach($cart as $id => $details)
                                        @php
                                            $total += $details['subtotal'];
                                        @endphp
                                        <tr data-product-id="{{ $id }}" id="cart-row-{{ $id }}">
                                            <td>
                                                <img src="{{ asset($details['image'] ?? 'uploads/pro_image/default.png') }}" class="cart-images">
                                            </td>
                                            <td class="fw-medium">{{ $details['name'] }}</td>
                                            <td>
                                                <div class="quantity">
                                                    <button type="button" class="qty-minus" data-id="{{ $id }}">−</button>
                                                    <input type="number" class="qty-input" value="{{ $details['quantity'] }}" min="1" data-id="{{ $id }}">
                                                    <button type="button" class="qty-plus" data-id="{{ $id }}">+</button>
                                                </div>
                                            </td>
                                            <td class="text-end fw-medium price">{{ \Auth::user()->priceFormat($details['price']) }}</td>
                                            <td class="text-end fw-bold subtotal" id="subtotal-{{ $id }}">{{ \Auth::user()->priceFormat($details['subtotal']) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="remove-item" data-id="{{ $id }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="text-center" id="empty-cart-message">
                                        <td colspan="6" class="py-5">
                                            <i class="ti ti-shopping-cart-off fs-1 text-secondary"></i>
                                            <h5 class="mt-2 text-secondary">{{ __('No items in cart') }}</h5>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        
                        <div class="bg-light p-3 rounded mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('Sub Total') }}:</span>
                                <span class="fw-bold" id="cart-subtotal">{{ \Auth::user()->priceFormat($total) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>{{ __('Discount') }}:</span>
                                <div class="d-flex align-items-center" style="width: 150px;">
                                    <span class="me-2">{{ \Auth::user()->currencySymbol() }}</span>
                                    <input type="number" class="form-control form-control-sm" id="discount" value="0" min="0" step="0.01">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between pt-3 border-top">
                                <h5>{{ __('Total') }}:</h5>
                                <h5 id="cart-total">{{ \Auth::user()->priceFormat($total) }}</h5>
                            </div>
                            
                            <div class="d-grid gap-2 mt-3">
                                <button type="button" class="btn btn-primary btn-lg" id="payButton" {{ empty($cart) ? 'disabled' : '' }}>
                                    <i class="ti ti-credit-card me-2"></i>{{ __('PAY') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="clearCartBtn" {{ empty($cart) ? 'disabled' : '' }}>
                                    <i class="ti ti-trash me-2"></i>{{ __('Empty Cart') }}
                                </button>
                            </div>
                            
                            <form method="post" action="{{ url('empty-cart') }}" id="delete-form-emptycart" style="display: none;">
                                @csrf
                                <input type="hidden" name="session_key" value="{{ $lastsegment }}">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="toast" class="toast text-white fade" role="alert">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

    <script>
        // Get CSRF token from meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        // Set up AJAX to always include CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        var session_key = '{{ $lastsegment }}';
        var currencySymbol = '{{ \Auth::user()->currencySymbol() }}';
        var updateCartUrl = '{{ url("/update-cart") }}';
        var removeFromCartUrl = '{{ url("/remove-from-cart") }}';
        var emptyCartUrl = '{{ url("/warehouse-empty-cart") }}';
        var posCreateUrl = '{{ route("pos.create") }}';
        var posStoreUrl = '{{ route("pos.store") }}';
        var searchUrl = '{{ route("search.products") }}';
        var categoriesUrl = '{{ route("product.categories") }}';

        $(document).ready(function() {
            console.log('POS page loaded with session key:', session_key);
            
            loadCategories();
            
            // Initial product load with warehouse
            var initialWarehouse = $('#warehouse').val() || '0';
            if (initialWarehouse) {
                loadProducts('', '0', initialWarehouse);
            }
            
            // Warehouse change
            $('#warehouse').change(function() {
                var warehouseId = $(this).val();
                $('#warehouse_name_hidden').val(warehouseId);
                loadProducts('', '0', warehouseId);
            });
            
            $('#customer').change(function() {
                $('#vc_name_hidden').val($(this).val());
            });
            
            // Search products
            $('#searchproduct, #barcodeScanner').on('keyup', debounce(function() {
                var search = $(this).val();
                var type = $(this).attr('id') === 'barcodeScanner' ? 'sku' : 'name';
                var warehouseId = $('#warehouse').val() || '0';
                var catId = $('.cat-active').length ? $('.cat-active').children().data('cat-id') : '0';
                loadProducts(search, catId, warehouseId, type);
            }, 500));
            
            // Category click
            $(document).on('click', '.category-select', function() {
                var catId = $(this).data('cat-id');
                $('.category-select').parent().removeClass('cat-active');
                $(this).parent().addClass('cat-active');
                var warehouseId = $('#warehouse').val() || '0';
                var search = $('#searchproduct').val() || '';
                loadProducts(search, catId, warehouseId, 'name');
            });
            
            // Add to cart
            $(document).on('click', '.toacart', function() {
                var url = $(this).data('url');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.code == 200 && response.product) {
                            addToCartTable(response.product);
                            updateCartTotals();
                            enableButtons();
                            showToast('success', response.success || 'Product added to cart');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error adding to cart:', xhr);
                        showToast('error', 'Error adding to cart');
                    }
                });
            });
            
            // Quantity plus
            $(document).on('click', '.qty-plus', function() {
                var id = $(this).data('id');
                var input = $(this).siblings('.qty-input');
                var newVal = parseInt(input.val()) + 1;
                input.val(newVal);
                updateQuantity(id, newVal);
            });
            
            // Quantity minus
            $(document).on('click', '.qty-minus', function() {
                var id = $(this).data('id');
                var input = $(this).siblings('.qty-input');
                var currentVal = parseInt(input.val());
                if (currentVal > 1) {
                    var newVal = currentVal - 1;
                    input.val(newVal);
                    updateQuantity(id, newVal);
                }
            });
            
            // Manual quantity change
            $(document).on('change', '.qty-input', function() {
                var id = $(this).data('id');
                var quantity = parseInt($(this).val());
                if (isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                    $(this).val(1);
                }
                updateQuantity(id, quantity);
            });
            
            // Remove item
            $(document).on('click', '.remove-item', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This item will be removed from cart',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: removeFromCartUrl,
                            type: 'DELETE',
                            data: { id: id, session_key: session_key },
                            success: function(response) {
                                if (response.code == 200) {
                                    $('#cart-row-' + id).fadeOut(300, function() {
                                        $(this).remove();
                                        updateCartTotals();
                                        if ($('#cart-items tr').length === 0) {
                                            showEmptyCart();
                                            disableButtons();
                                        }
                                    });
                                    showToast('success', response.success || 'Item removed');
                                }
                            },
                            error: function(xhr) {
                                console.error('Error removing item:', xhr);
                                showToast('error', 'Error removing item');
                            }
                        });
                    }
                });
            });
            
            // Discount change
            $('#discount').on('keyup change', function() {
                updateCartTotals();
            });
            
            // Clear cart
            $('#clearCartBtn').click(function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Remove all items from cart?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, empty it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: emptyCartUrl,
                            type: 'POST',
                            data: { session_key: session_key },
                            success: function(response) {
                                if (response.success) {
                                    $('#cart-items').empty();
                                    showEmptyCart();
                                    $('#cart-subtotal').text(currencySymbol + '0.00');
                                    $('#cart-total').text(currencySymbol + '0.00');
                                    $('#discount').val(0);
                                    disableButtons();
                                    showToast('success', 'Cart cleared successfully');
                                }
                            },
                            error: function(xhr) {
                                console.error('Error clearing cart:', xhr);
                                showToast('error', 'Error clearing cart');
                            }
                        });
                    }
                });
            });
            
            // Pay button handler
            $('#payButton').click(function(e) {
                e.preventDefault();
                if ($(this).is(':disabled')) return;
                
                var customer = $('#customer').val();
                var warehouse = $('#warehouse').val();
                
                if (!customer) {
                    showToast('error', 'Please select a customer');
                    $('#customer').focus();
                    return;
                }
                
                if (!warehouse) {
                    showToast('error', 'Please select a warehouse');
                    $('#warehouse').focus();
                    return;
                }
                
                // Check if cart is empty
                if ($('#cart-items tr').length === 0 || $('#cart-items tr#empty-cart-message').length > 0) {
                    showToast('error', 'No items in cart');
                    return;
                }
                
                var $btn = $(this);
                var originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
                
                // Get discount value properly
                var discountValue = parseFloat($('#discount').val()) || 0;
                
                $.ajax({
                    url: posCreateUrl,
                    type: 'GET',
                    data: {
                        vc_name: $('#vc_name_hidden').val(),
                        warehouse_name: $('#warehouse_name_hidden').val(),
                        discount: discountValue,
                        quotation_id: $('#quotation_id').val() || 0
                    },
                    success: function(data) {
                        $btn.prop('disabled', false).html(originalText);
                        
                        // Check if response is HTML
                        if (typeof data === 'string' && data.trim().startsWith('<')) {
                            $('#commonModal .modal-title').html('POS Invoice');
                            $('#commonModal .modal-body').html(data);
                            $('#commonModal').modal('show');
                        } else if (data.error) {
                            showToast('error', data.error);
                        } else {
                            console.error('Unexpected response:', data);
                            showToast('error', 'Invalid response format');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(originalText);
                        
                        var errorMessage = 'Error loading invoice';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.status === 404) {
                            errorMessage = 'No items in cart';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error';
                        }
                        
                        showToast('error', errorMessage);
                        console.error('AJAX Error:', xhr);
                    }
                });
            });
        });

        function loadCategories() {
            $.ajax({
                url: categoriesUrl,
                type: 'GET',
                success: function(data) {
                    $('#categories-listing').html(data);
                },
                error: function(xhr) {
                    console.error('Error loading categories:', xhr);
                }
            });
        }

        function loadProducts(search, category, warehouse, type = 'name') {
            $('#product-listing').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');
            
            $.ajax({
                url: searchUrl,
                type: 'GET',
                data: {
                    search: search,
                    cat_id: category,
                    war_id: warehouse,
                    session_key: session_key,
                    type: type
                },
                success: function(data) {
                    $('#product-listing').html(data);
                },
                error: function(xhr) {
                    console.error('Error loading products:', xhr);
                    $('#product-listing').html('<div class="col-12 text-center py-5 text-danger">Error loading products</div>');
                }
            });
        }

        function addToCartTable(product) {
            $('#empty-cart-message').remove();
            
            var existingRow = $('#cart-row-' + product.id);
            
            if (existingRow.length) {
                var input = existingRow.find('.qty-input');
                var newQty = parseInt(input.val()) + 1;
                input.val(newQty);
                updateQuantity(product.id, newQty);
            } else {
                var imageUrl = product.image || '{{ asset("uploads/pro_image/default.png") }}';
                var row = `
                    <tr data-product-id="${product.id}" id="cart-row-${product.id}">
                        <td><img src="${imageUrl}" class="cart-images"></td>
                        <td class="fw-medium">${product.name}</td>
                        <td>
                            <div class="quantity">
                                <button type="button" class="qty-minus" data-id="${product.id}">−</button>
                                <input type="number" class="qty-input" value="1" min="1" data-id="${product.id}">
                                <button type="button" class="qty-plus" data-id="${product.id}">+</button>
                            </div>
                        </td>
                        <td class="text-end fw-medium">${currencySymbol}${parseFloat(product.price).toFixed(2)}</td>
                        <td class="text-end fw-bold subtotal" id="subtotal-${product.id}">${currencySymbol}${parseFloat(product.subtotal).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="remove-item" data-id="${product.id}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#cart-items').append(row);
            }
        }

        function updateQuantity(id, quantity) {
            $.ajax({
                url: updateCartUrl,
                type: 'PATCH',
                data: {
                    id: id,
                    quantity: quantity,
                    session_key: session_key
                },
                success: function(response) {
                    if (response.code == 200) {
                        $('#subtotal-' + id).text(currencySymbol + parseFloat(response.product.subtotal).toFixed(2));
                        updateCartTotals();
                    }
                },
                error: function(xhr) {
                    console.error('Error updating quantity:', xhr);
                    if (xhr.status === 419) {
                        showToast('error', 'Session expired. Please refresh the page.');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showToast('error', 'Error updating quantity');
                    }
                }
            });
        }

        function updateCartTotals() {
            var subtotal = 0;
            $('.subtotal').each(function() {
                var val = parseFloat($(this).text().replace(currencySymbol, '')) || 0;
                subtotal += val;
            });
            
            var discount = parseFloat($('#discount').val()) || 0;
            if (discount > subtotal) {
                discount = subtotal;
                $('#discount').val(discount);
            }
            
            var total = subtotal - discount;
            $('#cart-subtotal').text(currencySymbol + subtotal.toFixed(2));
            $('#cart-total').text(currencySymbol + total.toFixed(2));
        }

        function showEmptyCart() {
            $('#cart-items').html(`
                <tr class="text-center" id="empty-cart-message">
                    <td colspan="6" class="py-5">
                        <i class="ti ti-shopping-cart-off fs-1 text-secondary"></i>
                        <h5 class="mt-2 text-secondary">{{ __('No items in cart') }}</h5>
                    </td>
                </tr>
            `);
        }

        function enableButtons() {
            $('#payButton, #clearCartBtn').removeAttr('disabled');
        }

        function disableButtons() {
            $('#payButton, #clearCartBtn').attr('disabled', 'disabled');
        }

        function showToast(type, message) {
            var toast = $('#toast');
            toast.removeClass('bg-success bg-danger');
            toast.addClass(type === 'success' ? 'bg-success' : 'bg-danger');
            toast.find('.toast-body').text(message);
            var bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>