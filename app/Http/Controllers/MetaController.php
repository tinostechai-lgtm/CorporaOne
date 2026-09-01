<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MetaController extends Controller
{
    /**
     * Display Meta settings page
     */
    public function settings()
    {
        $settings = Utility::settings();

        return view('meta.settings', compact('settings'));
    }

    /**
     * Save Meta settings
     */
    public function saveSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meta_app_id' => 'required|string',
            'meta_app_secret' => 'required|string',
            'meta_access_token' => 'required|string',
            'meta_verify_token' => 'required|string',
            'meta_page_id' => 'required|string',
            'meta_webhook_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = auth()->user()->creatorId();

        $settings = [
            'meta_app_id' => $request->meta_app_id,
            'meta_app_secret' => $request->meta_app_secret,
            'meta_access_token' => $request->meta_access_token,
            'meta_verify_token' => $request->meta_verify_token,
            'meta_page_id' => $request->meta_page_id,
            'meta_webhook_url' => $request->meta_webhook_url ?: url('/api/leads/meta-webhook'),
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['name' => $key, 'created_by' => $userId],
                ['value' => $value]
            );
        }

        Utility::clearStaticSettingsCache();

        return redirect()->back()->with('success', __('Meta settings saved successfully.'));
    }

    /**
     * Test Meta connection
     */
    /**
 * Test Meta connection
 */
