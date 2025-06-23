<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSemester;
use App\Models\Specialization;
use App\Models\Subscribe_Communities; // Assuming this is your model for community subscriptions
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

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roleID' => $roleMapping[$request->role],
                'email_verification_code' => $verificationCode,
                'email_verified_at' => null,
                'email_code_sent_at' => now(),
            ]);

            // Create user semester
            UserSemester::create([
                'userID' => $user->id,
                'SpecializationID' => $specialization_id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(), // Default semester duration
                'semester_number' => 1, // Default to first semester
                'semester_hours' => 0,  // Or a default value
                'year_degree' => 0,     // Or a default value
            ]);

            // Subscribe user to communities based on specialization
            // This logic applies if the user is a student (roleID == 1)
            if ($user->roleID == 1) {
                // These lines were in your original code, kept them in case they are used elsewhere or intended for future use.
                // $userSubjects = $user->userSubjects;
                // $com = $user->Subscribe_Communities;

                // Logic for community subscription:
                // Specialization 1: Community 1
                // Specialization 2: Community 1 & 2
                // Specialization 3: Community 1 & 3
                // Specialization 4: Community 1 & 4

                // All relevant specializations (1, 2, 3, 4) subscribe to Community 1
                if (in_array($specialization_id, [1, 2, 3, 4])) {
                    Subscribe_Communities::create([
                        'community_id' => 1,
                        'user_id' => $user->id
                    ]);
                }

                // Additional community subscriptions based on specialization
                if ($specialization_id == 2) {
                    Subscribe_Communities::create([
                        'community_id' => 2,
                        'user_id' => $user->id
                    ]);
                } elseif ($specialization_id == 3) {
                    Subscribe_Communities::create([
                        'community_id' => 3,
                        'user_id' => $user->id
                    ]);
                } elseif ($specialization_id == 4) {
                    Subscribe_Communities::create([
                        'community_id' => 4,
                        'user_id' => $user->id
                    ]);
                }
            }

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

        // Check if the code was sent and if it has expired (24 hours)
        if ($user->email_code_sent_at && now()->diffInHours(Carbon::parse($user->email_code_sent_at)) > 24) {
            return response()->json(['error' => 'Verification code has expired. Please request a new one.'], 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null; // Clear the code after successful verification
        $user->email_code_sent_at = null; // Clear the sent time
        $user->save();

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'roleID']), // Adjusted to return more user info
            'token' => $token,
        ]);
    }
}
