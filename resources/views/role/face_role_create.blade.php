@extends('layouts.admin')

@section('page-title', 'Create Face ID Role')

@section('content')
<style>
    .role-card {
        border-left: 4px solid #6c5ce7;
        transition: all 0.3s ease;
    }
    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .role-card.employee {
        border-left-color: #00b894;
    }
    .role-card.admin {
        border-left-color: #e17055;
    }
    .badge-role-type {
        font-size: 10px;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-role-type.employee {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-role-type.admin {
        background: #fee2e2;
        color: #991b1b;
    }
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
    }
    .permission-item {
        padding: 6px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .permission-item:hover {
        background: #e9ecef;
        border-color: #6c5ce7;
    }
    .btn-create-role {
        min-width: 200px;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 30px;
    }
    .btn-create-role:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    .permission-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 5px;
    }
    .permission-badge.enrollment {
        background: #8b5cf6;
        color: white;
    }
    .permission-badge.attendance {
        background: #10b981;
        color: white;
    }
    .permission-badge.admin {
        background: #ef4444;
        color: white;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0" id="pageTitle">
                        <i class="ti ti-face-id me-2"></i>
                        {{ __('Create Face ID Role') }}
                    </h4>
                    <small>{{ __('Create separate roles for employees and admins') }}</small>
                </div>

                <div class="card-body">
                    {{Form::open(array('url'=> route('roles.store'),'method'=>'post', 'class'=>'needs-validation', 'novalidate'))}}
                    @csrf
                    <input type="hidden" name="permissions_submitted" value="1">
                    
                    <div class="row">
                        <div class="col-12">
                            
                            <!-- ===== TABS ===== -->
                            <ul class="nav nav-pills mb-4" id="roleTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="employee-tab" data-bs-toggle="pill" data-bs-target="#employee-role" type="button" role="tab">
                                        <i class="ti ti-user me-2"></i> {{ __('Employee Role') }}
                                        <span class="badge-role-type employee ms-2">Self-Service</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-role" type="button" role="tab">
                                        <i class="ti ti-user-shield me-2"></i> {{ __('Admin Role') }}
                                        <span class="badge-role-type admin ms-2">Management</span>
                                    </button>
                                </li>
                            </ul>

                            <!-- Role Name - Single Field -->
                            <div class="form-group mb-3">
                                {{Form::label('name',__('Role Name'),['class'=>'form-label'])}}<x-required></x-required>
                                {{Form::text('name','Face ID Employee',array('class'=>'form-control','placeholder'=>__('Enter Role Name'),'required' => 'required', 'id' => 'role_name'))}}
                                <small class="text-muted" id="roleNameHint">{{ __('Recommended: "Face ID Employee" for self-service') }}</small>
                            </div>

                            <!-- Permission Error -->
                            <div id="permissionsError" class="alert alert-danger d-none">
                                <i class="ti ti-alert-circle me-2"></i>
                                {{ __('Please select at least one permission.') }}
                            </div>

                            <!-- ===== TAB 1: EMPLOYEE ROLE ===== -->
                            <div class="tab-content" id="roleTabsContent">
                                <div class="tab-pane fade show active" id="employee-role" role="tabpanel">
                                    <div class="card role-card employee">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="ti ti-user text-success me-2"></i>
                                                {{ __('Employee Face ID Role') }}
                                                <span class="badge-role-type employee ms-2">Self-Service</span>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="role-description">
                                                <i class="ti ti-info-circle text-success"></i>
                                                <strong>{{ __('What this role does:') }}</strong>
                                                <ul class="mb-0 mt-1">
                                                    <li>{{ __('Employees can mark their OWN attendance using face recognition') }}</li>
                                                    <li>{{ __('Employees can view their attendance status') }}</li>
                                                    <li>{{ __('Employees CANNOT mark attendance for others') }}</li>
                                                    <li>{{ __('Employees CANNOT enroll faces') }}</li>
                                                </ul>
                                            </div>

                                            <div class="mt-3">
                                                <h6 class="mb-2">
                                                    {{ __('Required Permissions:') }}
                                                    <span class="permission-badge attendance">Self-Service</span>
                                                </h6>
                                                <div class="permission-grid">
                                                    @if(in_array('view face id attendance', (array)$permissions))
                                                        @if($key = array_search('view face id attendance', $permissions))
                                                            <div class="permission-item employee-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input employee-perm-checkbox', 'id' =>'emp_perm'.$key])}}
                                                                    {{Form::label('emp_perm'.$key,'👁️ View',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('mark face id attendance', (array)$permissions))
                                                        @if($key = array_search('mark face id attendance', $permissions))
                                                            <div class="permission-item employee-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input employee-perm-checkbox', 'id' =>'emp_perm'.$key])}}
                                                                    {{Form::label('emp_perm'.$key,'✅ Mark Attendance',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <button type="button" class="btn btn-sm btn-success" id="selectEmployeePerms">
                                                    <i class="ti ti-check-all me-1"></i> {{ __('Select Recommended') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectEmployeePerms">
                                                    <i class="ti ti-check me-1"></i> {{ __('Deselect All') }}
                                                </button>
                                            </div>

                                            <input type="hidden" name="role_type" id="role_type" value="employee">
                                        </div>
                                    </div>
                                </div>

                                <!-- ===== TAB 2: ADMIN ROLE ===== -->
                                <div class="tab-pane fade" id="admin-role" role="tabpanel">
                                    <div class="card role-card admin">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="ti ti-user-shield text-danger me-2"></i>
                                                {{ __('Admin Face ID Role') }}
                                                <span class="badge-role-type admin ms-2">Management</span>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="role-description">
                                                <i class="ti ti-info-circle text-danger"></i>
                                                <strong>{{ __('What this role does:') }}</strong>
                                                <ul class="mb-0 mt-1">
                                                    <li>{{ __('Can mark attendance for ALL employees using face recognition') }}</li>
                                                    <li>{{ __('Can enroll and manage faces for all employees') }}</li>
                                                    <li>{{ __('Can view attendance reports and live attendance') }}</li>
                                                    <li>{{ __('Full access to Face ID management features') }}</li>
                                                </ul>
                                            </div>

                                            <div class="mt-3">
                                                <h6 class="mb-2">
                                                    {{ __('Required Permissions:') }}
                                                    <span class="permission-badge enrollment">Enrollment</span>
                                                    <span class="permission-badge attendance">Attendance</span>
                                                    <span class="permission-badge admin">Admin</span>
                                                </h6>
                                                <div class="permission-grid">
                                                    <!-- View Permission -->
                                                    @if(in_array('view face id attendance', (array)$permissions))
                                                        @if($key = array_search('view face id attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'👁️ View',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Mark Attendance Permission -->
                                                    @if(in_array('mark face id attendance', (array)$permissions))
                                                        @if($key = array_search('mark face id attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'✅ Mark Attendance',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Create/Enroll Permission -->
                                                    @if(in_array('create face id attendance', (array)$permissions))
                                                        @if($key = array_search('create face id attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'➕ Enroll Faces',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Edit Permission -->
                                                    @if(in_array('edit face id attendance', (array)$permissions))
                                                        @if($key = array_search('edit face id attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'✏️ Edit',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Delete Permission -->
                                                    @if(in_array('delete face id attendance', (array)$permissions))
                                                        @if($key = array_search('delete face id attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'🗑️ Delete',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Manage Permission -->
                                                    @if(in_array('manage face id attendance', (array)$permissions))
                                                        @if($key = array_search('manage face id attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'⚙️ Manage',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Manage Attendance -->
                                                    @if(in_array('manage attendance', (array)$permissions))
                                                        @if($key = array_search('manage attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'📊 Manage Attendance',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- View Attendance -->
                                                    @if(in_array('view attendance', (array)$permissions))
                                                        @if($key = array_search('view attendance', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'📋 View Attendance',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- HR Dashboard -->
                                                    @if(in_array('show hrm dashboard', (array)$permissions))
                                                        @if($key = array_search('show hrm dashboard', $permissions))
                                                            <div class="permission-item admin-perm">
                                                                <div class="form-check">
                                                                    {{Form::checkbox('permissions[]',$key,true, ['class'=>'form-check-input admin-perm-checkbox', 'id' =>'admin_perm'.$key])}}
                                                                    {{Form::label('admin_perm'.$key,'📊 HR Dashboard',['class'=>'form-check-label'])}}<br>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <button type="button" class="btn btn-sm btn-danger" id="selectAdminPerms">
                                                    <i class="ti ti-check-all me-1"></i> {{ __('Select All Admin Permissions') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAdminPerms">
                                                    <i class="ti ti-check me-1"></i> {{ __('Deselect All') }}
                                                </button>
                                            </div>

                                            <input type="hidden" name="role_type" id="role_type" value="admin">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Summary -->
                            <div class="card border-info mt-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-info-circle me-2"></i>
                                        {{ __('Role Comparison') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Feature') }}</th>
                                                    <th class="text-center">{{ __('Employee Role') }}</th>
                                                    <th class="text-center">{{ __('Admin Role') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>{{ __('Mark own attendance') }}</td>
                                                    <td class="text-center text-success">✅</td>
                                                    <td class="text-center text-success">✅</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Mark attendance for others') }}</td>
                                                    <td class="text-center text-danger">❌</td>
                                                    <td class="text-center text-success">✅</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Enroll faces') }}</td>
                                                    <td class="text-center text-danger">❌</td>
                                                    <td class="text-center text-success">✅</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('View attendance reports') }}</td>
                                                    <td class="text-center text-danger">❌</td>
                                                    <td class="text-center text-success">✅</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Live attendance monitoring') }}</td>
                                                    <td class="text-center text-danger">❌</td>
                                                    <td class="text-center text-success">✅</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Manage Face ID settings') }}</td>
                                                    <td class="text-center text-danger">❌</td>
                                                    <td class="text-center text-success">✅</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer px-0 mt-4">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary btn-create-role">
                            <i class="ti ti-device-floppy me-2"></i> {{ __('Create Role') }}
                        </button>
                    </div>

                    {{Form::close()}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // ===== TAB SWITCHING - UPDATE ROLE NAME =====
        $('#employee-tab').on('click', function() {
            $('#pageTitle').text('Create Employee Face ID Role');
            $('#roleNameHint').text('Recommended: "Face ID Employee" for self-service');
            var nameField = $('#role_name');
            var currentVal = nameField.val();
            if (currentVal === '' || currentVal === 'Face ID Manager' || currentVal === 'Face ID Admin') {
                nameField.val('Face ID Employee');
            }
            $('#role_type').val('employee');
        });
        
        $('#admin-tab').on('click', function() {
            $('#pageTitle').text('Create Admin Face ID Role');
            $('#roleNameHint').text('Recommended: "Face ID Manager" for management');
            var nameField = $('#role_name');
            var currentVal = nameField.val();
            if (currentVal === '' || currentVal === 'Face ID Employee') {
                nameField.val('Face ID Manager');
            }
            $('#role_type').val('admin');
        });

        // ===== EMPLOYEE PERMISSIONS =====
        $('#selectEmployeePerms').on('click', function() {
            $('.employee-perm-checkbox').prop('checked', true);
            updatePermissionsStatus();
        });
        
        $('#deselectEmployeePerms').on('click', function() {
            $('.employee-perm-checkbox').prop('checked', false);
            updatePermissionsStatus();
        });

        // ===== ADMIN PERMISSIONS =====
        $('#selectAdminPerms').on('click', function() {
            $('.admin-perm-checkbox').prop('checked', true);
            updatePermissionsStatus();
        });
        
        $('#deselectAdminPerms').on('click', function() {
            $('.admin-perm-checkbox').prop('checked', false);
            updatePermissionsStatus();
        });

        // ===== UPDATE PERMISSIONS STATUS =====
        function updatePermissionsStatus() {
            var anyChecked = $('input[name="permissions[]"]:checked').length > 0;
            if (anyChecked) {
                $('#permissionsError').addClass('d-none');
            } else {
                $('#permissionsError').removeClass('d-none');
            }
        }

        $(document).on('change', 'input[name="permissions[]"]', function() {
            updatePermissionsStatus();
        });

        // ===== FORM VALIDATION =====
        $('form').on('submit', function(e) {
            var anyChecked = $('input[name="permissions[]"]:checked').length > 0;
            if (!anyChecked) {
                e.preventDefault();
                $('#permissionsError').removeClass('d-none');
                $('html, body').animate({
                    scrollTop: $('#permissionsError').offset().top - 100
                }, 500);
                return false;
            }
            
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.html('<i class="ti ti-loader ti-spin me-2"></i> Creating...');
            submitBtn.prop('disabled', true);
        });

        // Initial check
        updatePermissionsStatus();
    });
</script>
@endsection