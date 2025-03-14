<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminAuthController extends Controller
{
    /**
     * Super Admin Login
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

        // Find the super admin user by email and roleID (3 for super admin)
        $superAdmin = User::where('email', $request->email)
                          ->where('roleID', 3) // roleID 3 for super admin
                          ->first();

        // Check if the user exists and the password is correct
        if (!$superAdmin || !Hash::check($request->password, $superAdmin->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Check if the super admin email is verified
        if (!$superAdmin->email_verified_at) {
            return response()->json([
                'error' => 'Email not verified. Please verify your email to log in.',
            ], 403);
        }

        // Generate a token for the super admin
        $token = $superAdmin->createToken('superAdmin_token', ['superadmin'])->plainTextToken;

        // Return the token
        return response()->json([
            'token' => $token,
            'user' => $superAdmin,
        ]);
    }

    /**
     * Super Admin Logout
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
     * Get Super Admin Details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuperAdminDetails(Request $request)
    {
        // Get the authenticated super admin
        $superAdmin = $request->user();

        // Ensure the user is a super admin
        if ($superAdmin->roleID !== 3) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Return super admin details
        return response()->json([
            'user' => $superAdmin,
        ]);
    }
}
