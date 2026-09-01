@extends('layouts.admin')

@section('page-title')
{{__('Manage Product Stock')}}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">
<a href="{{route('dashboard')}}">{{__('Dashboard')}}</a>
</li>
<li class="breadcrumb-item">{{__('Product Stock')}}</li>
@endsection

@section('action-btn')
<div class="float-end">
<a href="{{ route('productservice.create') }}" class="btn btn-sm btn-primary">
<i class="ti ti-plus"></i> {{__('Add Product')}}
</a>
</div>
@endsection

@section('content')

<div class="row">
<div class="col-12">

<div class="card">

<div class="card-header">
<h5>{{ __('Product Stock Management') }}</h5>
</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover">

<thead>
<tr>
<th>{{ __('Product') }}</th>
<th>{{ __('SKU') }}</th>
<th>{{ __('Main Stock') }}</th>
<th>{{ __('Warehouse Stock') }}</th>
<th>{{ __('Total Stock') }}</th>
<th>{{ __('Status') }}</th>
<th>{{ __('Last Updated') }}</th>
<th>{{ __('Action') }}</th>
</tr>
</thead>

<tbody>

@forelse ($productServices as $product)

<tr>

<td class="fw-bold">
<div class="d-flex align-items-center">

@if($product->pro_image)
<img src="{{ $product->getImageUrl() }}"
style="width:40px;height:40px;object-fit:cover"
class="rounded me-2">
@endif

{{ $product->name }}

</div>
</td>

<td>
<span class="badge bg-light text-dark">
{{ $product->sku ?: '-' }}
</span>
</td>

<td class="text-center">
<span class="badge bg-info">
{{ $product->main_stock }}
</span>
</td>

<td class="text-center">
<span class="badge bg-info">
{{ $product->warehouse_stock }}
</span>
</td>

<td class="text-center">
<span class="badge bg-primary">
{{ $product->total_stock }}
</span>
</td>

<td>

@if($product->stock_status == 'out')
<span class="badge bg-danger">
{{ __('Out of Stock') }}
</span>

@elseif($product->stock_status == 'low')
<span class="badge bg-warning">
{{ __('Low Stock') }}
</span>

@else
<span class="badge bg-success">
{{ __('In Stock') }}
</span>

@endif

</td>

<td>
{{ $product->updated_at ? $product->updated_at->format('Y-m-d H:i') : '-' }}
</td>

<td>

<a href="#"
data-url="{{ route('productstock.edit',$product->id) }}"
data-ajax-popup="true"
data-size="lg"
class="btn btn-sm btn-info"
title="{{__('Update Stock')}}">

<i class="ti ti-edit"></i> {{__('Update')}}

</a>

</td>

</tr>

@empty

<tr>

<td colspan="8" class="text-center py-5">

<i class="ti ti-package" style="font-size:50px"></i>

<h6 class="mt-3">{{ __('No products found') }}</h6>

<a href="{{ route('productservice.create') }}"
class="btn btn-primary mt-3">

<i class="ti ti-plus"></i>
{{ __('Add Product') }}

</a>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>
</div>
</div>
</div>

@endsection