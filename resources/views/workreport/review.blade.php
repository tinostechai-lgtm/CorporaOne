@extends('layouts.admin')
@section('page-title')
    {{__('Review Work Report')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('workreport.index')}}">{{__('Work Reports')}}</a></li>
    <li class="breadcrumb-item">{{__('Review')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{__('Work Report Details')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{__('Employee')}}:</h6>
                        <p>{{ $report->employee ? $report->employee->name : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>{{__('Report Date')}}:</h6>
                        <p>{{ \Auth::user()->dateFormat($report->report_date) }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>{{__('Tasks Completed')}}:</h6>
                        <p>{{ $report->tasks_completed }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>{{__('Hours Worked')}}:</h6>
                        <p>{{ $report->hours_worked }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>{{__('Attachment')}}:</h6>
                        @if($report->attachment)
                            <a href="{{ Storage::url($report->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i> {{__('View Attachment')}}
                            </a>
                        @else
                            <p>{{__('No attachment')}}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>{{__('Current Status')}}:</h6>
                        @if($report->status == 'approved')
                            <span class="badge bg-success">{{__('Approved')}}</span>
                        @elseif($report->status == 'rejected')
                            <span class="badge bg-danger">{{__('Rejected')}}</span>
                        @else
                            <span class="badge bg-warning">{{__('Pending')}}</span>
                        @endif
                    </div>
                    @if($report->status != 'pending')
                        <div class="col-md-12">
                            <h6>{{__('HRM Comment')}}:</h6>
                            <p>{{ $report->hrm_comment ?? 'N/A' }}</p>
                        </div>
                    @endif
                </div>

                @if($report->status == 'pending')
                    <hr>
                    <form method="POST" action="{{ route('workreport.review', $report->id) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">{{__('Status')}}</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="approved">{{__('Approve')}}</option>
                                        <option value="rejected">{{__('Reject')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="hrm_comment">{{__('Comment')}}</label>
                                    <textarea name="hrm_comment" id="hrm_comment" class="form-control" rows="4" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">{{__('Submit Review')}}</button>
                            <a href="{{ route('workreport.index') }}" class="btn btn-secondary">{{__('Cancel')}}</a>
                        </div>
                    </form>
                @else
                    <div class="text-end">
                        <a href="{{ route('workreport.index') }}" class="btn btn-secondary">{{__('Back to Reports')}}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
