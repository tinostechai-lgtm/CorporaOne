<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    /**
     * Display a listing of leaves
     */
    public function index(Request $request)
    {
        $leaves = Leave::where('created_by', $request->user()->creatorId())
            ->with(['employee', 'leaveType'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaves
        ], 200);
    }

    /**
     * Store a newly created leave
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'applied_on' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_leave_days' => 'required|numeric|min:0.5',
            'leave_reason' => 'nullable|string',
            'remark' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $leave = new Leave();
        $leave->fill($request->all());
        $leave->created_by = $request->user()->creatorId();
        $leave->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave request created successfully',
            'data' => $leave->load(['employee', 'leaveType'])
        ], 201);
    }

    /**
     * Display the specified leave
     */
    public function show(Request $request, $id)
    {
        $leave = Leave::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['employee', 'leaveType'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $leave
        ]);
    }

    /**
     * Update the specified leave
     */
    public function update(Request $request, $id)
    {
        $leave = Leave::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'sometimes|exists:leave_types,id',
            'applied_on' => 'sometimes|date',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'total_leave_days' => 'sometimes|numeric|min:0.5',
            'leave_reason' => 'nullable|string',
            'remark' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $leave->fill($request->only([
            'leave_type_id', 'applied_on', 'start_date', 'end_date', 'total_leave_days', 'leave_reason', 'remark', 'status'
        ]));
        $leave->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully',
            'data' => $leave->load(['employee', 'leaveType'])
        ]);
    }

    /**
     * Remove the specified leave
     */
    public function destroy(Request $request, $id)
    {
        $leave = Leave::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave request deleted successfully'
        ]);
    }

    /**
     * Get leave types
     */
    public function leaveTypes(Request $request)
    {
        $leaveTypes = LeaveType::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $leaveTypes
        ]);
    }

    /**
     * Store a new leave type
     */
    public function storeLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'days' => 'required|numeric|min:0',
            'color' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $leaveType = new LeaveType();
        $leaveType->fill($request->all());
        $leaveType->created_by = $request->user()->creatorId();
        $leaveType->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave type created successfully',
            'data' => $leaveType
        ], 201);
    }

    /**
     * Update leave type
     */
    public function updateLeaveType(Request $request, $id)
    {
        $leaveType = LeaveType::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'days' => 'sometimes|numeric|min:0',
            'color' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $leaveType->fill($request->only(['title', 'days', 'color']));
        $leaveType->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave type updated successfully',
            'data' => $leaveType
        ]);
    }

    /**
     * Delete leave type
     */
    public function destroyLeaveType(Request $request, $id)
    {
        $leaveType = LeaveType::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $leaveType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave type deleted successfully'
        ]);
    }
}
