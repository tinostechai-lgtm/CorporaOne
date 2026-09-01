<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $complaints = Complaint::where('complaint_from', $employee->id)
                ->with(['complaintFrom'])
                ->get();
        } else {
            $complaints = Complaint::where('created_by', $creatorId)
                ->with(['complaintFrom'])
                ->get();
        }

        return response()->json(['success' => true, 'data' => $complaints]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $rules = [
            'complaint_against' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'complaint_date' => 'required|date',
            'description' => 'nullable|string',
        ];

        // If user is not an Employee, complaint_from is required
        if ($user->type != 'Employee') {
            $rules['complaint_from'] = 'required|exists:employees,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $complaint = new Complaint();

        // Set complaint_from
        if ($user->type == 'Employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            $complaint->complaint_from = $employee->id;
        } else {
            $complaint->complaint_from = $request->complaint_from;
        }

        $complaint->complaint_against = $request->complaint_against;
        $complaint->title = $request->title;
        $complaint->complaint_date = $request->complaint_date;
        $complaint->description = $request->description;
        $complaint->created_by = $user->creatorId();
        $complaint->save();

        // Optionally send email (you can keep or skip for API)
        // ...

        return response()->json([
            'success' => true,
            'message' => 'Complaint created successfully',
            'data' => $complaint->load('complaintFrom')
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $complaint = Complaint::where('created_by', $request->user()->creatorId())
            ->with(['complaintFrom'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $complaint]);
    }

    public function update(Request $request, $id)
    {
        $complaint = Complaint::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $user = $request->user();

        $rules = [
            'complaint_against' => 'sometimes|exists:employees,id',
            'title' => 'sometimes|string|max:255',
            'complaint_date' => 'sometimes|date',
            'description' => 'nullable|string',
        ];

        if ($user->type != 'Employee') {
            $rules['complaint_from'] = 'sometimes|exists:employees,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Update complaint_from if provided and user is not Employee
        if ($user->type != 'Employee' && $request->has('complaint_from')) {
            $complaint->complaint_from = $request->complaint_from;
        }

        if ($request->has('complaint_against')) {
            $complaint->complaint_against = $request->complaint_against;
        }
        if ($request->has('title')) {
            $complaint->title = $request->title;
        }
        if ($request->has('complaint_date')) {
            $complaint->complaint_date = $request->complaint_date;
        }
        if ($request->has('description')) {
            $complaint->description = $request->description;
        }

        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'Complaint updated successfully',
            'data' => $complaint->load('complaintFrom')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $complaint = Complaint::where('created_by', $request->user()->creatorId())->findOrFail($id);
        $complaint->delete();

        return response()->json(['success' => true, 'message' => 'Complaint deleted successfully']);
    }
}
