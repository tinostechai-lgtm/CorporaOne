<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\Trainer;
use App\Models\TrainingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrainingController extends Controller
{
    /**
     * Display a listing of trainings
     */
    public function indexTrainings(Request $request)
    {
        $trainings = Training::where('created_by', $request->user()->creatorId())
            ->with(['trainer', 'trainingType', 'employees'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trainings
        ], 200);
    }

    /**
     * Store a newly created training
     */
    public function storeTraining(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch' => 'nullable|exists:branches,id',
            'trainer_option' => 'required|in:internal,external',
            'trainer' => 'nullable|exists:trainers,id',
            'training_type' => 'nullable|exists:training_types,id',
            'training_cost' => 'nullable|numeric',
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $training = new Training();
        $training->fill($request->except('employee'));
        $training->created_by = $request->user()->creatorId();
        $training->save();

        if ($request->has('employee')) {
            $training->employees()->sync($request->employee);
        }

        return response()->json([
            'success' => true,
            'message' => 'Training created successfully',
            'data' => $training->load(['trainer', 'trainingType', 'employees'])
        ], 201);
    }

    /**
     * Display the specified training
     */
    public function showTraining(Request $request, $id)
    {
        $training = Training::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['trainer', 'trainingType', 'employees'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $training
        ]);
    }

    /**
     * Update the specified training
     */
    public function updateTraining(Request $request, $id)
    {
        $training = Training::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'branch' => 'nullable|exists:branches,id',
            'trainer_option' => 'sometimes|in:internal,external',
            'trainer' => 'nullable|exists:trainers,id',
            'training_type' => 'nullable|exists:training_types,id',
            'training_cost' => 'nullable|numeric',
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $training->fill($request->except('employee'));
        $training->save();

        if ($request->has('employee')) {
            $training->employees()->sync($request->employee);
        }

        return response()->json([
            'success' => true,
            'message' => 'Training updated successfully',
            'data' => $training->load(['trainer', 'trainingType', 'employees'])
        ]);
    }

    /**
     * Remove the specified training
     */
    public function destroyTraining(Request $request, $id)
    {
        $training = Training::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $training->delete();

        return response()->json([
            'success' => true,
            'message' => 'Training deleted successfully'
        ]);
    }

    /**
     * Display a listing of trainers
     */
    public function indexTrainers(Request $request)
    {
        $trainers = Trainer::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $trainers
        ], 200);
    }

    /**
     * Store a newly created trainer
     */
    public function storeTrainer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'contact' => 'nullable|string',
            'email' => 'required|email|unique:trainers,email',
            'expertise' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $trainer = new Trainer();
        $trainer->fill($request->all());
        $trainer->created_by = $request->user()->creatorId();
        $trainer->save();

        return response()->json([
            'success' => true,
            'message' => 'Trainer created successfully',
            'data' => $trainer
        ], 201);
    }

    /**
     * Display the specified trainer
     */
    public function showTrainer(Request $request, $id)
    {
        $trainer = Trainer::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $trainer
        ]);
    }

    /**
     * Update the specified trainer
     */
    public function updateTrainer(Request $request, $id)
    {
        $trainer = Trainer::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'contact' => 'nullable|string',
            'email' => 'sometimes|email|unique:trainers,email,' . $id,
            'expertise' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $trainer->fill($request->only(['firstname', 'lastname', 'contact', 'email', 'expertise']));
        $trainer->save();

        return response()->json([
            'success' => true,
            'message' => 'Trainer updated successfully',
            'data' => $trainer
        ]);
    }

    /**
     * Remove the specified trainer
     */
    public function destroyTrainer(Request $request, $id)
    {
        $trainer = Trainer::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $trainer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trainer deleted successfully'
        ]);
    }
}
