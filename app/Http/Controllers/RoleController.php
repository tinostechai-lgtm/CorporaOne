<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Auth;

class RoleController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage role'))
        {
            $roles = Role::where('created_by', '=', \Auth::user()->creatorId())->get();
            return view('role.index')->with('roles', $roles);
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create role'))
        {
            $user = \Auth::user();
            if($user->type == 'super admin' || $user->type == 'company')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role)
                {
                    $permissions = $permissions->merge($role->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            return view('role.create', ['permissions' => $permissions]);
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create role')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:100|unique:roles,name,NULL,id,created_by,' . \Auth::user()->creatorId(),
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', $validator->errors()->first())
                    ->withInput();
            }

            // Create the role
            $role = new Role();
            $role->name = $request->name;
            $role->guard_name = 'web';
            $role->created_by = \Auth::user()->creatorId();
            $role->save();

            // Assign permissions if any are selected
            if ($request->has('permissions') && !empty($request->permissions)) {
                // Get permission IDs
                $permissionIds = $request->permissions;
                
                // If it's a string (comma-separated), convert to array
                if (is_string($permissionIds)) {
                    $permissionIds = explode(',', $permissionIds);
                }
                
                // Remove duplicates and empty values
                $permissionIds = array_unique(array_filter($permissionIds));
                
                // Convert to integers
                $permissionIds = array_map('intval', $permissionIds);
                
                // Get existing permissions
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                
                // Sync permissions (this accepts both IDs and Permission objects)
                $role->syncPermissions($permissions);
                
                $message = 'Role "' . $role->name . '" created with ' . $permissions->count() . ' permissions.';
            } else {
                $message = 'Role "' . $role->name . '" created with no permissions. You can edit it later to add permissions.';
            }

            // Log the activity
            \Log::info('Role created: ' . $role->name . ' by user: ' . \Auth::user()->id);

            return redirect()->route('roles.index')
                ->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function edit(Role $role)
    {
        if(\Auth::user()->can('edit role'))
        {
            $user = \Auth::user();
            if($user->type == 'super admin')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role1)
                {
                    $permissions = $permissions->merge($role1->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            return view('role.edit', compact('role', 'permissions'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function update(Request $request, Role $role)
    {
        if(\Auth::user()->can('edit role'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:100|unique:roles,name,' . $role['id'] . ',id,created_by,' . \Auth::user()->creatorId(),
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first())->withInput();
            }

            $input = $request->except(['permissions']);
            $role->fill($input)->save();

            // Sync permissions if any are selected
            if ($request->has('permissions') && !empty($request->permissions)) {
                // Get permission IDs
                $permissionIds = $request->permissions;
                
                // If it's a string (comma-separated), convert to array
                if (is_string($permissionIds)) {
                    $permissionIds = explode(',', $permissionIds);
                }
                
                // Remove duplicates and empty values
                $permissionIds = array_unique(array_filter($permissionIds));
                
                // Convert to integers
                $permissionIds = array_map('intval', $permissionIds);
                
                // Get existing permissions
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                
                // Sync permissions
                $role->syncPermissions($permissions);
                
                $message = 'Role "' . $role->name . '" updated with ' . $permissions->count() . ' permissions.';
            } else {
                $role->syncPermissions([]);
                $message = 'Role "' . $role->name . '" updated with no permissions.';
            }

            return redirect()->route('roles.index')->with('success', $message);
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function destroy(Role $role)
    {
        if(\Auth::user()->can('delete role'))
        {
            $role->delete();
            return redirect()->route('roles.index')->with('success', __('Role successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Assign role to a user
     */
    public function assignRole(Request $request)
    {
        if(\Auth::user()->can('manage role'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'user_id' => 'required|exists:users,id',
                    'role_id' => 'required|exists:roles,id'
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $user = \App\Models\User::find($request->user_id);
            $role = Role::find($request->role_id);

            if ($role->created_by != \Auth::user()->creatorId()) {
                return redirect()->back()->with('error', 'You cannot assign this role.');
            }

            $user->assignRole($role);
            $user->syncPermissions($role->permissions);

            return redirect()->back()->with('success', 'Role assigned successfully.');
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Get permissions for a specific role (AJAX)
     */
    public function getRolePermissions(Request $request)
    {
        if(\Auth::user()->can('manage role'))
        {
            $role = Role::find($request->role_id);
            if($role) {
                $permissions = $role->permissions->pluck('id')->toArray();
                return response()->json([
                    'success' => true,
                    'permissions' => $permissions
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ]);
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ]);
        }
    }
}