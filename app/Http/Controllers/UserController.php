<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['s' => false, 'message' => 'Invalid credentials'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['s' => false, 'message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['s' => true, 'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
        ], 'token' => $token], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|string|url',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_picture' => $request->profile_picture,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['s' => true, 'message' => 'User created successfully', 'user' => $user, 'token' => $token], 201);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'profile_picture' => $request->profile_picture,
        ]);
        return response()->json(['s' => true, 'message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function uploadProfilePicture(Request $request)
    {
        $user = User::find($request->user()->id);
        $user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->save();
        return response()->json(['s' => true, 'message' => 'Profile picture uploaded successfully', 'url' => $user->profile_picture]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['s' => false, 'message' => 'No token provided'], 401);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['s' => false, 'message' => 'Invalid token'], 401);
        }

        $user->currentAccessToken()->delete();
        return response()->json([
            's' => true,
            'message' => 'Logout successful',
            'user_id' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
            ]
        ], 200);
    }
}
