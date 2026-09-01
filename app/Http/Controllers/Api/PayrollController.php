<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaySlip;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Loan;
use App\Models\SaturationDeduction;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    /**
     * Display a listing of payslips
     */
    public function index(Request $request)
    {
        $payslips = PaySlip::where('created_by', $request->user()->id)
            ->with(['employee'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payslips
        ], 200);
    }

    /**
     * Store a newly created payslip (manual creation)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'salary_month' => 'required|string',
            'net_payble' => 'required|numeric',
            'status' => 'nullable|in:paid,unpaid',
            'allowance' => 'nullable|numeric',
            'commission' => 'nullable|numeric',
            'loan' => 'nullable|numeric',
            'saturation_deduction' => 'nullable|numeric',
            'other_payment' => 'nullable|numeric',
            'overtime' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payslip = new PaySlip();
        $payslip->fill($request->all());
        $payslip->created_by = $request->user()->id;
        $payslip->save();

        return response()->json([
            'success' => true,
            'message' => 'Payslip created successfully',
            'data' => $payslip->load('employee')
        ], 201);
    }

    /**
     * Display the specified payslip
     */
    public function show(Request $request, $id)
    {
        $payslip = PaySlip::where('created_by', $request->user()->id)
            ->where('id', $id)
            ->with('employee')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $payslip
        ]);
    }

    /**
     * Update the specified payslip
     */
    public function update(Request $request, $id)
    {
        $payslip = PaySlip::where('created_by', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'salary_month' => 'sometimes|string',
            'net_payble' => 'sometimes|numeric',
            'status' => 'nullable|in:paid,unpaid',
            'allowance' => 'nullable|numeric',
            'commission' => 'nullable|numeric',
            'loan' => 'nullable|numeric',
            'saturation_deduction' => 'nullable|numeric',
            'other_payment' => 'nullable|numeric',
            'overtime' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payslip->fill($request->only([
            'salary_month', 'net_payble', 'status', 'allowance', 'commission',
            'loan', 'saturation_deduction', 'other_payment', 'overtime'
        ]));
        $payslip->save();

        return response()->json([
            'success' => true,
            'message' => 'Payslip updated successfully',
            'data' => $payslip->load('employee')
        ]);
    }

    /**
     * Remove the specified payslip
     */
    public function destroy(Request $request, $id)
    {
        $payslip = PaySlip::where('created_by', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $payslip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payslip deleted successfully'
        ]);
    }

    /**
     * Generate payslip for an employee
     * 
     * Accepts optional overrides in request body:
     * - basic_salary (numeric)
     * - allowance (numeric)
     * - commission (numeric)
     * - loan (numeric)
     * - saturation_deduction (numeric)
     * - other_payment (numeric)
     * - overtime (numeric)
     * - net_payble (numeric) – if provided, overrides calculation
     * 
     * If not provided, uses employee's stored relationships.
     */
    public function generatePayslip(Request $request, $employeeId)
    {
        $employee = Employee::where('created_by', $request->user()->id)
            ->where('id', $employeeId)
            ->firstOrFail();

        // Get values from request or fallback to employee relationships
        $basicSalary = $request->input('basic_salary', $employee->salary ?? 0);
        $allowance = $request->input('allowance', $employee->allowances->sum('amount'));
        $commission = $request->input('commission', $employee->commissions->sum('amount'));
        $loan = $request->input('loan', $employee->loans->sum('amount'));
        $saturationDeduction = $request->input('saturation_deduction', $employee->saturationDeductions->sum('amount'));
        $otherPayment = $request->input('other_payment', $employee->otherPayments->sum('amount'));
        $overtime = $request->input('overtime', $employee->overtimes->sum(function ($ot) {
            return $ot->number_of_days * $ot->hours * $ot->rate;
        }));

        // Calculate net: base + additions - deductions
        $netPayble = $basicSalary + $allowance + $commission + $otherPayment + $overtime - $loan - $saturationDeduction;

        // Allow explicit net override
        if ($request->has('net_payble')) {
            $netPayble = $request->input('net_payble');
        }

        // Create payslip
        $payslip = new PaySlip();
        $payslip->employee_id = $employee->id;
        $payslip->salary_month = $request->input('salary_month', now()->format('Y-m'));
        $payslip->net_payble = $netPayble;
        $payslip->status = 'unpaid';
        $payslip->allowance = $allowance;
        $payslip->commission = $commission;
        $payslip->loan = $loan;
        $payslip->saturation_deduction = $saturationDeduction;
        $payslip->other_payment = $otherPayment;
        $payslip->overtime = $overtime;
        $payslip->created_by = $request->user()->id;
        $payslip->save();

        return response()->json([
            'success' => true,
            'message' => 'Payslip generated successfully',
            'data' => $payslip->load('employee')
        ], 201);
    }
}