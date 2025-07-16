<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSemester;
use App\Models\Specialization; // Make sure this is imported
use App\Models\Subscribe_Communities;
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
            'specialization' => 'required|string|exists:specializations,name',
            'BirthDate' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'current_location' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'bio' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $roleMapping = ['student' => 1, 'admin' => 2, 'superadmin' => 3];

        try {
            DB::beginTransaction();

            $specializationNameLower = strtolower($request->specialization);
            $specialization = Specialization::where('name', $specializationNameLower)->first();

            if (!$specialization) {
                throw new \Exception('The specified specialization does not exist in the system.');
            }

            $verificationCode = random_int(1000, 9999);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roleID' => $roleMapping[$request->role],
                'email_verification_code' => $verificationCode,
                'email_verified_at' => null,
                'email_code_sent_at' => now(),
                'phone_number' => $request->phone_number,
                'country' => $request->country,
                'current_location' => $request->current_location,
                'gender' => $request->gender,
                'birth_date' => $request->BirthDate,
                'bio' => $request->bio,
                'number_of_completed_hours' => 0,
                'initial_subjects_configured' => false, 
            ]);

            UserSemester::create([
                'userID' => $user->id,
                'SpecializationID' => $specialization->SpecializationID,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'semester_number' => 1, 
                'semester_hours' => 0,
                'year_degree' => 0,
                'has_registered_subjects' => false, 
            ]);

            if ($user->roleID == 1) { 
                if (in_array($specialization->SpecializationID, [1, 2, 3, 4])) {
                    Subscribe_Communities::create([
                        'community_id' => 1, 
                        'user_id' => $user->id
                    ]);
                }
                if ($specialization->SpecializationID == 2) {
                    Subscribe_Communities::create(['community_id' => 2, 'user_id' => $user->id]);
                } elseif ($specialization->SpecializationID == 3) {
                    Subscribe_Communities::create(['community_id' => 3, 'user_id' => $user->id]);
                } elseif ($specialization->SpecializationID == 4) {
                    Subscribe_Communities::create(['community_id' => 4, 'user_id' => $user->id]);
                }
            }

            Mail::raw("Your email verification code is: $verificationCode\n\nThis code will expire in 24 hours.", function ($message) use ($request) {
                $message->to($request->email)->subject('Email Verification Code');
            });

            DB::commit();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully. Please check your email for the verification code. You can now submit your previously completed subjects.',
                'token' => $token,
                'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'roleID', 'initial_subjects_configured']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json([
                'error' => 'Registration failed. Please try again later.',
                'details' => $e->getMessage()
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

        if ($user->email_code_sent_at && now()->diffInHours(Carbon::parse($user->email_code_sent_at)) > 24) {
            return response()->json(['error' => 'Verification code has expired. Please request a new one.'], 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->email_code_sent_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'roleID', 'initial_subjects_configured']),
            'token' => $token,
        ]);
    }
}

  