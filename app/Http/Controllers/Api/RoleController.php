<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

class RoleController extends Controller
{
    /**
     * List all roles for the authenticated user's company
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $roles = Role::where('created_by', $creatorId)->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Show a single role with its permissions
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $role = Role::where('created_by', $creatorId)->with('permissions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    /**
     * Create a new role
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100|unique:roles,name,NULL,id,created_by,' . $creatorId,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'created_by' => $creatorId,
            'guard_name' => 'web', // or 'sanctum' if you want API-specific roles
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Role successfully created.',
            'data' => $role->load('permissions')
        ], 201);
    }

    /**
     * Update an existing role
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $role = Role::where('created_by', $creatorId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100|unique:roles,name,' . $role->id . ',id,created_by,' . $creatorId,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $role->update([
            'name' => $request->name,
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Role successfully updated.',
            'data' => $role->load('permissions')
        ]);
    }

    /**
     * Delete a role
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $role = Role::where('created_by', $creatorId)->findOrFail($id);

        // Optional: prevent deleting critical roles
        if (in_array($role->name, ['company', 'super admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system role.'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role successfully deleted.'
        ]);
    }

    /**
     * Get all available permissions for role creation/edit
     */
    public function permissions(Request $request)
    {
        $user = $request->user();

        if ($user->type == 'super admin') {
            $permissions = Permission::all()->pluck('name', 'id');
        } else {
            $permissions = new Collection();
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
            $permissions = $permissions->pluck('name', 'id');
        }

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }
}