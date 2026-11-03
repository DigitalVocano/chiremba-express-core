<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        // Apply middleware for authentication and permissions to the respective methods.
        $this->middleware('auth');
        $this->middleware('permission:create-admins')->only(['index', 'create', 'store']);
        $this->middleware('permission:edit-admins')->only(['edit', 'update']);
        $this->middleware('permission:delete-admins')->only(['destroy']);
    }

    public function index()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'patient') // Exclude patients
                  ->where('name', '!=', 'super-admin'); // Exclude super-admins
        })->with('roles')->get();

        return view('admin.admins.index', ['users' => $users]);
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'super-admin')->get(); // Exclude super-admin
        return view('admin.admins.create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'role' => 'required|exists:roles,name|not_in:super-admin',  // Validate the role and forbid creating super-admins
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.admins.create')->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name, // The new setNameAttribute mutator handles this
            'email' => $request->email,
            'password' => $request->password, // The setPasswordAttribute mutator handles hashing
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user created successfully.');
    }

    public function edit(User $user)
    {
        // Prevent editing super-admins at all
        if ($user->hasRole('super-admin')) {
            abort(403, 'Super Admins cannot be edited from this interface.');
        }

        $roles = Role::where('name', '!=', 'super-admin')->get();
        return view('admin.admins.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent updating super-admins at all
        if ($user->hasRole('super-admin')) {
            abort(403, 'Super Admins cannot be updated from this interface.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|confirmed|min:8',
            'role' => 'required|exists:roles,name|not_in:super-admin',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.admins.edit', $user->id)->withErrors($validator)->withInput();
        }

        // Use fill() and the model's mutators for cleaner updates
        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->password = $request->password; // The mutator will hash this
        }
        $user->save();

        // The validation rule 'not_in:super-admin' already prevents assigning this role.
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting super-admins at all
        if ($user->hasRole('super-admin')) {
            abort(403, 'Super Admins cannot be deleted.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.admins.index')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Admin user deleted successfully.');
    }
}