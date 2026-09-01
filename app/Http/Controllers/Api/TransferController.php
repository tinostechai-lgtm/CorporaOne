<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    /**
     * Display a listing of transfers.
     * GET /api/transfers
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Check permission
            if (!$user->can('manage transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. You cannot manage transfers.',
                ], 403);
            }

            $creatorId = $user->creatorId();
            $query = Transfer::where('created_by', $creatorId)
                ->with(['employee', 'branch', 'department']);

            // Apply filters
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('transfer_date', [$request->from_date, $request->to_date]);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Get transfers
            $transfers = $query->orderBy('created_at', 'desc')
                ->paginate($request->limit ?? 50);

            return response()->json([
                'success' => true,
                'data' => $transfers->items(),
                'pagination' => [
                    'total' => $transfers->total(),
                    'per_page' => $transfers->perPage(),
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created transfer.
     * POST /api/transfers
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            // Check permission
            if (!$user->can('create transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. You cannot create transfers.',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'branch_id' => 'required|exists:branches,id',
                'department_id' => 'required|exists:departments,id',
                'transfer_date' => 'required|date|date_format:Y-m-d',
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|in:pending,approved,rejected,cancelled',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $creatorId = $user->creatorId();

            // Check if employee exists and belongs to the same company
            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $request->employee_id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found or does not belong to your company.',
                ], 404);
            }

            // Create transfer
            $transfer = new Transfer();
            $transfer->employee_id = $request->employee_id;
            $transfer->branch_id = $request->branch_id;
            $transfer->department_id = $request->department_id;
            $transfer->transfer_date = $request->transfer_date;
            $transfer->description = $request->description;
            $transfer->status = $request->status ?? 'pending';
            $transfer->created_by = $creatorId;
            $transfer->save();

            // Load relationships
            $transfer->load(['employee', 'branch', 'department']);

            // Send email notification if enabled
            $this->sendTransferNotification($transfer, 'created');

            Log::info('Transfer created successfully', [
                'transfer_id' => $transfer->id,
                'employee_id' => $transfer->employee_id,
                'created_by' => $creatorId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer created successfully.',
                'data' => $transfer,
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified transfer.
     * GET /api/transfers/{id}
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            // Check permission
            if (!$user->can('manage transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. You cannot view transfers.',
                ], 403);
            }

            $creatorId = $user->creatorId();

            $transfer = Transfer::where('created_by', $creatorId)
                ->with(['employee', 'branch', 'department'])
                ->find($id);

            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $transfer,
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified transfer.
     * PUT /api/transfers/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Check permission
            if (!$user->can('edit transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. You cannot edit transfers.',
                ], 403);
            }

            $creatorId = $user->creatorId();

            $transfer = Transfer::where('created_by', $creatorId)
                ->find($id);

            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'employee_id' => 'sometimes|exists:employees,id',
                'branch_id' => 'sometimes|exists:branches,id',
                'department_id' => 'sometimes|exists:departments,id',
                'transfer_date' => 'sometimes|date|date_format:Y-m-d',
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|in:pending,approved,rejected,cancelled',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Update transfer
            $transfer->fill($request->only([
                'employee_id',
                'branch_id',
                'department_id',
                'transfer_date',
                'description',
                'status',
            ]));

            $transfer->save();

            // Load relationships
            $transfer->load(['employee', 'branch', 'department']);

            // Send notification if status changed
            if ($request->has('status')) {
                $this->sendTransferNotification($transfer, 'status_updated');
            }

            Log::info('Transfer updated successfully', [
                'transfer_id' => $transfer->id,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer updated successfully.',
                'data' => $transfer,
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified transfer.
     * DELETE /api/transfers/{id}
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            // Check permission
            if (!$user->can('delete transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. You cannot delete transfers.',
                ], 403);
            }

            $creatorId = $user->creatorId();

            $transfer = Transfer::where('created_by', $creatorId)
                ->find($id);

            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer not found.',
                ], 404);
            }

            $transfer->delete();

            Log::info('Transfer deleted successfully', [
                'transfer_id' => $id,
                'deleted_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update transfer status.
     * PATCH /api/transfers/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Check permission
            if (!$user->can('edit transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. You cannot update transfer status.',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,approved,rejected,cancelled',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $creatorId = $user->creatorId();

            $transfer = Transfer::where('created_by', $creatorId)
                ->find($id);

            if (!$transfer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer not found.',
                ], 404);
            }

            $transfer->status = $request->status;
            $transfer->save();

            // Load relationships
            $transfer->load(['employee', 'branch', 'department']);

            // Send notification
            $this->sendTransferNotification($transfer, 'status_updated');

            Log::info('Transfer status updated', [
                'transfer_id' => $transfer->id,
                'status' => $request->status,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer status updated successfully.',
                'data' => $transfer,
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer status update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transfer status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transfers for a specific employee.
     * GET /api/transfers/employee/{employeeId}
     */
    public function getEmployeeTransfers(Request $request, $employeeId)
    {
        try {
            $user = $request->user();

            // Check permission
            if (!$user->can('manage transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.',
                ], 403);
            }

            $creatorId = $user->creatorId();

            $employee = Employee::where('created_by', $creatorId)
                ->where('id', $employeeId)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            $transfers = Transfer::where('created_by', $creatorId)
                ->where('employee_id', $employeeId)
                ->with(['employee', 'branch', 'department'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->limit ?? 50);

            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => $employee,
                    'transfers' => $transfers->items(),
                    'pagination' => [
                        'total' => $transfers->total(),
                        'per_page' => $transfers->perPage(),
                        'current_page' => $transfers->currentPage(),
                        'last_page' => $transfers->lastPage(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Employee transfers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee transfers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get transfer statistics.
     * GET /api/transfers/stats
     */
    public function getStats(Request $request)
    {
        try {
            $user = $request->user();

            // Check permission
            if (!$user->can('manage transfer')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied.',
                ], 403);
            }

            $creatorId = $user->creatorId();

            $stats = [
                'total' => Transfer::where('created_by', $creatorId)->count(),
                'pending' => Transfer::where('created_by', $creatorId)->where('status', 'pending')->count(),
                'approved' => Transfer::where('created_by', $creatorId)->where('status', 'approved')->count(),
                'rejected' => Transfer::where('created_by', $creatorId)->where('status', 'rejected')->count(),
                'cancelled' => Transfer::where('created_by', $creatorId)->where('status', 'cancelled')->count(),
            ];

            // Monthly stats
            $monthlyStats = Transfer::where('created_by', $creatorId)
                ->selectRaw('YEAR(transfer_date) as year, MONTH(transfer_date) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'monthly' => $monthlyStats,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfer statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send transfer notification.
     */
    private function sendTransferNotification($transfer, $action = 'created')
    {
        try {
            $settings = Utility::settings();

            if ($settings['transfer_sent'] != 1) {
                return;
            }

            $employee = Employee::find($transfer->employee_id);
            $branch = Branch::find($transfer->branch_id);
            $department = Department::find($transfer->department_id);

            if (!$employee) {
                return;
            }

            $transferArr = [
                'transfer_name' => $employee->name,
                'transfer_email' => $employee->email,
                'transfer_date' => $transfer->transfer_date,
                'transfer_department' => $department ? $department->name : 'N/A',
                'transfer_branch' => $branch ? $branch->name : 'N/A',
                'transfer_description' => $transfer->description ?? 'No description provided',
                'transfer_status' => ucfirst($transfer->status),
                'action' => $action,
            ];

            // Send email
            $resp = Utility::sendEmailTemplate('transfer_sent', [$employee->id => $employee->email], $transferArr);

            if ($resp['is_success'] == false && !empty($resp['error'])) {
                Log::warning('Transfer notification email failed: ' . $resp['error']);
            }

        } catch (\Exception $e) {
            Log::error('Transfer notification error: ' . $e->getMessage());
        }
    }
}