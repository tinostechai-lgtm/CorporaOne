@extends('layouts.admin')
@section('page-title')
    {{__('Manage Employee')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Employee')}}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex">
        <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('employee.file.import') }}" data-ajax-popup="true" data-title="{{__('Import employee CSV file')}}" class="btn btn-sm btn-primary me-2">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('employee.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary me-2">
            <i class="ti ti-file-export"></i>
        </a>
        {{-- REMOVED: Create Employee Button --}}
    </div>
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
                                <th>{{__('Employee ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('Branch') }}</th>
                                <th>{{__('Department') }}</th>
                                <th>{{__('Designation') }}</th>
                                <th>{{__('Date Of Joining') }}</th>
                                <th>{{__('Last Login')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                @php
                                    // ============================================================
                                    // GET EMPLOYEE PROFILE IMAGE OR FALLBACK TO INITIALS
                                    // ============================================================
                                    $defaultAvatar = asset('assets/img/user-avatar.png');
                                    $avatarUrl = $defaultAvatar;
                                    $employeeName = $employee->name ?? 'Employee';
                                    
                                    // Get first letter of name
                                    $employeeInitial = strtoupper(substr($employeeName, 0, 1));
                                    
                                    // ============================================================
                                    // CHECK EMPLOYEE AVATAR
                                    // ============================================================
                                    if (!empty($employee->avatar)) {
                                        $possiblePaths = [
                                            'uploads/avatar/' . $employee->avatar,
                                            'storage/uploads/avatar/' . $employee->avatar,
                                            'storage/avatar/' . $employee->avatar,
                                            'avatar/' . $employee->avatar,
                                        ];
                                        
                                        foreach ($possiblePaths as $path) {
                                            $fullPath = public_path($path);
                                            if (file_exists($fullPath)) {
                                                $avatarUrl = asset($path) . '?v=' . time();
                                                break;
                                            }
                                        }
                                        
                                        if ($avatarUrl == $defaultAvatar) {
                                            try {
                                                $utilityUrl = \App\Models\Utility::get_file('uploads/avatar/' . $employee->avatar);
                                                if ($utilityUrl && filter_var($utilityUrl, FILTER_VALIDATE_URL)) {
                                                    $avatarUrl = $utilityUrl . '?v=' . time();
                                                }
                                            } catch (\Exception $e) {
                                                // Keep default avatar
                                            }
                                        }
                                    }
                                    
                                    // ============================================================
                                    // CHECK USER AVATAR (if employee has user relationship)
                                    // ============================================================
                                    if (!empty($employee->user) && !empty($employee->user->avatar)) {
                                        $userAvatar = $employee->user->avatar;
                                        $possiblePaths = [
                                            'uploads/avatar/' . $userAvatar,
                                            'storage/uploads/avatar/' . $userAvatar,
                                            'storage/avatar/' . $userAvatar,
                                            'avatar/' . $userAvatar,
                                        ];
                                        
                                        foreach ($possiblePaths as $path) {
                                            $fullPath = public_path($path);
                                            if (file_exists($fullPath)) {
                                                $avatarUrl = asset($path) . '?v=' . time();
                                                break;
                                            }
                                        }
                                        
                                        if ($avatarUrl == $defaultAvatar) {
                                            try {
                                                $utilityUrl = \App\Models\Utility::get_file('uploads/avatar/' . $userAvatar);
                                                if ($utilityUrl && filter_var($utilityUrl, FILTER_VALIDATE_URL)) {
                                                    $avatarUrl = $utilityUrl . '?v=' . time();
                                                }
                                            } catch (\Exception $e) {
                                                // Keep default avatar
                                            }
                                        }
                                    }
                                    
                                    // ============================================================
                                    // CHECK EMPLOYEE PROFILE PHOTO (specific employee profile image)
                                    // ============================================================
                                    if (!empty($employee->profile_photo)) {
                                        $profilePhoto = $employee->profile_photo;
                                        $possiblePaths = [
                                            'uploads/employee/' . $profilePhoto,
                                            'storage/uploads/employee/' . $profilePhoto,
                                            'storage/employee/' . $profilePhoto,
                                            'employee/' . $profilePhoto,
                                            'uploads/profile/' . $profilePhoto,
                                            'uploads/avatar/' . $profilePhoto,
                                        ];
                                        
                                        foreach ($possiblePaths as $path) {
                                            $fullPath = public_path($path);
                                            if (file_exists($fullPath)) {
                                                $avatarUrl = asset($path) . '?v=' . time();
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // ============================================================
                                    // GET BADGE COLOR FOR STATUS
                                    // ============================================================
                                    $statusColor = $employee->is_active == 1 ? 'success' : 'danger';
                                    $statusText = $employee->is_active == 1 ? __('Active') : __('Inactive');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-wrapper position-relative" style="width: 40px; height: 40px; margin-right: 10px;">
                                                @if($avatarUrl && $avatarUrl != $defaultAvatar)
                                                    <img src="{{ $avatarUrl }}"
                                                         class="img-fluid rounded-circle border border-primary"
                                                         width="40px" height="40px" 
                                                         alt="{{ $employeeName }}"
                                                         style="object-fit: cover;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="avatar-initials d-none"
                                                         style="width: 40px; height: 40px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-size: 16px; font-weight: 600; color: #667eea; background: #f0f2ff; border: 2px solid #667eea;">
                                                        {{ $employeeInitial }}
                                                    </div>
                                                @else
                                                    <div class="avatar-initials"
                                                         style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 600; color: #667eea; background: #f0f2ff; border: 2px solid #667eea;">
                                                        {{ $employeeInitial }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="fw-bold">{{ $employee->name }}</span>
                                                <br>
                                                <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="Id">
                                        @can('show employee profile')
                                            <a href="{{route('employee.show',\Illuminate\Support\Facades\Crypt::encrypt($employee->id))}}" class="btn btn-outline-primary btn-sm">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary btn-sm">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                        @endcan
                                    </td>
                                    <td class="font-style">{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    @if($employee->branch_id)
                                        <td class="font-style">{{$employee->branch ? $employee->branch->name:''}}</td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    @if($employee->department_id)
                                        <td class="font-style">{{$employee->department ? $employee->department->name:''}}</td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    @if($employee->designation_id)
                                        <td class="font-style">{{$employee->designation ? $employee->designation->name:''}}</td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    @if($employee->company_doj)
                                        <td class="font-style">{{ \Auth::user()->dateFormat($employee->company_doj )}}</td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    <td>
                                        {{ (!empty($employee->user->last_login_at)) ? $employee->user->last_login_at : '-' }}
                                    </td>
                                    @if(Gate::check('edit employee') || Gate::check('delete employee'))
                                        <td>
                                            @if($employee->is_active==1)
                                                @can('edit employee')
                                                    <div class="action-btn me-2">
                                                        <a href="{{route('employee.edit',\Illuminate\Support\Facades\Crypt::encrypt($employee->id))}}" class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete employee')
                                                    <div class="action-btn">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['employee.destroy', $employee->id],'id'=>'delete-form-'.$employee->id]) !!}
                                                            <a href="#" class="btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$employee->id}}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            @else
                                                <i class="ti ti-lock"></i>
                                            @endif
                                        </td>
                                    @endif
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

@push('css')
<style>
    .avatar-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }
    .avatar-wrapper img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
    }
    .avatar-wrapper .avatar-initials {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 600;
        color: #667eea;
        background: #f0f2ff;
        border: 2px solid #667eea;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }
    .avatar-wrapper .avatar-initials:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.2);
    }
    .avatar-wrapper img:hover {
        transform: scale(1.05);
        transition: all 0.3s ease;
    }
    .table td {
        vertical-align: middle;
    }
    .action-btn .btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 0;
    }
    .action-btn .btn i {
        font-size: 16px;
    }
</style>
@endpush