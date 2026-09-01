<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appraisal;
use App\Models\Goal;
use App\Models\Competencies;
use App\Models\GoalTracking;
use App\Models\Indicator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerformanceController extends Controller
{
    /**
     * Display a listing of appraisals
     */
    public function indexAppraisals(Request $request)
    {
        $appraisals = Appraisal::where('created_by', $request->user()->creatorId())
            ->with(['employee'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appraisals
        ], 200);
    }

    /**
     * Store a newly created appraisal
     */
    public function storeAppraisal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee' => 'required|exists:employees,id',
            'appraisal_date' => 'required|date',
            'rating' => 'required|numeric|min:0|max:5',
            'remark' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $appraisal = new Appraisal();
        $appraisal->fill($request->only(['employee', 'appraisal_date', 'rating', 'remark']));
        $appraisal->created_by = $request->user()->creatorId();
        $appraisal->save();

        return response()->json([
            'success' => true,
            'message' => 'Appraisal created successfully',
            'data' => $appraisal->load(['employee'])
        ], 201);
    }

    public function showAppraisal(Request $request, $id)
    {
        $appraisal = Appraisal::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['employee'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $appraisal
        ]);
    }

    public function updateAppraisal(Request $request, $id)
    {
        $appraisal = Appraisal::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'appraisal_date' => 'sometimes|date',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'remark' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $appraisal->fill($request->only(['appraisal_date', 'rating', 'remark']));
        $appraisal->save();

        return response()->json([
            'success' => true,
            'message' => 'Appraisal updated successfully',
            'data' => $appraisal->load(['employee'])
        ]);
    }

    public function destroyAppraisal(Request $request, $id)
    {
        $appraisal = Appraisal::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $appraisal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appraisal deleted successfully'
        ]);
    }

    // ==================== GOALS (Financial) ====================

    public function indexGoals(Request $request)
    {
        $goals = Goal::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $goals
        ], 200);
    }

    public function storeGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:0,1,2,3', // 0=Invoice, 1=Bill, 2=Revenue, 3=Payment
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'amount' => 'required|numeric|min:0',
            'is_display' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $goal = new Goal();
        $goal->fill($request->only(['name', 'type', 'from', 'to', 'amount', 'is_display']));
        $goal->created_by = $request->user()->creatorId();
        $goal->save();

        return response()->json([
            'success' => true,
            'message' => 'Goal created successfully',
            'data' => $goal
        ], 201);
    }

    public function showGoal(Request $request, $id)
    {
        $goal = Goal::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $goal
        ]);
    }

    public function updateGoal(Request $request, $id)
    {
        $goal = Goal::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:0,1,2,3',
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after_or_equal:from',
            'amount' => 'sometimes|numeric|min:0',
            'is_display' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $goal->fill($request->only(['name', 'type', 'from', 'to', 'amount', 'is_display']));
        $goal->save();

        return response()->json([
            'success' => true,
            'message' => 'Goal updated successfully',
            'data' => $goal
        ]);
    }

    public function destroyGoal(Request $request, $id)
    {
        $goal = Goal::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Goal deleted successfully'
        ]);
    }

    // ==================== COMPETENCIES ====================

    public function indexCompetencies(Request $request)
    {
        $competencies = Competencies::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $competencies
        ], 200);
    }

    public function storeCompetency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $competency = new Competencies();
        $competency->fill($request->all());
        $competency->created_by = $request->user()->creatorId();
        $competency->save();

        return response()->json([
            'success' => true,
            'message' => 'Competency created successfully',
            'data' => $competency
        ], 201);
    }

    public function updateCompetency(Request $request, $id)
    {
        $competency = Competencies::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $competency->fill($request->only(['name']));
        $competency->save();

        return response()->json([
            'success' => true,
            'message' => 'Competency updated successfully',
            'data' => $competency
        ]);
    }

    public function destroyCompetency(Request $request, $id)
    {
        $competency = Competencies::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $competency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Competency deleted successfully'
        ]);
    }
}