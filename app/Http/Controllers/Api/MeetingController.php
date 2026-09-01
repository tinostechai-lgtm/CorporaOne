<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $meetings = Meeting::where('created_by', $request->user()->creatorId())
            ->with('meetingEmployees')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $meetings
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'duration' => 'nullable|numeric',
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

        $meeting = new Meeting();
        $meeting->fill($request->except('employee'));
        $meeting->created_by = $request->user()->creatorId();
        $meeting->save();

        if ($request->has('employee')) {
            $creatorId = $request->user()->creatorId();
            foreach ($request->employee as $employeeId) {
                MeetingEmployee::create([
                    'meeting_id' => $meeting->id,
                    'employee_id' => $employeeId,
                    'created_by' => $creatorId,   // ✅ now set
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Meeting created successfully',
            'data' => $meeting->load('meetingEmployees')
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $meeting = Meeting::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with('meetingEmployees')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $meeting
        ]);
    }

    public function update(Request $request, $id)
    {
        $meeting = Meeting::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'time' => 'sometimes',
            'duration' => 'nullable|numeric',
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

        $meeting->fill($request->except('employee'));
        $meeting->save();

        if ($request->has('employee')) {
            // Delete old assignments
            MeetingEmployee::where('meeting_id', $meeting->id)->delete();
            $creatorId = $request->user()->creatorId();
            foreach ($request->employee as $employeeId) {
                MeetingEmployee::create([
                    'meeting_id' => $meeting->id,
                    'employee_id' => $employeeId,
                    'created_by' => $creatorId,   // ✅ now set
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Meeting updated successfully',
            'data' => $meeting->load('meetingEmployees')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $meeting = Meeting::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $meeting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meeting deleted successfully'
        ]);
    }
}