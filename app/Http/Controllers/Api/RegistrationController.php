<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $validatedData = $request->validate([
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
            'role' => 'required|string|in:admin,student,superadmin',
        ]);


        $roleMapping = [
            'superadmin' => 1,
            'admin' => 2,
            'student' => 3,
        ];

        $validatedData['roleID'] = $roleMapping[$validatedData['role']];
        unset($validatedData['role']);


        $validatedData['password'] = Hash::make($validatedData['password']);


        $user = User::create($validatedData);


        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }
}
