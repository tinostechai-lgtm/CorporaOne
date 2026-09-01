<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Employee;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $promotions = Promotion::where('created_by', $creatorId)
                ->where('employee_id', $employee->id)
                ->with(['designation', 'employee'])
                ->get();
        } else {
            $promotions = Promotion::where('created_by', $creatorId)
                ->with(['designation', 'employee'])
                ->get();
        }

        return response()->json(['success' => true, 'data' => $promotions]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'designation_id' => 'required|exists:designations,id',
            'promotion_title' => 'required|string|max:255',
            'promotion_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $promotion = new Promotion();
        $promotion->fill($request->only(['employee_id', 'designation_id', 'promotion_title', 'promotion_date', 'description']));
        $promotion->created_by = $request->user()->creatorId();
        $promotion->save();

        // Optionally send email notification (you can keep or skip for API)
        // ...

        return response()->json([
            'success' => true,
            'message' => 'Promotion created successfully',
            'data' => $promotion->load(['designation', 'employee'])
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $promotion = Promotion::where('created_by', $request->user()->creatorId())
            ->with(['designation', 'employee'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $promotion]);
    }

    public function update(Request $request, $id)
    {
        $promotion = Promotion::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|exists:employees,id',
            'designation_id' => 'sometimes|exists:designations,id',
            'promotion_title' => 'sometimes|string|max:255',
            'promotion_date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $promotion->fill($request->only(['employee_id', 'designation_id', 'promotion_title', 'promotion_date', 'description']));
        $promotion->save();

        return response()->json([
            'success' => true,
            'message' => 'Promotion updated successfully',
            'data' => $promotion->load(['designation', 'employee'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $promotion = Promotion::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $promotion->delete();

        return response()->json(['success' => true, 'message' => 'Promotion deleted successfully']);
    }
}