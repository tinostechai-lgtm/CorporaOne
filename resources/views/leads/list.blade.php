@extends('layouts.app')

@section('page-title', __('Lead List'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('Leads') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col">
                        <h5>{{ __('Lead List') }}</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-layout-kanban"></i> {{ __('Kanban View') }}
                        </a>
                        @can('create lead')
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                            <i class="ti ti-plus"></i> {{ __('New Lead') }}
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Leads') }}</h5>
                                <h2 class="mb-0">{{ $totalLeads }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Assigned') }}</h5>
                                <h2 class="mb-0">{{ $assignedLeads ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Unassigned') }}</h5>
                                <h2 class="mb-0">{{ $unassignedLeads ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Converted') }}</h5>
                                <h2 class="mb-0">{{ $convertedLeads ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="converted">{{ __('Converted') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="assignedFilter">
                            <option value="">{{ __('All Leads') }}</option>
                            <option value="assigned">{{ __('Assigned') }}</option>
                            <option value="unassigned">{{ __('Unassigned') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="{{ __('Search by name, email, phone...') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-danger w-100" id="bulkDeleteBtn" disabled>
                            <i class="ti ti-trash"></i> {{ __('Bulk Delete') }}
                        </button>
                    </div>
                </div>

                <!-- Leads Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="leadsTable">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Subject') }}</th>
                                <th>{{ __('Assigned To') }}</th>
                                <th>{{ __('Stage') }}</th>
                                <th>{{ __('Labels') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th width="150">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                            <tr>
                                <td>
                                    <input type="checkbox" class="lead-checkbox" value="{{ $lead->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('leads.show', $lead->id) }}" class="text-dark">
                                        <strong>{{ $lead->name }}</strong>
                                    </a>
                                </td>
                                <td>{{ $lead->email ?? '-' }}</td>
                                <td>{{ $lead->phone ?? '-' }}</td>
                                <td>{{ \Str::limit($lead->subject, 30) }}</td>
                                <td>
                                    @php $assignedUser = $lead->users->first(); @endphp
                                    @if($assignedUser)
                                        <span class="badge bg-success">{{ $assignedUser->name }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('Unassigned') }}</span>
                                    @endif
                                </td>
                                <td>{{ $lead->stage->name ?? '-' }}</td>
                                <td>
                                    @php $leadLabels = $lead->labels(); @endphp
                                    @if($leadLabels && count($leadLabels) > 0)
                                        @foreach($leadLabels as $label)
                                            <span class="badge" style="background: {{ $label->color ?? '#6c5ce7' }}; font-size: 0.7rem;">
                                                {{ $label->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->is_converted)
                                        <span class="badge bg-info">{{ __('Converted') }}</span>
                                    @else
                                        <span class="badge bg-primary">{{ __('Active') }}</span>
                                    @endif
                                </td>
                                <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        @can('edit lead')
                                        <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete lead')
                                        <button class="btn btn-sm btn-danger delete-lead" data-id="{{ $lead->id }}" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">{{ __('No leads found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $leads->links() }}
            </div>
        </div>
    </div>
</div>

@include('leads.create')
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.lead-checkbox').prop('checked', $(this).prop('checked'));
        $('#bulkDeleteBtn').prop('disabled', !$(this).prop('checked'));
    });
    
    $('.lead-checkbox').change(function() {
        $('#bulkDeleteBtn').prop('disabled', $('.lead-checkbox:checked').length === 0);
    });
    
    // Bulk delete
    $('#bulkDeleteBtn').click(function() {
        var selectedIds = [];
        $('.lead-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if(selectedIds.length === 0) return;
        
        if(confirm('{{ __("Are you sure you want to delete selected leads?") }}')) {
            $.ajax({
                url: '{{ route("leads.bulk-delete") }}',
                type: 'POST',
                data: {
                    lead_ids: selectedIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('{{ __("Error deleting leads") }}');
                }
            });
        }
    });
    
    // Delete single lead
    $('.delete-lead').click(function() {
        var leadId = $(this).data('id');
        if(confirm('{{ __("Are you sure you want to delete this lead?") }}')) {
            $.ajax({
                url: '/leads/' + leadId,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('{{ __("Error deleting lead") }}');
                }
            });
        }
    });
    
    // Search filter
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#leadsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Status filter
    $('#statusFilter').change(function() {
        filterTable();
    });
    
    // Assigned filter
    $('#assignedFilter').change(function() {
        filterTable();
    });
    
    function filterTable() {
        var status = $('#statusFilter').val();
        var assigned = $('#assignedFilter').val();
        
        $('#leadsTable tbody tr').each(function() {
            var show = true;
            var $row = $(this);
            
            if(status === 'converted' && !$row.find('td:eq(9) .badge.bg-info').length) {
                show = false;
            }
            if(status === 'active' && $row.find('td:eq(9) .badge.bg-info').length) {
                show = false;
            }
            
            if(assigned === 'assigned' && $row.find('td:eq(5) .badge.bg-warning').length) {
                show = false;
            }
            if(assigned === 'unassigned' && !$row.find('td:eq(5) .badge.bg-warning').length) {
                show = false;
            }
            
            $row.toggle(show);
        });
    }
});
</script>
@endpush