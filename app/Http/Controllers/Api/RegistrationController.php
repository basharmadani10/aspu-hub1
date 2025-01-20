<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\RegisterMail; // Import the Mailable class
use Illuminate\Support\Facades\Mail; // Import the Mail facade

class RegistrationController extends Controller
{
    /**
     * Handle user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string',
            'country' => 'nullable|string',
            'current_location' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other',
            'birth_date' => 'nullable|date',
            'bio' => 'nullable|string',
            'role' => 'required|string|in:student,admin,superadmin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Map role to roleID
        $roleMapping = [
            'student' => 1,
            'admin' => 2,
            'superadmin' => 3,
        ];

        // Generate a unique email verification token
        $emailVerificationToken = \Illuminate\Support\Str::random(60);

        // Create the user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'country' => $request->country,
            'current_location' => $request->current_location,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'bio' => $request->bio,
            'roleID' => $roleMapping[$request->role], // Assign roleID based on role
            'email_verification_token' => $emailVerificationToken, // Set the token
        ]);

        // Log the user creation for debugging
        \Log::info('User created:', ['user' => $user, 'token' => $emailVerificationToken]);

        // Send the verification email
        Mail::to($user->email)->send(new RegisterMail($user, $emailVerificationToken));

        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => $user,
        ], 201);
    }
}
