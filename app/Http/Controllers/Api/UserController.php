<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role as SpatieRole;

class UserController extends Controller
{
    /**
     * Display a listing of users (for company or super admin)
     */
    public function index(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->type == 'super admin') {
            $users = User::where('created_by', $authUser->creatorId())
                ->where('type', 'company')
                ->with('currentPlan')
                ->get();
        } else {
            $users = User::where('created_by', $authUser->creatorId())
                ->where('type', '!=', 'client')
                ->with('currentPlan')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $authUser = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|min:6|required_if:enable_login,true',
            'enable_login' => 'boolean',
            'role' => $authUser->type !== 'super admin' ? 'required|string' : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->filled('password') ? Hash::make($request->password) : null;
        $user->is_enable_login = $request->enable_login ?? 0;
        $user->lang = Utility::getValByName('default_language') ?: 'en';

        if ($authUser->type == 'super admin') {
            // Super admin creates a company user
            $user->type = 'company';
            $user->created_by = $authUser->creatorId();
            $user->plan = Plan::first()->id ?? 1;

            $role_r = SpatieRole::findByName('company');
        } else {
            // Company admin creates an employee/user
            $roleName = $request->input('role');

            $role_r = SpatieRole::where('name', $roleName)
                               ->where('created_by', $authUser->creatorId())
                               ->first();

            if (!$role_r) {
                return response()->json([
                    'success' => false,
                    'message' => "Role '{$roleName}' not found or not accessible."
                ], 422);
            }

            $user->type = $role_r->name;
            $user->created_by = $authUser->creatorId();
        }

        $user->save();

        // Assign the role
        $user->assignRole($role_r);

        // Automatically verify email so the user can login via API immediately
        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->fresh()
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, $id)
    {
        $user = User::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $authUser = $request->user();

        $user = User::where('created_by', $authUser->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:120',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('role') && $authUser->type !== 'super admin') {
            $role_r = SpatieRole::where('name', $request->role)
                               ->where('created_by', $authUser->creatorId())
                               ->firstOrFail();

            $user->syncRoles([$role_r]); // Remove old roles, assign new one
            $user->type = $role_r->name;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        $user = User::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}