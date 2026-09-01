<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Project;
use App\Models\User;
use App\Models\ContractComment;
use App\Models\Contract_attachment;
use App\Models\ContractNotes;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /**
     * List all contracts (company or client view)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        if ($user->type == 'company') {
            $contracts = Contract::where('created_by', $creatorId)
                ->with(['client', 'project', 'type'])
                ->get();
        } elseif ($user->type == 'client') {
            $contracts = Contract::where('client_name', $user->id)
                ->with(['type'])
                ->get();
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Summary (total, this month, this week, last 30 days)
        $summary = [
            'total' => Contract::getContractSummary($contracts),
            'this_month' => Contract::getContractSummary($contracts->whereMonth('start_date', now()->month)),
            'this_week' => Contract::getContractSummary($contracts->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()])),
            'last_30_days' => Contract::getContractSummary($contracts->where('start_date', '>', now()->subDays(30))),
        ];

        return response()->json([
            'success' => true,
            'data' => $contracts,
            'summary' => $summary
        ]);
    }

    /**
     * Create a new contract
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'company') {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_name' => 'required|exists:users,id',
            'subject' => 'required|string',
            'type' => 'required|exists:contract_types,id',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $contract = Contract::create([
            'client_name' => $request->client_name,
            'subject' => $request->subject,
            'project_id' => $request->project_id,
            'type' => $request->type,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'created_by' => $user->creatorId(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract created successfully',
            'data' => $contract->load(['client', 'project', 'type'])
        ], 201);
    }

    /**
     * Show single contract
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $contract = Contract::with(['client', 'project', 'type', 'comments', 'attachments', 'notes'])
            ->when($user->type == 'company', fn($q) => $q->where('created_by', $user->creatorId()))
            ->when($user->type == 'client', fn($q) => $q->where('client_name', $user->id))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $contract
        ]);
    }

    /**
     * Update contract
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user->type !== 'company') {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        $contract = Contract::where('created_by', $user->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_name' => 'sometimes|required|exists:users,id',
            'subject' => 'sometimes|required|string',
            'type' => 'sometimes|required|exists:contract_types,id',
            'value' => 'sometimes|required|numeric|min:0',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $contract->update($request->only([
            'client_name', 'subject', 'type', 'value',
            'start_date', 'end_date', 'project_id', 'description'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully',
            'data' => $contract->fresh()->load(['client', 'project', 'type'])
        ]);
    }

    /**
     * Delete contract
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($user->type !== 'company') {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        $contract = Contract::where('created_by', $user->creatorId())->findOrFail($id);
        $contract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract deleted successfully'
        ]);
    }

    /**
     * Add comment to contract
     */
    public function commentStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $comment = ContractComment::create([
            'contract_id' => $id,
            'comment' => $request->comment,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added',
            'data' => $comment
        ]);
    }

    /**
     * Upload attachment to contract
     */
    public function fileUpload(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240' // 10MB
        ]);

        $contract = Contract::findOrFail($id);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('contract_attechment', $fileName);

        $attachment = Contract_attachment::create([
            'contract_id' => $id,
            'user_id' => $request->user()->id,
            'files' => $fileName,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded',
            'data' => $attachment,
            'download_url' => route('contracts.file.download', [$id, $attachment->id]),
            'delete_url' => route('contracts.file.delete', [$id, $attachment->id]),
        ]);
    }

    /**
     * Update contract description
     */
    public function descriptionStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'contract_description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $contract = Contract::findOrFail($id);
        $contract->contract_description = $request->contract_description;
        $contract->save();

        return response()->json([
            'success' => true,
            'message' => 'Description saved'
        ]);
    }

    /**
     * Update contract status
     */
    public function statusUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Approved,Rejected'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $contract = Contract::findOrFail($id);
        $contract->status = $request->status;
        $contract->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated'
        ]);
    }
}