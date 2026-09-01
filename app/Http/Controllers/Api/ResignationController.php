<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resignation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResignationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $resignations = Resignation::where('created_by', $creatorId)
                ->where('employee_id', $employee->id)
                ->with('employee')
                ->get();
        } else {
            $resignations = Resignation::where('created_by', $creatorId)
                ->with('employee')
                ->get();
        }

        return response()->json(['success' => true, 'data' => $resignations]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'notice_date' => 'required|date',
            'resignation_date' => 'required|date|after_or_equal:notice_date',
            'description' => 'nullable|string',
        ];

        // If user is not Employee, employee_id is required
        if ($user->type != 'Employee') {
            $rules['employee_id'] = 'required|exists:employees,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $resignation = new Resignation();

        // Set employee_id
        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $resignation->employee_id = $employee->id;
        } else {
            $resignation->employee_id = $request->employee_id;
        }

        $resignation->notice_date = $request->notice_date;
        $resignation->resignation_date = $request->resignation_date;
        $resignation->description = $request->description;
        $resignation->created_by = $user->creatorId();
        $resignation->save();

        // Optionally send email notification (skip for API or keep as needed)
        // ...

        return response()->json([
            'success' => true,
            'message' => 'Resignation created successfully',
            'data' => $resignation->load('employee')
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $resignation = Resignation::where('created_by', $request->user()->creatorId())
            ->with('employee')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $resignation]);
    }

    public function update(Request $request, $id)
    {
        $resignation = Resignation::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $user = $request->user();

        $rules = [
            'notice_date' => 'sometimes|date',
            'resignation_date' => 'sometimes|date|after_or_equal:notice_date',
            'description' => 'nullable|string',
        ];

        if ($user->type != 'Employee') {
            $rules['employee_id'] = 'sometimes|exists:employees,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Update employee_id only if user is not Employee and field is provided
        if ($user->type != 'Employee' && $request->has('employee_id')) {
            $resignation->employee_id = $request->employee_id;
        }

        if ($request->has('notice_date')) {
            $resignation->notice_date = $request->notice_date;
        }
        if ($request->has('resignation_date')) {
            $resignation->resignation_date = $request->resignation_date;
        }
        if ($request->has('description')) {
            $resignation->description = $request->description;
        }

        $resignation->save();

        return response()->json([
            'success' => true,
            'message' => 'Resignation updated successfully',
            'data' => $resignation->load('employee')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $resignation = Resignation::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $resignation->delete();

        return response()->json(['success' => true, 'message' => 'Resignation deleted successfully']);
    }
}