<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=true),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        try {
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
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while logging in');
        }
    }
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="profile_picture", type="string", format="url")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        try {
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
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while registering');
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
                'profile_picture' => 'nullable|string|url',
            ]);

            $user = $request->user();
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'profile_picture' => $request->profile_picture,
            ]);
            return response()->json(['s' => true, 'message' => 'Profile updated successfully', 'user' => $user]);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while updating the profile');
        }
    }

    public function uploadProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|max:2048' // 2MB max size
            ]);

            if ($request->file('profile_picture')->getSize() > 2048 * 1024) {
                return response()->json([
                    's' => false,
                    'message' => 'Image file is too large. Maximum size is 2MB.'
                ], 400);
            }

            $user = User::find($request->user()->id);

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                's' => true,
                'message' => 'Profile picture uploaded successfully',
                'url' => $url
            ]);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while uploading the profile picture');
        }
    }

    public function logout(Request $request)
    {
        try {
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
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while logging out');
        }
    }
}
