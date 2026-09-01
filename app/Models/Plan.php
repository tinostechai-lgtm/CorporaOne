<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration',
        'max_users',
        'max_customers',
        'max_venders',
        'max_clients',
        'trial',
        'trial_days',
        'description',
        'image',
        'crm',
        'hrm',
        'account',
        'project',
        'pos',
        'chatgpt',
        'storage_limit',
        'enabled_accounts',
        'is_disable',
    ];

    protected $casts = [
        'enabled_accounts' => 'array',
    ];

    private static $getplans = NULL;

    public static $arrDuration = [
        'lifetime' => 'Lifetime',
        'month' => 'Per Month',
        'year' => 'Per Year',
    ];

    public function status()
    {
        return [
            __('lifetime'),
            __('Per Month'),
            __('Per Year'),
        ];
    }

    public static function total_plan()
    {
        return Plan::count();
    }

    public static function most_purchese_plan()
    {
        $free_plan = Plan::where('price', '<=', 0)->first();

        if (!$free_plan) {
            return null;
        }

        $plan = User::select(DB::raw('count(*) as total'), 'plan')
            ->where('type', '=', 'company')
            ->where('plan', '!=', $free_plan->id)
            ->groupBy('plan')
            ->first();

        return $plan;
    }

    public static function getPlan($id)
    {
        if(self::$getplans == null)
        {
            $plan = Plan::find($id);
            self::$getplans = $plan;
        }

        return self::$getplans;
    }

    /**
     * Get enabled accounts for this plan
     * Returns array of account IDs that are enabled
     */
    public function getEnabledAccountsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        return json_decode($value, true) ?: [];
    }

    /**
     * Check if a specific account is enabled for this plan
     */
    public function isAccountEnabled($accountId)
    {
        $enabledAccounts = $this->enabled_accounts;
        return empty($enabledAccounts) || in_array($accountId, $enabledAccounts);
    }
}