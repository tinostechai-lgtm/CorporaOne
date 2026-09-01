<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssetController extends Controller
{
    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        $assets = Asset::where('created_by', $request->user()->creatorId())
            ->with('employee')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assets
        ], 200);
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'purchase_date' => 'nullable|date',
            'supported_date' => 'nullable|date',
            'amount' => 'nullable|numeric',
            'description' => 'nullable|string',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $asset = new Asset();
        $asset->fill($request->all());
        $asset->created_by = $request->user()->creatorId();
        $asset->save();

        return response()->json([
            'success' => true,
            'message' => 'Asset created successfully',
            'data' => $asset->load('employee')
        ], 201);
    }

    /**
     * Display the specified asset
     */
    public function show(Request $request, $id)
    {
        $asset = Asset::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with('employee')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $asset
        ]);
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'purchase_date' => 'nullable|date',
            'supported_date' => 'nullable|date',
            'amount' => 'nullable|numeric',
            'description' => 'nullable|string',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $asset->fill($request->only(['name', 'purchase_date', 'supported_date', 'amount', 'description', 'employee_id']));
        $asset->save();

        return response()->json([
            'success' => true,
            'message' => 'Asset updated successfully',
            'data' => $asset->load('employee')
        ]);
    }

    /**
     * Remove the specified asset
     */
    public function destroy(Request $request, $id)
    {
        $asset = Asset::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset deleted successfully'
        ]);
    }
}
