<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Verify the user's email address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $token = $request->query('token');


        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid verification token.'], 404);
        }


        $user->email_verified_at = now();
        $user->email_verification_token = null; // Clear the token
        $user->save();

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
