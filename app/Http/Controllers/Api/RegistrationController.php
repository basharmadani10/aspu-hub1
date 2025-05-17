<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSemester;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|string|in:student,admin,superadmin',
            'specialization' => 'required|string|in:global information technology,software,networking,ai',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Role and specialization mappings
        $roleMapping = ['student' => 1, 'admin' => 2, 'superadmin' => 3];
        $specializationMapping = [
            'global information technology' => 1,
            'software' => 2,
            'networking' => 3,
            'ai' => 4,
        ];

        try {
            DB::beginTransaction();

            $specialization_id = $specializationMapping[strtolower($request->specialization)];
            $verificationCode = random_int(1000, 9999);

            // Verify specialization exists
            if (!Specialization::where('SpecializationID', $specialization_id)->exists()) {
                throw new \Exception('The specified specialization does not exist in the system');
            }

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roleID' => $roleMapping[$request->role],
                'email_verification_code' => $verificationCode,
                'verification_code_sent_at' => now(), // Track when code was sent
            ]);

            // Create user semester
            UserSemester::create([
                'userID' => $user->id,
                'SpecializationID' => $specialization_id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'semester_number' => 1,
                'semester_hours' => 0,
                'year_degree' => 0,
            ]);

            // Send verification email
            Mail::raw("Your email verification code is: $verificationCode\n\nThis code will expire in 24 hours.", function ($message) use ($request) {
                $message->to($request->email)->subject('Email Verification Code');
            });

            DB::commit();

            return response()->json([
                'message' => 'User registered successfully. Please check your email for the verification code.',
            
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
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

        // Check if verification code is expired (24 hours)
        if ($user->verification_code_sent_at->diffInHours(now()) > 1) {
            return response()->json(['error' => 'Verification code has expired. Please request a new one.'], 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->verification_code_sent_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
