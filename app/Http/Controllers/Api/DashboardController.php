<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Bug;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Pos;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Purchase;
use App\Models\Revenue;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get Dashboard Data based on user type
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $data = [];

        if ($user->type == 'super admin') {
            return $this->superAdminDashboard($user);
        }

        if ($user->type == 'company') {
            if ($user->can('show account dashboard')) {
                return $this->accountDashboard($user);
            } elseif ($user->can('show project dashboard')) {
                return $this->projectDashboard($user);
            } elseif ($user->can('show hrm dashboard')) {
                return $this->hrmDashboard($user);
            } elseif ($user->can('show crm dashboard')) {
                return $this->crmDashboard($user);
            } elseif ($user->can('show pos dashboard')) {
                return $this->posDashboard($user);
            }
        }

        if ($user->type == 'client') {
            return $this->clientDashboard($user);
        }

        // Default fallback
        return response()->json([
            'success' => false,
            'message' => 'No dashboard available for your role.'
        ], 403);
    }

    private function superAdminDashboard($user)
    {
        $data = [
            'total_companies' => $user->countCompany(),
            'total_paid_companies' => $user->countPaidCompany(),
            'total_orders' => Order::total_orders(),
            'total_orders_amount' => Order::total_orders_price(),
            'total_plans' => Plan::total_plan(),
            'most_purchased_plan' => Plan::most_purchese_plan() ? Plan::find(Plan::most_purchese_plan()['plan'])->name : '-',
            'weekly_orders_chart' => $this->getOrderChart('week'),
        ];

        return response()->json([
            'success' => true,
            'dashboard_type' => 'super_admin',
            'data' => $data
        ]);
    }

    private function accountDashboard($user)
    {
        $creatorId = $user->creatorId();

        $data = [
            'latest_income' => Revenue::with('customer')->where('created_by', $creatorId)->orderByDesc('id')->limit(5)->get(),
            'latest_expense' => \App\Models\Payment::with('vender')->where('created_by', $creatorId)->orderByDesc('id')->limit(5)->get(),
            'income_categories' => $this->getCategoryData('income', $creatorId),
            'expense_categories' => $this->getCategoryData('expense', $creatorId),
            'inc_exp_chart' => $user->getincExpBarChartData(),
            'inc_exp_line_chart' => $user->getIncExpLineChartDate(),
            'constants' => [
                'taxes' => \App\Models\Tax::where('created_by', $creatorId)->count(),
                'categories' => ProductServiceCategory::where('created_by', $creatorId)->count(),
                'units' => ProductServiceUnit::where('created_by', $creatorId)->count(),
                'bank_accounts' => BankAccount::where('created_by', $creatorId)->count(),
            ],
            'recent_invoices' => Invoice::join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->where('invoices.created_by', $creatorId)
                ->orderByDesc('invoices.id')
                ->limit(5)
                ->select('invoices.*', 'customers.name as customer_name')
                ->get(),
            'weekly_invoice' => $user->weeklyInvoice(),
            'monthly_invoice' => $user->monthlyInvoice(),
            'recent_bills' => Bill::join('venders', 'bills.vender_id', '=', 'venders.id')
                ->where('bills.created_by', $creatorId)
                ->orderByDesc('bills.id')
                ->limit(5)
                ->select('bills.*', 'venders.name as vender_name')
                ->get(),
            'weekly_bill' => $user->weeklyBill(),
            'monthly_bill' => $user->monthlyBill(),
            'goals' => Goal::where('created_by', $creatorId)->where('is_display', 1)->get(),
            'storage_usage_percent' => $this->getStorageUsage($user),
        ];

        return response()->json([
            'success' => true,
            'dashboard_type' => 'account',
            'data' => $data
        ]);
    }

    private function projectDashboard($user)
    {
        $user_projects = $user->projects()->pluck('project_id')->toArray();

        $complete_project = $user->projects()->where('status', 'complete')->count();
        $total_project = count($user_projects);

        $complete_task = ProjectTask::where('is_complete', 1)
            ->whereRaw("find_in_set(?,assign_to)", [$user->id])
            ->whereIn('project_id', $user_projects)
            ->count();

        $total_task = ProjectTask::whereIn('project_id', $user_projects)->count();

        $project_expense = Expense::whereIn('project_id', $user_projects)->sum('amount');
        $total_budget = $user->projects()->sum('budget');

        $data = [
            'total_projects' => $total_project,
            'completed_projects_percent' => $total_project ? round(($complete_project / $total_project) * 100) : 0,
            'total_tasks' => $total_task,
            'completed_tasks_percent' => $total_task ? round(($complete_task / $total_task) * 100) : 0,
            'total_expense_percent' => $total_budget ? round(($project_expense / $total_budget) * 100) : 0,
            'total_users' => $user->contacts->count(),
            'task_overview_last_7_days' => $this->getTaskOverviewLast7Days($user_projects),
            'timesheet_logged_last_7_days' => $this->getTimesheetLoggedLast7Days($user_projects),
            'project_status' => $this->getProjectStatusBreakdown($user),
            'due_projects' => $user->projects()->orderBy('end_date')->limit(5)->get(),
            'due_tasks' => ProjectTask::where('is_complete', 0)->whereIn('project_id', $user_projects)->orderBy('end_date')->limit(5)->get(),
        ];

        return response()->json([
            'success' => true,
            'dashboard_type' => 'project',
            'data' => $data
        ]);
    }

    private function hrmDashboard($user)
    {
        $emp = \App\Models\Employee::where('user_id', $user->id)->first();

        $announcements = Announcement::orderByDesc('id')
            ->take(5)
            ->leftJoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')
            ->where(function ($q) use ($emp) {
                $q->where('announcement_employees.employee_id', $emp?->id)
                  ->orWhereRaw("JSON_CONTAINS(department_id, '0')")
                  ->orWhereRaw("JSON_CONTAINS(employee_id, '0')");
            })
            ->get();

        $meetings = Meeting::orderByDesc('id')
            ->take(5)
            ->leftJoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
            ->where(function ($q) use ($emp) {
                $q->where('meeting_employees.employee_id', $emp?->id)
                  ->orWhereRaw("JSON_CONTAINS(department_id, '0')")
                  ->orWhereRaw("JSON_CONTAINS(employee_id, '0')");
            })
            ->get();

        $events = Event::leftJoin('event_employees', 'events.id', '=', 'event_employees.event_id')
            ->where(function ($q) use ($emp) {
                $q->where('event_employees.employee_id', $emp?->id)
                  ->orWhereRaw("JSON_CONTAINS(department_id, '0')")
                  ->orWhereRaw("JSON_CONTAINS(employee_id, '0')");
            })
            ->get();

        $calendar_events = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'backgroundColor' => $event->color,
                'borderColor' => '#fff',
                'textColor' => 'white',
            ];
        });

        $today = date('Y-m-d');
        $attendance = AttendanceEmployee::where('employee_id', $emp?->id)->where('date', $today)->first();

        $office_time = [
            'start' => Utility::getValByName('company_start_time'),
            'end' => Utility::getValByName('company_end_time'),
        ];

        return response()->json([
            'success' => true,
            'dashboard_type' => 'hrm',
            'data' => [
                'announcements' => $announcements,
                'meetings' => $meetings,
                'events' => $calendar_events,
                'today_attendance' => $attendance,
                'office_time' => $office_time,
            ]
        ]);
    }

    private function crmDashboard($user)
    {
        $leads = Lead::where('created_by', $user->creatorId())->get();
        $deals = Deal::where('created_by', $user->creatorId())->get();

        $lead_stages = \App\Models\LeadStage::withCount('lead')->get()->map(function ($stage) use ($leads) {
            return [
                'name' => $stage->name,
                'total' => $stage->lead_count,
                'percentage' => $leads->count() ? round(($stage->lead_count / $leads->count()) * 100) : 0,
            ];
        });

        $deal_stages = \App\Models\Stage::withCount('deals')->get()->map(function ($stage) use ($deals) {
            return [
                'name' => $stage->name,
                'total' => $stage->deals_count,
                'percentage' => $deals->count() ? round(($stage->deals_count / $deals->count()) * 100) : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'dashboard_type' => 'crm',
            'data' => [
                'total_leads' => $leads->count(),
                'total_deals' => $deals->count(),
                'total_contracts' => Contract::where('created_by', $user->creatorId())->count(),
                'lead_stages' => $lead_stages,
                'deal_stages' => $deal_stages,
                'latest_contracts' => Contract::where('created_by', $user->creatorId())
                    ->with(['clients', 'projects', 'types'])
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(),
            ]
        ]);
    }

    private function posDashboard($user)
    {
        return response()->json([
            'success' => true,
            'dashboard_type' => 'pos',
            'data' => [
                'monthly_pos_amount' => Pos::totalPosAmount(true),
                'total_pos_amount' => Pos::totalPosAmount(),
                'monthly_purchase_amount' => Purchase::totalPurchaseAmount(true),
                'total_purchase_amount' => Purchase::totalPurchaseAmount(),
                'purchase_chart' => Purchase::getPurchaseReportChart(),
                'pos_chart' => Pos::getPosReportChart(),
            ]
        ]);
    }

    private function clientDashboard($user)
    {
        // Simplified client dashboard data
        return response()->json([
            'success' => true,
            'dashboard_type' => 'client',
            'data' => [
                'total_deals' => $user->clientDeals->count(),
                'total_tasks' => $user->clientDeals->sum(function ($deal) {
                    return $deal->tasks->count();
                }),
                'projects' => Project::where('client_id', $user->id)->limit(5)->get(),
                'calendar_events' => $this->getClientCalendarEvents($user),
            ]
        ]);
    }

    // Helper methods
    private function getCategoryData($type, $creatorId)
    {
        $categories = ProductServiceCategory::where('created_by', $creatorId)->where('type', $type)->get();

        return $categories->map(function ($cat) {
            return [
                'name' => $cat->name,
                'color' => $cat->color,
                'amount' => $cat->type == 'income' ? $cat->incomeCategoryRevenueAmount() : $cat->expenseCategoryAmount(),
            ];
        });
    }

    private function getStorageUsage($user)
    {
        $plan = Plan::getPlan($user->show_dashboard());
        if ($plan && $plan->storage_limit > 0) {
            return round(($user->storage_limit / $plan->storage_limit) * 100);
        }
        return 0;
    }

    private function getTaskOverviewLast7Days($projectIds)
    {
        // Implement 7-day task completion chart
        return []; // You can enhance this
    }

    private function getTimesheetLoggedLast7Days($projectIds)
    {
        return []; // You can enhance this
    }

    private function getProjectStatusBreakdown($user)
    {
        $statuses = [];
        foreach (Project::$project_status as $key => $name) {
            $count = $user->projects()->where('status', $key)->count();
            $statuses[] = [
                'status' => $name,
                'count' => $count,
                'percentage' => $user->projects()->count() ? round(($count / $user->projects()->count()) * 100) : 0,
            ];
        }
        return $statuses;
    }

    private function getClientCalendarEvents($user)
    {
        $events = [];
        foreach ($user->clientDeals as $deal) {
            foreach ($deal->tasks as $task) {
                $events[] = [
                    'title' => $task->name,
                    'start' => $task->date,
                    'className' => $task->status ? 'bg-primary' : 'bg-warning',
                ];
            }
        }
        return $events;
    }

    private function getOrderChart($duration = 'week')
    {
        // Same logic as your original getOrderChart
        $arrDuration = [];
        if ($duration == 'week') {
            $previous_week = strtotime("-2 week +1 day");
            for ($i = 0; $i < 14; $i++) {
                $date = date('Y-m-d', $previous_week);
                $arrDuration[$date] = date('d-M', $previous_week);
                $previous_week = strtotime("$date +1 day");
            }
        }

        $labels = [];
        $data = [];
        foreach ($arrDuration as $date => $label) {
            $count = Order::whereDate('created_at', $date)->count();
            $labels[] = $label;
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}