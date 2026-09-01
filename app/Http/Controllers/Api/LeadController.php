<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\UserLead;
use App\Models\User;
use App\Models\Source;
use App\Models\ProductService;
use App\Models\Label;
use App\Models\LeadDiscussion;
use App\Models\LeadFile;
use App\Models\LeadCall;
use App\Models\LeadEmail;
use App\Models\LeadActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    /**
     * List all leads for the authenticated user's company
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $leads = Lead::where('created_by', $creatorId)
            ->with(['pipeline', 'stage', 'users'])
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leads
        ]);
    }

    /**
     * Create a new lead
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:leads,email',
            'phone' => 'nullable|string',
            'subject' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
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
            return response()->json([
                'success' => false,
                'message' => 'No pipeline found. Please create a pipeline first.'
            ], 400);
        }

        $stage = LeadStage::where('pipeline_id', $pipeline->id)->orderBy('order')->first();
        if (!$stage) {
            return response()->json([
                'success' => false,
                'message' => 'No stage found in pipeline.'
            ], 400);
        }

        $lead = Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'user_id' => $request->user_id ?? $user->id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'created_by' => $creatorId,
            'date' => now()->format('Y-m-d'),
        ]);

        // Assign lead to users
        $assignedUsers = [$user->id];
        if ($request->user_id && $request->user_id != $user->id) {
            $assignedUsers[] = $request->user_id;
        }

        foreach ($assignedUsers as $assignedUserId) {
            UserLead::create([
                'user_id' => $assignedUserId,
                'lead_id' => $lead->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'data' => $lead->load(['pipeline', 'stage', 'users'])
        ], 201);
    }

    /**
     * Show single lead
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $lead = Lead::where('created_by', $creatorId)
            ->with(['pipeline', 'stage', 'users', 'discussions', 'files', 'calls', 'emails'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $lead
        ]);
    }

    /**
     * Update lead
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $lead = Lead::where('created_by', $creatorId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:leads,email,' . $id,
            'phone' => 'nullable|string',
            'subject' => 'sometimes|required|string',
            'stage_id' => 'sometimes|required|exists:lead_stages,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $lead->update($request->only(['name', 'email', 'phone', 'subject', 'stage_id', 'user_id']));

        if ($request->has('user_id')) {
            // Sync assigned users
            UserLead::where('lead_id', $lead->id)->delete();
            $assignedUsers = array_unique([$user->id, $request->user_id]);
            foreach ($assignedUsers as $assignedUserId) {
                UserLead::create([
                    'user_id' => $assignedUserId,
                    'lead_id' => $lead->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead->fresh()->load(['pipeline', 'stage', 'users'])
        ]);
    }

    /**
     * Delete lead
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $lead = Lead::where('created_by', $creatorId)->findOrFail($id);

        // Delete related data
        LeadDiscussion::where('lead_id', $id)->delete();
        LeadFile::where('lead_id', $id)->delete();
        UserLead::where('lead_id', $id)->delete();
        LeadActivityLog::where('lead_id', $id)->delete();

        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully'
        ]);
    }

    /**
     * Get stages for a pipeline (for dynamic dropdowns)
     */
    public function stages(Request $request)
    {
        $pipelineId = $request->query('pipeline_id');
        if (!$pipelineId) {
            return response()->json(['success' => false, 'message' => 'pipeline_id required'], 400);
        }

        $stages = LeadStage::where('pipeline_id', $pipelineId)
            ->orderBy('order')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $stages
        ]);
    }

    /**
     * Add discussion/comment to lead
     */
    public function discussionStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $discussion = LeadDiscussion::create([
            'lead_id' => $id,
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
     * Upload file to lead
     */
    public function fileUpload(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240' // 10MB max
        ]);

        $lead = Lead::findOrFail($id);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs('lead_files', time() . '_' . $fileName);

        $leadFile = LeadFile::create([
            'lead_id' => $id,
            'file_name' => $fileName,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded',
            'data' => $leadFile
        ]);
    }

    /**
     * Handle Meta (Facebook) webhook for leads
     */
    public function metaWebhook(Request $request)
    {
        // Get settings from database
        $settings = \App\Models\Utility::settings();

        // Verify webhook (Meta sends a hub_verify_token for verification)
        if ($request->has('hub_mode') && $request->hub_mode === 'subscribe') {
            // Verify token - get from settings
            $verifyToken = $settings['meta_verify_token'] ?? null;
            if ($verifyToken && $request->hub_verify_token === $verifyToken) {
                return response($request->hub_challenge, 200);
            }
            return response('Forbidden', 403);
        }

        $data = $request->all();

        // Log the webhook data for debugging
        \Log::info('Meta Webhook Received', $data);

        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'leadgen') {
                            $leadData = $change['value'];

                            // Extract lead information
                            $leadgenId = $leadData['leadgen_id'];
                            $formId = $leadData['form_id'];
                            $pageId = $leadData['page_id'];

                            // Get detailed lead info from Meta API
                            $leadDetails = $this->getMetaLeadDetails($leadgenId);

                            if ($leadDetails) {
                                $this->createLeadFromMeta($leadDetails);
                            }
                        }
                    }
                }
            }
        }

        return response('OK', 200);
    }

    /**
     * Get lead details from Meta API
     */
    private function getMetaLeadDetails($leadgenId)
    {
        $settings = \App\Models\Utility::settings();
        $accessToken = $settings['meta_access_token'] ?? '';

        if (!$accessToken) {
            \Log::error('Meta access token not configured');
            return null;
        }

        $url = "https://graph.facebook.com/v18.0/{$leadgenId}?access_token={$accessToken}";

        try {
            $response = \Http::get($url);
            if ($response->successful()) {
                return $response->json();
            } else {
                \Log::error('Meta API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch Meta lead details: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Create lead from Meta webhook data
     */
    private function createLeadFromMeta($leadDetails)
    {
        $fieldData = collect($leadDetails['field_data'])->keyBy('name');

        $name = $fieldData->get('full_name')['values'][0] ?? 'Unknown';
        $email = $fieldData->get('email')['values'][0] ?? null;
        $phone = $fieldData->get('phone_number')['values'][0] ?? null;
        $metaLeadgenId = $leadDetails['id'] ?? null;

        // Get settings for default creator
        $settings = \App\Models\Utility::settings();
        $defaultCreatorId = $settings['meta_default_creator_id'] ?? 1; // Default to 1 if not set

        // Get default pipeline and stage
        $pipeline = Pipeline::where('created_by', $defaultCreatorId)->first();
        if (!$pipeline) {
            \Log::error('No pipeline found for Meta lead creation', ['creator_id' => $defaultCreatorId]);
            return;
        }

        $stage = LeadStage::where('pipeline_id', $pipeline->id)->orderBy('order')->first();
        if (!$stage) {
            \Log::error('No stage found in pipeline for Meta lead creation', ['pipeline_id' => $pipeline->id]);
            return;
        }

        // Check if lead already exists by meta_leadgen_id or email
        $existingLead = null;
        if ($metaLeadgenId) {
            $existingLead = Lead::where('meta_leadgen_id', $metaLeadgenId)->first();
        }
        if (!$existingLead && $email) {
            $existingLead = Lead::where('email', $email)->first();
        }
        
        if ($existingLead) {
            \Log::info('Lead already exists', ['meta_leadgen_id' => $metaLeadgenId, 'email' => $email]);
            return $existingLead;
        }

        $lead = Lead::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => 'Lead from Meta Ads',
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'created_by' => $defaultCreatorId,
            'date' => now()->format('Y-m-d'),
            'notes' => 'Created via Meta webhook',
            'meta_leadgen_id' => $metaLeadgenId,
            'lead_source' => 'meta',
        ]);

        // Assign to default user or admin
        $defaultUser = User::where('type', 'company')->first();
        if ($defaultUser) {
            UserLead::create([
                'user_id' => $defaultUser->id,
                'lead_id' => $lead->id,
            ]);
        }

        \Log::info('Lead created from Meta webhook', ['lead_id' => $lead->id, 'email' => $email, 'meta_leadgen_id' => $metaLeadgenId]);
        
        return $lead;
    }
}