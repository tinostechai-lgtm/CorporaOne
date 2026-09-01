<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\ClientDeal;
use App\Models\UserDeal;
use App\Models\User;
use App\Models\DealDiscussion;
use App\Models\DealFile;
use App\Models\DealTask;
use App\Models\DealCall;
use App\Models\DealEmail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DealController extends Controller
{
    /**
     * List all deals for the authenticated user's company
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        // Get deals the user is assigned to (via UserDeal or ClientDeal)
        $dealIds = UserDeal::where('user_id', $user->id)->pluck('deal_id')
            ->merge(ClientDeal::where('client_id', $user->id)->pluck('deal_id'));

        $deals = Deal::whereIn('id', $dealIds)
            ->orWhere('created_by', $creatorId)
            ->with(['pipeline', 'stage', 'clients', 'users', 'tasks'])
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deals
        ]);
    }

    /**
     * Create a new deal
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string',
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Get default pipeline and stage
        $pipeline = Pipeline::where('created_by', $creatorId)->first();
        if (!$pipeline) {
            return response()->json(['success' => false, 'message' => 'No pipeline found.'], 400);
        }

        $stage = Stage::where('pipeline_id', $pipeline->id)->orderBy('order')->first();
        if (!$stage) {
            return response()->json(['success' => false, 'message' => 'No stage found in pipeline.'], 400);
        }

        $deal = Deal::create([
            'name' => $request->name,
            'price' => $request->price ?? 0,
            'phone' => $request->phone,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'status' => 'Active',
            'created_by' => $creatorId,
        ]);

        // Assign clients
        foreach ($request->client_ids as $clientId) {
            ClientDeal::create([
                'deal_id' => $deal->id,
                'client_id' => $clientId,
            ]);
        }

        // Assign current user + company owner
        $assignedUsers = [$user->id];
        if ($user->type != 'company') {
            $assignedUsers[] = $user->ownerId();
        }

        foreach ($assignedUsers as $assignedUserId) {
            UserDeal::create([
                'user_id' => $assignedUserId,
                'deal_id' => $deal->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
            'data' => $deal->load(['pipeline', 'stage', 'clients', 'users'])
        ], 201);
    }

    /**
     * Show single deal
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $deal = Deal::where('created_by', $creatorId)
            ->orWhereHas('users', fn($q) => $q->where('user_id', $user->id))
            ->orWhereHas('clients', fn($q) => $q->where('client_id', $user->id))
            ->with(['pipeline', 'stage', 'clients', 'users', 'tasks', 'discussions', 'files', 'calls', 'emails'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $deal
        ]);
    }

    /**
     * Update deal
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $deal = Deal::where('created_by', $creatorId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string',
            'stage_id' => 'sometimes|required|exists:stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $deal->update($request->only(['name', 'price', 'phone', 'stage_id']));

        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
            'data' => $deal->fresh()->load(['pipeline', 'stage', 'clients', 'users'])
        ]);
    }

    /**
     * Delete deal
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $deal = Deal::where('created_by', $creatorId)->findOrFail($id);

        // Delete related data
        DealDiscussion::where('deal_id', $id)->delete();
        DealFile::where('deal_id', $id)->delete();
        ClientDeal::where('deal_id', $id)->delete();
        UserDeal::where('deal_id', $id)->delete();
        DealTask::where('deal_id', $id)->delete();
        ActivityLog::where('deal_id', $id)->delete();

        $deal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully'
        ]);
    }

    /**
     * Add discussion/comment to deal
     */
    public function discussionStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $discussion = DealDiscussion::create([
            'deal_id' => $id,
            'comment' => $request->comment,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added',
            'data' => $discussion
        ]);
    }

    /**
     * Upload file to deal
     */
    public function fileUpload(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240' // 10MB max
        ]);

        $deal = Deal::findOrFail($id);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs('deal_files', time() . '_' . $fileName);

        $dealFile = DealFile::create([
            'deal_id' => $id,
            'file_name' => $fileName,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded',
            'data' => $dealFile
        ]);
    }

    /**
     * Create task for deal
     */
    public function taskStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $task = DealTask::create([
            'deal_id' => $id,
            'name' => $request->name,
            'date' => $request->date,
            'time' => $request->time,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created',
            'data' => $task
        ]);
    }
}