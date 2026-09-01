<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IvrSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class IvrController extends Controller
{
    /**
     * Display a listing of IVR settings
     */
    public function index(Request $request)
    {
        $settings = IvrSetting::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $settings
        ], 200);
    }

    /**
     * Store a newly created IVR setting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255|unique:ivr_settings,key',
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $setting = new IvrSetting();
        $setting->fill($request->all());
        $setting->created_by = $request->user()->creatorId();
        $setting->save();

        return response()->json([
            'success' => true,
            'message' => 'IVR setting created successfully',
            'data' => $setting
        ], 201);
    }

    /**
     * Display the specified IVR setting
     */
    public function show(Request $request, $id)
    {
        $setting = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    /**
     * Update the specified IVR setting
     */
    public function update(Request $request, $id)
    {
        $setting = IvrSetting::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|string|max:255|unique:ivr_settings,key,' . $id,
            'value' => 'sometimes|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $setting->fill($request->only(['key', 'value', 'description']));
        $setting->save();

        return response()->json([
            'success' => true,
            'message' => 'IVR setting updated successfully',
            'data' => $setting
        ]);
    }

    /**
     * Remove the specified IVR setting
     */
    public function destroy(Request $request, $id)
    {
        $setting = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'IVR setting deleted successfully'
        ]);
    }

    /**
     * Import VoxBay setup
     */
    public function importVoxBaySetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voxbay_api_key' => 'required|string',
            'voxbay_api_secret' => 'nullable|string',
            'voxbay_base_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $createdBy = $request->user()->creatorId();

        // Import VoxBay settings
        $settings = [
            [
                'key' => 'voxbay_api_key',
                'value' => $request->voxbay_api_key,
                'description' => 'VoxBay API Key',
                'created_by' => $createdBy
            ],
            [
                'key' => 'voxbay_api_secret',
                'value' => $request->voxbay_api_secret ?? '',
                'description' => 'VoxBay API Secret',
                'created_by' => $createdBy
            ],
            [
                'key' => 'voxbay_base_url',
                'value' => $request->voxbay_base_url ?? 'https://api.voxbay.com',
                'description' => 'VoxBay Base URL',
                'created_by' => $createdBy
            ],
        ];

        foreach ($settings as $settingData) {
            IvrSetting::updateOrCreate(
                ['key' => $settingData['key'], 'created_by' => $createdBy],
                $settingData
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'VoxBay setup imported successfully',
            'data' => IvrSetting::where('created_by', $createdBy)->whereIn('key', ['voxbay_api_key', 'voxbay_api_secret', 'voxbay_base_url'])->get()
        ], 200);
    }

    /**
     * Test VoxBay connection
     */
    public function testVoxBayConnection(Request $request)
    {
        $apiKey = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_api_key')
            ->first();

        $baseUrl = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_base_url')
            ->first();

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'VoxBay settings not configured'
            ], 400);
        }

        try {
            // Assuming VoxBay has a test endpoint, adjust as needed
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey->value,
                'Accept' => 'application/json',
            ])->get($baseUrl->value . '/test');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'VoxBay connection successful',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'VoxBay connection failed',
                    'data' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing VoxBay connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a call using VoxBay IVR
     */
    public function makeCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string',
            'from' => 'nullable|string',
            'ivr_flow_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $apiKey = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_api_key')
            ->first();

        $baseUrl = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_base_url')
            ->first();

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'VoxBay settings not configured'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey->value,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($baseUrl->value . '/calls', [
                'to' => $request->to,
                'from' => $request->from,
                'ivr_flow_id' => $request->ivr_flow_id,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Call initiated successfully',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initiate call',
                    'data' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error initiating call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hang up a call
     */
    public function hangupCall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $apiKey = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_api_key')
            ->first();

        $baseUrl = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_base_url')
            ->first();

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'VoxBay settings not configured'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey->value,
                'Accept' => 'application/json',
            ])->delete($baseUrl->value . '/calls/' . $request->call_id);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Call hung up successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to hang up call',
                    'data' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error hanging up call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get call history
     */
    public function callHistory(Request $request)
    {
        $apiKey = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_api_key')
            ->first();

        $baseUrl = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_base_url')
            ->first();

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'VoxBay settings not configured'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey->value,
                'Accept' => 'application/json',
            ])->get($baseUrl->value . '/calls', $request->query());

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve call history',
                    'data' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving call history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get call details
     */
    public function callDetails(Request $request, $callId)
    {
        $apiKey = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_api_key')
            ->first();

        $baseUrl = IvrSetting::where('created_by', $request->user()->creatorId())
            ->where('key', 'voxbay_base_url')
            ->first();

        if (!$apiKey || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'VoxBay settings not configured'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey->value,
                'Accept' => 'application/json',
            ])->get($baseUrl->value . '/calls/' . $callId);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve call details',
                    'data' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving call details: ' . $e->getMessage()
            ], 500);
        }
    }
}
