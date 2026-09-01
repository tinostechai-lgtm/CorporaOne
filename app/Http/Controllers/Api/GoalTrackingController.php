<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoalTracking;
use App\Models\GoalType;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoalTrackingController extends Controller
{
    /**
     * List all goal tracking records
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $goalTrackings = GoalTracking::where('created_by', $creatorId)
            ->with(['goalType', 'branch'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $goalTrackings
        ]);
    }

    /**
     * Store a new goal tracking record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'goal_type' => 'required|exists:goal_types,id',
            'subject' => 'required|string|max:255',
            'branch' => 'required|exists:branches,id',
            'target_achievement' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rating' => 'nullable|numeric|min:0|max:5',
            'progress' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $goalTracking = new GoalTracking();
        $goalTracking->fill($request->all());
        $goalTracking->created_by = $request->user()->creatorId();
        $goalTracking->save();

        return response()->json([
            'success' => true,
            'message' => 'Goal tracking created successfully',
            'data' => $goalTracking->load(['goalType', 'branch'])
        ], 201);
    }

    /**
     * Show a specific goal tracking record
     */
    public function show(Request $request, $id)
    {
        $goalTracking = GoalTracking::where('created_by', $request->user()->creatorId())
            ->with(['goalType', 'branch'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $goalTracking]);
    }

    /**
     * Update a goal tracking record
     */
    public function update(Request $request, $id)
    {
        $goalTracking = GoalTracking::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'goal_type' => 'sometimes|exists:goal_types,id',
            'subject' => 'sometimes|string|max:255',
            'branch' => 'sometimes|exists:branches,id',
            'target_achievement' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'rating' => 'nullable|numeric|min:0|max:5',
            'progress' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $goalTracking->fill($request->only([
            'goal_type', 'subject', 'branch', 'target_achievement',
            'start_date', 'end_date', 'rating', 'progress', 'description'
        ]));
        $goalTracking->save();

        return response()->json([
            'success' => true,
            'message' => 'Goal tracking updated successfully',
            'data' => $goalTracking->load(['goalType', 'branch'])
        ]);
    }

    /**
     * Delete a goal tracking record
     */
    public function destroy(Request $request, $id)
    {
        $goalTracking = GoalTracking::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $goalTracking->delete();

        return response()->json(['success' => true, 'message' => 'Goal tracking deleted successfully']);
    }
}