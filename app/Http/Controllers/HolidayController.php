<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Utility;
use App\Models\Department;
use App\Models\Branch;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage holiday'))
        {
            $holidays = Holiday::where('created_by', '=', \Auth::user()->creatorId());
            if(!empty($request->start_date))
            {
                $holidays->where('date', '>=', $request->start_date);
            }
            if(!empty($request->end_date))
            {
                $holidays->where('date', '<=', $request->end_date);
            }
            $holidays = $holidays->orderBy('date', 'desc')->get();

            return view('holiday.index', compact('holidays'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create holiday'))
        {
            $settings = Utility::settings();
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get();
            $branches = Branch::where('created_by', \Auth::user()->creatorId())->get();
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            
            return view('holiday.create', compact('settings', 'departments', 'branches', 'leaveTypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if(!\Auth::user()->can('create holiday'))
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // ============================================================
        // VALIDATION BASED ON TYPE
        // ============================================================
        $rules = [
            'type' => 'required|in:holiday,week_off,paid_leave,unpaid_leave,sick_leave,casual_leave,maternity_leave,paternity_leave,compensatory_off,other',
        ];

        // Holiday validation
        if ($request->type == 'holiday') {
            $rules['occasion'] = 'required|string|max:255';
            $rules['date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:date';
            $rules['holiday_is_paid'] = 'nullable|in:0,1';
        }

        // Week Off validation
        if ($request->type == 'week_off') {
            $rules['week_off_days'] = 'required|array|min:1';
            $rules['week_off_days.*'] = 'in:1,2,3,4,5,6,7';
            $rules['week_off_applicable'] = 'required|in:all,specific';
            $rules['weekoff_is_paid'] = 'nullable|in:0,1';
        }

        // Leave validation
        if (in_array($request->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
            $rules['leave_type_id'] = 'required|exists:leave_types,id';
            $rules['leave_duration'] = 'required|in:full_day,half_day,first_half,second_half';
            $rules['leave_date_from'] = 'required|date';
            $rules['leave_date_to'] = 'required|date|after_or_equal:leave_date_from';
            $rules['leave_reason'] = 'required|string|min:5';
            $rules['leave_is_paid'] = 'nullable|in:0,1';
        }

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        try {
            // ============================================================
            // FIX: CORRECTLY HANDLE IS_PAID VALUE BASED ON TYPE
            // ============================================================
            $isPaid = 0; // Default to unpaid
            
            // Get the correct is_paid value based on the type
            if ($request->type == 'holiday') {
                // Holiday uses holiday_is_paid
                $isPaid = $request->has('holiday_is_paid') && $request->holiday_is_paid == 1 ? 1 : 0;
            } elseif ($request->type == 'week_off') {
                // Week Off uses weekoff_is_paid
                $isPaid = $request->has('weekoff_is_paid') && $request->weekoff_is_paid == 1 ? 1 : 0;
            } elseif (in_array($request->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
                // Leave uses leave_is_paid
                $isPaid = $request->has('leave_is_paid') && $request->leave_is_paid == 1 ? 1 : 0;
            }

            // Debug logging - remove after testing
            \Log::info('Holiday is_paid value:', [
                'type' => $request->type,
                'holiday_is_paid' => $request->holiday_is_paid ?? 'not_set',
                'weekoff_is_paid' => $request->weekoff_is_paid ?? 'not_set',
                'leave_is_paid' => $request->leave_is_paid ?? 'not_set',
                'final_is_paid' => $isPaid
            ]);

            // ============================================================
            // PREPARE DATA
            // ============================================================
            $data = [
                'type' => $request->type,
                'is_paid' => $isPaid,
                'applicable_to' => $request->applicable_to ?? 'all',
                'departments' => json_encode($request->departments ?? []),
                'description' => $request->description,
                'synchronize_type' => $request->synchronize_type ?? 0,
                'created_by' => \Auth::user()->creatorId(),
            ];

            // ============================================================
            // HOLIDAY DATA
            // ============================================================
            if ($request->type == 'holiday') {
                $data['occasion'] = $request->occasion;
                $data['date'] = $request->date;
                $data['end_date'] = $request->end_date;
                $data['holiday_type'] = 'public';
            }

            // ============================================================
            // WEEK OFF DATA
            // ============================================================
            if ($request->type == 'week_off') {
                $data['occasion'] = 'Week Off';
                $data['date'] = date('Y-m-d');
                $data['end_date'] = date('Y-m-d');
                $data['week_off_days'] = json_encode($request->week_off_days);
                $data['week_off_applicable'] = $request->week_off_applicable;
                $data['holiday_type'] = 'week_off';
            }

            // ============================================================
            // LEAVE DATA
            // ============================================================
            if (in_array($request->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
                $data['occasion'] = ucfirst(str_replace('_', ' ', $request->type));
                $data['date'] = $request->leave_date_from;
                $data['end_date'] = $request->leave_date_to;
                $data['leave_type_id'] = $request->leave_type_id;
                $data['leave_duration'] = $request->leave_duration;
                $data['leave_date_from'] = $request->leave_date_from;
                $data['leave_date_to'] = $request->leave_date_to;
                $data['leave_reason'] = $request->leave_reason;
                $data['holiday_type'] = 'leave';
            }

            // ============================================================
            // CREATE HOLIDAY
            // ============================================================
            $holiday = Holiday::create($data);

            // ============================================================
            // NOTIFICATIONS
            // ============================================================
            $setting = Utility::settings(\Auth::user()->creatorId());
            $holidayNotificationArr = [
                'holiday_title' => $holiday->occasion,
                'holiday_date' => $holiday->date,
            ];

            // Slack Notification
            if(isset($setting['holiday_notification']) && $setting['holiday_notification'] == 1)
            {
                Utility::send_slack_msg('new_holiday', $holidayNotificationArr);
            }

            // Telegram Notification
            if(isset($setting['telegram_holiday_notification']) && $setting['telegram_holiday_notification'] == 1)
            {
                Utility::send_telegram_msg('new_holiday', $holidayNotificationArr);
            }

            // ============================================================
            // GOOGLE CALENDAR
            // ============================================================
            if($request->synchronize_type == 'google_calender')
            {
                $type = 'holiday';
                $request1 = new Holiday();
                $request1->title = $holiday->occasion;
                $request1->start_date = $holiday->date;
                $request1->end_date = $holiday->end_date;
                Utility::addCalendarData($request1, $type);
            }

            // ============================================================
            // WEBHOOK
            // ============================================================
            $module = 'New Holiday';
            $webhook = Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($holiday);
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if($status == true)
                {
                    return redirect()->route('holiday.index')->with('success', 'Holiday successfully created.');
                }
                else
                {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('holiday.index')->with('success', 'Holiday successfully created.');

        } catch (\Exception $e) {
            \Log::error('Holiday creation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating holiday: ' . $e->getMessage());
        }
    }

    public function show(Holiday $holiday)
    {
        // Not used
    }

    public function edit(Holiday $holiday)
    {
        if(\Auth::user()->can('edit holiday'))
        {
            $settings = Utility::settings();
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get();
            $branches = Branch::where('created_by', \Auth::user()->creatorId())->get();
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            
            return view('holiday.edit', compact('holiday', 'settings', 'departments', 'branches', 'leaveTypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, Holiday $holiday)
    {
        if(!\Auth::user()->can('edit holiday'))
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // ============================================================
        // VALIDATION BASED ON TYPE
        // ============================================================
        $rules = [
            'type' => 'required|in:holiday,week_off,paid_leave,unpaid_leave,sick_leave,casual_leave,maternity_leave,paternity_leave,compensatory_off,other',
        ];

        if ($request->type == 'holiday') {
            $rules['occasion'] = 'required|string|max:255';
            $rules['date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:date';
            $rules['holiday_is_paid'] = 'nullable|in:0,1';
        }

        if ($request->type == 'week_off') {
            $rules['week_off_days'] = 'required|array|min:1';
            $rules['week_off_days.*'] = 'in:1,2,3,4,5,6,7';
            $rules['weekoff_is_paid'] = 'nullable|in:0,1';
        }

        if (in_array($request->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
            $rules['leave_type_id'] = 'required|exists:leave_types,id';
            $rules['leave_date_from'] = 'required|date';
            $rules['leave_date_to'] = 'required|date|after_or_equal:leave_date_from';
            $rules['leave_reason'] = 'required|string|min:5';
            $rules['leave_is_paid'] = 'nullable|in:0,1';
        }

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        try {
            // ============================================================
            // FIX: CORRECTLY HANDLE IS_PAID VALUE FOR UPDATE
            // ============================================================
            $isPaid = 0;
            
            if ($request->type == 'holiday') {
                $isPaid = $request->has('holiday_is_paid') && $request->holiday_is_paid == 1 ? 1 : 0;
            } elseif ($request->type == 'week_off') {
                $isPaid = $request->has('weekoff_is_paid') && $request->weekoff_is_paid == 1 ? 1 : 0;
            } elseif (in_array($request->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
                $isPaid = $request->has('leave_is_paid') && $request->leave_is_paid == 1 ? 1 : 0;
            }

            $data = [
                'type' => $request->type,
                'is_paid' => $isPaid,
                'applicable_to' => $request->applicable_to ?? 'all',
                'departments' => json_encode($request->departments ?? []),
                'description' => $request->description,
                'synchronize_type' => $request->synchronize_type ?? 0,
            ];

            if ($request->type == 'holiday') {
                $data['occasion'] = $request->occasion;
                $data['date'] = $request->date;
                $data['end_date'] = $request->end_date;
            }

            if ($request->type == 'week_off') {
                $data['occasion'] = 'Week Off';
                $data['date'] = date('Y-m-d');
                $data['end_date'] = date('Y-m-d');
                $data['week_off_days'] = json_encode($request->week_off_days);
                $data['week_off_applicable'] = $request->week_off_applicable;
            }

            if (in_array($request->type, ['paid_leave', 'unpaid_leave', 'sick_leave', 'casual_leave', 'maternity_leave', 'paternity_leave', 'compensatory_off', 'other'])) {
                $data['occasion'] = ucfirst(str_replace('_', ' ', $request->type));
                $data['date'] = $request->leave_date_from;
                $data['end_date'] = $request->leave_date_to;
                $data['leave_type_id'] = $request->leave_type_id;
                $data['leave_duration'] = $request->leave_duration;
                $data['leave_date_from'] = $request->leave_date_from;
                $data['leave_date_to'] = $request->leave_date_to;
                $data['leave_reason'] = $request->leave_reason;
            }

            $holiday->update($data);

            return redirect()->route('holiday.index')->with('success', 'Holiday successfully updated.');

        } catch (\Exception $e) {
            \Log::error('Holiday update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating holiday: ' . $e->getMessage());
        }
    }

    public function destroy(Holiday $holiday)
    {
        if(\Auth::user()->can('delete holiday'))
        {
            $holiday->delete();
            return redirect()->route('holiday.index')->with('success', 'Holiday successfully deleted.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function calender(Request $request)
    {
        if(\Auth::user()->can('manage holiday'))
        {
            $transdate = date('Y-m-d', time());
            $holidays = Holiday::where('created_by', '=', \Auth::user()->creatorId());

            if(!empty($request->start_date))
            {
                $holidays->where('date', '>=', $request->start_date);
            }
            if(!empty($request->end_date))
            {
                $holidays->where('date', '<=', $request->end_date);
            }

            $holidays = $holidays->get();

            $arrHolidays = [];

            foreach($holidays as $holiday)
            {
                $arr['id']        = $holiday['id'];
                $arr['title']     = $holiday['occasion'] . ' (' . $holiday->type_label . ')';
                $arr['start']     = $holiday['date'];
                $arr['end']       = $holiday['end_date'];
                
                // Set color based on type
                $color = match($holiday->type) {
                    'holiday' => 'event-primary',
                    'week_off' => 'event-success',
                    'paid_leave' => 'event-info',
                    'unpaid_leave' => 'event-warning',
                    'sick_leave' => 'event-danger',
                    default => 'event-primary',
                };
                
                $arr['className'] = $color;
                $arr['url']       = route('holiday.edit', $holiday['id']);
                $arrHolidays[]    = $arr;
            }
            $arrHolidays = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrHolidays)));

            return view('holiday.calender', compact('arrHolidays','transdate','holidays'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // For Google Calendar
    public function get_holiday_data(Request $request)
    {
        if($request->get('calender_type') == 'goggle_calender')
        {
            $type = 'holiday';
            $arrayJson = Utility::getCalendarData($type);
        }
        else
        {
            $data = Holiday::where('created_by', '=', \Auth::user()->creatorId())->get();

            $arrayJson = [];
            foreach($data as $val)
            {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                
                $color = match($val->type) {
                    'holiday' => '#51459d',
                    'week_off' => '#28a745',
                    'paid_leave' => '#17a2b8',
                    'unpaid_leave' => '#ffc107',
                    'sick_leave' => '#dc3545',
                    default => '#51459d',
                };
                
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->occasion . ' (' . $val->type_label . ')',
                    "start" => $val->date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => 'event-primary',
                    "textColor" => $color,
                    'url' => route('holiday.edit', $val->id),
                    "allDay" => true,
                ];
            }
        }

        return $arrayJson;
    }

    // ============================================================
    // GET WEEK OFF DAYS
    // ============================================================
    public function getWeekOffDays(Request $request)
    {
        try {
            $weekOffDays = Holiday::where('created_by', \Auth::user()->creatorId())
                            ->where('type', 'week_off')
                            ->get();
            
            return response()->json([
                'success' => true,
                'data' => $weekOffDays
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // GET HOLIDAY BY TYPE
    // ============================================================
    public function getByType(Request $request)
    {
        try {
            $type = $request->get('type');
            $holidays = Holiday::where('created_by', \Auth::user()->creatorId())
                            ->when($type, function($query, $type) {
                                return $query->where('type', $type);
                            })
                            ->get();
            
            return response()->json([
                'success' => true,
                'data' => $holidays
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
 * Get holiday calendar data
 */
public function calendarData(Request $request)
{
    $user = \Auth::user();
    $creatorId = $user->creatorId();
    
    $holidays = Holiday::where('created_by', $creatorId)
        ->where(function($query) use ($user) {
            if ($user->type != 'super admin' && $user->type != 'company') {
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $query->where('applicable_to', 'all')
                        ->orWhereHas('departments', function($q) use ($employee) {
                            $q->where('department_id', $employee->department_id);
                        });
                }
            }
        })
        ->get();

    $events = [];
    foreach ($holidays as $holiday) {
        $events[] = [
            'id' => $holiday->id,
            'title' => $holiday->occasion,
            'start' => $holiday->date,
            'end' => $holiday->end_date ?? $holiday->date,
            'color' => $holiday->type == 'week_off' ? '#ffc107' : '#6fd943',
            'extendedProps' => [
                'type' => ucfirst(str_replace('_', ' ', $holiday->type)),
                'date' => $holiday->date . ($holiday->end_date ? ' - ' . $holiday->end_date : ''),
                'description' => $holiday->description ?? 'No description',
                'applicable' => $holiday->applicable_to == 'all' ? 'All Employees' : 'Specific Departments',
                'is_paid' => $holiday->is_paid ? 'Yes' : 'No',
            ]
        ];
    }

    return response()->json([
        'success' => true,
        'events' => $events
    ]);
}

    // ============================================================
    // CHECK IF DATE IS HOLIDAY
    // ============================================================
    public function checkDate(Request $request)
    {
        try {
            $date = $request->get('date', date('Y-m-d'));
            
            // Check if date falls within any holiday range
            $holiday = Holiday::where('created_by', \Auth::user()->creatorId())
                            ->where('date', '<=', $date)
                            ->where('end_date', '>=', $date)
                            ->first();
            
            // Check if date is a week off
            $dayOfWeek = date('N', strtotime($date));
            $weekOff = Holiday::where('created_by', \Auth::user()->creatorId())
                            ->where('type', 'week_off')
                            ->whereJsonContains('week_off_days', (string)$dayOfWeek)
                            ->first();
            
            return response()->json([
                'success' => true,
                'is_holiday' => !is_null($holiday),
                'is_week_off' => !is_null($weekOff),
                'holiday' => $holiday,
                'week_off' => $weekOff,
                'date' => $date
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}