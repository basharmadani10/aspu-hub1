<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    //when register give role to the user

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $student = User::where('email', $request->email)
            ->where('roleID', 1) // student in database (roles)
            ->first();

        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Check if the email is verified
        if (!$student->email_verified_at) {
            return response()->json(['error' => 'Email not verified. Please verify your email address.'], 403);
        }

        $token = $student->createToken('student_token', ['student'])->plainTextToken;

        return response()->json(['token' => $token]);
    }



    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function resetPassword(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);

    }




}
