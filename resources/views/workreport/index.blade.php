@extends('layouts.admin')
@section('page-title')
    {{__('Work Reports')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Work Reports')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>{{__('Employee')}}</th>
                                <th>{{__('Report Date')}}</th>
                                <th>{{__('Tasks Completed')}}</th>
                                <th>{{__('Hours Worked')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Attachment')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="font-style">{{ $report->employee ? $report->employee->name : 'N/A' }}</td>
                                    <td>{{ \Auth::user()->dateFormat($report->report_date) }}</td>
                                    <td>{{ Str::limit($report->tasks_completed, 50) }}</td>
                                    <td>{{ $report->hours_worked }}</td>
                                    <td>
                                        @if($report->status == 'approved')
                                            <span class="badge bg-success">{{__('Approved')}}</span>
                                        @elseif($report->status == 'rejected')
                                            <span class="badge bg-danger">{{__('Rejected')}}</span>
                                        @else
                                            <span class="badge bg-warning">{{__('Pending')}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->attachment)
                                            <a href="{{ Storage::url($report->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-eye"></i> {{__('View')}}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('workreport.review', $report->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="{{__('Review')}}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
