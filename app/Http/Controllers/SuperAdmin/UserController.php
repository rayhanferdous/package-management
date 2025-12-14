<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        // Only Super Admin can access these methods

        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Only Super Admin can access this section.');
        }
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Search users
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        $roles = Role::all();

        return Inertia::render('SuperAdmin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Super Admin')->get();

        return Inertia::render('SuperAdmin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::where('name', '!=', 'Super Admin')->get();

        return Inertia::render('SuperAdmin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent editing Super Admin unless you are that Super Admin
        if ($user->hasRole('Super Admin') && $user->id !== auth()->id()) {
            abort(403, 'Cannot edit another Super Admin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sync roles (remove all and assign new)
        $user->syncRoles([$validated['role']]);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Promote user to admin.
     */
    public function promoteToAdmin(User $user)
    {
        // Check if user already has a role
        if ($user->hasRole(['Admin', 'Super Admin'])) {
            return back()->with('error', 'User already has admin privileges.');
        }

        $user->assignRole('Admin');

        return back()->with('success', 'User promoted to Admin successfully.');
    }

    /**
     * Demote admin to regular user.
     */
    public function demoteToUser(User $user)
    {
        // Cannot demote Super Admin
        if ($user->hasRole('Super Admin')) {
            abort(403, 'Cannot demote Super Admin.');
        }

        $user->removeRole('Admin');
        $user->assignRole('User');

        return back()->with('success', 'User demoted to regular user successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Cannot delete yourself or Super Admin
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot delete your own account.');
        }

        if ($user->hasRole('Super Admin')) {
            abort(403, 'Cannot delete Super Admin.');
        }

        $user->delete();

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        // Cannot deactivate yourself
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot deactivate your own account.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully.");
    }
}
