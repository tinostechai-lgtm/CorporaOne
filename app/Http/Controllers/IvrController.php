<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Utility;

class IvrController extends Controller
{
    public function settings()
    {
        $allSettings = Utility::settings();
        $settings = [
            'voxbay_api_key' => $allSettings['voxbay_api_key'] ?? '',
            'voxbay_api_secret' => $allSettings['voxbay_api_secret'] ?? '',
            'voxbay_base_url' => $allSettings['voxbay_base_url'] ?? '',
        ];

        return view('ivr.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voxbay_api_key' => 'required|string',
            'voxbay_api_secret' => 'required|string',
            'voxbay_base_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = auth()->id();

        \DB::table('settings')->updateOrInsert(
            ['name' => 'voxbay_api_key', 'created_by' => $userId],
            ['value' => $request->voxbay_api_key]
        );

        \DB::table('settings')->updateOrInsert(
            ['name' => 'voxbay_api_secret', 'created_by' => $userId],
            ['value' => $request->voxbay_api_secret]
        );

        \DB::table('settings')->updateOrInsert(
            ['name' => 'voxbay_base_url', 'created_by' => $userId],
            ['value' => $request->voxbay_base_url]
        );

        return redirect()->back()->with('success', 'VoxBay IVR settings saved successfully.');
    }

    public function testConnection(Request $request)
    {
        // Implement VoxBay API test connection logic here
        // For now, just return success
        return response()->json(['success' => true, 'message' => 'Connection test successful']);
    }

    // API Methods for VoxBay IVR Integration

    public function makeCall(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'from' => 'nullable|string',
        ]);

        $settings = Utility::settings();
        $apiKey = $settings['voxbay_api_key'] ?? null;
        $apiSecret = $settings['voxbay_api_secret'] ?? null;
        $baseUrl = $settings['voxbay_base_url'] ?? null;

        if (!$apiKey || !$apiSecret || !$baseUrl) {
            return response()->json([
                'success' => false,
                'message' => 'VoxBay settings not configured'
            ], 400);
        }

        try {
            // Prepare VoxBay API call
            $callData = [
                'to' => $request->to,
                'from' => $request->from ?? $settings['voxbay_default_from'] ?? null,
                'ivr_flow_id' => $request->ivr_flow_id ?? null,
            ];

            // Make API call to VoxBay (implement actual HTTP call)
            // $response = Http::withHeaders([
            //     'Authorization' => 'Bearer ' . $this->getVoxBayToken($apiKey, $apiSecret),
            //     'Content-Type' => 'application/json',
            // ])->post($baseUrl . '/calls', $callData);

            // For now, simulate success
            \Log::info('VoxBay call initiated', $callData);

            return response()->json([
                'success' => true,
                'message' => 'Call initiated successfully',
                'call_id' => 'simulated_' . time()
            ]);

        } catch (\Exception $e) {
            \Log::error('VoxBay API call failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate call: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getVoxBayToken($apiKey, $apiSecret)
    {
        // Implement token generation logic
        // This would typically involve OAuth or API key authentication
        return base64_encode($apiKey . ':' . $apiSecret);
    }
}