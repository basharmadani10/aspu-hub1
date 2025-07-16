<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{

    public function show()
    {
      
        return view('admin.profile.show');
    }


    public function edit()
    {
  
        return view('admin.profile.edit');
    }


    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'first_name'       => ['required', 'string', 'max:255'],
            'last_name'        => ['required', 'string', 'max:255'],
            'phone_number'     => ['nullable', 'string', 'max:20'],
            'country'          => ['nullable', 'string', 'max:255'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'gender'           => ['nullable', 'in:male,female,other'],
            'birth_date'       => ['nullable', 'date'],
            'bio'              => ['nullable', 'string', 'max:1000'],
        ]);


        $user->update($validatedData);

        return redirect()->route('admin.profile.show')->with('success', 'Profile information updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

     
        $request->validate([
            'current_password' => ['required', 'current_password'], // Laravel's built-in rule to check current password
            'password'         => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' checks for password_confirmation field
        ]);

  
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.profile.show')->with('success', 'Password updated successfully!');
    }
}
