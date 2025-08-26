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

    // Authenticated user info
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

    // Get users based on role
    public function allUsers(Request $request)
    {
        $authUser = $request->user();
        $roleQuery = $request->query('role', null);

        if ($authUser->hasRole('Super Admin')) {
            $users = $roleQuery
                ? User::role(ucwords(strtolower($roleQuery)))->get()
                : User::all();
        } elseif ($authUser->hasRole('Admin')) {
            $users = $roleQuery
                ? User::role('User')->get() // Admin can only see Users
                : User::role('User')->get();
        } else {
            $users = User::where('id', $authUser->id)->get(); // Users see only themselves
        }

        $users = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ];
        });

        return response()->json($users);
    }

    // Delete users (Super Admin only)
    public function deleteAllUsers(Request $request)
    {
        $roleToDelete = $request->query('role', null);

        if (!$roleToDelete) {
            return response()->json(['message' => 'Role query parameter is required'], 400);
        }

        $roleToDelete = ucwords(strtolower($roleToDelete));

        if ($roleToDelete === 'Super Admin') {
            return response()->json(['message' => 'Cannot delete Super Admin users'], 403);
        }

        $users = User::role($roleToDelete)->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => "No users found with role $roleToDelete"], 404);
        }

        foreach ($users as $user) {
            $user->delete();
        }

        return response()->json(['message' => "All $roleToDelete users have been deleted"]);
    }
}
