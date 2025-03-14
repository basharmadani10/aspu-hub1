<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $student = User::where('email', $request->email)
            ->where('roleID', 1)
            ->first();

        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$student->email_verified_at) {
            return response()->json([
                'error' => 'Email not verified. Please verify your email to log in.',
            ], 403);
        }

        $token = $student->createToken('student_token', ['student'])->plainTextToken;

        return response()->json(['token' => $token, 'user' => $student]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
