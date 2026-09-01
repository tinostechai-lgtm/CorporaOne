<!-- Products Modal -->
<div class="modal fade" id="productsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Manage Products') }} - {{ $lead->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Current Products -->
                <div class="mb-4">
                    <h6>{{ __('Current Products') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Product Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="currentProductsList">
                                @php $leadProducts = $lead->products()->get(); @endphp
                                @forelse($leadProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ Utility::priceFormat($product->price) }}</td>
                                    <td>
                                        <a href="{{ route('leads.products.remove', [$lead->id, $product->id]) }}" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('{{ __('Remove this product?') }}')">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No products added') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>

                <!-- Add Products -->
                <div>
                    <h6>{{ __('Add Products') }}</h6>
                    <form action="{{ route('leads.products.add', $lead->id) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-8">
                            <select name="products[]" class="form-select" multiple size="5" required>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Hold Ctrl to select multiple products') }}</small>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Add Products') }}</button>
                        </div>
                    </form>
                    
                    @if($products->isEmpty())
                        <div class="alert alert-warning mt-3">
                            <i class="ti ti-alert-triangle"></i>
                            {{ __('No products available. Please create products first.') }}
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-link">{{ __('Create Product') }}</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>