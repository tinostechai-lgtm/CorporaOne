<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IndicatorController extends Controller
{
    /**
     * List all indicators
     */
    public function index(Request $request)
    {
        $indicators = Indicator::where('created_by', $request->user()->creatorId())
            ->with(['branches', 'departments', 'designations'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $indicators
        ]);
    }

    /**
     * Store a new indicator
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch' => 'required|exists:branches,id',
            'department' => 'required|exists:departments,id',
            'designation' => 'required|exists:designations,id',
            'customer_experience' => 'nullable|integer|min:0|max:5',
            'marketing' => 'nullable|integer|min:0|max:5',
            'administration' => 'nullable|integer|min:0|max:5',
            'professionalism' => 'nullable|integer|min:0|max:5',
            'integrity' => 'nullable|integer|min:0|max:5',
            'attendance' => 'nullable|integer|min:0|max:5',
            'technical' => 'nullable|array',
            'technical.*' => 'string',
            'organizational' => 'nullable|array',
            'organizational.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $indicator = new Indicator();
        $indicator->fill($request->all());
        $indicator->created_by = $request->user()->creatorId();
        $indicator->save();

        return response()->json([
            'success' => true,
            'message' => 'Indicator created successfully',
            'data' => $indicator->load(['branches', 'departments', 'designations'])
        ], 201);
    }

    /**
     * Show a specific indicator
     */
    public function show(Request $request, $id)
    {
        $indicator = Indicator::where('created_by', $request->user()->creatorId())
            ->with(['branches', 'departments', 'designations'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $indicator]);
    }

    /**
     * Update an indicator
     */
    public function update(Request $request, $id)
    {
        $indicator = Indicator::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'branch' => 'sometimes|exists:branches,id',
            'department' => 'sometimes|exists:departments,id',
            'designation' => 'sometimes|exists:designations,id',
            'customer_experience' => 'nullable|integer|min:0|max:5',
            'marketing' => 'nullable|integer|min:0|max:5',
            'administration' => 'nullable|integer|min:0|max:5',
            'professionalism' => 'nullable|integer|min:0|max:5',
            'integrity' => 'nullable|integer|min:0|max:5',
            'attendance' => 'nullable|integer|min:0|max:5',
            'technical' => 'nullable|array',
            'technical.*' => 'string',
            'organizational' => 'nullable|array',
            'organizational.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $indicator->fill($request->only([
            'branch', 'department', 'designation',
            'customer_experience', 'marketing', 'administration',
            'professionalism', 'integrity', 'attendance',
            'technical', 'organizational'
        ]));
        $indicator->save();

        return response()->json([
            'success' => true,
            'message' => 'Indicator updated successfully',
            'data' => $indicator->load(['branches', 'departments', 'designations'])
        ]);
    }

    /**
     * Delete an indicator
     */
    public function destroy(Request $request, $id)
    {
        $indicator = Indicator::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $indicator->delete();

        return response()->json(['success' => true, 'message' => 'Indicator deleted successfully']);
    }
}