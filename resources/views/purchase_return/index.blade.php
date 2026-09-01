{{-- resources/views/purchase_return/index.blade.php --}}
@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Returns') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Returns') }}</li>
@endsection

@section('action-btn')
    @can('create purchase return')
        <a href="{{ route('purchase-return.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{ __('Create Return') }}
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <select name="vender" class="form-control select" id="vender">
                                    <option value="">{{ __('Select Vendor') }}</option>
                                    @foreach($venders as $id => $vendor)
                                        <option value="{{ $vendor }}" {{ request('vender') == $vendor ? 'selected' : '' }}>{{ $vendor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select name="status" class="form-control select" id="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    @foreach($status as $key => $stat)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $stat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="date" name="date" class="form-control" id="date" value="{{ request('date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="filter-btn">
                                    <i class="ti ti-filter"></i> {{ __('Filter') }}
                                </button>
                                <a href="{{ route('purchase-return.index') }}" class="btn btn-secondary">
                                    <i class="ti ti-refresh"></i> {{ __('Reset') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table datatable" id="purchase-return-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Return #') }}</th>
                                    <th>{{ __('Supplier') }}</th>
                                    <th>{{ __('Return Date') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseReturns as $purchaseReturn)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>#{{ $purchaseReturn->id }}</td>
                                        <td>{{ $purchaseReturn->supplier }}</td>
                                        <td>{{ \Auth::user()->dateFormat($purchaseReturn->return_date) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($purchaseReturn->total_amount) }}</td>
                                        <td>
                                            @php
                                                $statusClass = match($purchaseReturn->status) {
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'completed' => 'success',
                                                    'rejected' => 'danger',
                                                    'cancelled' => 'secondary',
                                                    default => 'warning'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }} p-2">
                                                {{ ucfirst($purchaseReturn->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ti ti-more-alt"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @can('show purchase return')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('purchase-return.show', Crypt::encrypt($purchaseReturn->id)) }}">
                                                                <i class="ti ti-eye text-primary me-2"></i>{{ __('View') }}
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @if($purchaseReturn->status == 'pending' && Auth::user()->can('edit purchase return'))
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('purchase-return.edit', Crypt::encrypt($purchaseReturn->id)) }}">
                                                                <i class="ti ti-edit text-warning me-2"></i>{{ __('Edit') }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if($purchaseReturn->status == 'pending' && Auth::user()->can('edit purchase return'))
                                                        <li>
                                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#statusModal{{ $purchaseReturn->id }}">
                                                                <i class="ti ti-check text-success me-2"></i>{{ __('Update Status') }}
                                                            </button>
                                                        </li>
                                                    @endif

                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('purchase-return.print', Crypt::encrypt($purchaseReturn->id)) }}" target="_blank">
                                                            <i class="ti ti-printer text-info me-2"></i>{{ __('Print') }}
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('purchase-return.pdf', Crypt::encrypt($purchaseReturn->id)) }}" target="_blank">
                                                            <i class="ti ti-file-text text-secondary me-2"></i>{{ __('PDF') }}
                                                        </a>
                                                    </li>

                                                    @can('delete purchase return')
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['purchase-return.destroy', Crypt::encrypt($purchaseReturn->id)],
                                                                'class' => 'd-inline',
                                                                'onsubmit' => 'return confirm("Are you sure you want to delete this return?")'
                                                            ]) !!}
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="ti ti-trash me-2"></i>{{ __('Delete') }}
                                                                </button>
                                                            {!! Form::close() !!}
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Status Update Modal --}}
                                    @if($purchaseReturn->status == 'pending' && Auth::user()->can('edit purchase return'))
                                        <div class="modal fade" id="statusModal{{ $purchaseReturn->id }}" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel{{ $purchaseReturn->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    {!! Form::open([
                                                        'route' => ['purchase-return.update.status', $purchaseReturn->id],
                                                        'method' => 'POST'
                                                    ]) !!}
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="statusModalLabel{{ $purchaseReturn->id }}">
                                                                {{ __('Update Return Status') }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                                                {{ Form::select('status', [
                                                                    'approved' => 'Approved',
                                                                    'completed' => 'Completed',
                                                                    'rejected' => 'Rejected',
                                                                    'cancelled' => 'Cancelled'
                                                                ], null, [
                                                                    'class' => 'form-control',
                                                                    'required' => true,
                                                                    'placeholder' => 'Select Status'
                                                                ]) }}
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                {{ __('Cancel') }}
                                                            </button>
                                                            {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
                                                        </div>
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="p-3">
                                                <i class="ti ti-package fs-5"></i>
                                                <p>{{ __('No purchase returns found.') }}</p>
                                                @can('create purchase return')
                                                    <a href="{{ route('purchase-return.create') }}" class="btn btn-primary btn-sm">
                                                        {{ __('Create Your First Return') }}
                                                    </a>
                                                @endcan
                                            </div>
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

@push('script-page')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        if ($.fn.DataTable) {
            $('#purchase-return-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/{{ Auth::user()->lang }}.json'
                },
                order: [[1, 'desc']], // Sort by Return # descending
                pageLength: 25
            });
        }

        // Filter functionality
        $('#filter-btn').on('click', function() {
            let url = new URL(window.location.href);
            
            let vender = $('#vender').val();
            let status = $('#status').val();
            let date = $('#date').val();
            
            if (vender) {
                url.searchParams.set('vender', vender);
            } else {
                url.searchParams.delete('vender');
            }
            
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            
            if (date) {
                url.searchParams.set('date', date);
            } else {
                url.searchParams.delete('date');
            }
            
            window.location.href = url.toString();
        });

        // Delete confirmation
        $('.delete-return').on('click', function(e) {
            e.preventDefault();
            let url = $(this).data('url');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete-form-' + $(this).data('id')).submit();
                }
            });
        });
    });
</script>
@endpush