<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use File;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage plan'))
        {
            if(\Auth::user()->type == 'super admin')
            {
                $plans = Plan::get();
            }
            else
            {
                $plans = Plan::where('is_disable', 1)->get();
            }
            $admin_payment_setting = Utility::getAdminPaymentSetting();

            return view('plan.index', compact('plans', 'admin_payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create plan'))
        {
            $arrDuration = [
                'lifetime' => __('Lifetime'),
                'month' => __('Per Month'),
                'year' => __('Per Year'),
            ];

            // Get Chart of Account data for account configuration
            $accountData = $this->getChartOfAccountData();

            return view('plan.create', compact('arrDuration', 'accountData'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create plan'))
        {
            // Validation rules
            $validation = [];
            $validation['name'] = 'required|unique:plans';
            $validation['price'] = 'required|numeric|min:0';
            $validation['max_users'] = 'required|numeric';
            $validation['max_customers'] = 'required|numeric';
            $validation['max_venders'] = 'required|numeric';
            $validation['max_clients'] = 'required|numeric';
            $validation['storage_limit'] = 'required|numeric';

            // Duration is required only for paid plans
            if ($request->price > 0) {
                $validation['duration'] = 'required';
            }

            if($request->image)
            {
                $validation['image'] = 'required|max:20480';
            }
            
            $request->validate($validation);
            
            $post = $request->all();
            
            // For free plans, set duration to 'lifetime' and price to 0
            if ($request->price <= 0) {
                $post['duration'] = 'lifetime';
                $post['price'] = 0;
            }
            
            // Collect enabled accounts from all account types
            $enabledAccounts = [];
            $types = ChartOfAccountType::where('created_by', 1)->get();
            
            foreach ($types as $type) {
                $accountField = 'account_ids_' . $type->id;
                if ($request->has($accountField) && is_array($request->$accountField)) {
                    $enabledAccounts = array_merge($enabledAccounts, $request->$accountField);
                }
            }
            
            $post['enabled_accounts'] = json_encode(array_unique($enabledAccounts));
            
            // Handle module enable/disable
            $post['project'] = isset($request->enable_project) ? 1 : 0;
            $post['crm'] = isset($request->enable_crm) ? 1 : 0;
            $post['hrm'] = isset($request->enable_hrm) ? 1 : 0;
            $post['account'] = isset($request->enable_account) ? 1 : 0;
            $post['pos'] = isset($request->enable_pos) ? 1 : 0;
            $post['chatgpt'] = isset($request->enable_chatgpt) ? 1 : 0;
            
            if(isset($request->trial))
            {
                $post['trial'] = 1;
                $post['trial_days'] = $request->trial_days;
            }
            else
            {
                $post['trial'] = 0;
                $post['trial_days'] = null;
            }
            
            if($request->hasFile('image'))
            {
                $filenameWithExt = $request->file('image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = 'plan_' . time() . '.' . $extension;

                $dir = public_path('uploads/plan/');
                if(!file_exists($dir))
                {
                    mkdir($dir, 0777, true);
                }
                $path = $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);
                $post['image'] = $fileNameToStore;
            }

            if(Plan::create($post))
            {
                return redirect()->route('plans.index')->with('success', __('Plan Successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($plan_id)
    {
        if(\Auth::user()->can('edit plan'))
        {
            $arrDuration = Plan::$arrDuration;
            $plan = Plan::find($plan_id);
            
            // Get Chart of Account data with saved selections
            $accountData = $this->getChartOfAccountDataWithSelections($plan);

            return view('plan.edit', compact('plan', 'arrDuration', 'accountData'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $plan_id)
    {
        if(\Auth::user()->can('edit plan'))
        {
            $plan = Plan::find($plan_id);
            
            if(!empty($plan))
            {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|unique:plans,name,' . $plan_id,
                        'duration' => function ($attribute, $value, $fail) use ($plan_id, $request) {
                            // Duration is required only for paid plans and if plan is not the default free plan
                            if ($request->price > 0 && $plan_id != 1 && empty($value)) {
                                $fail($attribute.' is required.');
                            }
                        },
                        'max_users' => 'required|numeric',
                        'max_customers' => 'required|numeric',
                        'max_venders' => 'required|numeric',
                        'max_clients' => 'required|numeric',
                        'storage_limit' => 'required|numeric',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $post = $request->all();
                
                // For free plans, set duration to 'lifetime' and price to 0
                if ($request->price <= 0) {
                    $post['duration'] = 'lifetime';
                    $post['price'] = 0;
                }
                
                // Collect enabled accounts from all account types
                $enabledAccounts = [];
                $types = ChartOfAccountType::where('created_by', 1)->get();
                
                foreach ($types as $type) {
                    $accountField = 'account_ids_' . $type->id;
                    if ($request->has($accountField) && is_array($request->$accountField)) {
                        $enabledAccounts = array_merge($enabledAccounts, $request->$accountField);
                    }
                }
                
                $post['enabled_accounts'] = json_encode(array_unique($enabledAccounts));

                // Handle module enable/disable
                $post['project'] = array_key_exists('enable_project', $post) ? 1 : 0;
                $post['crm'] = array_key_exists('enable_crm', $post) ? 1 : 0;
                $post['hrm'] = array_key_exists('enable_hrm', $post) ? 1 : 0;
                $post['account'] = array_key_exists('enable_account', $post) ? 1 : 0;
                $post['pos'] = array_key_exists('enable_pos', $post) ? 1 : 0;
                $post['chatgpt'] = array_key_exists('enable_chatgpt', $post) ? 1 : 0;
                
                if(isset($request->trial))
                {
                    $post['trial'] = 1;
                    $post['trial_days'] = $request->trial_days;
                }
                else
                {
                    $post['trial'] = 0;
                    $post['trial_days'] = null;
                }
                
                if($request->hasFile('image'))
                {
                    $filenameWithExt = $request->file('image')->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = 'plan_' . time() . '.' . $extension;

                    $dir = public_path('uploads/plan/');
                    if(!file_exists($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    $image_path = $dir . '/' . $plan->image;
                    if(File::exists($image_path))
                    {
                        chmod($image_path, 0755);
                        File::delete($image_path);
                    }
                    $path = $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);
                    $post['image'] = $fileNameToStore;
                }

                if($plan->update($post))
                {
                    return redirect()->route('plans.index')->with('success', __('Plan successfully updated.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Plan not found.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Request $request, $id)
    {
        $userPlan = User::where('plan', $id)->first();
        if($userPlan != null)
        {
            return redirect()->back()->with('error', __('The company has subscribed to this plan, so it cannot be deleted.'));
        }
        $plan = Plan::find($id);
        if($plan->id == $id)
        {
            $plan->delete();
            return redirect()->route('plans.index')->with('success', __('Plan deleted successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Something went wrong'));
        }
    }

    public function userPlan(Request $request)
    {
        $objUser = \Auth::user();
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->code);
        $plan = Plan::find($planID);
        if($plan)
        {
            if($plan->price <= 0)
            {
                $objUser->assignPlan($plan->id);
                return redirect()->route('plans.index')->with('success', __('Plan successfully activated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Plan not found.'));
        }
    }

    public function planTrial(Request $request, $plan)
    {
        $objUser = \Auth::user();
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan = Plan::find($planID);

        if($plan)
        {
            if($plan->price > 0)
            {
                $user = User::find($objUser->id);
                $user->trial_plan = $planID;
                $currentDate = date('Y-m-d');
                $numberOfDaysToAdd = $plan->trial_days;

                $newDate = date('Y-m-d', strtotime($currentDate . ' + ' . $numberOfDaysToAdd . ' days'));
                $user->trial_expire_date = $newDate;
                $user->save();

                $objUser->assignPlan($planID);

                return redirect()->route('plans.index')->with('success', __('Plan successfully activated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Plan not found.'));
        }
    }

    public function planDisable(Request $request)
    {
        $userPlan = User::where('plan', $request->id)->first();
        if($userPlan != null)
        {
            return response()->json(['error' => __('The company has subscribed to this plan, so it cannot be disabled.')]);
        }

        Plan::where('id', $request->id)->update(['is_disable' => $request->is_disable]);

        if ($request->is_disable == 1) {
            return response()->json(['success' => __('Plan successfully enable.')]);
        } else {
            return response()->json(['success' => __('Plan successfully disable.')]);
        }
    }

    /**
     * Get Chart of Account data grouped by type for plan configuration
     * This is used when creating a new plan
     * 
     * @return array
     */
    private function getChartOfAccountData()
    {
        // Get all account types created by super admin (created_by = 1)
        $types = ChartOfAccountType::where('created_by', 1)->get();
        
        $accountData = [];
        
        foreach ($types as $type) {
            // Get all accounts for this type
            $accounts = ChartOfAccount::where('type', $type->id)
                ->where('created_by', 1)
                ->orderBy('code')
                ->get();
            
            if ($accounts->count() > 0) {
                $accountData[] = [
                    'type_id' => $type->id,
                    'type_name' => $type->name,
                    'accounts' => $accounts,
                ];
            }
        }
        
        return $accountData;
    }

    /**
     * Get Chart of Account data with plan's saved selections
     * This is used when editing an existing plan
     * 
     * @param Plan $plan
     * @return array
     */
    private function getChartOfAccountDataWithSelections($plan)
    {
        $types = ChartOfAccountType::where('created_by', 1)->get();
        $savedEnabledAccounts = $plan->enabled_accounts ? json_decode($plan->enabled_accounts, true) : [];
        
        $accountData = [];
        
        foreach ($types as $type) {
            $accounts = ChartOfAccount::where('type', $type->id)
                ->where('created_by', 1)
                ->orderBy('code')
                ->get();
            
            if ($accounts->count() > 0) {
                $accountData[] = [
                    'type_id' => $type->id,
                    'type_name' => $type->name,
                    'accounts' => $accounts,
                    'saved_ids' => $savedEnabledAccounts,
                ];
            }
        }
        
        return $accountData;
    }
}