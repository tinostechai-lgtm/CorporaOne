@extends('layouts.admin')

@section('page-title', 'Work Report Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hrm.dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('workreport.my') }}">{{ __('My Work Reports') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Report Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-file-text me-2 text-primary"></i>
                        {{ __('Work Report Details') }}
                    </h5>
                    <div>
                        <a href="{{ route('workreport.my') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left me-1"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">{{ __('Date') }}</th>
                                    <td>{{ $report->date ? date('d M Y', strtotime($report->date)) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Clock In') }}</th>
                                    <td>{{ $report->clock_in ? date('h:i A', strtotime($report->clock_in)) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Clock Out') }}</th>
                                    <td>{{ $report->clock_out ? date('h:i A', strtotime($report->clock_out)) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Status') }}</th>
                                    <td>
                                        <span class="badge {{ $report->review_status == 'approved' ? 'bg-success' : ($report->review_status == 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ ucfirst($report->review_status ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">{{ __('Hours - Project') }}</th>
                                    <td>{{ number_format($report->hours_project ?? 0, 1) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Hours - Meetings') }}</th>
                                    <td>{{ number_format($report->hours_meeting ?? 0, 1) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Hours - Admin') }}</th>
                                    <td>{{ number_format($report->hours_admin ?? 0, 1) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Total Hours') }}</th>
                                    <td><strong>{{ number_format(($report->hours_project ?? 0) + ($report->hours_meeting ?? 0) + ($report->hours_admin ?? 0), 1) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Work Description') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $report->work_description ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Quick Tasks') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $report->quick_tasks ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Achievements') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $report->achievements ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Challenges') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $report->challenges ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Tomorrow\'s Plan') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $report->tomorrow_plan ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($report->review_notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Review Notes') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $report->review_notes }}</p>
                                    <small class="text-muted">
                                        {{ __('Reviewed by') }}: {{ $report->reviewer ? $report->reviewer->name : 'N/A' }}
                                        {{ __('on') }} {{ $report->reviewed_at ? date('d M Y h:i A', strtotime($report->reviewed_at)) : '--' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection