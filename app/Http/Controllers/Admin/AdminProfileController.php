<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        // View for displaying the admin's profile information
        return view('admin.profile.show');
    }

    /**
     * Show the form for editing the authenticated user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // View for editing the admin's profile information
        return view('admin.profile.edit');
    }

    /**
     * Update the authenticated user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            // Email is typically not updated via profile form
            // 'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number'     => ['nullable', 'string', 'max:20'],
            'country'          => ['nullable', 'string', 'max:255'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'gender'           => ['nullable', 'in:male,female,other'],
            'birth_date'       => ['nullable', 'date'],
            'bio'              => ['nullable', 'string', 'max:1000'],
        ]);

        // Update user information
        $user->update($validatedData);

        return redirect()->route('admin.profile.show')->with('success', 'Profile information updated successfully!');
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validate password change request
        $request->validate([
            'current_password' => ['required', 'current_password'], // Laravel's built-in rule to check current password
            'password'         => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' checks for password_confirmation field
        ]);

        // Update the password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.profile.show')->with('success', 'Password updated successfully!');
    }
}
