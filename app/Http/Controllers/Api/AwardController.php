<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Employee;
use App\Models\AwardType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AwardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $awards = Award::where('employee_id', $employee->id)
                ->with(['employee', 'awardType'])
                ->get();
        } else {
            $awards = Award::where('created_by', $creatorId)
                ->with(['employee', 'awardType'])
                ->get();
        }

        return response()->json(['success' => true, 'data' => $awards]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'award_type' => 'required|exists:award_types,id',
            'date' => 'required|date',
            'gift' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $award = new Award();
        $award->fill($request->only(['employee_id', 'award_type', 'date', 'gift', 'description']));
        $award->created_by = $request->user()->creatorId();
        $award->save();

        // Optionally handle notifications (Slack, Telegram, Email, Webhook) – can be added if needed

        return response()->json([
            'success' => true,
            'message' => 'Award created successfully',
            'data' => $award->load(['employee', 'awardType'])
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $award = Award::where('created_by', $request->user()->creatorId())
            ->with(['employee', 'awardType'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $award]);
    }

    public function update(Request $request, $id)
    {
        $award = Award::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|exists:employees,id',
            'award_type' => 'sometimes|exists:award_types,id',
            'date' => 'sometimes|date',
            'gift' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $award->fill($request->only(['employee_id', 'award_type', 'date', 'gift', 'description']));
        $award->save();

        return response()->json([
            'success' => true,
            'message' => 'Award updated successfully',
            'data' => $award->load(['employee', 'awardType'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $award = Award::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $award->delete();

        return response()->json(['success' => true, 'message' => 'Award deleted successfully']);
    }
}