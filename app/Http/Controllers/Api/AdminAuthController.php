<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * Admin Login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the admin user by email and roleID (2 for admin)
        $admin = User::where('email', $request->email)
                     ->where('roleID', 2) // roleID 2 for admin
                     ->first();

        // Check if the user exists and the password is correct
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Check if the admin email is verified
        if (!$admin->email_verified_at) {
            return response()->json([
                'error' => 'Email not verified. Please verify your email to log in.',
            ], 403);
        }

        // Generate a token for the admin
        $token = $admin->createToken('admin_token', ['admin'])->plainTextToken;

        // Return the token and admin details
        return response()->json([
            'token' => $token,
            'user' => $admin,
        ]);
    }

    /**
     * Admin Logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the user's tokens
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get Admin Details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminDetails(Request $request)
    {
        // Get the authenticated admin
        $admin = $request->user();

        // Ensure the user is an admin
        if ($admin->roleID !== 2) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Return admin details
        return response()->json([
            'user' => $admin,
        ]);
    }
}
