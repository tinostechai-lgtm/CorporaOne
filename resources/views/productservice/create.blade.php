<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Create Product') }}</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/select2.min.css') }}">

    <style>
        body {
            background: #f4f6f9;
        }

        .page-container {
            max-width: 1200px;
            margin: auto;
            padding: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .required:after {
            content: " *";
            color: #dc3545;
        }

        #image-preview-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            background: #f8f9fa;
            border: 2px dashed #ced4da;
            border-radius: 8px;
            overflow: hidden;
        }

        #preview-img {
            max-height: 170px;
            max-width: 100%;
            object-fit: contain;
        }

        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="page-container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ __('Create Product') }}</h4>
        <a href="{{ route('productservice.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            {!! Form::open([
                'route' => 'productservice.store',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'id' => 'product-form'
            ]) !!}

            <div class="row g-3">

                <!-- Product Name -->
                <div class="col-md-6">
                    {!! Form::label('name', __('Product Name'), ['class' => 'form-label required']) !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Enter product name')]) !!}
                </div>

                <!-- SKU -->
                <div class="col-md-6">
                    {!! Form::label('sku', __('SKU'), ['class' => 'form-label']) !!}
                    {!! Form::text('sku', null, ['class' => 'form-control', 'placeholder' => __('Enter SKU (optional)')]) !!}
                </div>

                <!-- Category -->
                <div class="col-md-6">
                    {!! Form::label('category_id', __('Category'), ['class' => 'form-label required']) !!}
                    {!! Form::select('category_id', $category, null, [
                        'class' => 'form-control select2',
                        'required' => true,
                        'placeholder' => __('Select Category')
                    ]) !!}
                </div>

                <!-- Unit -->
                <div class="col-md-6">
                    {!! Form::label('unit_id', __('Unit'), ['class' => 'form-label required']) !!}
                    {!! Form::select('unit_id', $unit, null, [
                        'class' => 'form-control select2',
                        'required' => true,
                        'placeholder' => __('Select Unit')
                    ]) !!}
                </div>

                <!-- Product Type -->
                <div class="col-md-6">
                    {!! Form::label('type', __('Product Type'), ['class' => 'form-label required']) !!}
                    {!! Form::select('type', [
                        'product' => __('Physical Product'),
                        'service' => __('Service')
                    ], null, ['class' => 'form-control', 'id' => 'product-type']) !!}
                </div>

                <!-- Stock Quantity -->
                <div class="col-md-6">
                    {!! Form::label('quantity', __('Stock Quantity'), ['class' => 'form-label']) !!}
                    {!! Form::number('quantity', 0, [
                        'class' => 'form-control',
                        'id' => 'quantity',
                        'min' => '0'
                    ]) !!}
                </div>

                <!-- Sale Price -->
                <div class="col-md-6">
                    {!! Form::label('sale_price', __('Sale Price'), ['class' => 'form-label']) !!}
                    {!! Form::number('sale_price', null, [
                        'class' => 'form-control',
                        'step' => '0.01',
                        'min' => '0',
                        'placeholder' => '0.00'
                    ]) !!}
                </div>

                <!-- Purchase Price -->
                <div class="col-md-6">
                    {!! Form::label('purchase_price', __('Purchase Price'), ['class' => 'form-label']) !!}
                    {!! Form::number('purchase_price', null, [
                        'class' => 'form-control',
                        'step' => '0.01',
                        'min' => '0',
                        'placeholder' => '0.00'
                    ]) !!}
                </div>

                <!-- Product Image -->
                <div class="col-12">
                    {!! Form::label('pro_image', __('Product Image'), ['class' => 'form-label']) !!}
                    <input type="file" name="pro_image" id="pro_image" class="form-control" accept="image/*">

                    <div id="image-preview-container" class="mt-3">
                        <img id="preview-img" class="d-none" alt="Preview">
                        <span id="preview-text" class="text-muted">No image selected</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-12">
                    {!! Form::label('description', __('Description'), ['class' => 'form-label']) !!}
                    {!! Form::textarea('description', null, [
                        'class' => 'form-control',
                        'rows' => 5,
                        'placeholder' => __('Enter product description...')
                    ]) !!}
                </div>

                <!-- Buttons -->
                <div class="col-12 text-end mt-4">
                    <a href="{{ route('productservice.index') }}" class="btn btn-light me-2">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save"></i> {{ __('Create Product') }}
                    </button>
                </div>

            </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>

<script>
    // Initialize Select2
    $('.select2').select2({
        theme: "bootstrap-5",
        width: '100%'
    });

    // Image Preview
    document.getElementById('pro_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const img = document.getElementById('preview-img');
        const text = document.getElementById('preview-text');
        const container = document.getElementById('image-preview-container');

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.remove('d-none');
                text.style.display = 'none';
            };

            reader.readAsDataURL(file);
        } else {
            img.classList.add('d-none');
            text.style.display = 'block';
        }
    });

    // Optional: Disable quantity field for Services
    document.getElementById('product-type').addEventListener('change', function() {
        const quantityField = document.getElementById('quantity');
        if (this.value === 'service') {
            quantityField.value = 0;
            quantityField.disabled = true;
        } else {
            quantityField.disabled = false;
        }
    });
</script>

</body>
</html>