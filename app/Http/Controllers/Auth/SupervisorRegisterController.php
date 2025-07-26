<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RequestJob;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Str; 
use App\Mail\SupervisorCredentialsMail; 
use Illuminate\Support\Facades\Log;

class SupervisorRegisterController extends Controller
{

    public function showRegistrationForm()
    {
    
        return view('auth.supervisor-register');
    }

    public function register(Request $request)
    {
   
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:request_jobs,email', // البريد الإلكتروني يجب أن يكون فريدًا في جدول طلبات الوظائف
            'cv'         => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

    
        $cvPath = $request->file('cv')->store('supervisor_cvs', 'public');


        $requestJob = RequestJob::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'doc_url'    => $cvPath,
        ]);



        $email = $validated['email'];
        $generatedPassword = Str::random(10); 


        $user = User::where('email', $email)->first();

        if ($user) {
          
            Log::info('User with email ' . $email . ' already exists during supervisor registration.');

      
            if ($user->roleID != 2) { 
                $user->roleID = 2;
                $user->is_approved = true; 
                $user->save();
                Log::info('User role updated to Supervisor for ' . $email);
            }

            Mail::to($email)->send(new SupervisorCredentialsMail($user, null));
            Log::info('Supervisor status update email sent to existing user: ' . $email);

        } else {
            Log::info('No existing user found for ' . $email . '. Creating new supervisor user.');
            try {
                $newUser = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name'  => $validated['last_name'],
                    'email'      => $email,
                    'password'   => Hash::make($generatedPassword), 
                    'roleID'     => 2, 
                    'is_approved'=> true, 
                ]);


                Mail::to($newUser->email)->send(new SupervisorCredentialsMail($newUser, $generatedPassword));
                Log::info('New supervisor user created and credentials email sent to: ' . $newUser->email);

            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error while creating supervisor user for ' . $email . ': ' . $e->getMessage());
            }
        }
        return redirect()->route('register.thankyou');
    }
}

