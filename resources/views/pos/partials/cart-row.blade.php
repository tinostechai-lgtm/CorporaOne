@php
    $product = \App\Models\ProductService::find($item['id']);
@endphp
<tr data-product-id="{{ $id }}" id="product-id-{{ $id }}">
    <td class="cart-images">
        <img alt="Image placeholder"
        src="{{ asset($item['image'] ?? 'uploads/pro_image/default.png') }}"

            class="card-image avatar rounded-circle-sale shadow hover-shadow-lg">
    </td>
    <td class="name">{{ $item['name'] }}</td>
    <td>
        <span class="quantity buttons_added">
            <input type="button" value="-" class="minus">
            <input type="number" step="1" min="1"
                max="" name="quantity"
                title="{{ __('Quantity') }}" class="input-number"
                data-url="{{ url('pos/update-cart') }}"
                data-id="{{ $id }}" size="4"
                value="{{ $item['quantity'] }}" placeholder="{{ __('Enter Quantity') }}">
            <input type="button" value="+" class="plus">
        </span>
    </td>
    <td>
        {!! $item['product_tax'] !!}
    </td>
    <td class="price text-right">
        {{ Auth::user()->priceFormat($item['price']) }}</td>
    <td class="col-sm-3 mt-2">
        <span
            class="subtotal">{{ Auth::user()->priceFormat($item['subtotal']) }}</span>
    </td>
    <td class="col-sm-2 mt-2">
    <div class="action-btn">
        <a href="#" class="btn btn-sm bg-danger bs-pass-para-pos"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $id }}"
            title="{{ __('Delete') }}" data-id="{{ $id }}">
            <i class="ti ti-trash text-white "
                title="{{ __('Delete') }}"></i>
        </a>
        {!! Form::open(['method' => 'delete', 'url' => ['pos/remove-from-cart'], 'id' => 'delete-form-' . $id]) !!}
        <input type="hidden" name="id" value="{{ $id }}">
        {!! Form::close() !!}
    </div>
    </td>
</tr>
