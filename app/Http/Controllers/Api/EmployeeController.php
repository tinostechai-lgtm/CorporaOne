<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $employees = Employee::where('created_by', $request->user()->creatorId())
            ->with(['department', 'designation', 'branch', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees
        ], 200);
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:employees,email',
            'employee_id' => 'required|string|unique:employees,employee_id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'company_doj' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'salary_type' => 'nullable|exists:payslip_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $employee = new Employee();
        $employee->fill($request->all());
        $employee->created_by = $request->user()->creatorId();
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee->load(['department', 'designation', 'branch', 'user'])
        ], 201);
    }

    /**
     * Display the specified employee
     */
    public function show(Request $request, $id)
    {
        $employee = Employee::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['department', 'designation', 'branch', 'user', 'allowances', 'commissions', 'loans', 'saturationDeductions', 'otherPayments', 'overtimes'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'email' => 'sometimes|email|unique:employees,email,' . $id,
            'employee_id' => 'sometimes|string|unique:employees,employee_id,' . $id,
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'company_doj' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'salary_type' => 'nullable|exists:payslip_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $employee->fill($request->only([
            'name', 'dob', 'gender', 'phone', 'address', 'email', 'employee_id',
            'branch_id', 'department_id', 'designation_id', 'company_doj', 'salary', 'salary_type'
        ]));
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee->load(['department', 'designation', 'branch', 'user'])
        ]);
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Request $request, $id)
    {
        $employee = Employee::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }

    /**
     * Get net salary for an employee
     */
    public function getNetSalary(Request $request, $id)
    {
        $employee = Employee::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $netSalary = $employee->get_net_salary();

        return response()->json([
            'success' => true,
            'data' => [
                'employee_id' => $employee->id,
                'net_salary' => $netSalary
            ]
        ]);
    }
}
