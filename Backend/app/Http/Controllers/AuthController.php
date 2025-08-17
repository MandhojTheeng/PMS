<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:Super Admin,Admin,User',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        $user->assignRole($fields['role']);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // Get authenticated user
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }

    // Get all users (role-aware)
    public function allUsers()
    {
        $authUser = auth()->user();

        if ($authUser->hasRole('Super Admin')) {
            $users = User::all();
        } elseif ($authUser->hasRole('Admin')) {
            $users = User::role('User')->get();
        } else {
            $users = User::where('id', $authUser->id)->get();
        }

        $users = $users->map(function ($user) {
            return [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ];
        });

        return response()->json($users);
    }

    // Delete users (role-aware)
    public function deleteAllUsers(Request $request)
    {
        $roleToDelete = $request->query('role', 'all');

        if ($roleToDelete === 'User') {
            User::role('User')->delete();
            $message = "All Users have been deleted.";
        } elseif ($roleToDelete === 'Admin') {
            User::role('Admin')->delete();
            $message = "All Admins have been deleted.";
        } else {
            $superAdmins = User::role('Super Admin')->pluck('id');
            User::whereNotIn('id', $superAdmins)->delete();
            $message = "All non-Super Admin users have been deleted.";
        }

        return response()->json([
            'message' => $message
        ]);
    }
}
