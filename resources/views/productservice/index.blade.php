@extends('layouts.admin')
@section('page-title')
    {{__('Manage Product & Services')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Product & Services')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('productservice.file.import') }}" 
           data-ajax-popup="true" data-title="{{__('Import product CSV file')}}" class="btn btn-sm btn-primary me-1">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('productservice.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary me-1">
            <i class="ti ti-file-export"></i>
        </a>
        <a href="{{ route('productservice.create') }}" data-bs-toggle="tooltip" 
           data-bs-original-title="{{__('Create')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{__('Create')}}
        </a>
    </div>
@endsection

@section('content')
    {{-- Filter Card --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['productservice.index'], 'method' => 'GET', 'id' => 'product_service']) }}
                    <div class="row align-items-center">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="form-group mb-0">
                                {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                                {{ Form::select('category', $category, isset($_GET['category']) ? $_GET['category'] : '', ['class' => 'form-control select', 'id' => 'category', 'placeholder' => __('Select Category')]) }}
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="{{ __('Apply Filter') }}">
                                    <i class="ti ti-search"></i> <span class="d-none d-sm-inline">{{ __('Apply') }}</span>
                                </button>
                                <a href="{{ route('productservice.index') }}" class="btn btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset Filter') }}">
                                    <i class="ti ti-refresh"></i> <span class="d-none d-sm-inline">{{ __('Reset') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Product Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Product & Services List') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="product-table">
                            <thead class="thead-light">
                                <tr>
                                    <th width="15%">{{ __('Name') }}</th>
                                    <th width="8%">{{ __('Image') }}</th>
                                    <th width="10%">{{ __('SKU') }}</th>
                                    <th width="10%">{{ __('Sale Price') }}</th>
                                    <th width="10%">{{ __('Purchase Price') }}</th>
                                    <th width="12%">{{ __('Tax') }}</th>
                                    <th width="10%">{{ __('Category') }}</th>
                                    <th width="8%">{{ __('Unit') }}</th>
                                    <th width="8%">{{ __('Stock') }}</th>
                                    <th width="8%">{{ __('Type') }}</th>
                                    <th width="15%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productServices as $productService)
                                    <tr>
                                        <td class="fw-bold">{{ $productService->name }}</td>
                                        <td class="text-center">
                                            @if($productService->pro_image)
                                                <img src="{{ $productService->getImageUrl() }}" 
                                                     alt="{{ $productService->name }}" 
                                                     class="rounded shadow-sm product-image"
                                                     onerror="this.src='{{ asset('assets/img/product-placeholder.png') }}';">
                                            @else
                                                <div class="product-placeholder">
                                                    <i class="ti ti-package"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $productService->sku ?: '-' }}</span>
                                        </td>
                                        <td class="fw-medium">{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($productService->purchase_price) }}</td>
                                        <td>
                                            @if(!empty($productService->tax_id))
                                                @php
                                                    $taxIds = explode(',', $productService->tax_id);
                                                    $taxes = \App\Models\Tax::whereIn('id', $taxIds)->get();
                                                @endphp
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($taxes as $tax)
                                                        <span class="badge bg-info">{{ $tax->name }} ({{ $tax->rate }}%)</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $productService->category->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $productService->unit->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if($productService->type == 'product')
                                                @php
                                                    $stock = $productService->getTotalProductQuantity();
                                                    if ($stock <= 0) {
                                                        $badgeClass = 'bg-danger';
                                                        $status = __('Out of Stock');
                                                    } elseif ($stock < 10) {
                                                        $badgeClass = 'bg-warning';
                                                        $status = __('Low Stock');
                                                    } else {
                                                        $badgeClass = 'bg-success';
                                                        $status = __('In Stock');
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $stock }} {{ $status }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($productService->type == 'product')
                                                <span class="badge bg-primary">{{ __('Product') }}</span>
                                            @else
                                                <span class="badge bg-info">{{ __('Service') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- Warehouse Details --}}
                                                <a href="#" class="btn btn-sm btn-warning" 
                                                   data-url="{{ route('productservice.detail', $productService->id) }}" 
                                                   data-ajax-popup="true" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('Warehouse Details') }}" 
                                                   data-title="{{ __('Warehouse Details') }}">
                                                    <i class="ti ti-eye"></i>
                                                </a>

                                                {{-- Edit --}}
                                                @can('edit product & service')
                                                <a href="{{ route('productservice.edit', $productService->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('Edit') }}">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                @endcan

                                                {{-- Delete --}}
                                                @can('delete product & service')
                                                <form method="POST" action="{{ route('productservice.destroy', $productService->id) }}" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('{{ __('Are you sure? This action cannot be undone.') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="ti ti-package" style="font-size: 48px; opacity: 0.5;"></i>
                                                <h6 class="mt-3">{{ __('No products found') }}</h6>
                                                <p class="text-muted">{{ __('Create your first product to get started') }}</p>
                                                <a href="{{ route('productservice.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus"></i> {{ __('Create Product') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end mt-4">
                        {{ $productServices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-page')
<style>
    /* Product Image Styles */
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: transform 0.2s;
    }
    
    .product-image:hover {
        transform: scale(1.1);
    }
    
    .product-placeholder {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        margin: 0 auto;
    }
    
    /* Table Styles */
    .table th {
        font-weight: 600;
        color: #495057;
        border-top: none;
        background-color: #f8f9fa;
    }
    
    .table td {
        vertical-align: middle;
        color: #212529;
    }
    
    /* Badge Styles */
    .badge {
        padding: 6px 10px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .badge.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
    }
    
    /* Button Styles */
    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 6px;
    }
    
    .btn-warning {
        color: #fff;
        background-color: #ffc107;
        border-color: #ffc107;
    }
    
    .btn-warning:hover {
        color: #fff;
        background-color: #e0a800;
        border-color: #d39e00;
    }
    
    .btn-info {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    
    .btn-info:hover {
        color: #fff;
        background-color: #138496;
        border-color: #117a8b;
    }
    
    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-danger:hover {
        color: #fff;
        background-color: #c82333;
        border-color: #bd2130;
    }
    
    /* Action Button Group */
    .gap-2 {
        gap: 0.5rem !important;
    }
    
    /* Empty State */
    .empty-state {
        padding: 3rem;
        text-align: center;
    }
    
    .empty-state i {
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .empty-state h6 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
    
    /* Filter Card */
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .select2-container .select2-selection--single {
        height: 40px;
        border-radius: 6px;
        border: 1px solid #ced4da;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
    
    /* Card Styles */
    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }
    
    .card-header h5 {
        margin: 0;
        color: #495057;
        font-weight: 600;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #6777ef;
        border-color: #6777ef;
    }
    
    .page-link {
        color: #6777ef;
        border-radius: 4px;
        margin: 0 2px;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .product-image, .product-placeholder {
            width: 40px;
            height: 40px;
        }
        
        .btn-sm {
            padding: 0.3rem 0.6rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    }
    
    /* DataTable Custom Styles */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 1rem;
    }
    
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 0.375rem 0.75rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 0.375rem 1.5rem 0.375rem 0.75rem;
        margin: 0 0.5rem;
    }
    
    /* Tooltip Styles */
    .tooltip-inner {
        max-width: 200px;
        padding: 0.4rem 0.8rem;
        background-color: #495057;
        border-radius: 4px;
    }
    
    .bs-tooltip-top .arrow::before {
        border-top-color: #495057;
    }
</style>
@endpush

@push('script-page')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#product-table').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "{{__('Search')}}:",
            lengthMenu: "{{__('Show')}} _MENU_ {{__('entries')}}",
            info: "{{__('Showing')}} _START_ {{__('to')}} _END_ {{__('of')}} _TOTAL_ {{__('entries')}}",
            paginate: {
                first: "{{__('First')}}",
                last: "{{__('Last')}}",
                next: "{{__('Next')}}",
                previous: "{{__('Previous')}}"
            },
            emptyTable: "{{__('No data available in table')}}",
            infoEmpty: "{{__('Showing 0 to 0 of 0 entries')}}",
            infoFiltered: "{{__('(filtered from _MAX_ total entries)')}}",
            zeroRecords: "{{__('No matching records found')}}"
        },
        columnDefs: [
            { orderable: false, targets: [1, 10] }, // Disable sorting on image and action columns
            { searchable: false, targets: [1, 10] } // Disable search on image and action columns
        ]
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '{{ __("Are you sure?") }}',
            text: '{{ __("This action cannot be undone!") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("Yes, delete it!") }}',
            cancelButtonText: '{{ __("Cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Auto close alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Handle filter form reset
function resetFilter() {
    window.location.href = '{{ route("productservice.index") }}';
}
</script>
@endpush