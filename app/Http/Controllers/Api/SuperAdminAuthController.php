<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SuperAdminAuthController extends Controller
{
        //when register give role to the user

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $superAdmin = User::where('email', $request->email)        ->where('roleID', 3)
        ->first();

        if (!$superAdmin || !Hash::check($request->password, $superAdmin->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $superAdmin->createToken('superAdmin_token', ['admin'])->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);



    }














    
}
