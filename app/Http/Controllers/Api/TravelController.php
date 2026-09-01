<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TravelController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $travels = Travel::where('created_by', $creatorId)
                ->where('employee_id', $employee->id)
                ->with('employee')
                ->get();
        } else {
            $travels = Travel::where('created_by', $creatorId)
                ->with('employee')
                ->get();
        }

        return response()->json(['success' => true, 'data' => $travels]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'purpose_of_visit' => 'required|string|max:255',
            'place_of_visit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $travel = new Travel();
        $travel->fill($request->only([
            'employee_id',
            'start_date',
            'end_date',
            'purpose_of_visit',
            'place_of_visit',
            'description'
        ]));
        $travel->created_by = $request->user()->creatorId();
        $travel->save();

        // Optionally send email notification (you can keep or skip for API)
        // ...

        return response()->json([
            'success' => true,
            'message' => 'Travel created successfully',
            'data' => $travel->load('employee')
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $travel = Travel::where('created_by', $request->user()->creatorId())
            ->with('employee')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $travel]);
    }

    public function update(Request $request, $id)
    {
        $travel = Travel::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|exists:employees,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'purpose_of_visit' => 'sometimes|string|max:255',
            'place_of_visit' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $travel->fill($request->only([
            'employee_id',
            'start_date',
            'end_date',
            'purpose_of_visit',
            'place_of_visit',
            'description'
        ]));
        $travel->save();

        return response()->json([
            'success' => true,
            'message' => 'Travel updated successfully',
            'data' => $travel->load('employee')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $travel = Travel::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $travel->delete();

        return response()->json(['success' => true, 'message' => 'Travel deleted successfully']);
    }
}