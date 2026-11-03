<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('manage-roles');
        $roles = Role::where('name', '!=', 'super-admin')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $this->authorize('manage-roles');
        if ($role->name === 'super-admin') {
            abort(403, 'The Super Admin role cannot be edited.');
        }
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('-', $permission->name)[0]; // Group by prefix (e.g., 'manage', 'create')
        });
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('manage-roles');
        if ($role->name === 'super-admin') {
            abort(403, 'The Super Admin role cannot be edited.');
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role permissions updated successfully.');
    }
}