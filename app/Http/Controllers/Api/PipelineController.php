<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\Deal;
use App\Models\DealDiscussion;
use App\Models\DealFile;
use App\Models\ClientDeal;
use App\Models\UserDeal;
use App\Models\DealTask;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PipelineController extends Controller
{
    /**
     * List all pipelines for the authenticated user's company
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $pipelines = Pipeline::where('created_by', $creatorId)
            ->withCount('stages')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pipelines
        ]);
    }

    /**
     * Create a new pipeline
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:20|unique:pipelines,name,NULL,id,created_by,' . $creatorId
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $pipeline = Pipeline::create([
            'name' => $request->name,
            'created_by' => $creatorId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pipeline created successfully',
            'data' => $pipeline
        ], 201);
    }

    /**
     * Show single pipeline
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $pipeline = Pipeline::where('created_by', $creatorId)
            ->with(['stages' => fn($q) => $q->orderBy('order')])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pipeline
        ]);
    }

    /**
     * Update pipeline
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $pipeline = Pipeline::where('created_by', $creatorId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:20|unique:pipelines,name,' . $id . ',id,created_by,' . $creatorId
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $pipeline->name = $request->name;
        $pipeline->save();

        return response()->json([
            'success' => true,
            'message' => 'Pipeline updated successfully',
            'data' => $pipeline
        ]);
    }

    /**
     * Delete pipeline (only if no stages/deals)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $pipeline = Pipeline::where('created_by', $creatorId)->findOrFail($id);

        // Check if pipeline has stages or deals
        if ($pipeline->stages()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete pipeline with stages or deals. Remove them first.'
            ], 400);
        }

        $pipeline->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pipeline deleted successfully'
        ]);
    }
}