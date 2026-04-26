<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    use APIResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $heading = 'Roles & Permissions';
        $roles = Role::with('permissions')->get();
        
        if ($request->wantsJson()) {
            return $this->success($roles);
        }
        
        return view('backend.role.index', compact('roles', 'heading'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $heading = 'Add Role';
        $permissions = Permission::all()->groupBy(function($item) {
            // Group by the first word of the permission name (e.g. "view", "create", "manage")
            return explode(' ', $item->name)[1] ?? 'other';
        });
        return view('backend.role.create', compact('permissions', 'heading'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        activity()->causedBy(Auth::user())->performedOn($role)->log('Role created');

        return $this->success($role, 'Role created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $heading = 'Edit Role';
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy(function($item) {
            return explode(' ', $item->name)[1] ?? 'other';
        });
        return view('backend.role.edit', compact('role', 'permissions', 'heading'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'nullable|array'
        ]);

        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        activity()->causedBy(Auth::user())->performedOn($role)->log('Role updated');

        return $this->success($role, 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->name === 'Admin') {
            return $this->error('Admin role cannot be deleted', 403);
        }

        $role->delete();
        
        activity()->causedBy(Auth::user())->performedOn($role)->log('Role deleted');

        return $this->success([], 'Role deleted successfully');
    }
}