public function testConnection(Request $request)
{
    try {
        // Get credentials from request or from saved settings
        $accessToken = $request->meta_access_token;
        $pageId = $request->meta_page_id;
        
        // If not provided in request, try to get from saved settings
        if (!$accessToken || !$pageId) {
            $settings = Utility::settings();
            $accessToken = $settings['meta_access_token'] ?? '';
            $pageId = $settings['meta_page_id'] ?? '';
        }
        
        // Log the test attempt
        Log::info('Meta test connection attempt', [
            'page_id' => $pageId,
            'has_token' => !empty($accessToken)
        ]);
        
        if (!$accessToken || !$pageId) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide Access Token and Page ID in the form or save settings first.'
            ]);
        }
        
        // Test API call to get page info
        $url = "https://graph.facebook.com/v18.0/{$pageId}?fields=name,id,access_token&access_token={$accessToken}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        Log::info('Meta test connection response', [
            'http_code' => $httpCode,
            'response' => substr($response, 0, 500)
        ]);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'data' => $data
            ]);
        } else {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Invalid access token or page ID';
            
            // Check for specific error types
            if (strpos($errorMessage, 'invalid') !== false) {
                $errorMessage = 'Invalid access token. Please generate a new Page Access Token.';
            } elseif (strpos($errorMessage, 'expired') !== false) {
                $errorMessage = 'Access token has expired. Please generate a new one.';
            } elseif (strpos($errorMessage, 'permission') !== false) {
                $errorMessage = 'Insufficient permissions. Make sure your token has pages_read_engagement permission.';
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $errorMessage,
                'http_code' => $httpCode
            ]);
        }
        
    } catch (\Exception $e) {
        Log::error('Meta test connection error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Connection test failed: ' . $e->getMessage()
        ]);
    }
}
    /**
     * Display Meta leads page
     */
    public function leads()
    {
        $creatorId = Auth::user()->creatorId();
        
        // Get Meta leads (leads with lead_source = 'meta')
        $leads = Lead::where('created_by', $creatorId)
            ->where('lead_source', 'meta')
            ->with(['pipeline', 'stage', 'users'])
            ->orderBy('id', 'desc')
            ->get();

        $totalLeads = $leads->count();
        
        // Format dates for display
        foreach ($leads as $lead) {
            $lead->date = Auth::user()->dateFormat($lead->created_at);
        }
        
        // Get leads by stage
        $leadsByStage = Lead::where('created_by', $creatorId)
            ->where('lead_source', 'meta')
            ->with('stage')
            ->get()
            ->groupBy('stage_id')
            ->map(function($group) {
                return $group->count();
            });

        return view('meta.leads', compact('leads', 'totalLeads', 'leadsByStage'));
    }

    /**
     * Get Meta lead details via AJAX
     */
    public function getLeadDetails(Request $request)
    {
        try {
            $leadId = $request->lead_id;
            $creatorId = Auth::user()->creatorId();

            $lead = Lead::where('created_by', $creatorId)
                ->where('id', $leadId)
                ->with(['pipeline', 'stage', 'users'])
                ->first();

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ]);
            }

            // Format date
            $lead->date = Auth::user()->dateFormat($lead->created_at);
            
            // Get notes
            $lead->notes = $lead->description ?? 'No notes available';

            return response()->json([
                'success' => true,
                'data' => $lead
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get lead details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading lead details: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync leads from Meta API
     */
    public function syncLeads(Request $request)
    {
        try {
            $settings = Utility::settings();
            $accessToken = $settings['meta_access_token'] ?? '';
            $pageId = $settings['meta_page_id'] ?? '';

            if (!$accessToken || !$pageId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meta settings are not configured properly.'
                ]);
            }

            // Get leadgen forms from Meta API
            $url = "https://graph.facebook.com/v18.0/{$pageId}/leadgen_forms?access_token={$accessToken}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                
                // Process forms and get leads from each form
                $forms = $data['data'] ?? [];
                $syncedCount = 0;
                
                foreach ($forms as $form) {
                    $formId = $form['id'];
                    
                    // Get leads for this form
                    $leadsUrl = "https://graph.facebook.com/v18.0/{$formId}/leads?access_token={$accessToken}";
                    
                    $ch2 = curl_init();
                    curl_setopt($ch2, CURLOPT_URL, $leadsUrl);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                    
                    $leadsResponse = curl_exec($ch2);
                    $leadsHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                    curl_close($ch2);
                    
                    if ($leadsHttpCode === 200) {
                        $leadsData = json_decode($leadsResponse, true);
                        $syncedCount += count($leadsData['data'] ?? []);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => "Synced {$syncedCount} leads successfully!",
                    'data' => $data
                ]);
            } else {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error']['message'] ?? 'Failed to fetch leads';
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch leads: ' . $errorMessage
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Meta sync leads error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle Meta webhook for lead generation
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Verify webhook for Meta
            if ($request->has('hub_verify_token')) {
                $verifyToken = $request->hub_verify_token;
                $settings = Utility::settings();
                $expectedToken = $settings['meta_verify_token'] ?? '';
                
                if ($verifyToken === $expectedToken) {
                    return response($request->hub_challenge, 200);
                }
                return response('Invalid verify token', 403);
            }
            
            // Process lead data
            $payload = $request->all();
            Log::info('Meta webhook received', ['payload' => $payload]);
            
            // Process leads from the webhook
            if (isset($payload['entry'])) {
                foreach ($payload['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            if ($change['field'] === 'leadgen') {
                                $leadId = $change['value']['leadgen_id'] ?? null;
                                $formId = $change['value']['form_id'] ?? null;
                                
                                if ($leadId) {
                                    $this->processLead($leadId, $formId);
                                }
                            }
                        }
                    }
                }
            }
            
            return response()->json(['success' => true], 200);
            
        } catch (\Exception $e) {
            Log::error('Meta webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Process a single lead from Meta
     */
    private function processLead($leadId, $formId)
    {
        try {
            $settings = Utility::settings();
            $accessToken = $settings['meta_access_token'] ?? '';
            
            if (!$accessToken) {
                return false;
            }
            
            // Get lead details from Meta
            $url = "https://graph.facebook.com/v18.0/{$leadId}?access_token={$accessToken}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $leadData = json_decode($response, true);
                
                // Extract lead information
                $leadInfo = [];
                if (isset($leadData['field_data'])) {
                    foreach ($leadData['field_data'] as $field) {
                        $leadInfo[$field['name']] = $field['values'][0] ?? '';
                    }
                }
                
                // Create lead in your system
                $creatorId = auth()->user() ? auth()->user()->creatorId() : 1;
                
                // Get default pipeline and stage
                $defaultPipeline = Pipeline::where('created_by', $creatorId)->first();
                $defaultStage = LeadStage::where('created_by', $creatorId)->first();
                
                $lead = Lead::create([
                    'name' => $leadInfo['full_name'] ?? $leadInfo['name'] ?? 'Meta Lead',
                    'email' => $leadInfo['email'] ?? null,
                    'phone' => $leadInfo['phone_number'] ?? null,
                    'subject' => 'Lead from Meta Form',
                    'lead_source' => 'meta',
                    'meta_leadgen_id' => $leadId,
                    'meta_form_id' => $formId,
                    'pipeline_id' => $defaultPipeline ? $defaultPipeline->id : null,
                    'stage_id' => $defaultStage ? $defaultStage->id : null,
                    'created_by' => $creatorId,
                ]);
                
                Log::info('Meta lead created', ['lead_id' => $lead->id, 'meta_lead_id' => $leadId]);
                
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Process lead error: ' . $e->getMessage());
            return false;
        }
    }
}