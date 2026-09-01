<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warning;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarningController extends Controller
{
    /**
     * List all warnings (with optional filters)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
            }
            $warnings = Warning::where('warning_by', $employee->id)
                ->with(['warningTo'])
                ->get();
        } else {
            $warnings = Warning::where('created_by', $creatorId)
                ->with(['warningTo'])
                ->get();
        }

        return response()->json(['success' => true, 'data' => $warnings]);
    }

    /**
     * Store a new warning
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $rules = [
            'warning_to' => 'required|exists:employees,id',
            'subject' => 'required|string|max:255',
            'warning_date' => 'required|date',
            'description' => 'nullable|string',
        ];

        // If user is not an Employee, warning_by is required
        if ($user->type != 'Employee') {
            $rules['warning_by'] = 'required|exists:employees,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $warning = new Warning();

        // Set warning_by
        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
            }
            $warning->warning_by = $employee->id;
        } else {
            $warning->warning_by = $request->warning_by;
        }

        $warning->warning_to = $request->warning_to;
        $warning->subject = $request->subject;
        $warning->warning_date = $request->warning_date;
        $warning->description = $request->description;
        $warning->created_by = $user->creatorId();
        $warning->save();

        // Email notification (optional – can be skipped for API)
        // $settings = Utility::settings();
        // if ($settings['warning_sent'] == 1) { ... }

        return response()->json([
            'success' => true,
            'message' => 'Warning created successfully',
            'data' => $warning->load('warningTo')
        ], 201);
    }

    /**
     * Show a specific warning
     */
    public function show(Request $request, $id)
    {
        $warning = Warning::where('created_by', $request->user()->creatorId())
            ->with(['warningTo'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $warning]);
    }

    /**
     * Update a warning
     */
    public function update(Request $request, $id)
    {
        $warning = Warning::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $user = $request->user();

        $rules = [
            'warning_to' => 'sometimes|exists:employees,id',
            'subject' => 'sometimes|string|max:255',
            'warning_date' => 'sometimes|date',
            'description' => 'nullable|string',
        ];

        if ($user->type != 'Employee') {
            $rules['warning_by'] = 'sometimes|exists:employees,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Update warning_by if user is not Employee and field is provided
        if ($user->type != 'Employee' && $request->has('warning_by')) {
            $warning->warning_by = $request->warning_by;
        }
        // If user is Employee, warning_by cannot be changed (set by system)

        if ($request->has('warning_to')) {
            $warning->warning_to = $request->warning_to;
        }
        if ($request->has('subject')) {
            $warning->subject = $request->subject;
        }
        if ($request->has('warning_date')) {
            $warning->warning_date = $request->warning_date;
        }
        if ($request->has('description')) {
            $warning->description = $request->description;
        }

        $warning->save();

        return response()->json([
            'success' => true,
            'message' => 'Warning updated successfully',
            'data' => $warning->load('warningTo')
        ]);
    }

    /**
     * Delete a warning
     */
    public function destroy(Request $request, $id)
    {
        $warning = Warning::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $warning->delete();

        return response()->json(['success' => true, 'message' => 'Warning deleted successfully']);
    }
}