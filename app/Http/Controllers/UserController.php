<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Admin-only list of accounts with their roles.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('users.index', [
            'users' => User::with('role')->orderBy('name')->paginate(50),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    /**
     * Admin-only role change.
     */
    public function assignRole(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        // Stop an admin from locking everyone out by demoting themselves.
        if ($user->is($request->user()) && (int) $data['role_id'] !== $user->role_id) {
            return back()->withErrors(['role_id' => "You can't change your own role."]);
        }

        $user->forceFill(['role_id' => $data['role_id']])->save();

        return back()->with('success', "{$user->name}'s role was updated.");
    }
}
