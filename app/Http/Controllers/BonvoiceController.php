<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Utility;
use App\Models\CallLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class BonvoiceController extends Controller
{
    /**
     * Get authentication token from Bonvoice API
     * Endpoint: /usermanagement/external-auth/
     */
    private function getBonvoiceToken()
    {
        $settings = Utility::settings();
        $apiUsername = $settings['bonvoice_api_username'] ?? null;
        $apiPassword = $settings['bonvoice_api_password'] ?? null;
        $baseUrl = $settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com';

        if (!$apiUsername || !$apiPassword) {
            Log::error('Bonvoice: Missing API credentials');
            return null;
        }

        // Check cache first
        $cacheKey = 'bonvoice_token_' . md5($apiUsername);
        if (Cache::has($cacheKey)) {
            Log::info('Bonvoice: Using cached token');
            return Cache::get($cacheKey);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]);
            
            $response = $client->post(rtrim($baseUrl, '/') . '/usermanagement/external-auth/', [
                'json' => [
                    'username' => $apiUsername,
                    'password' => $apiPassword,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            
            Log::info('Bonvoice: Token response', ['response' => $result]);
            
            if (isset($result['status']) && $result['status'] == '1') {
                $token = $result['data']['token'] ?? null;
                if ($token) {
                    Cache::put($cacheKey, $token, 82800); // 23 hours
                    Log::info('Bonvoice: Token obtained and cached successfully');
                    return $token;
                }
            }
            
            Log::error('Bonvoice: Token auth failed', ['response' => $result]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Bonvoice: Token auth error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Display Bonvoice settings page
     */
    public function settings()
    {
        if (!\Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        $allSettings = Utility::settings();
        $settings = [
            'bonvoice_api_username' => $allSettings['bonvoice_api_username'] ?? '',
            'bonvoice_api_password' => $allSettings['bonvoice_api_password'] ?? '',
            'bonvoice_base_url' => $allSettings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com',
        ];

        return view('bonvoice.settings', compact('settings'));
    }

    /**
     * Save Bonvoice settings
     */
    public function saveSettings(Request $request)
    {
        if (!\Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'bonvoice_api_username' => 'required|string',
            'bonvoice_api_password' => 'required|string',
            'bonvoice_base_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = auth()->user()->creatorId();

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_api_username', 'created_by' => $userId],
            ['value' => $request->bonvoice_api_username]
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_api_password', 'created_by' => $userId],
            ['value' => $request->bonvoice_api_password]
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_base_url', 'created_by' => $userId],
            ['value' => rtrim($request->bonvoice_base_url, '/')]
        );

        // Clear cache
        Utility::clearStaticSettingsCache();
        
        // Clear token cache
        $cacheKey = 'bonvoice_token_' . md5($request->bonvoice_api_username);
        Cache::forget($cacheKey);

        return redirect()->back()->with('success', __('Bonvoice settings saved successfully.'));
    }

    /**
     * Test connection to Bonvoice API
     */
    public function testConnection(Request $request)
    {
        try {
            $username = $request->bonvoice_api_username;
            $password = $request->bonvoice_api_password;
            $baseUrl = rtrim($request->bonvoice_base_url, '/');
            
            if (!$username || !$password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide username and password'
                ], 400);
            }
            
            Log::info('Bonvoice test connection attempt', [
                'username' => $username,
                'base_url' => $baseUrl,
                'endpoint' => $baseUrl . '/usermanagement/external-auth/'
            ]);
            
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
                'http_errors' => false,
            ]);
            
            $response = $client->post($baseUrl . '/usermanagement/external-auth/', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
            
            $statusCode = $response->getStatusCode();
            $result = json_decode($response->getBody(), true);
            
            Log::info('Bonvoice auth response', [
                'status_code' => $statusCode,
                'response' => $result
            ]);
            
            if ($statusCode == 200 && isset($result['status']) && $result['status'] == '1') {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Authentication worked.',
                    'token' => $result['data']['token'] ?? null
                ]);
            } else {
                $errorMessage = $result['message'] ?? ($result['error'] ?? 'Authentication failed');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed: ' . $errorMessage
                ], $statusCode);
            }
            
        } catch (\Exception $e) {
            Log::error('Bonvoice test connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make an auto call (Text to Speech)
     * Endpoint: /autoDialManagement/autoCallBridging/
     * autocallType: 4 for Text to Speech
     */
    public function makeTextToSpeechCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination' => 'required|string',
            'legACallerID' => 'nullable|string',
            'speechContent' => 'required|string',
            'speechLanguage' => 'nullable|string',
            'eventID' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $token = $this->getBonvoiceToken();
        $settings = Utility::settings();
        $baseUrl = $settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com';

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please check your credentials.'
            ], 401);
        }

        try {
            $callData = [
                'autocallType' => '4',
                'destination' => $request->destination,
                'legACallerID' => $request->legACallerID ?? '',
                'speechContent' => $request->speechContent,
                'speechLanguage' => $request->speechLanguage ?? 'ENGLISH',
                'legADialAttempts' => '1',
                'eventID' => $request->eventID ?? 'EVT_' . uniqid(),
            ];

            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->post(rtrim($baseUrl, '/') . '/autoDialManagement/autoCallBridging/', [
                'headers' => [
                    'Authorization' => 'Token ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $callData,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'message' => $result['responseDescription'] ?? 'Call initiated successfully',
                'data' => $result,
                'event_id' => $callData['eventID']
            ]);

        } catch (\Exception $e) {
            Log::error('Bonvoice Text to Speech call failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a Voicebot call
     * Endpoint: /autoDialManagement/autoCallBridging/
     * autocallType: 5 for Voicebot
     */
    public function makeVoicebotCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination' => 'required|string',
            'legACallerID' => 'nullable|string',
            'voicebotProvider' => 'required|string',
            'voicebotURL' => 'required|url',
            'eventID' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $token = $this->getBonvoiceToken();
        $settings = Utility::settings();
        $baseUrl = $settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com';

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please check your credentials.'
            ], 401);
        }

        try {
            $callData = [
                'autocallType' => '5',
                'destination' => $request->destination,
                'legACallerID' => $request->legACallerID ?? '',
                'eventID' => $request->eventID ?? 'EVT_' . uniqid(),
                'voicebotProvider' => $request->voicebotProvider,
                'voicebotURL' => $request->voicebotURL,
            ];

            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->post(rtrim($baseUrl, '/') . '/autoDialManagement/autoCallBridging/', [
                'headers' => [
                    'Authorization' => 'Token ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $callData,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'message' => $result['responseDescription'] ?? 'Voicebot call initiated successfully',
                'data' => $result,
                'event_id' => $callData['eventID']
            ]);

        } catch (\Exception $e) {
            Log::error('Bonvoice Voicebot call failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate voicebot call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch call logs from Bonvoice API by Event ID
     */
    public function fetchCallLogsFromBonvoice($eventId)
    {
        $token = $this->getBonvoiceToken();
        $settings = Utility::settings();
        $baseUrl = $settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com';

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please check your credentials.'
            ], 401);
        }

        try {
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->get(rtrim($baseUrl, '/') . '/get-autocall-log/' . $eventId . '/', [
                'headers' => ['Authorization' => 'Token ' . $token],
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);
            
            if (isset($result['status']) && $result['status'] == '1') {
                // Store the call log in database
                $this->storeCallLogFromApi($result);
                
                return response()->json([
                    'success' => true,
                    'data' => $result
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'No call log found for this event ID'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Bonvoice fetch call logs error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch call logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store call log from API response to database
     */
    private function storeCallLogFromApi($data)
    {
        if (!isset($data['status']) || $data['status'] != '1') {
            return;
        }
        
        $callData = $data['data']['outbound'] ?? [];
        $companyId = auth()->user()->creatorId();
        
        // Store the outbound call
        $callLog = CallLog::updateOrCreate(
            ['call_uuid' => $callData['callID'] ?? null],
            [
                'provider' => 'Bonvoice',
                'call_uuid' => $callData['callID'] ?? null,
                'called_number' => $callData['DestinationNumber'] ?? null,
                'caller_number' => $callData['SourceNumber'] ?? null,
                'source_number' => $callData['SourceNumber'] ?? null,
                'destination_number' => $callData['DestinationNumber'] ?? null,
                'display_number' => $callData['DisplayNumber'] ?? null,
                'data_source' => $callData['DataSource'] ?? 'Bonvoice',
                'call_type' => $callData['callType'] ?? null,
                'account_id' => $callData['AccountID'] ?? null,
                'leg' => $callData['Leg'] ?? null,
                'agent_status' => $callData['AgentStatus'] ?? null,
                'event_id' => $callData['eventID'] ?? null,
                'status' => $callData['Status'] ?? null,
                'direction' => $callData['Direction'] ?? null,
                'duration' => $callData['CallDuration'] ?? 0,
                'recording_url' => $callData['ResourceURL'] ?? null,
                'start_time' => $callData['StartTime'] ?? null,
                'end_time' => $callData['EndTime'] ?? null,
                'callback_params' => json_encode($callData['callBackParams'] ?? []),
                'created_by' => $companyId,
            ]
        );
        
        Log::info('Bonvoice: Stored call log from API', ['call_log_id' => $callLog->id]);
        
        // Store callbacks if present
        if (isset($data['data']['callbacks']) && is_array($data['data']['callbacks'])) {
            foreach ($data['data']['callbacks'] as $callback) {
                CallLog::updateOrCreate(
                    ['call_uuid' => $callback['callBackParentID'] ?? null],
                    [
                        'provider' => 'Bonvoice',
                        'call_uuid' => $callback['callBackParentID'] ?? null,
                        'called_number' => $callback['DestinationNumber'] ?? null,
                        'caller_number' => $callback['SourceNumber'] ?? null,
                        'source_number' => $callback['SourceNumber'] ?? null,
                        'destination_number' => $callback['DestinationNumber'] ?? null,
                        'display_number' => $callback['DisplayNumber'] ?? null,
                        'data_source' => 'Bonvoice',
                        'call_type' => $callback['callType'] ?? null,
                        'leg' => 'B',
                        'status' => $callback['Status'] ?? null,
                        'direction' => $callback['Direction'] ?? null,
                        'duration' => $callback['CallDuration'] ?? 0,
                        'recording_url' => $callback['ResourceURL'] ?? null,
                        'start_time' => $callback['StartTime'] ?? null,
                        'end_time' => $callback['EndTime'] ?? null,
                        'dtmf' => $callback['DTMF'] ?? null,
                        'callback_parent_id' => $callback['callBackParentID'] ?? null,
                        'created_by' => $companyId,
                    ]
                );
            }
        }
    }

    /**
     * Display fetch logs page
     */
    public function fetchLogsPage()
    {
        return view('bonvoice.fetch_logs');
    }

    /**
     * Handle webhook from Bonvoice for call logs
     * Maps Bonvoice fields to your database columns
     */
    public function handleWebhook(Request $request)
    {
        try {
            $callData = $request->all();

            if (empty($callData)) {
                Log::warning('Bonvoice webhook: No data received');
                return response()->json(['error' => 'No data received'], 400);
            }

            Log::info('Bonvoice webhook received', ['data' => $callData]);

            // Map Bonvoice fields to your database columns
            $callLogData = [
                'provider' => 'Bonvoice',
                'call_uuid' => $request->input('callID', ''),
                'called_number' => $request->input('DestinationNumber', ''),
                'caller_number' => $request->input('SourceNumber', ''),
                'source_number' => $request->input('SourceNumber', ''),
                'destination_number' => $request->input('DestinationNumber', ''),
                'display_number' => $request->input('DisplayNumber', ''),
                'data_source' => $request->input('DataSource', 'Bonvoice'),
                'call_type' => $request->input('callType', ''),
                'account_id' => $request->input('AccountID', ''),
                'leg' => $request->input('Leg', ''),
                'agent_status' => $request->input('AgentStatus', ''),
                'event_id' => $request->input('eventID', ''),
                'callback_parent_id' => $request->input('callBackParentID', ''),
                'callback_params' => $request->input('callBackParams', null),
                'agent_number' => $request->input('AgentNumber', ''),
                'status' => $request->input('Status', ''),
                'direction' => $request->input('Direction', ''),
                'duration' => $request->input('CallDuration', 0),
                'recording_url' => $request->input('ResourceURL', null),
                'start_time' => $request->input('StartTime', now()),
                'end_time' => $request->input('EndTime', null),
                'dtmf' => $request->input('DTMF', null),
                'transferred_number' => $request->input('TransferredNumber', ''),
                'raw_payload' => json_encode($callData),
                'created_by' => \Auth::check() ? auth()->user()->creatorId() : 1,
            ];

            // Determine call status based on callType
            $callType = $request->input('callType', '');
            if ($callType == '1') {
                $callLogData['status'] = 'answered';
            } elseif ($callType == '2') {
                $callLogData['status'] = 'completed';
            } elseif ($callType == '0') {
                $callLogData['status'] = 'initiated';
            }

            $callLog = CallLog::create($callLogData);

            Log::info('Bonvoice call log created', ['call_log_id' => $callLog->id, 'call_uuid' => $callLog->call_uuid]);

            return response()->json(['success' => true, 'message' => 'Call log processed'], 200);

        } catch (\Exception $e) {
            Log::error('Bonvoice webhook error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display call logs for the current user's company
     */
    public function callLogs()
    {
        $user = auth()->user();
        $callLogs = CallLog::where('created_by', $user->creatorId())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bonvoice.call_logs', compact('callLogs'));
    }

    /**
     * Display all call logs for admin/super admin
     */
    public function adminCallLogs(Request $request)
    {
        $user = auth()->user();
        
        $query = CallLog::orderBy('created_at', 'desc');
        
        // Super admin sees all, others see only their company
        if ($user->type != 'super admin') {
            $query->where('created_by', $user->creatorId());
        }
        
        // Filter by company (for super admin)
        if ($user->type == 'super admin' && $request->has('company_id') && !empty($request->company_id)) {
            $query->where('created_by', $request->company_id);
        }
        
        $callLogs = $query->get();
        
        // Get companies for filter dropdown (super admin only)
        $companies = [];
        if ($user->type == 'super admin') {
            $companies = User::where('type', 'company')->get();
        }
        
        // Statistics
        $totalCalls = $callLogs->count();
        $completedCalls = $callLogs->whereIn('status', ['answered', 'completed'])->count();
        $failedCalls = $callLogs->whereIn('status', ['missed', 'failed', 'unknown'])->count();
        $inProgressCalls = $callLogs->where('status', 'initiated')->count();
        
        return view('bonvoice.admin_call_logs', compact('callLogs', 'companies', 'totalCalls', 'completedCalls', 'failedCalls', 'inProgressCalls'));
    }

    /**
     * Display call details
     */
    public function callDetails($id)
    {
        $userId = auth()->user()->creatorId();
        $callLog = CallLog::where('id', $id)
            ->where('created_by', $userId)
            ->first();

        if (!$callLog) {
            return redirect()->back()->with('error', 'Call log not found.');
        }

        return view('bonvoice.call_details', compact('callLog'));
    }

    /**
     * Display reports
     */
    public function reports()
    {
        $userId = auth()->user()->creatorId();

        $totalCalls = CallLog::where('created_by', $userId)->count();
        $completedCalls = CallLog::where('created_by', $userId)
            ->whereIn('status', ['answered', 'completed'])
            ->count();
        $failedCalls = CallLog::where('created_by', $userId)
            ->whereIn('status', ['missed', 'failed', 'unknown'])
            ->count();
        $inProgressCalls = CallLog::where('created_by', $userId)
            ->where('status', 'initiated')
            ->count();

        $reports = CallLog::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        return view('bonvoice.reports', compact('totalCalls', 'completedCalls', 'failedCalls', 'inProgressCalls', 'reports'));
    }

    /**
     * Display IVR configuration page
     */
    public function ivrConfig()
    {
        $allSettings = Utility::settings();
        $settings = [
            'bonvoice_ivr_enabled' => $allSettings['bonvoice_ivr_enabled'] ?? 'off',
            'bonvoice_ivr_flow_id' => $allSettings['bonvoice_ivr_flow_id'] ?? '',
            'bonvoice_default_greeting' => $allSettings['bonvoice_default_greeting'] ?? '',
            'bonvoice_timeout' => $allSettings['bonvoice_timeout'] ?? 30,
            'bonvoice_max_attempts' => $allSettings['bonvoice_max_attempts'] ?? 3,
            'bonvoice_invalid_option' => $allSettings['bonvoice_invalid_option'] ?? 'Invalid option. Please try again.',
            'bonvoice_webhook_url' => $allSettings['bonvoice_webhook_url'] ?? '',
        ];

        return view('bonvoice.ivr_config', compact('settings'));
    }

    /**
     * Save IVR configuration
     */
    public function saveIvrConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bonvoice_ivr_enabled' => 'nullable|string',
            'bonvoice_ivr_flow_id' => 'nullable|string',
            'bonvoice_default_greeting' => 'nullable|string',
            'bonvoice_timeout' => 'nullable|integer|min:5|max:120',
            'bonvoice_max_attempts' => 'nullable|integer|min:1|max:5',
            'bonvoice_invalid_option' => 'nullable|string',
            'bonvoice_webhook_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = auth()->user()->creatorId();

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_ivr_enabled', 'created_by' => $userId],
            ['value' => $request->bonvoice_ivr_enabled ?? 'off']
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_ivr_flow_id', 'created_by' => $userId],
            ['value' => $request->bonvoice_ivr_flow_id ?? '']
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_default_greeting', 'created_by' => $userId],
            ['value' => $request->bonvoice_default_greeting ?? '']
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_timeout', 'created_by' => $userId],
            ['value' => $request->bonvoice_timeout ?? 30]
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_max_attempts', 'created_by' => $userId],
            ['value' => $request->bonvoice_max_attempts ?? 3]
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_invalid_option', 'created_by' => $userId],
            ['value' => $request->bonvoice_invalid_option ?? 'Invalid option. Please try again.']
        );

        DB::table('settings')->updateOrInsert(
            ['name' => 'bonvoice_webhook_url', 'created_by' => $userId],
            ['value' => $request->bonvoice_webhook_url ?? '']
        );

        Utility::clearStaticSettingsCache();

        return redirect()->back()->with('success', 'IVR configuration saved successfully.');
    }

    /**
     * Test IVR configuration
     */
    public function testIvr(Request $request)
    {
        try {
            $settings = Utility::settings();
            
            return response()->json([
                'success' => true,
                'message' => 'IVR configuration test',
                'data' => [
                    'ivr_enabled' => $settings['bonvoice_ivr_enabled'] ?? 'off',
                    'ivr_flow_id' => $settings['bonvoice_ivr_flow_id'] ?? '',
                    'greeting' => $settings['bonvoice_default_greeting'] ?? '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simple test endpoint
     */
    public function testSimple()
    {
        return response()->json([
            'success' => true,
            'message' => 'Bonvoice test endpoint is working!',
            'time' => now()->toDateTimeString()
        ]);
    }

    /**
     * Test POST endpoint
     */
    public function testPost(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'POST request received successfully',
            'data' => $request->all()
        ]);
    }

    /**
     * Debug API endpoint
     */
    public function debugApi(Request $request)
    {
        try {
            $username = $request->bonvoice_api_username;
            $password = $request->bonvoice_api_password;
            $baseUrl = rtrim($request->bonvoice_base_url, '/');
            
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]);
            
            $response = $client->post($baseUrl . '/usermanagement/external-auth/', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
            
            return response()->json([
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Make a call (wrapper method)
     */
    public function makeCall(Request $request)
    {
        return $this->makeTextToSpeechCall($request);
    }

    /**
     * Get call record by UUID (public endpoint for webhook)
     */
    public function getCallRecord($callId)
    {
        $callLog = CallLog::where('call_uuid', $callId)->first();
        
        if ($callLog) {
            return response()->json([
                'success' => true,
                'data' => $callLog
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Call record not found'
        ], 404);
    }

    /**
     * Get call log by Event ID
     */
    public function getCallLogByEventId($eventId)
    {
        $callLog = CallLog::where('event_id', $eventId)->first();
        
        if ($callLog) {
            return response()->json([
                'success' => true,
                'data' => $callLog
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Call log not found'
        ], 404);
    }

    /**
     * Get route list
     */
    public function getRouteList()
    {
        $token = $this->getBonvoiceToken();
        $settings = Utility::settings();
        $baseUrl = $settings['bonvoice_base_url'] ?? 'https://backend.pbx.bonvoice.com';

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please check your credentials.'
            ], 401);
        }

        try {
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->get(rtrim($baseUrl, '/') . '/external-route-list/', [
                'headers' => [
                    'Authorization' => 'Token ' . $token,
                ],
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Bonvoice get route list failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get route list: ' . $e->getMessage()
            ], 500);
        }
    }
}