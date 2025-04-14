<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:student,admin,superadmin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $roleMapping = ['student' => 1, 'admin' => 2, 'superadmin' => 3];
        $verificationCode = random_int(1000, 9999);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roleID' => $roleMapping[$request->role],
            'email_verification_code' => $verificationCode,
        ]);

      
        Mail::raw("Your email verification code is: $verificationCode", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Email Verification Code');
        });

        return response()->json([
            'message' => 'User registered successfully. Check your email for the verification code.',

        ], 201);
        return response()->json(['messag'=>"user created succefully",$user], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|integer|digits:4',
        ]);

        $user = User::where('email', $request->email)
                    ->where('email_verification_code', $request->code)
                    ->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid email or verification code.'], 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null; // Clear the code after verification
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
