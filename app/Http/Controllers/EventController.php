<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Event as LocalEvent;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use App\Models\EventEmployee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Projects;
use App\Models\Tasks;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('manage event')) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get();
            $events = LocalEvent::where('created_by', '=', \Auth::user()->creatorId())->get();

            $transdate = date('Y-m-d', time());
            $today_date = date('m');
            $current_month_event = LocalEvent::select('id', 'start_date', 'end_date', 'title', 'created_at', 'color')
                ->whereRaw('MONTH(start_date)=' . $today_date)->whereRaw('MONTH(end_date)=' . $today_date)->get();

            $arrEvents = [];
            foreach ($events as $event) {
                $arr['id'] = $event['id'];
                $arr['title'] = $event['title'];
                $arr['start'] = $event['start_date'];
                $arr['end'] = $event['end_date'];
                $arr['className'] = $event['color'];
                $arr['url'] = route('event.edit', $event['id']);
                $arrEvents[] = $arr;
            }
            $arrEvents = json_encode($arrEvents);

            return view('event.index', compact('arrEvents', 'employees', 'transdate', 'events', 'current_month_event'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->can('create event')) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get();
            $settings = Utility::settings();

            return view('event.create', compact('employees', 'branch', 'departments', 'settings'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create event')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'employee_id' => 'required',
                    'title' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'color' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $event = new Event();
            $event->branch_id = $request->branch_id;
            $event->department_id = json_encode($request->department_id);
            $event->employee_id = json_encode($request->employee_id);
            $event->title = $request->title;
            $event->start_date = $request->start_date;
            $event->end_date = $request->end_date;
            $event->color = $request->color;
            $event->description = $request->description;
            $event->created_by = \Auth::user()->creatorId();
            $event->save();

            if (in_array('0', $request->employee_id)) {
                $departmentEmployee = Employee::whereIn('department_id', [$request->department_id])->get()->pluck('id');
                $departmentEmployee = $departmentEmployee;
            } else {
                $departmentEmployee = $request->employee_id;
            }
            foreach ($departmentEmployee as $employee) {
                $eventEmployee = new EventEmployee();
                $eventEmployee->event_id = $event->id;
                $eventEmployee->employee_id = $employee;
                $eventEmployee->created_by = Auth::user()->creatorId();
                $eventEmployee->save();
            }
            //For Notification
            $setting = Utility::settings(\Auth::user()->creatorId());

            if ($request->branch_id == 0) {
                $branch = Branch::all()->pluck('name');

                $result = '';
                $separator = ',';
                foreach ($branch as $value) {
                    if (is_array($value)) {
                        $result .= arrayToString($value, $separator) . $separator;
                    } else {
                        $result .= $value . $separator;
                    }
                }

                $result = rtrim($result, $separator);
            } else {
                $branch = Branch::find($request->branch_id);
                $result = $branch->name;
            }
            $eventNotificationArr = [
                'event_title' => $request->title,
                'branch_name' => $result,
                'event_start_date' => $request->start_date,
                'event_end_date' => $request->end_date,
            ];
            //Slack Notification
            if (isset($setting['event_notification']) && $setting['event_notification'] == 1) {
                Utility::send_slack_msg('new_event', $eventNotificationArr);
            }
            //Telegram Notification
            if (isset($setting['telegram_event_notification']) && $setting['telegram_event_notification'] == 1) {
                Utility::send_telegram_msg('new_event', $eventNotificationArr);
            }

            //For Google Calendar
            if ($request->get('synchronize_type') == 'google_calender') {
                $type = 'event';
                Utility::addCalendarData($request, $type);
            }
            //webhook
            $module = 'New Event';
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($event);
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
            }

            return redirect()->route('event.index')->with('success', __('Event  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return redirect()->route('event.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($event)
    {
        $event = LocalEvent::find($event);
        if ($event->created_by == Auth::user()->creatorId()) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('event.edit', compact('event', 'employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        if (\Auth::user()->can('edit event')) {
            if ($event->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'color' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $event->title = $request->title;
                $event->start_date = $request->start_date;
                $event->end_date = $request->end_date;
                $event->color = $request->color;
                $event->description = $request->description;
                $event->save();

                return redirect()->route('event.index')->with('success', __('Event successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        if (\Auth::user()->can('delete event')) {
            if ($event->created_by == \Auth::user()->creatorId()) {
                $event->delete();

                return redirect()->route('event.index')->with('success', __('Event successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Get departments by branch for AJAX.
     */
    public function getdepartment(Request $request)
    {
        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    /**
     * Get employees by department for AJAX.
     */
    public function getemployee(Request $request)
    {
        $employees = [];
        if (in_array('0', $request->department_id)) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else if (!empty($request->department_id)) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereIn('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
        }
        return response()->json($employees);
    }

    /**
     * Get event data for FullCalendar (Dashboard AJAX)
     * ✅ FIXED: Returns proper JSON response
     */
    public function get_event_data(Request $request)
    {
        try {
            $arrayJson = [];

            if ($request->get('calender_type') == 'goggle_calender') {
                $type = 'event';
                $arrayJson = Utility::getCalendarData($type);
            } else {
                $data = LocalEvent::where('created_by', '=', \Auth::user()->creatorId())->get();
                
                foreach ($data as $val) {
                    $end_date = date_create($val->end_date);
                    date_add($end_date, date_interval_create_from_date_string("1 days"));
                    
                    $arrayJson[] = [
                        "id" => $val->id,
                        "title" => $val->title,
                        "start" => $val->start_date,
                        "end" => date_format($end_date, "Y-m-d H:i:s"),
                        "className" => $val->color,
                        'url' => route('event.edit', $val->id),
                        "allDay" => true,
                    ];
                }
            }

            // ✅ FIX: Return JSON response
            return response()->json($arrayJson);

        } catch (\Exception $e) {
            \Log::error('get_event_data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternative method for getEventData (if called via route)
     */
    public function getEventData(Request $request)
    {
        try {
            $arrayJson = [];

            if ($request->get('calender_type') == 'goggle_calender') {
                $type = 'event';
                $arrayJson = Utility::getCalendarData($type);
            } else {
                $data = LocalEvent::where('created_by', '=', \Auth::user()->creatorId())->get();
                
                foreach ($data as $val) {
                    $end_date = date_create($val->end_date);
                    date_add($end_date, date_interval_create_from_date_string("1 days"));
                    
                    $arrayJson[] = [
                        "id" => $val->id,
                        "title" => $val->title,
                        "start" => $val->start_date,
                        "end" => date_format($end_date, "Y-m-d H:i:s"),
                        "className" => $val->color,
                        'url' => route('event.edit', $val->id),
                        "allDay" => true,
                    ];
                }
            }

            return response()->json($arrayJson);

        } catch (\Exception $e) {
            \Log::error('getEventData error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard event data (for dashboard calendar)
     */
    public function get_dashboard_event_data(Request $request)
    {
        try {
            $arrayJson = [];
            $user = \Auth::user();
            $creatorId = $user->creatorId();

            // Get events
            $data = LocalEvent::where('created_by', '=', $creatorId)->get();
            
            foreach ($data as $val) {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                
                $arrayJson[] = [
                    "id" => 'event_' . $val->id,
                    "title" => $val->title,
                    "start" => $val->start_date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => $val->color,
                    "backgroundColor" => $val->color,
                    "borderColor" => $val->color,
                    'url' => route('event.edit', $val->id),
                    "allDay" => true,
                    "extendedProps" => [
                        'type' => 'event',
                        'description' => $val->description ?? '',
                    ],
                ];
            }

            // Add holidays if Holiday model exists
            if (class_exists('App\Models\Holiday')) {
                $holidays = Holiday::where('created_by', '=', $creatorId)->get();
                foreach ($holidays as $holiday) {
                    $backgroundColor = '#dc3545'; // Red for holidays
                    $textColor = '#ffffff';
                    $typeLabel = '🎉 Holiday';
                    
                    if ($holiday->type == 'week_off') {
                        $backgroundColor = '#ffc107'; // Yellow for week-offs
                        $textColor = '#000000';
                        $typeLabel = '📅 Week Off';
                    }
                    
                    $arrayJson[] = [
                        "id" => 'holiday_' . $holiday->id,
                        "title" => $typeLabel . ': ' . ($holiday->title ?? $holiday->occasion ?? $holiday->type),
                        "start" => $holiday->date,
                        "end" => $holiday->end_date ?? $holiday->date,
                        "className" => $backgroundColor,
                        "backgroundColor" => $backgroundColor,
                        "borderColor" => $backgroundColor,
                        "textColor" => $textColor,
                        "allDay" => true,
                        "extendedProps" => [
                            'type' => 'holiday',
                            'holiday_type' => $holiday->type ?? 'holiday',
                            'description' => $holiday->description ?? '',
                            'applicable_to' => $holiday->applicable_to ?? 'All Employees',
                            'is_paid' => $holiday->is_paid ?? 'Yes',
                        ],
                    ];
                }
            }

            // Add approved leaves if Leave model exists
            if (class_exists('App\Models\Leave')) {
                $leaves = Leave::where('created_by', '=', $creatorId)
                    ->where('status', 'Approved')
                    ->get();
                
                foreach ($leaves as $leave) {
                    $employee = Employee::find($leave->employee_id);
                    $employeeName = $employee ? $employee->name : 'Employee';
                    
                    $leaveColors = [
                        'Paid' => '#28a745',
                        'Unpaid' => '#fd7e14',
                        'Sick' => '#17a2b8',
                        'Casual' => '#20c997',
                    ];
                    $color = $leaveColors[$leave->leave_type] ?? '#6c757d';
                    
                    $arrayJson[] = [
                        "id" => 'leave_' . $leave->id,
                        "title" => '🏖️ ' . $employeeName . ' - ' . ($leave->leave_type ?? 'Leave'),
                        "start" => $leave->start_date,
                        "end" => $leave->end_date ?? $leave->start_date,
                        "className" => $color,
                        "backgroundColor" => $color,
                        "borderColor" => $color,
                        "textColor" => '#ffffff',
                        "allDay" => true,
                        "extendedProps" => [
                            'type' => 'leave',
                            'employee_name' => $employeeName,
                            'leave_type' => $leave->leave_type ?? '',
                            'duration' => $leave->duration ?? 'Full Day',
                            'reason' => $leave->reason ?? '',
                            'status' => $leave->status ?? 'Approved',
                        ],
                    ];
                }
            }

            return response()->json($arrayJson);

        } catch (\Exception $e) {
            \Log::error('get_dashboard_event_data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch dashboard events'
            ], 500);
        }
    }
}