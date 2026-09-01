@extends('layouts.admin')
@section('page-title')
    {{__('Edit Product')}}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            {!! Form::model($productService, ['route' => ['productservice.update', $productService->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', '@submit.prevent' => 'onSubmit']) !!}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}
                                {!! Form::text('name', null, ['class' => 'form-control font-style', 'required' => 'required']) !!}
                                @error('name')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! Form::label('sku', __('SKU'), ['class' => 'form-label']) !!}
                                {!! Form::text('sku', null, ['class' => 'form-control font-style']) !!}
                                @error('sku')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! Form::label('category_id', __('Category'), ['class' => 'form-label']) !!}
                                {!! Form::select('category_id', $category, null, ['class' => 'form-control select2', 'required' => 'required']) !!}
                                @error('category_id')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! Form::label('unit_id', __('Unit'), ['class' => 'form-label']) !!}
                                {!! Form::select('unit_id', $unit, null, ['class' => 'form-control select2', 'required' => 'required']) !!}
                                @error('unit_id')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                {!! Form::label('sale_price', __('Sale Price'), ['class' => 'form-label']) !!}
                                {!! Form::number('sale_price', null, ['class' => 'form-control font-style', 'step' => '0.01']) !!}
                                @error('sale_price')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                {!! Form::label('purchase_price', __('Purchase Price'), ['class' => 'form-label']) !!}
                                {!! Form::number('purchase_price', null, ['class' => 'form-control font-style', 'step' => '0.01']) !!}
                                @error('purchase_price')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                {!! Form::label('quantity', __('Stock Quantity'), ['class' => 'form-label']) !!}
                                {!! Form::number('quantity', null, ['class' => 'form-control font-style', 'min' => '0']) !!}
                                @error('quantity')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- PRODUCT IMAGE UPLOAD WITH PREVIEW --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! Form::label('pro_image', __('Product Image'), ['class' => 'form-label']) !!}
                                <div class="position-relative">
                                    <div class="pos-relative">
                                        {!! Form::file('pro_image', ['class' => 'form-control', 'accept' => 'image/*', 'id' => 'pro_image']) !!}
                                        <label class="filepos" for="pro_image">
                                            <i class="ti ti-upload"></i>
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">{{__('JPG, PNG, Max 2MB (Leave empty to keep current image)')}}</small>
                                @error('pro_image')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- CURRENT IMAGE PREVIEW --}}
                            @if($productService->pro_image)
                                <div class="current-image-preview mb-3 p-3 border rounded bg-light">
                                    <strong>{{__('Current Image')}}:</strong>
                                    <div class="mt-2">
                                        <img src="{{ $productService->getImageUrl() }}?v={{ time() }}" 
                                             alt="Current Product Image" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px; max-width: 100%;">
                                    </div>
                                </div>
                            @endif

                            {{-- NEW IMAGE PREVIEW --}}
                            <div class="image-preview-container">
                                <div id="image-preview" class="image-preview border rounded p-3 bg-light d-none">
                                    <strong>{{__('New Image Preview')}}:</strong>
                                    <img id="preview-img" src="" alt="Preview" class="img-thumbnail mx-auto mt-2" style="max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-image" style="display: none;">
                                        <i class="ti ti-trash"></i> {{__('Remove')}}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! Form::label('type', __('Type'), ['class' => 'form-label']) !!}
                                {!! Form::select('type', ['product' => 'Product', 'service' => 'Service'], null, ['class' => 'form-control select2', 'required' => 'required']) !!}
                                @error('type')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                {!! Form::label('description', __('Description'), ['class' => 'form-label']) !!}
                                {!! Form::textarea('description', null, ['class' => 'form-control font-style', 'rows' => 3]) !!}
                                @error('description')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @if(isset($customFields) && count($customFields) > 0)
                            <div class="col-md-12">
                                <div class="row">
                                    @foreach($customFields as $customField)
                                        <div class="col-md-{{(12/count($customFields))}}">
                                            {!! $customField->view($productService) !!}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <div class="col-12 text-end">
                        <input type="submit" value="{{__('Update Product')}}" class="btn-create btn-primary">
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

@push('css-page')
<link rel="stylesheet" href="{{ asset('assets/css/plugins/select2.min.css') }}">
@endpush

@push('script-page')
<script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const proImageInput = document.getElementById('pro_image');
    const previewContainer = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeBtn = document.querySelector('.remove-image');

    // Image Preview for new upload
    proImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size must be less than 2MB');
                this.value = '';
                return;
            }

            // Validate image type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('d-none');
                removeBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove new image preview
    removeBtn.addEventListener('click', function() {
        proImageInput.value = '';
        previewContainer.classList.add('d-none');
        previewImg.src = '';
        removeBtn.style.display = 'none';
    });

    // Initialize Select2
    $('.select2').select2({
        dropdownParent: $(proImageInput).closest('.modal') || $('body'),
        width: '100%'
    });
});
</script>
@endpush
@endsection

