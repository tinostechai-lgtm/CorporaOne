<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function location()
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $settings = Utility::settings();
        return view('settings.location', compact('settings'));
    }

    public function updateLocation(Request $request)
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'office_latitude' => 'nullable|numeric|between:-90,90',
                'office_longitude' => 'nullable|numeric|between:-180,180',
                'office_radius' => 'nullable|numeric|min:50|max:5000',
                'office_address' => 'nullable|string|max:500',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get the creator ID
            $creatorId = Auth::user()->creatorId();
            
            // Prepare settings
            $settings = [
                'office_latitude' => $request->office_latitude ?? null,
                'office_longitude' => $request->office_longitude ?? null,
                'office_radius' => $request->office_radius ?? 300,
                'office_address' => $request->office_address ?? null,
                'location_restriction' => $request->has('location_restriction') ? 'on' : 'off',
            ];

            // Save each setting directly using DB facade
            foreach ($settings as $key => $value) {
                $exists = DB::table('settings')
                    ->where('created_by', $creatorId)
                    ->where('name', $key)
                    ->exists();

                if ($exists) {
                    DB::table('settings')
                        ->where('created_by', $creatorId)
                        ->where('name', $key)
                        ->update(['value' => $value]);
                } else {
                    DB::table('settings')->insert([
                        'created_by' => $creatorId,
                        'name' => $key,
                        'value' => $value,
                    ]);
                }
            }

            // Clear the cache
            if (method_exists('App\Models\Utility', 'clearStaticSettingsCache')) {
                Utility::clearStaticSettingsCache();
            }

            // Log the update
            \Log::info('Location settings updated by user: ' . Auth::user()->id, $settings);

            return redirect()->route('settings.location')
                ->with('success', 'Location settings updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Location settings update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update location settings: ' . $e->getMessage())
                ->withInput();
        }
    }
}