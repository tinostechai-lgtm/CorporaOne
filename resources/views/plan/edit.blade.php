@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Plan') }}
@endsection

@push('css-page')
<style>
    .account-config-section .card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .account-config-section .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    .account-checkbox {
        margin-right: 8px;
    }
    .badge {
        font-size: 11px;
        padding: 3px 6px;
        margin-right: 5px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{Form::model($plan, array('route' => array('plans.update', $plan->id), 'method' => 'PUT', 'enctype' => "multipart/form-data", 'class'=>'needs-validation', 'novalidate')) }}
                <div class="modal-body">
                    {{-- start for ai module--}}
                    @php
                        $settings = \App\Models\Utility::settings();
                    @endphp
                    @if(!empty($settings['chat_gpt_key']))
                    <div class="text-end">
                        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['plan']) }}"
                           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
                        </a>
                    </div>
                    @endif
                    {{-- end for ai module--}}

                    <div class="row">
                        <div class="form-group col-md-6">
                            {{Form::label('name',__('Name'),['class'=>'form-label'])}}<x-required></x-required>
                            {{Form::text('name',null,array('class'=>'form-control font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required'))}}
                        </div>
                        <div class="form-group col-md-6">
                            {{Form::label('price',__('Price'),['class'=>'form-label'])}}<x-required></x-required>
                            {{Form::number('price',null,array('class'=>'form-control','placeholder'=>__('Enter Plan Price'),'required'=>'required' ,'step' => '0.01'))}}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('duration', __('Duration'),['class'=>'form-label']) }}<x-required></x-required>
                            {!! Form::select('duration', $arrDuration, null,array('class' => 'form-control select','required'=>'required')) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {{Form::label('max_users',__('Maximum Users'),['class'=>'form-label'])}}<x-required></x-required>
                            {{Form::number('max_users',null,array('class'=>'form-control','required'=>'required', 'placeholder' => __('Enter Maximum Users')))}}
                            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
                        </div>
                        <div class="form-group col-md-6">
                            {{Form::label('max_customers',__('Maximum Customers'),['class'=>'form-label'])}}<x-required></x-required>
                            {{Form::number('max_customers',null,array('class'=>'form-control','required'=>'required', 'placeholder' => __('Enter Maximum Customers')))}}
                            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
                        </div>
                        <div class="form-group col-md-6">
                            {{Form::label('max_venders',__('Maximum Venders'),['class'=>'form-label'])}}<x-required></x-required>
                            {{Form::number('max_venders',null,array('class'=>'form-control','required'=>'required', 'placeholder' => __('Enter Maximum Vendors')))}}
                            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
                        </div>
                        <div class="form-group col-md-6">
                            {{Form::label('max_clients',__('Maximum Clients'),['class'=>'form-label'])}}<x-required></x-required>
                            {{Form::number('max_clients',null,array('class'=>'form-control','required'=>'required', 'placeholder' => __('Enter Maximum Clients')))}}
                            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('storage_limit', __('Storage limit'), ['class' => 'form-label']) }}<x-required></x-required>
                            <div class="input-group">
                                {{ Form::number('storage_limit', null,array('class'=>'form-control','required'=>'required', 'placeholder' => __('Maximum Storage Limit'))) }}
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">{{__('MB')}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2', 'placeholder' => __('Enter Description')]) !!}
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="trial" class="form-label">{{ __('Trial is enable(on/off)') }}</label>
                                <div class="form-check form-switch custom-switch-v1 float-end">
                                    <input type="checkbox" name="trial" class="form-check-input input-primary pointer" value="1" id="trial" {{ $plan->trial == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="trial"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 {{ $plan->trial != 1 ? 'd-none' : '' }} plan_div">
                            <div class="form-group">
                                {{ Form::label('trial_days', __('Trial Days'), ['class' => 'form-label']) }}
                                {{ Form::number('trial_days', $plan->trial_days, ['class' => 'form-control trial_days','placeholder' => __('Enter Trial days'),'step' => '1','min'=>'1']) }}
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 mt-2">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enable_crm" id="enable_crm" {{ $plan->crm == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label form-label" for="enable_crm">{{__('CRM')}}</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3 mt-2">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enable_project" id="enable_project" {{ $plan->project == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label form-label" for="enable_project">{{__('Project')}}</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3 mt-2">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enable_hrm" id="enable_hrm" {{ $plan->hrm == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label form-label" for="enable_hrm">{{__('HRM')}}</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3 mt-2">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enable_account" id="enable_account" {{ $plan->account == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label form-label" for="enable_account">{{__('Account')}}</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enable_pos" id="enable_pos" {{ $plan->pos == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label form-label" for="enable_pos">{{__('POS')}}</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enable_chatgpt" id="enable_chatgpt" {{ $plan->chatgpt == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label form-label" for="enable_chatgpt">{{__('Chat GPT')}}</label>
                            </div>
                        </div>
                    </div>

                    {{-- Account Configuration Section --}}
                    <div class="row account-config-section {{ $plan->account != 1 ? 'd-none' : '' }} mt-4" id="accountConfig">
                        <div class="col-12">
                            <h6 class="mb-3">{{__('Account Configuration')}}</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> {{__('Select which accounts should be available for this plan.')}}
                            </div>
                            
                            @if(isset($accountData) && count($accountData) > 0)
                                @foreach($accountData as $data)
                                    @php
                                        $accountCount = isset($data['accounts']) ? count($data['accounts']) : 0;
                                        // Get the saved enabled accounts from the plan
                                        $savedAccounts = !empty($plan->enabled_accounts) ? json_decode($plan->enabled_accounts, true) : [];
                                    @endphp
                                    
                                    @if($accountCount > 0)
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h6 class="mb-0">{{ $data['type_name'] }}</h6>
                                                    <small class="text-muted">{{ $accountCount }} {{ __('accounts available') }}</small>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <button type="button" class="btn btn-sm btn-link select-all-accounts" data-type="{{ $data['type_id'] }}">
                                                        <i class="fas fa-check-double"></i> {{ __('Select All') }}
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-link deselect-all-accounts" data-type="{{ $data['type_id'] }}">
                                                        <i class="fas fa-times"></i> {{ __('Deselect All') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($data['accounts'] as $account)
                                                    @php
                                                        // Check if this account is enabled in the plan
                                                        // If savedAccounts is empty (new plan or no selection), check all by default
                                                        // Otherwise, only check if account ID is in savedAccounts array
                                                        $isChecked = false;
                                                        if(empty($savedAccounts)) {
                                                            // If no saved accounts, enable all by default when account module is enabled
                                                            $isChecked = ($plan->account == 1);
                                                        } else {
                                                            $isChecked = in_array($account->id, $savedAccounts);
                                                        }
                                                    @endphp
                                                    <div class="col-md-4 col-lg-3 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input account-checkbox account-type-{{ $data['type_id'] }}" 
                                                                   type="checkbox" 
                                                                   name="account_ids_{{ $data['type_id'] }}[]" 
                                                                   value="{{ $account->id }}" 
                                                                   id="account_{{ $account->id }}"
                                                                   {{ $isChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="account_{{ $account->id }}">
                                                                <span class="badge bg-secondary">{{ $account->code }}</span>
                                                                {{ $account->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    {{ __('No account data available. Please make sure you have Chart of Account Types and Accounts created.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="button" value="{{__('Cancel')}}" class="btn btn-secondary" data-bs-dismiss="modal">
                    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Toggle trial days
    $('#trial').on('change', function() {
        if ($(this).is(':checked')) {
            $('.plan_div').removeClass('d-none');
        } else {
            $('.plan_div').addClass('d-none');
        }
    });
    
    // Trigger trial days on page load
    if ($('#trial').is(':checked')) {
        $('.plan_div').removeClass('d-none');
    }

    // Toggle account config section
    $('#enable_account').on('change', function() {
        if ($(this).is(':checked')) {
            $('#accountConfig').removeClass('d-none');
            // When enabling account module, check all account checkboxes by default if none are checked
            var anyChecked = $('.account-checkbox:checked').length;
            if (anyChecked === 0) {
                $('.account-checkbox').prop('checked', true);
            }
        } else {
            $('#accountConfig').addClass('d-none');
        }
    });
    
    // Trigger account config on page load
    if ($('#enable_account').is(':checked')) {
        $('#accountConfig').removeClass('d-none');
    }
    
    // Select all accounts for a specific account type
    $(document).on('click', '.select-all-accounts', function(e) {
        e.preventDefault();
        var typeId = $(this).data('type');
        $('.account-type-' + typeId).prop('checked', true);
    });
    
    // Deselect all accounts for a specific account type
    $(document).on('click', '.deselect-all-accounts', function(e) {
        e.preventDefault();
        var typeId = $(this).data('type');
        $('.account-type-' + typeId).prop('checked', false);
    });
});
</script>
@endpush