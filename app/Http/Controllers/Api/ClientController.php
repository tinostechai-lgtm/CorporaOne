<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomField;
use App\Models\Estimation;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    /**
     * List all clients for the authenticated user's company
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $clients = User::where('created_by', $creatorId)
            ->where('type', 'client')
            ->select('id', 'name', 'email', 'job_title', 'is_enable_login', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    /**
     * Create a new client
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'job_title' => 'nullable|string',
            'password' => 'nullable|min:6',
            'enable_login' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Check client limit
        $creator = User::find($creatorId);
        $plan = Plan::find($creator->plan);

        $currentClients = User::where('created_by', $creatorId)->where('type', 'client')->count();

        if ($plan->max_clients != -1 && $currentClients >= $plan->max_clients) {
            return response()->json([
                'success' => false,
                'message' => 'Client limit reached. Please upgrade your plan.'
            ], 403);
        }

        $enableLogin = $request->enable_login ?? ($request->filled('password') ? 1 : 0);

        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'job_title' => $request->job_title,
            'password' => $request->filled('password') ? Hash::make($request->password) : null,
            'type' => 'client',
            'lang' => Utility::getValByName('default_language') ?: 'en',
            'created_by' => $creatorId,
            'email_verified_at' => now(), // Auto verify
            'is_enable_login' => $enableLogin,
        ]);

        // Assign client role
        $role = Role::findByName('client');
        $client->assignRole($role);

        // Save custom fields if any (optional)
        CustomField::saveData($client, $request->customField ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data' => $client
        ], 201);
    }

    /**
     * Show single client with summary (estimations, contracts)
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $client = User::where('created_by', $creatorId)
            ->where('type', 'client')
            ->where('id', $id)
            ->with(['clientEstimations', 'clientContracts'])
            ->firstOrFail();

        // Estimation summary
        $estimations = $client->clientEstimations;
        $estimation_summary = [
            'total' => $estimations->count(),
            'this_month' => $estimations->whereMonth('issue_date', now()->month)->count(),
            'this_week' => $estimations->whereBetween('issue_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'last_30_days' => $estimations->where('issue_date', '>', now()->subDays(30))->count(),
        ];

        // Contract summary
        $contracts = $client->clientContracts;
        $contract_summary = [
            'total' => $contracts->count(),
            'this_month' => $contracts->whereMonth('start_date', now()->month)->count(),
            'this_week' => $contracts->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'last_30_days' => $contracts->where('start_date', '>', now()->subDays(30))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client,
                'estimation_summary' => $estimation_summary,
                'contract_summary' => $contract_summary,
                'estimations' => $estimations,
                'contracts' => $contracts,
            ]
        ]);
    }

    /**
     * Update client
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $client = User::where('created_by', $creatorId)
            ->where('type', 'client')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'job_title' => 'nullable|string',
            'password' => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'job_title']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $client->update($updateData);

        CustomField::saveData($client, $request->customField ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            'data' => $client
        ]);
    }

    /**
     * Delete client (only if no estimations)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $client = User::where('created_by', $creatorId)
            ->where('type', 'client')
            ->findOrFail($id);

        // Check if client has estimations
        if (Estimation::where('client_id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete client with assigned estimations.'
            ], 400);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully'
        ]);
    }

    /**
     * Reset client password (admin only)
     */
    public function resetPassword(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $client = User::where('created_by', $creatorId)
            ->where('type', 'client')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $client->password = Hash::make($request->password);
        $client->save();

        return response()->json([
            'success' => true,
            'message' => 'Client password reset successfully'
        ]);
    }
}