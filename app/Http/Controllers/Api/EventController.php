<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index(Request $request)
    {
        $events = Event::where('created_by', $request->user()->creatorId())
            ->with('eventEmployees')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ], 200);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'color' => 'nullable|string',
            'description' => 'nullable|string',
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $event = new Event();
        $event->fill($request->except('employee'));
        $event->created_by = $request->user()->creatorId();
        $event->save();

        if ($request->has('employee')) {
            foreach ($request->employee as $employeeId) {
                EventEmployee::create([
                    'event_id' => $event->id,
                    'employee_id' => $employeeId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event->load('eventEmployees')
        ], 201);
    }

    /**
     * Display the specified event
     */
    public function show(Request $request, $id)
    {
        $event = Event::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with('eventEmployees')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id)
    {
        $event = Event::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'color' => 'nullable|string',
            'description' => 'nullable|string',
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $event->fill($request->except('employee'));
        $event->save();

        if ($request->has('employee')) {
            EventEmployee::where('event_id', $event->id)->delete();
            foreach ($request->employee as $employeeId) {
                EventEmployee::create([
                    'event_id' => $event->id,
                    'employee_id' => $employeeId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event->load('eventEmployees')
        ]);
    }

    /**
     * Remove the specified event
     */
    public function destroy(Request $request, $id)
    {
        $event = Event::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }
}
