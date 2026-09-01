<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyPolicyController extends Controller
{
    /**
     * Display a listing of company policies
     */
    public function index(Request $request)
    {
        $policies = CompanyPolicy::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $policies
        ], 200);
    }

    /**
     * Store a newly created company policy
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $policy = new CompanyPolicy();
        $policy->fill($request->all());
        $policy->created_by = $request->user()->creatorId();
        $policy->save();

        return response()->json([
            'success' => true,
            'message' => 'Company policy created successfully',
            'data' => $policy
        ], 201);
    }

    /**
     * Display the specified company policy
     */
    public function show(Request $request, $id)
    {
        $policy = CompanyPolicy::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $policy
        ]);
    }

    /**
     * Update the specified company policy
     */
    public function update(Request $request, $id)
    {
        $policy = CompanyPolicy::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $policy->fill($request->only(['title', 'description']));
        $policy->save();

        return response()->json([
            'success' => true,
            'message' => 'Company policy updated successfully',
            'data' => $policy
        ]);
    }

    /**
     * Remove the specified company policy
     */
    public function destroy(Request $request, $id)
    {
        $policy = CompanyPolicy::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $policy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company policy deleted successfully'
        ]);
    }
}
