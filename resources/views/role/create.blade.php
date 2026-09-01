{{Form::open(array('url'=>'roles','method'=>'post', 'class'=>'needs-validation', 'novalidate'))}}
<input type="hidden" name="permissions_submitted" value="1">
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{Form::label('name',__('Name'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Role Name'),'required' => 'required'))}}
                @error('name')
                <small class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
            
            <!-- ===== QUICK ROLE TYPE SELECTOR ===== -->
            <div class="alert alert-info mb-3">
                <i class="ti ti-info-circle me-2"></i>
                <strong>{{ __('Quick Role Templates') }}</strong>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-success" id="quickEmployeeRole">
                        <i class="ti ti-user me-1"></i> {{ __('Employee Face ID') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="quickAdminRole">
                        <i class="ti ti-user-shield me-1"></i> {{ __('Admin Face ID') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-info" id="quickWorkReportRole">
                        <i class="ti ti-file-text me-1"></i> {{ __('Work Report') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" id="quickClearRole">
                        <i class="ti ti-clear me-1"></i> {{ __('Clear Selection') }}
                    </button>
                </div>
                <small class="text-muted d-block mt-1">
                    {{ __('Click a button to auto-select permissions for that role type') }}
                </small>
            </div>

            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pills-staff-tab" data-bs-toggle="pill" href="#staff" role="tab" aria-controls="pills-home" aria-selected="true">{{__('Staff')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-crm-tab" data-bs-toggle="pill" href="#crm" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('CRM')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-project-tab" data-bs-toggle="pill" href="#project" role="tab" aria-controls="pills-contact" aria-selected="false">{{__('Project')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-hrmpermission-tab" data-bs-toggle="pill" href="#hrmpermission" role="tab" aria-controls="pills-contact" aria-selected="false">{{__('HRM')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-account-tab" data-bs-toggle="pill" href="#account" role="tab" aria-controls="pills-contact" aria-selected="false">{{__('Account')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-pos-tab" data-bs-toggle="pill" href="#pos" role="tab" aria-controls="pills-contact" aria-selected="false">{{__('POS')}}</a>
                </li>
                <!-- ===== FACE RECOGNITION TAB ===== -->
                <li class="nav-item">
                    <a class="nav-link" id="pills-face-tab" data-bs-toggle="pill" href="#face" role="tab" aria-controls="pills-face" aria-selected="false">
                        <i class="ti ti-face-id me-1"></i> {{__('Face ID')}}
                    </a>
                </li>
            </ul>
            
            <!-- Permission Error Message -->
            <div id="permissionsError" class="alert alert-danger d-none">
                <i class="ti ti-alert-circle me-2"></i>
                {{ __('Please select at least one permission for the role.') }}
            </div>
            
            <div class="tab-content" id="pills-tabContent">
                
                <!-- ==================== STAFF TAB ==================== -->
                <div class="tab-pane fade show active" id="staff" role="tabpanel" aria-labelledby="pills-home-tab">
                    @php
                        $modules=['user','role','client','product & service','constant unit','constant tax','constant category', 'zoom meeting','company settings','employee task tracker'];
                        if(\Auth::user()->type == 'company'){
                            $modules[] = 'permission';
                        }
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign General Permission to Roles')}}</h6>
                                <table class="table table-striped mb-0" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input" name="staff_checkall" id="staff_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck staff_checkall" data-id="{{str_replace(' ', '', str_replace('&', '', $module))}}"></td>
                                            <td><label class="ischeck staff_checkall" data-id="{{str_replace(' ', '', str_replace('&', '', $module))}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                        @if($key = array_search('income vs expense '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck staff_checkall isscheck_'.str_replace(' ', '', str_replace('&', '', $module)),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ==================== CRM TAB ==================== -->
                <div class="tab-pane fade" id="crm" role="tabpanel" aria-labelledby="pills-profile-tab">
                    @php
                        $modules=['crm dashboard','lead','pipeline','lead stage','source','label','lead email','lead call','deal','stage','task','form builder','form response','contract','contract type'];
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign CRM related Permission to Roles')}}</h6>
                                <table class="table table-striped mb-0" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input custom_align_middle" name="crm_checkall" id="crm_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck crm_checkall" data-id="{{str_replace(' ', '', $module)}}"></td>
                                            <td><label class="ischeck crm_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox>
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck crm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ==================== PROJECT TAB ==================== -->
                <div class="tab-pane fade" id="project" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['project dashboard','project','milestone','grant chart','project stage','timesheet','project expense','project task','activity','CRM activity','project task stage','bug report','bug status'];
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign Project related Permission to Roles')}}</h6>
                                <table class="table table-striped mb-0" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input align-middle custom_align_middle" name="project_checkall" id="project_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input align-middle ischeck project_checkall" data-id="{{str_replace(' ', '', $module)}}"></td>
                                            <td><label class="ischeck project_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox>
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck project_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ==================== HRM TAB WITH WORK REPORT ==================== -->
                <div class="tab-pane fade" id="hrmpermission" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules = [
                            'hrm dashboard','employee','employee profile','department','designation','branch','document type','document','payslip type','allowance','commission','allowance option','loan option','deduction option','loan','saturation deduction','other payment','overtime','set salary','pay slip','company policy','appraisal','goal tracking','goal type','indicator','event','meeting','training','trainer','training type','award','award type','resignation','travel','promotion','complaint','warning','termination','termination type','job application','job application note','job onBoard','job category','job','job stage','custom question','interview schedule','career','estimation','holiday','transfer','announcement','leave','leave type','attendance',
                            
                            // ===== WORK REPORT MODULE =====
                            'work report'
                        ];
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign HRM related Permission to Roles')}}</h6>
                                <table class="table table-striped mb-0" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input align-middle custom_align_middle" name="hrm_checkall" id="hrm_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input align-middle ischeck hrm_checkall" data-id="{{str_replace(' ', '', $module)}}">
                                            </td>
                                            <td>
                                                <label class="ischeck hrm_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label>
                                            </td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck hrm_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ==================== ACCOUNT TAB ==================== -->
                <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['account dashboard','proposal','invoice','bill','revenue','payment','proposal product','invoice product','bill product','goal','credit note','debit note','bank account','bank transfer','transaction','customer','vender','constant custom field','assets','chart of account','journal entry','report'];
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign Account related Permission to Roles')}}</h6>
                                <table class="table table-striped mb-0" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input custom_align_middle" name="account_checkall" id="account_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck account_checkall" data-id="{{str_replace(' ', '', $module)}}"></td>
                                            <td><label class="ischeck account_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox>
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('move '.$module,(array) $permissions))
                                                        @if($key = array_search('move '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Move',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('duplicate '.$module,(array) $permissions))
                                                        @if($key = array_search('duplicate '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Duplicate',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income '.$module,(array) $permissions))
                                                        @if($key = array_search('income '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Income',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('expense '.$module,(array) $permissions))
                                                        @if($key = array_search('expense '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('tax '.$module,(array) $permissions))
                                                        @if($key = array_search('tax '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Tax',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('invoice '.$module,(array) $permissions))
                                                        @if($key = array_search('invoice '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Invoice',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('bill '.$module,(array) $permissions))
                                                        @if($key = array_search('bill '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Bill',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('balance sheet '.$module,(array) $permissions))
                                                        @if($key = array_search('balance sheet '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Balance Sheet',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('ledger '.$module,(array) $permissions))
                                                        @if($key = array_search('ledger '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Ledger',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('trial balance '.$module,(array) $permissions))
                                                        @if($key = array_search('trial balance '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Trial Balance',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('income vs expense '.$module,(array) $permissions))
                                                        @if($key = array_search('income vs expense '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Income VS Expense',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('loss & profit '.$module,(array) $permissions))
                                                        @if($key = array_search('loss & profit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Loss & Profit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('convert '.$module,(array) $permissions))
                                                        @if($key = array_search('convert '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Convert',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck account_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ==================== POS TAB ==================== -->
                <div class="tab-pane fade" id="pos" role="tabpanel" aria-labelledby="pills-contact-tab">
                    @php
                        $modules=['warehouse','quotation','purchase','pos','barcode'];
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign POS related Permission to Roles')}}</h6>
                                <table class="table table-striped mb-0" id="dataTable-1">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input custom_align_middle" name="pos_checkall" id="pos_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input ischeck pos_checkall" data-id="{{str_replace(' ', '', $module)}}"></td>
                                            <td><label class="ischeck pos_checkall" data-id="{{str_replace(' ', '', $module)}}">{{ ucfirst($module) }}</label></td>
                                            <td>
                                                <div class="row">
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'View',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('add '.$module,(array) $permissions))
                                                        @if($key = array_search('add '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox>
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Add',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox>
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Edit',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Manage',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('show '.$module,(array) $permissions))
                                                        @if($key = array_search('show '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Show',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('send '.$module,(array) $permissions))
                                                        @if($key = array_search('send '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Send',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('convert '.$module,(array) $permissions))
                                                        @if($key = array_search('convert '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Convert',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('create payment '.$module,(array) $permissions))
                                                        @if($key = array_search('create payment '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Create Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if(in_array('delete payment '.$module,(array) $permissions))
                                                        @if($key = array_search('delete payment '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,false, ['class'=>'form-check-input isscheck pos_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'Delete Payment',['class'=>'custom-control-label'])}}<br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ==================== FACE RECOGNITION TAB ==================== -->
                <div class="tab-pane fade" id="face" role="tabpanel" aria-labelledby="pills-face-tab">
                    <div class="alert alert-success mb-3">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>{{ __('Face ID Role Templates') }}</strong>
                        <div class="mt-2">
                            <span class="badge bg-success me-1">Employee Role</span>
                            <small>{{ __('View + Enroll + Mark Attendance (Self-Service)') }}</small>
                            <br>
                            <span class="badge bg-danger me-1 mt-1">Admin Role</span>
                            <small>{{ __('All Face ID permissions (Full Control)') }}</small>
                        </div>
                    </div>

                    @php
                        $modules = [
                            'face id attendance'
                        ];
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            @if(!empty($permissions))
                                <h6 class="my-3">{{__('Assign Face Recognition Permission to Roles')}}</h6>
                                
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong>Face Recognition Module</strong><br>
                                    This module allows users to mark attendance using face recognition technology.
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-success" id="selectEmployeeFacePerms">
                                            <i class="ti ti-user me-1"></i> {{ __('Employee Permissions') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" id="selectAdminFacePerms">
                                            <i class="ti ti-user-shield me-1"></i> {{ __('Admin Permissions') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAllFacePerms">
                                            <i class="ti ti-check me-1"></i> {{ __('Deselect All') }}
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning mt-2" id="facePermissionWarning" style="display: none;">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    <strong>Note:</strong> You need to select at least one permission for the Face ID Attendance module.
                                </div>
                                
                                <table class="table table-striped mb-0" id="dataTable-face">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input align-middle custom_align_middle" name="face_checkall" id="face_checkall">
                                            </th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Permissions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $module)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input align-middle ischeck face_checkall" data-id="{{str_replace(' ', '', $module)}}">
                                            </td>
                                            <td>
                                                <label class="ischeck face_checkall" data-id="{{str_replace(' ', '', $module)}}">
                                                    <i class="ti ti-face-id text-primary me-1"></i>
                                                    {{ ucfirst($module) }}
                                                </label>
                                            </td>
                                            <td>
                                                <div class="row">
                                                    <!-- View Permission -->
                                                    @if(in_array('view '.$module,(array) $permissions))
                                                        @if($key = array_search('view '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck face_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'👁️ View',['class'=>'custom-control-label'])}}<br>
                                                                <small class="text-muted" style="font-size:10px;">(Employee + Admin)</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Create/Enroll Permission -->
                                                    @if(in_array('create '.$module,(array) $permissions))
                                                        @if($key = array_search('create '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck face_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'➕ Enroll Face',['class'=>'custom-control-label'])}}<br>
                                                                <small class="text-muted" style="font-size:10px;">(Employee + Admin)</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Edit Permission -->
                                                    @if(in_array('edit '.$module,(array) $permissions))
                                                        @if($key = array_search('edit '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck face_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'✏️ Edit',['class'=>'custom-control-label'])}}<br>
                                                                <small class="text-muted" style="font-size:10px;">(Admin only)</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Delete Permission -->
                                                    @if(in_array('delete '.$module,(array) $permissions))
                                                        @if($key = array_search('delete '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck face_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'🗑️ Delete',['class'=>'custom-control-label'])}}<br>
                                                                <small class="text-muted" style="font-size:10px;">(Admin only)</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Manage Permission -->
                                                    @if(in_array('manage '.$module,(array) $permissions))
                                                        @if($key = array_search('manage '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck face_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'⚙️ Manage',['class'=>'custom-control-label'])}}<br>
                                                                <small class="text-muted" style="font-size:10px;">(Admin only)</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                    
                                                    <!-- Mark Attendance Permission -->
                                                    @if(in_array('mark '.$module,(array) $permissions))
                                                        @if($key = array_search('mark '.$module,$permissions))
                                                            <div class="col-md-3 custom-control custom-checkbox">
                                                                {{Form::checkbox('permissions[]',$key,isset($role)?in_array($key,$role->permissions):false, ['class'=>'form-check-input isscheck face_checkall isscheck_'.str_replace(' ', '', $module),'id' =>'permission'.$key])}}
                                                                {{Form::label('permission'.$key,'✅ Mark Attendance',['class'=>'custom-control-label'])}}<br>
                                                                <small class="text-muted" style="font-size:10px;">(Employee + Admin)</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                <!-- Legend -->
                                <div class="mt-3 p-2 bg-light rounded">
                                    <h6 class="mb-2"><i class="ti ti-info-circle me-1"></i> Permission Legend:</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <span class="badge bg-success">Employee + Admin</span>
                                            <small class="d-block text-muted">View, Enroll, Mark Attendance</small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge bg-danger">Admin only</span>
                                            <small class="d-block text-muted">Edit, Delete, Manage</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- ==================== END FACE RECOGNITION TAB ==================== -->

            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>

{{Form::close()}}

<script>
    $(document).ready(function () {
        // Staff tab - Select All
        $("#staff_checkall").click(function(){
            $('.staff_checkall').not(this).prop('checked', this.checked);
        });
        // CRM tab - Select All
        $("#crm_checkall").click(function(){
            $('.crm_checkall').not(this).prop('checked', this.checked);
        });
        // Project tab - Select All
        $("#project_checkall").click(function(){
            $('.project_checkall').not(this).prop('checked', this.checked);
        });
        // HRM tab - Select All
        $("#hrm_checkall").click(function(){
            $('.hrm_checkall').not(this).prop('checked', this.checked);
        });
        // Account tab - Select All
        $("#account_checkall").click(function(){
            $('.account_checkall').not(this).prop('checked', this.checked);
        });
        // POS tab - Select All
        $("#pos_checkall").click(function(){
            $('.pos_checkall').not(this).prop('checked', this.checked);
        });
        // Face tab - Select All
        $("#face_checkall").click(function(){
            $('.face_checkall').not(this).prop('checked', this.checked);
        });
        
        // Individual checkbox handler - checks/unchecks all permissions in a row
        $(".ischeck").click(function(){
            var ischeck = $(this).data('id');
            $('.isscheck_'+ ischeck).prop('checked', this.checked);
        });

        // ===========================================
        // ===== QUICK ROLE TEMPLATES =====
        // ===========================================

        // ---- Employee Face ID Role - ONLY Face ID permissions (View + Enroll + Mark) ----
        $('#quickEmployeeRole').on('click', function() {
            // Clear ALL checkboxes in ALL tabs
            $('.face_checkall').prop('checked', false);
            $('.hrm_checkall').prop('checked', false);
            $('.staff_checkall').prop('checked', false);
            $('.crm_checkall').prop('checked', false);
            $('.project_checkall').prop('checked', false);
            $('.account_checkall').prop('checked', false);
            $('.pos_checkall').prop('checked', false);
            
            // Find the module ID for face id attendance
            var moduleId = 'faceidattendance';
            
            // ONLY check Face ID permissions for Employee:
            // 1. View - Can see the face ID pages
            // 2. Create - Can enroll their face (Enroll Face)
            // 3. Mark - Can mark their own attendance (Punch In/Out)
            $('.isscheck_' + moduleId).each(function() {
                var label = $(this).closest('.custom-control').find('.custom-control-label').text().trim();
                var labelLower = label.toLowerCase();
                
                // ONLY check View, Enroll, and Mark permissions
                // DO NOT check Edit, Delete, or Manage
                if(labelLower.includes('view') || 
                   labelLower.includes('enroll') || 
                   labelLower.includes('mark')) {
                    $(this).prop('checked', true);
                }
            });
            
            // Set role name
            $('#name').val('Face ID Employee');
            
            updatePermissionsStatus();
            $('#permissionsError').addClass('d-none');
            $('#facePermissionWarning').hide();
            
            // Switch to face tab
            $('#pills-face-tab').tab('show');
            
            showToast('Employee Face ID permissions selected: View, Enroll, Mark Attendance', 'success');
        });

        // ---- Admin Face ID Role - ONLY ALL Face ID permissions (no HRM) ----
        $('#quickAdminRole').on('click', function() {
            // Clear ALL checkboxes in ALL tabs
            $('.face_checkall').prop('checked', false);
            $('.hrm_checkall').prop('checked', false);
            $('.staff_checkall').prop('checked', false);
            $('.crm_checkall').prop('checked', false);
            $('.project_checkall').prop('checked', false);
            $('.account_checkall').prop('checked', false);
            $('.pos_checkall').prop('checked', false);
            
            // ONLY check ALL Face ID permissions (no HRM or other permissions)
            $('.face_checkall').each(function() {
                $(this).prop('checked', true);
            });
            
            // Set role name
            $('#name').val('Face ID Admin');
            
            updatePermissionsStatus();
            $('#permissionsError').addClass('d-none');
            $('#facePermissionWarning').hide();
            
            // Switch to face tab
            $('#pills-face-tab').tab('show');
            
            showToast('Admin Face ID permissions selected: All Face ID permissions', 'success');
        });

        // ---- Work Report Role - View + Create for Employees, Manage for Admin ----
        $('#quickWorkReportRole').on('click', function() {
            // Clear ALL checkboxes in ALL tabs
            $('.face_checkall').prop('checked', false);
            $('.hrm_checkall').prop('checked', false);
            $('.staff_checkall').prop('checked', false);
            $('.crm_checkall').prop('checked', false);
            $('.project_checkall').prop('checked', false);
            $('.account_checkall').prop('checked', false);
            $('.pos_checkall').prop('checked', false);
            
            var moduleId = 'workreport';
            
            // Check View, Create, Edit for Employee (self-service)
            // Also check Manage for Admin (full control)
            $('.isscheck_' + moduleId).each(function() {
                var label = $(this).closest('.custom-control').find('.custom-control-label').text().trim();
                var labelLower = label.toLowerCase();
                
                // For Work Report, employees need: View, Create, Edit
                // Admins need: View, Create, Edit, Delete, Manage, Show
                if(labelLower.includes('view') || 
                   labelLower.includes('create') || 
                   labelLower.includes('edit') ||
                   labelLower.includes('delete') ||
                   labelLower.includes('manage') ||
                   labelLower.includes('show')) {
                    $(this).prop('checked', true);
                }
            });
            
            // Set role name
            $('#name').val('Work Report User');
            
            updatePermissionsStatus();
            $('#permissionsError').addClass('d-none');
            
            // Switch to HRM tab
            $('#pills-hrmpermission-tab').tab('show');
            
            showToast('Work Report permissions selected: View, Create, Edit, Manage', 'success');
        });

        // ---- Clear Selection - Clear ALL checkboxes in ALL tabs ----
        $('#quickClearRole').on('click', function() {
            // Clear ALL checkboxes in ALL tabs
            $('.face_checkall').prop('checked', false);
            $('.hrm_checkall').prop('checked', false);
            $('.staff_checkall').prop('checked', false);
            $('.crm_checkall').prop('checked', false);
            $('.project_checkall').prop('checked', false);
            $('.account_checkall').prop('checked', false);
            $('.pos_checkall').prop('checked', false);
            
            $('#name').val('');
            updatePermissionsStatus();
            
            showToast('All selections cleared', 'info');
        });

        // ===========================================
        // ===== FACE TAB QUICK SELECT BUTTONS =====
        // ===========================================

        // ---- Employee Permissions (View + Enroll + Mark) ----
        $('#selectEmployeeFacePerms').on('click', function() {
            // Clear all face checkboxes first
            $('.face_checkall').prop('checked', false);
            
            // Check View, Create (Enroll), and Mark permissions
            $('.face_checkall').each(function() {
                var label = $(this).closest('.custom-control').find('.custom-control-label').text().trim();
                var labelLower = label.toLowerCase();
                if(labelLower.includes('view') || 
                   labelLower.includes('enroll') || 
                   labelLower.includes('mark')) {
                    $(this).prop('checked', true);
                }
            });
            
            updatePermissionsStatus();
            showToast('Employee Face ID permissions selected', 'success');
        });

        // ---- Admin Permissions (ALL Face ID) ----
        $('#selectAdminFacePerms').on('click', function() {
            // Check ALL face checkboxes
            $('.face_checkall').each(function() {
                $(this).prop('checked', true);
            });
            
            updatePermissionsStatus();
            showToast('Admin Face ID permissions selected (All)', 'success');
        });

        // ---- Deselect All Face Permissions ----
        $('#deselectAllFacePerms').on('click', function() {
            $('.face_checkall').prop('checked', false);
            updatePermissionsStatus();
            showToast('All Face ID permissions deselected', 'info');
        });

        // ===========================================
        // ===== UPDATE PERMISSIONS STATUS =====
        // ===========================================
        function updatePermissionsStatus() {
            var anyChecked = $('input[name="permissions[]"]:checked').length > 0;
            
            if (anyChecked) {
                $('#permissionsError').addClass('d-none');
                $('#facePermissionWarning').hide();
            }
        }

        // Check permissions status on any change
        $(document).on('change', 'input[name="permissions[]"]', function() {
            updatePermissionsStatus();
        });

        // Also update when select all is clicked
        $(document).on('click', 'input[type="checkbox"][id$="_checkall"]', function() {
            setTimeout(updatePermissionsStatus, 100);
        });

        // Show warning if face tab is active and no permissions selected
        $('#pills-face-tab').on('shown.bs.tab', function() {
            var anyChecked = $('input[name="permissions[]"]:checked').length > 0;
            if (!anyChecked) {
                $('#facePermissionWarning').show();
            } else {
                $('#facePermissionWarning').hide();
            }
        });

        // ===========================================
        // ===== TOAST NOTIFICATION =====
        // ===========================================
        function showToast(message, type) {
            var toastHtml = `
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'info' ? 'info' : 'secondary'} border-0 show" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="ti ti-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'x'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing toasts
            $('.position-fixed.bottom-0.end-0.p-3').remove();
            
            // Add new toast
            $('body').append(toastHtml);
            
            // Auto-remove after 3 seconds
            setTimeout(function() {
                $('.position-fixed.bottom-0.end-0.p-3').remove();
            }, 3000);
        }

        // ===========================================
        // ===== FORM VALIDATION =====
        // ===========================================
        $('form').on('submit', function(e) {
            var anyChecked = $('input[name="permissions[]"]:checked').length > 0;
            
            if (!anyChecked) {
                e.preventDefault();
                $('#permissionsError').removeClass('d-none');
                // Scroll to error
                $('html, body').animate({
                    scrollTop: $('#permissionsError').offset().top - 100
                }, 500);
                return false;
            }
            
            // Show loading
            var submitBtn = $(this).find('input[type="submit"]');
            submitBtn.val('Creating...');
            submitBtn.prop('disabled', true);
        });
    });
</script>